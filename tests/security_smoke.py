"""HTTP regressions in an isolated copy. No access to the real MySQL database."""
import http.cookiejar
import json
import os
from pathlib import Path
import re
import shutil
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


class FixtureDatabase:
    # Use PHP's PDO SQLite so the test has no Python database dependencies.
    def __init__(self, php, path):
        self.php, self.path, self.rows = php, path, []

    def execute(self, sql, params=(), script=False):
        code = ("$q=json_decode(stream_get_contents(STDIN),true);"
                "$p=new PDO('sqlite:'.$argv[1],null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);"
                "if($q['script']){$p->exec($q['sql']);echo '[]';}"
                "else{$s=$p->prepare($q['sql']);$s->execute($q['params']);echo json_encode($s->fetchAll(PDO::FETCH_NUM));}")
        output = subprocess.check_output([self.php, '-r', code, str(self.path)],
                                         input=json.dumps(dict(sql=sql, params=params, script=script)).encode())
        self.rows = json.loads(output)
        return self

    def fetchone(self):
        return tuple(self.rows[0]) if self.rows else None

    def executescript(self, sql):
        self.execute(sql, script=True)

    def commit(self):
        pass  # Each isolated fixture command commits automatically.

    def close(self):
        pass


class Client:
    def __init__(self, base):
        self.base = base
        self.cookies = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(
            urllib.request.ProxyHandler({}), NoRedirect(),
            urllib.request.HTTPCookieProcessor(self.cookies))

    def request(self, path, data=None, files=None, headers=None):
        headers = dict(headers or {})
        if files is not None:
            boundary = 'netfish-test-boundary'
            parts = []
            for key, value in (data or {}).items():
                parts.append(('--' + boundary + '\r\nContent-Disposition: form-data; name="'
                              + key + '"\r\n\r\n' + str(value) + '\r\n').encode())
            for key, name, content in files:
                parts.append(('--' + boundary + '\r\nContent-Disposition: form-data; name="'
                              + key + '"; filename="' + name + '"\r\n'
                              + 'Content-Type: application/octet-stream\r\n\r\n').encode()
                             + content + b'\r\n')
            parts.append(('--' + boundary + '--\r\n').encode())
            data = b''.join(parts)
            headers['Content-Type'] = 'multipart/form-data; boundary=' + boundary
        elif data is not None:
            data = urllib.parse.urlencode(data).encode()
        req = urllib.request.Request(self.base + path, data=data, headers=headers)
        try:
            response = self.opener.open(req, timeout=45)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            body = response.read()
            if response.headers.get_content_type() == 'text/html':
                assert b'Warning:' not in body and b'Fatal error:' not in body, body[:500]
            return response.code, response.headers, body

    def token(self, path):
        status, _, body = self.request(path)
        assert status == 200, (path, status, body[:200])
        return re.search(rb'name="csrf_token" value="([a-f0-9]+)"', body).group(1).decode()

    def session_id(self):
        return next(cookie.value for cookie in self.cookies if cookie.name == 'PHPSESSID')


def main():
    repo = Path(__file__).resolve().parents[1]
    php = os.environ.get('NETFISH_TEST_PHP') or shutil.which('php')
    if not php:
        raise RuntimeError('PHP must be on PATH (or set NETFISH_TEST_PHP).')
    with tempfile.TemporaryDirectory(prefix='netfish-security-') as temp:
        root = Path(temp)
        app = root / 'video-streaming-platform'
        shutil.copytree(repo, app, ignore=shutil.ignore_patterns('.git', 'config.local.php', '.env*', '*.sqlite*'))
        database = root / 'fixture.sqlite'
        db = FixtureDatabase(php, database)
        db.executescript('''
            CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT UNIQUE, email TEXT UNIQUE,
                                password TEXT, isAdmin INTEGER DEFAULT 0);
            CREATE TABLE videos (id INTEGER PRIMARY KEY, title TEXT, category TEXT,
                                 video_path TEXT, thumbnail_path TEXT,
                                 created_at TEXT DEFAULT CURRENT_TIMESTAMP);
            INSERT INTO videos (title, category, video_path, thumbnail_path)
            VALUES ('Legacy video', 'Demo', 'videos/harry.mp4', 'thumbnails/harry-potter.jpg');
        ''')
        password = 'local-test-password'
        hashed = subprocess.check_output([php, '-r', "echo password_hash('local-test-password', PASSWORD_DEFAULT);"]).decode()
        db.execute('INSERT INTO users VALUES (1, ?, ?, ?, 1)', ('Admin', 'admin@example.test', hashed))
        db.commit()
        # Replace ONLY the copied connection file; the application connection is never loaded.
        (app / 'includes/db.php').write_text(
            "<?php\n$pdo = new PDO('sqlite:' . dirname(__DIR__, 2) . '/fixture.sqlite', null, null, "
            "[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);\n",
            encoding='utf-8')
        sessions = root / 'sessions'
        sessions.mkdir()
        uploads = root / 'uploads'
        uploads.mkdir()
        with socket.socket() as sock:
            sock.bind(('127.0.0.1', 0))
            port = sock.getsockname()[1]
        log = open(root / 'server.log', 'w+', encoding='utf-8')
        server = subprocess.Popen([php, '-d', 'output_buffering=0', '-d', 'display_errors=1',
                                   '-d', 'session.save_path=' + str(sessions),
                                   '-d', 'upload_tmp_dir=' + str(uploads),
                                   '-d', 'session.upload_progress.enabled=0',
                                   '-d', 'upload_max_filesize=50M', '-d', 'post_max_size=55M',
                                   '-S', '127.0.0.1:' + str(port), '-t', str(root)],
                                  stdout=log, stderr=log)
        try:
            base = 'http://127.0.0.1:' + str(port) + '/video-streaming-platform/'
            guest = Client(base)
            for _ in range(50):
                try:
                    guest.request('pages/login.php')
                    break
                except urllib.error.URLError:
                    time.sleep(0.1)
            else:
                raise RuntimeError('Test server did not start.')
            for path in ('admin/index.php', 'admin/add_video.php', 'admin/edit_video.php?id=1', 'admin/delete_video.php'):
                status, headers, _ = guest.request(path)
                assert status == 302 and headers['Location'].endswith('/index.php'), path
            assert guest.request('pages/profile.php')[0] == 302
            assert guest.request('pages/logout.php')[0] == 405
            for path in ('pages/login.php', 'pages/register.php', 'pages/logout.php'):
                assert guest.request(path, {})[0] == 403, path
                assert guest.request(path, {'csrf_token': 'invalid'})[0] == 403, path
            token = guest.token('pages/register.php')
            fields = dict(csrf_token=token, username='Student', email='student@example.test', password='short')
            assert guest.request('pages/register.php', fields)[0] == 200
            assert db.execute('SELECT COUNT(*) FROM users').fetchone()[0] == 1
            fields['password'] = password
            assert guest.request('pages/register.php', fields)[0] == 302
            assert db.execute('SELECT isAdmin FROM users WHERE email=?', (fields['email'],)).fetchone()[0] == 0
            old_session = guest.session_id()
            assert guest.request('pages/login.php', dict(csrf_token=token, email=fields['email'], password=password))[0] == 302
            assert guest.session_id() != old_session
            assert guest.request('pages/profile.php')[0] == 200
            for path in ('admin/index.php', 'admin/add_video.php', 'admin/edit_video.php?id=1', 'admin/delete_video.php'):
                assert guest.request(path)[0] == 403, path
            print('PASS: guest/user authorization, CSRF, registration, session regeneration, profile')

            admin = Client(base)
            token = admin.token('pages/login.php')
            status, headers, _ = admin.request('pages/login.php', dict(csrf_token=token, email='admin@example.test', password=password))
            assert status == 302 and headers['Location'].endswith('/admin/index.php')
            token = admin.token('admin/add_video.php')
            for path in ('admin/add_video.php', 'admin/edit_video.php?id=1', 'admin/delete_video.php'):
                assert admin.request(path, {'id': 1})[0] == 403, path
            assert admin.request('admin/delete_video.php')[0] == 405
            status, _, body = admin.request('pages/video.php?id=1')
            assert status == 200 and b'videos/big-buck-bunny-demo.mp4' in body and b'vervangen' in body
            assert b'CC BY 3.0' in body and b'Blender Foundation' in body
            assert b'big-buck-bunny-demo.jpg' in admin.request('index.php')[2]
            original = db.execute('SELECT video_path, thumbnail_path FROM videos WHERE id=1').fetchone()
            edit = dict(csrf_token=token, title='Edited', category='Demo', video_path=original[0], thumbnail_path=original[1])
            assert admin.request('admin/edit_video.php?id=1', edit)[0] == 302
            assert db.execute('SELECT video_path, thumbnail_path FROM videos WHERE id=1').fetchone() == original
            edit['video_path'] = 'videos/../includes/db.php'
            assert admin.request('admin/edit_video.php?id=1', edit)[0] == 200
            assert db.execute('SELECT video_path, thumbnail_path FROM videos WHERE id=1').fetchone() == original
            edit['video_path'] = 'thumbnails/harry-potter.jpg'
            assert admin.request('admin/edit_video.php?id=1', edit)[0] == 200
            print('PASS: admin CSRF, POST-only delete, legacy playback/thumbnail references, traversal rejection')

            video = (app / 'videos/big-buck-bunny-demo.mp4').read_bytes()
            thumb = (app / 'images/thumbnails/big-buck-bunny-demo.jpg').read_bytes()
            fields = dict(csrf_token=token, title='Upload', category='Demo', video_name='../../evil.php', thumb_name='../../evil.php')
            before = db.execute('SELECT COUNT(*) FROM videos').fetchone()[0]
            bad_files = [
                [('video_file', 'fake.mp4', b'<?php echo "bad"; ?>'), ('thumb_file', 'demo.jpg', thumb)],
                [('video_file', 'demo.php', video), ('thumb_file', 'demo.jpg', thumb)],
                [('video_file', 'demo.webm', video), ('thumb_file', 'demo.jpg', thumb)],
                [('video_file', 'demo.mp4', video), ('thumb_file', 'fake.jpg', b'not an image')],
                [('video_file', 'demo.mp4', video), ('thumb_file', 'too-big.jpg', thumb + b'x' * (2 * 1024 * 1024))],
                [('video_file', 'empty.mp4', b''), ('thumb_file', 'demo.jpg', thumb)],
                [('video_file', 'demo.mp4', video)],
            ]
            for files in bad_files:
                assert admin.request('admin/add_video.php', fields, files)[0] == 200
                assert db.execute('SELECT COUNT(*) FROM videos').fetchone()[0] == before
            oversized = [('video_file', 'too-big.mp4', video + b'x' * (50 * 1024 * 1024)), ('thumb_file', 'demo.jpg', thumb)]
            assert admin.request('admin/add_video.php', fields, oversized)[0] == 200
            assert db.execute('SELECT COUNT(*) FROM videos').fetchone()[0] == before
            files = [('video_file', 'demo.mp4', video), ('thumb_file', 'demo.jpg', thumb)]
            assert admin.request('admin/add_video.php', fields, files)[0] == 302
            row = db.execute('SELECT id, video_path, thumbnail_path FROM videos ORDER BY id DESC LIMIT 1').fetchone()
            assert re.fullmatch(r'videos/evil-php-[a-f0-9]{32}\.mp4', row[1]), row
            assert re.fullmatch(r'thumbnails/evil-php-[a-f0-9]{32}\.jpg', row[2]), row
            assert admin.request(row[1])[2] == video
            assert admin.request('images/' + row[2])[2] == thumb
            assert row[1].encode() in admin.request('pages/video.php?id=' + str(row[0]))[2]
            current_files = set((app / 'videos').iterdir()) | set((app / 'images/thumbnails').iterdir())
            db.execute("CREATE TRIGGER fail_insert BEFORE INSERT ON videos BEGIN SELECT RAISE(FAIL, 'test insert failure'); END")
            db.commit()
            assert admin.request('admin/add_video.php', fields, files)[0] == 200
            assert current_files == set((app / 'videos').iterdir()) | set((app / 'images/thumbnails').iterdir())
            assert db.execute('SELECT COUNT(*) FROM videos').fetchone()[0] == before + 1
            db.execute('DROP TRIGGER fail_insert')
            db.commit()
            assert admin.request('admin/delete_video.php', dict(csrf_token=token, id=row[0]))[0] == 302
            assert db.execute('SELECT COUNT(*) FROM videos').fetchone()[0] == before
            print('PASS: multipart upload MIME/extension/size/error checks, safe filenames, playback, failed-insert cleanup, deletion')

            db.execute('UPDATE users SET isAdmin=0 WHERE id=1')
            db.commit()
            assert admin.request('admin/index.php')[0] == 403
            token = admin.token('pages/profile.php')
            assert admin.request('pages/logout.php', dict(csrf_token=token))[0] == 302
            assert admin.request('pages/profile.php')[0] == 302
            print('PASS: revoked admin role and CSRF-protected logout')
        finally:
            server.terminate()
            server.wait(timeout=10)
            db.close()
            log.seek(0)
            output = log.read()
            log.close()
            errors = '\n'.join(line for line in output.splitlines() if 'PHP Warning' in line or 'PHP Fatal' in line)
            assert not errors, errors
    print('All security smoke checks passed; real MySQL database was not used.')


if __name__ == '__main__':
    main()
