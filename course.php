<?php
require_once 'common/config.php';

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';

$sql = "SELECT * FROM courses WHERE 1=1";

if ($search) {
    $sql .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
}

switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY created_at ASC";
        break;
    default:
        $sql .= " ORDER BY created_at DESC";
}

$courses = $conn->query($sql);

include 'common/header.php';
include 'common/sidebar.php';
?>

<!-- Page Title -->
<div class="p-4 bg-white border-b">
    <h1 class="text-xl font-bold text-gray-800">All Courses</h1>
</div>

<!-- Search & Filters -->
<div class="p-4 space-y-3">
    <form action="course.php" method="GET" class="relative">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search courses..." class="w-full px-4 py-3 pl-10 bg-white border border-gray-300 focus:border-sky-600 focus:outline-none">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input type="hidden" name="sort" value="<?php echo $sort; ?>">
    </form>
    
    <!-- simplified sorting options -->
    <div class="flex gap-2">
        <select name="sort" onchange="window.location.href='course.php?search=<?php echo urlencode($search); ?>&sort='+this.value" class="flex-1 px-4 py-2 bg-white border border-gray-300 focus:outline-none">
            <option value="latest" <?php echo $sort == 'latest' ? 'selected' : ''; ?>>Latest First</option>
            <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
        </select>
    </div>
</div>

<!-- Course Grid - removed pricing display -->
<div class="px-4 pb-4">
    <div class="grid grid-cols-2 gap-4">
        <?php 
        if ($courses->num_rows > 0):
            while ($course = $courses->fetch_assoc()): 
        ?>
        <a href="course_detail.php?id=<?php echo $course['id']; ?>" class="bg-white relative">
            <span class="absolute top-2 left-2 bg-green-500 text-white text-xs px-2 py-1 z-10">FREE</span>
            <img src="uploads/courses/<?php echo $course['image']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="w-full aspect-video object-cover">
            <div class="p-3">
                <h3 class="font-semibold text-gray-800 text-sm line-clamp-2"><?php echo htmlspecialchars($course['title']); ?></h3>
                <div class="mt-2">
                    <span class="text-green-600 font-bold text-sm">Free Access</span>
                </div>
            </div>
        </a>
        <?php 
            endwhile;
        else:
        ?>
        <div class="col-span-2 text-center py-8">
            <i class="fas fa-search text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">No courses found.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
