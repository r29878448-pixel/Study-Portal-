<?php
require_once 'common/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$course_id) {
    header('Location: downloads.php');
    exit;
}

$course = $conn->query("SELECT * FROM courses WHERE id = $course_id")->fetch_assoc();
if (!$course) {
    header('Location: downloads.php');
    exit;
}

$chapters = $conn->query("SELECT * FROM chapters WHERE course_id = $course_id ORDER BY id");

include 'common/header.php';
include 'common/sidebar.php';
?>

<!-- Page Title -->
<div class="p-4 bg-white border-b">
    <a href="downloads.php" class="text-sky-600 text-sm mb-2 inline-block">
        <i class="fas fa-arrow-left mr-1"></i> Back to Downloads
    </a>
    <h1 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($course['title']); ?></h1>
</div>

<div class="p-4">
    <?php if ($chapters->num_rows > 0): ?>
    <div class="space-y-4">
        <?php while ($chapter = $chapters->fetch_assoc()): ?>
        <div class="bg-white">
            <div class="p-4 bg-gray-50 font-semibold text-gray-800">
                <i class="fas fa-folder mr-2 text-sky-600"></i><?php echo htmlspecialchars($chapter['title']); ?>
            </div>
            
            <!-- Videos -->
            <?php 
            $videos = $conn->query("SELECT * FROM videos WHERE chapter_id = " . $chapter['id'] . " AND filename != '' ORDER BY id");
            if ($videos->num_rows > 0):
            ?>
            <div class="border-t">
                <p class="px-4 py-2 text-xs font-semibold text-gray-500 bg-gray-50">VIDEOS</p>
                <?php while ($video = $videos->fetch_assoc()): ?>
                <div class="flex items-center justify-between p-4 border-b last:border-b-0">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-play-circle text-sky-600"></i>
                        <span class="text-gray-700"><?php echo htmlspecialchars($video['title']); ?></span>
                    </div>
                    <a href="uploads/videos/<?php echo $video['filename']; ?>" download class="text-sky-600 px-3 py-1 bg-sky-50">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
            
            <!-- Notes -->
            <?php 
            $notes = $conn->query("SELECT * FROM notes WHERE chapter_id = " . $chapter['id'] . " AND (file_path != '' OR external_url != '') ORDER BY id");
            if ($notes->num_rows > 0):
            ?>
            <div class="border-t">
                <p class="px-4 py-2 text-xs font-semibold text-gray-500 bg-gray-50">NOTES</p>
                <?php while ($note = $notes->fetch_assoc()): ?>
                <div class="flex items-center justify-between p-4 border-b last:border-b-0">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-alt text-green-600"></i>
                        <span class="text-gray-700"><?php echo htmlspecialchars($note['title']); ?></span>
                    </div>
                    <?php if (!empty($note['file_path'])): ?>
                    <a href="uploads/notes/<?php echo $note['file_path']; ?>" download class="text-green-600 px-3 py-1 bg-green-50">
                        <i class="fas fa-download"></i>
                    </a>
                    <?php elseif (!empty($note['external_url'])): ?>
                    <a href="<?php echo $note['external_url']; ?>" target="_blank" class="text-green-600 px-3 py-1 bg-green-50">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-12">
        <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
        <p class="text-gray-500">No content available in this course yet.</p>
    </div>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>
