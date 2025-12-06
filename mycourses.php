<?php
require_once 'common/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$courses = $conn->query("SELECT * FROM courses ORDER BY created_at DESC");

include 'common/header.php';
include 'common/sidebar.php';
?>

<!-- Page Title -->
<div class="p-4 bg-white border-b">
    <h1 class="text-xl font-bold text-gray-800">My Learning</h1>
</div>

<!-- Course List -->
<div class="p-4">
    <?php if ($courses->num_rows > 0): ?>
    <div class="space-y-4">
        <?php while ($course = $courses->fetch_assoc()): ?>
        <div class="bg-white flex gap-4 p-4">
            <img src="uploads/courses/<?php echo $course['image']; ?>" alt="" class="w-28 h-20 object-cover">
            <div class="flex-1">
                <h3 class="font-semibold text-gray-800 line-clamp-2"><?php echo htmlspecialchars($course['title']); ?></h3>
                <a href="watch.php?id=<?php echo $course['id']; ?>" class="inline-block mt-2 bg-sky-600 text-white text-sm px-4 py-2">
                    <i class="fas fa-play mr-1"></i> Start
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-12">
        <i class="fas fa-graduation-cap text-6xl text-gray-300 mb-4"></i>
        <p class="text-gray-500 mb-4">No courses available yet.</p>
        <a href="course.php" class="inline-block bg-sky-600 text-white px-6 py-3">Browse Courses</a>
    </div>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>
