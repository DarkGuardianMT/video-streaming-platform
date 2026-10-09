-- Import manually into a NEW, empty local database only. No DROP, DELETE or reset.
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(254) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    isAdmin TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE videos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    video_path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO videos (title, category, video_path, thumbnail_path) VALUES
('Big Buck Bunny - demo excerpt', 'Animation', 'videos/big-buck-bunny-demo.mp4', 'thumbnails/big-buck-bunny-demo.jpg'),
('Big Buck Bunny - forest excerpt', 'Animation', 'videos/big-buck-bunny-forest.mp4', 'thumbnails/big-buck-bunny-forest.jpg');
