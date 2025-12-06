<?php
require_once 'common/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get all courses with downloadable content
$courses = $conn->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM videos v JOIN chapters ch ON v.chapter_id = ch.id WHERE ch.course_id = c.id AND v.filename != '') as video_count,
           (SELECT COUNT(*) FROM notes n JOIN chapters ch ON n.chapter_id = ch.id WHERE ch.course_id = c.id AND (n.file_path != '' OR n.external_url != '')) as note_count
    FROM courses c
    ORDER BY c.title
");

include 'common/header.php';
include 'common/sidebar.php';
?>

<!-- Page Title -->
<div class="p-4 bg-white border-b">
    <h1 class="text-xl font-bold text-gray-800">Downloads</h1>
    <p class="text-sm text-gray-500">Download videos and notes for offline study</p>
</div>

<div class="p-4">
    <?php if ($courses->num_rows > 0): ?>
    <div class="space-y-4">
        <?php while ($course = $courses->fetch_assoc()): ?>
        <div class="bg-white">
            <div class="flex gap-4 p-4">
                <img src="uploads/courses/<?php echo $course['image']; ?>" alt="" class="w-20 h-14 object-cover">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($course['title']); ?></h3>
                    <div class="flex gap-4 text-sm text-gray-500 mt-1">
                        <span><i class="fas fa-play-circle mr-1"></i><?php echo $course['video_count']; ?> Videos</span>
                        <span><i class="fas fa-file-alt mr-1"></i><?php echo $course['note_count']; ?> Notes</span>
                    </div>
                </div>
            </div>
            <div class="border-t">
                <a href="download_course.php?id=<?php echo $course['id']; ?>" class="block p-3 text-center text-sky-600">
                    <i class="fas fa-download mr-1"></i> View Downloads
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-12">
        <i class="fas fa-download text-6xl text-gray-300 mb-4"></i>
        <p class="text-gray-500">No downloadable content available yet.</p>
    </div>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>
