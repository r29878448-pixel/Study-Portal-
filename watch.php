<?php
require_once 'common/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$course_id) {
    header('Location: course.php');
    exit;
}

$course = $conn->query("SELECT * FROM courses WHERE id = $course_id")->fetch_assoc();
if (!$course) {
    header('Location: course.php');
    exit;
}

$chapters = $conn->query("SELECT * FROM chapters WHERE course_id = $course_id ORDER BY id");

$current_video = null;
if (isset($_GET['video'])) {
    $video_id = (int)$_GET['video'];
    $stmt = $conn->prepare("SELECT v.*, c.course_id FROM videos v JOIN chapters c ON v.chapter_id = c.id WHERE v.id = ? AND c.course_id = ?");
    $stmt->bind_param("ii", $video_id, $course_id);
    $stmt->execute();
    $current_video = $stmt->get_result()->fetch_assoc();
}

$current_note = null;
if (isset($_GET['note'])) {
    $note_id = (int)$_GET['note'];
    $stmt = $conn->prepare("SELECT n.*, c.course_id FROM notes n JOIN chapters c ON n.chapter_id = c.id WHERE n.id = ? AND c.course_id = ?");
    $stmt->bind_param("ii", $note_id, $course_id);
    $stmt->execute();
    $current_note = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($course['title']); ?> - <?php echo $app_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { -webkit-user-select: none; user-select: none; }
        /* Advanced video player styles */
        .video-container { position: relative; background: #000; }
        .video-controls { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 10px; opacity: 0; transition: opacity 0.3s; }
        .video-container:hover .video-controls { opacity: 1; }
        .progress-bar { width: 100%; height: 4px; background: rgba(255,255,255,0.3); cursor: pointer; }
        .progress-bar .progress { height: 100%; background: #0ea5e9; }
        .speed-menu, .quality-menu { position: absolute; bottom: 50px; right: 10px; background: rgba(0,0,0,0.9); border-radius: 4px; display: none; }
        .speed-menu.active, .quality-menu.active { display: block; }
        .speed-menu button, .quality-menu button { display: block; width: 100%; padding: 8px 16px; color: white; text-align: left; }
        .speed-menu button:hover, .quality-menu button:hover { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" oncontextmenu="return false;">
    <!-- Header -->
    <header class="bg-sky-600 text-white h-14 flex items-center px-4 sticky top-0 z-40">
        <a href="course_detail.php?id=<?php echo $course_id; ?>" class="mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <h1 class="text-lg font-bold truncate"><?php echo htmlspecialchars($course['title']); ?></h1>
    </header>
    
    <?php if ($current_video): ?>
    <!-- Advanced Video Player with playback speed and quality controls -->
    <div class="video-container" id="videoContainer">
        <?php if (!empty($current_video['video_url'])): ?>
            <!-- External video (YouTube/Vimeo embed) -->
            <?php 
            $video_url = $current_video['video_url'];
            // Convert YouTube URL to embed
            if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $match);
                if (isset($match[1])) {
                    $video_url = 'https://www.youtube.com/embed/' . $match[1];
                }
            }
            ?>
            <iframe src="<?php echo $video_url; ?>" class="w-full aspect-video" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
        <?php else: ?>
            <!-- Local video with custom controls -->
            <video id="videoPlayer" class="w-full aspect-video" disablePictureInPicture>
                <source src="uploads/videos/<?php echo $current_video['filename']; ?>" type="video/mp4">
            </video>
            <div class="video-controls" id="videoControls">
                <div class="progress-bar mb-2" id="progressBar">
                    <div class="progress" id="progress" style="width: 0%"></div>
                </div>
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <button id="playBtn" class="text-xl"><i class="fas fa-play"></i></button>
                        <button id="rewindBtn" class="text-sm"><i class="fas fa-backward"></i> 10s</button>
                        <button id="forwardBtn" class="text-sm">10s <i class="fas fa-forward"></i></button>
                        <span id="timeDisplay" class="text-sm">0:00 / 0:00</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button id="speedBtn" class="text-sm">1x</button>
                        <button id="qualityBtn" class="text-sm"><i class="fas fa-cog"></i></button>
                        <button id="fullscreenBtn"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
                <!-- Speed Menu -->
                <div class="speed-menu" id="speedMenu">
                    <button data-speed="0.25">0.25x</button>
                    <button data-speed="0.5">0.5x</button>
                    <button data-speed="0.75">0.75x</button>
                    <button data-speed="1" class="font-bold">Normal</button>
                    <button data-speed="1.25">1.25x</button>
                    <button data-speed="1.5">1.5x</button>
                    <button data-speed="1.75">1.75x</button>
                    <button data-speed="2">2x</button>
                </div>
                <!-- Quality Menu -->
                <div class="quality-menu" id="qualityMenu">
                    <button data-quality="auto">Auto</button>
                    <button data-quality="1080">1080p</button>
                    <button data-quality="720">720p</button>
                    <button data-quality="480">480p</button>
                    <button data-quality="360">360p</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="bg-white p-4 border-b flex justify-between items-center">
        <h2 class="font-semibold text-gray-800"><?php echo htmlspecialchars($current_video['title']); ?></h2>
        <!-- Download button for video -->
        <?php if (!empty($current_video['filename'])): ?>
        <a href="uploads/videos/<?php echo $current_video['filename']; ?>" download class="text-sky-600">
            <i class="fas fa-download"></i>
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Video Player Script -->
    <script>
    (function() {
        const video = document.getElementById('videoPlayer');
        if (!video) return;
        
        const playBtn = document.getElementById('playBtn');
        const progressBar = document.getElementById('progressBar');
        const progress = document.getElementById('progress');
        const timeDisplay = document.getElementById('timeDisplay');
        const speedBtn = document.getElementById('speedBtn');
        const speedMenu = document.getElementById('speedMenu');
        const qualityBtn = document.getElementById('qualityBtn');
        const qualityMenu = document.getElementById('qualityMenu');
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        const rewindBtn = document.getElementById('rewindBtn');
        const forwardBtn = document.getElementById('forwardBtn');
        const container = document.getElementById('videoContainer');
        
        // Play/Pause
        playBtn.addEventListener('click', () => {
            if (video.paused) {
                video.play();
                playBtn.innerHTML = '<i class="fas fa-pause"></i>';
            } else {
                video.pause();
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
            }
        });
        
        video.addEventListener('click', () => playBtn.click());
        
        // Progress
        video.addEventListener('timeupdate', () => {
            const percent = (video.currentTime / video.duration) * 100;
            progress.style.width = percent + '%';
            timeDisplay.textContent = formatTime(video.currentTime) + ' / ' + formatTime(video.duration);
        });
        
        progressBar.addEventListener('click', (e) => {
            const rect = progressBar.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            video.currentTime = percent * video.duration;
        });
        
        // Rewind/Forward
        rewindBtn.addEventListener('click', () => video.currentTime -= 10);
        forwardBtn.addEventListener('click', () => video.currentTime += 10);
        
        // Speed
        speedBtn.addEventListener('click', () => {
            speedMenu.classList.toggle('active');
            qualityMenu.classList.remove('active');
        });
        
        speedMenu.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', () => {
                const speed = parseFloat(btn.dataset.speed);
                video.playbackRate = speed;
                speedBtn.textContent = speed + 'x';
                speedMenu.classList.remove('active');
            });
        });
        
        // Quality (placeholder - actual quality switching needs HLS/DASH)
        qualityBtn.addEventListener('click', () => {
            qualityMenu.classList.toggle('active');
            speedMenu.classList.remove('active');
        });
        
        // Fullscreen
        fullscreenBtn.addEventListener('click', () => {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                container.requestFullscreen();
            }
        });
        
        function formatTime(seconds) {
            if (isNaN(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.code === 'Space') { e.preventDefault(); playBtn.click(); }
            if (e.code === 'ArrowLeft') { video.currentTime -= 5; }
            if (e.code === 'ArrowRight') { video.currentTime += 5; }
            if (e.code === 'ArrowUp') { video.volume = Math.min(1, video.volume + 0.1); }
            if (e.code === 'ArrowDown') { video.volume = Math.max(0, video.volume - 0.1); }
            if (e.code === 'KeyF') { fullscreenBtn.click(); }
        });
    })();
    </script>
    
    <?php elseif ($current_note): ?>
    <!-- Note Viewer -->
    <div class="bg-white p-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-gray-800"><?php echo htmlspecialchars($current_note['title']); ?></h2>
            <?php if (!empty($current_note['file_path'])): ?>
            <a href="uploads/notes/<?php echo $current_note['file_path']; ?>" download class="bg-sky-600 text-white px-4 py-2 text-sm">
                <i class="fas fa-download mr-1"></i> Download
            </a>
            <?php elseif (!empty($current_note['external_url'])): ?>
            <a href="<?php echo $current_note['external_url']; ?>" target="_blank" class="bg-sky-600 text-white px-4 py-2 text-sm">
                <i class="fas fa-external-link-alt mr-1"></i> Open Link
            </a>
            <?php endif; ?>
        </div>
        <?php if (!empty($current_note['content'])): ?>
        <div class="prose max-w-none text-gray-700">
            <?php echo nl2br(htmlspecialchars($current_note['content'])); ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($current_note['file_path']) && pathinfo($current_note['file_path'], PATHINFO_EXTENSION) === 'pdf'): ?>
        <iframe src="uploads/notes/<?php echo $current_note['file_path']; ?>" class="w-full h-96 mt-4"></iframe>
        <?php endif; ?>
    </div>
    
    <?php else: ?>
    <!-- Course Thumbnail -->
    <div class="bg-black">
        <img src="uploads/courses/<?php echo $course['image']; ?>" alt="" class="w-full aspect-video object-cover opacity-70">
    </div>
    <div class="bg-white p-4 border-b text-center">
        <p class="text-gray-600">Select a video or note to start learning</p>
    </div>
    <?php endif; ?>
    
    <!-- Chapters & Content -->
    <div class="p-4">
        <h3 class="font-bold text-gray-800 mb-3">Course Content</h3>
        
        <?php if ($chapters->num_rows > 0): ?>
        <div class="space-y-2">
            <?php while ($chapter = $chapters->fetch_assoc()): ?>
            <div class="bg-white">
                <button onclick="toggleChapter(<?php echo $chapter['id']; ?>)" class="w-full flex items-center justify-between p-4 text-left">
                    <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($chapter['title']); ?></span>
                    <i class="fas fa-chevron-down text-gray-400 transition-transform" id="icon-<?php echo $chapter['id']; ?>"></i>
                </button>
                <div id="chapter-<?php echo $chapter['id']; ?>" class="hidden border-t">
                    <!-- Videos -->
                    <?php 
                    $videos = $conn->query("SELECT * FROM videos WHERE chapter_id = " . $chapter['id'] . " ORDER BY id");
                    while ($video = $videos->fetch_assoc()): 
                        $isActive = $current_video && $current_video['id'] == $video['id'];
                    ?>
                    <a href="watch.php?id=<?php echo $course_id; ?>&video=<?php echo $video['id']; ?>" class="flex items-center gap-3 p-4 border-b <?php echo $isActive ? 'bg-sky-50 text-sky-600' : 'text-gray-600'; ?>">
                        <i class="fas fa-play-circle"></i>
                        <span class="flex-1"><?php echo htmlspecialchars($video['title']); ?></span>
                        <?php if ($isActive): ?>
                        <span class="text-xs bg-sky-600 text-white px-2 py-1">Playing</span>
                        <?php endif; ?>
                    </a>
                    <?php endwhile; ?>
                    
                    <!-- Notes section -->
                    <?php 
                    $chapter_notes = $conn->query("SELECT * FROM notes WHERE chapter_id = " . $chapter['id'] . " ORDER BY id");
                    while ($note = $chapter_notes->fetch_assoc()): 
                        $isActive = $current_note && $current_note['id'] == $note['id'];
                    ?>
                    <a href="watch.php?id=<?php echo $course_id; ?>&note=<?php echo $note['id']; ?>" class="flex items-center gap-3 p-4 border-b <?php echo $isActive ? 'bg-green-50 text-green-600' : 'text-gray-600'; ?>">
                        <i class="fas fa-file-alt"></i>
                        <span class="flex-1"><?php echo htmlspecialchars($note['title']); ?></span>
                        <?php if (!empty($note['external_url'])): ?>
                        <i class="fas fa-link text-xs"></i>
                        <?php endif; ?>
                        <?php if ($isActive): ?>
                        <span class="text-xs bg-green-600 text-white px-2 py-1">Viewing</span>
                        <?php endif; ?>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <p class="text-gray-500 text-center py-8">No content available yet.</p>
        <?php endif; ?>
    </div>
    
    <script>
        function toggleChapter(id) {
            const content = document.getElementById('chapter-' + id);
            const icon = document.getElementById('icon-' + id);
            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
        
        // Auto-expand chapter with current content
        <?php if ($current_video): ?>
        document.addEventListener('DOMContentLoaded', function() {
            toggleChapter(<?php echo $current_video['chapter_id']; ?>);
        });
        <?php elseif ($current_note): ?>
        document.addEventListener('DOMContentLoaded', function() {
            toggleChapter(<?php echo $current_note['chapter_id']; ?>);
        });
        <?php endif; ?>
    </script>
</body>
</html>
