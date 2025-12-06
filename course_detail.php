<?php
require_once 'common/config.php';

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$course_id) {
    header('Location: course.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header('Location: course.php');
    exit;
}

// Get chapters count
$chapters = $conn->query("SELECT COUNT(*) as count FROM chapters WHERE course_id = $course_id")->fetch_assoc();
$videos = $conn->query("SELECT COUNT(*) as count FROM videos v JOIN chapters c ON v.chapter_id = c.id WHERE c.course_id = $course_id")->fetch_assoc();
$notes = $conn->query("SELECT COUNT(*) as count FROM notes n JOIN chapters c ON n.chapter_id = c.id WHERE c.course_id = $course_id")->fetch_assoc();

include 'common/header.php';
include 'common/sidebar.php';
?>

<!-- Course Image -->
<div class="relative">
    <img src="uploads/courses/<?php echo $course['image']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="w-full aspect-video object-cover">
    <!-- replaced discount badge with FREE badge -->
    <span class="absolute top-4 left-4 bg-green-500 text-white px-3 py-1 font-semibold">FREE</span>
</div>

<!-- Course Info -->
<div class="p-4 pb-24">
    <h1 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($course['title']); ?></h1>
    
    <!-- removed price section, show free badge -->
    <div class="mt-3">
        <span class="text-2xl font-bold text-green-600">Free Access</span>
    </div>
    
    <!-- Stats - added notes count -->
    <div class="mt-4 flex gap-4 flex-wrap">
        <div class="flex items-center gap-2 text-gray-600">
            <i class="fas fa-book"></i>
            <span><?php echo $chapters['count']; ?> Chapters</span>
        </div>
        <div class="flex items-center gap-2 text-gray-600">
            <i class="fas fa-play-circle"></i>
            <span><?php echo $videos['count']; ?> Videos</span>
        </div>
        <div class="flex items-center gap-2 text-gray-600">
            <i class="fas fa-file-alt"></i>
            <span><?php echo $notes['count']; ?> Notes</span>
        </div>
    </div>
    
    <!-- Description -->
    <div class="mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Description</h2>
        <p class="text-gray-600 leading-relaxed"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
    </div>
</div>

<!-- Sticky Bottom Button - simplified for free access -->
<div class="fixed bottom-16 left-0 right-0 bg-white border-t p-4 z-20">
    <?php if (!isLoggedIn()): ?>
    <a href="login.php" class="block w-full bg-sky-600 text-white text-center py-3 font-semibold">
        <i class="fas fa-lock mr-2"></i> Login to Start Learning
    </a>
    <?php else: ?>
    <a href="watch.php?id=<?php echo $course_id; ?>" class="block w-full bg-green-500 text-white text-center py-3 font-semibold">
        <i class="fas fa-play mr-2"></i> Start Learning - FREE
    </a>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>
