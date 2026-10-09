# Media audit — 9 October 2026

## Scope and findings

Inspected all tracked images/videos and every tree in the 42 commits reachable from local branches and fetched remote branches, before this cleanup. The remote has `main` and `Muhammed`, no tags, and pull-request head refs 1–9; every advertised pull-request head is already in the scanned history. No additional historical media paths or versions were found. This scope cannot enumerate other people's forks, clones, deleted GitHub refs, or server caches.

All 16 original assets lacked a source, author, or redistribution license in the repository or relevant commits. Thumbnail inspection identified Harry Potter and Vikings promotional artwork and GTA VI artwork. The other photographs/illustrations also had no permission records. `planet-earth.jpg` was actually WebP content.

Video frames were sampled at five-second intervals across their duration and containers/codecs inspected. Despite their names, `harry.mp4` showed an animated globe and `vikings.mp4` showed sky/sunset footage, not identifiable Harry Potter/Vikings scenes in the sampled frames. Other clips showed a lion, Mars, and ocean waves. These observations do not establish authorship or redistribution permission; all originals were removed conservatively.

## Exact historical exposure

The following files are deleted from the updated `main` tree, but their original bytes remain retrievable through earlier commits. All 16 also remain in the current remote `Muhammed` tree at `b2b18298d597ff9976d2dd4fc51c6a1b4db4f433` and pre-cleanup `main` at `9d2e51e3090b61d036362471fa2bb2714723a189`.

| Historical file | Bytes | Commit containing original |
| --- | ---: | --- |
| `videos/harry.mp4` | 80,473,213 | `eae722b3edc957ce5f688fa101da00d4e164a4f6` |
| `videos/vikings.mp4` | 5,210,646 | `eae722b3edc957ce5f688fa101da00d4e164a4f6` |
| `videos/lion.mp4` | 49,559,873 | `cec9a0a45a143e36711a134c93518d779d6cad20` |
| `videos/ocean-waves.mp4` | 56,038,183 | `cec9a0a45a143e36711a134c93518d779d6cad20` |
| `videos/ocean.mp4` | 56,038,183 | `cec9a0a45a143e36711a134c93518d779d6cad20` |
| `videos/mars.mp4` | 11,893,899 | `fc1de8506cca71d96e19983e1df8a15f0af69411` |
| `images/thumbnails/gaming.jpg` | 47,115 | `4a544d351a60adb64c342cf0a6b34fbd7dae831b` |
| `images/thumbnails/gta6.jpg` | 55,087 | `4a544d351a60adb64c342cf0a6b34fbd7dae831b` |
| `images/thumbnails/harry-potter.jpg` | 233,294 | `4a544d351a60adb64c342cf0a6b34fbd7dae831b` |
| `images/thumbnails/planet-earth.jpg` | 250,734 | `4a544d351a60adb64c342cf0a6b34fbd7dae831b` |
| `images/thumbnails/sql.png` | 200,894 | `4a544d351a60adb64c342cf0a6b34fbd7dae831b` |
| `images/thumbnails/vikings-season1.jpg` | 981,116 | `4a544d351a60adb64c342cf0a6b34fbd7dae831b` |
| `images/thumbnails/lion.jpg` | 62,938 | `cec9a0a45a143e36711a134c93518d779d6cad20` |
| `images/thumbnails/ocean-waves.jpg` | 116,630 | `cec9a0a45a143e36711a134c93518d779d6cad20` |
| `images/thumbnails/ocean-waves-2.jpg` | 116,630 | `cec9a0a45a143e36711a134c93518d779d6cad20` |
| `images/thumbnails/mars.jpg` | 73,030 | `fc1de8506cca71d96e19983e1df8a15f0af69411` |

[historical-media.tsv](historical-media.tsv) records exact path/blob pairs and counts of pre-cleanup commits containing them. The two ocean videos have identical bytes; the two ocean thumbnails also match. The 16 paths total 261,351,465 bytes (about 249 MiB), with 14 unique blobs totaling 205,196,652 bytes. A deletion commit does not remove these blobs or reduce existing clone history.

## Replacement and database compatibility

Added two licensed *Big Buck Bunny* excerpts (12 and 10 seconds) and their JPEG frames: 459,930 bytes combined. [MEDIA_LICENSES.md](../MEDIA_LICENSES.md) records the publisher's verified CC BY 3.0 license, required credit, source, edit details, and SHA-256 checksums. The application footer carries attribution, and MP4 metadata carries the credit/license link.

`includes/media.php` maps removed paths to a demo only when rendering. Existing database IDs, paths, titles, categories, and account records are untouched. The video page states when footage is substituted. New installations can manually import the separate fresh-database schema; it is not a migration and was not run against any real database. Uploaded assets are ignored by Git unless deliberately reviewed and allowed.

## Removing historical assets requires approval

No history rewrite, force-push, branch deletion, or repository recreation was performed. A normal push of the cleanup only fixes the current `main` tree. Permission records could resolve some original files' status; without them, historical removal is recommended before treating the entire repository as cleared for redistribution.

The safest removal procedure, **only after explicit approval**, is to coordinate with collaborators, preserve a private backup, and use a fresh mirror clone with `git filter-repo` to remove exactly the 16 paths listed above from all affected branches/tags. Verify every rewritten tree and blob inventory, then update only the affected remote refs with a coordinated force push. Commit IDs change for affected commits and descendants, signatures may be invalidated, old commit links break, and collaborators must re-clone or carefully reconcile their work to avoid restoring the old objects.

GitHub pull-request refs cannot be cleaned simply by rewriting branches. Old PRs 6–9 contain media in their history; PRs 8–9 contain all 16 at their heads, while PRs 6–7 contain earlier subsets. GitHub Support may need to remove cached views and pull-request references after a rewrite. Existing forks, clones, and third-party copies remain outside this repository's control. See GitHub's [history-removal guidance](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/removing-sensitive-data-from-a-repository) for mechanics and limitations; support eligibility for copyrighted media must be confirmed separately.
