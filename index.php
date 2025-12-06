<?php
require_once 'common/config.php';

// Get banners
$banners = $conn->query("SELECT * FROM banners ORDER BY id DESC");

// Get latest courses
$latest_courses = $conn->query("SELECT * FROM courses ORDER BY created_at DESC LIMIT 10");

$top_courses = $conn->query("SELECT * FROM courses ORDER BY id DESC LIMIT 10");

include 'common/header.php';
include 'common/sidebar.php';
?>

<!-- Search Bar -->
<div class="p-4">
    <form action="course.php" method="GET" class="relative">
        <input type="text" name="search" placeholder="Search courses..." class="w-full px-4 py-3 pl-10 bg-white border border-gray-300 focus:border-sky-600 focus:outline-none">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
    </form>
</div>

<!-- Banner Slider -->
<?php if ($banners->num_rows > 0): ?>
<div class="relative overflow-hidden" id="bannerSlider">
    <div class="flex transition-transform duration-500" id="bannerTrack">
        <?php while ($banner = $banners->fetch_assoc()): ?>
        <div class="min-w-full">
            <a href="<?php echo $banner['link'] ?: '#'; ?>">
                <img src="uploads/banners/<?php echo $banner['image']; ?>" alt="Banner" class="w-full aspect-video object-cover">
            </a>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<script>
(function() {
    const track = document.getElementById('bannerTrack');
    const slides = track.children.length;
    let current = 0;
    if (slides > 1) {
        setInterval(() => {
            current = (current + 1) % slides;
            track.style.transform = 'translateX(-' + (current * 100) + '%)';
        }, 4000);
    }
})();
</script>
<?php endif; ?>

<!-- Latest Courses - removed pricing display -->
<section class="mt-6">
    <h2 class="px-4 text-lg font-bold text-gray-800 mb-3">Latest Courses</h2>
    <div class="flex overflow-x-auto hide-scrollbar px-4 gap-4 pb-4">
        <?php 
        if ($latest_courses->num_rows > 0):
            while ($course = $latest_courses->fetch_assoc()): 
        ?>
        <a href="course_detail.php?id=<?php echo $course['id']; ?>" class="min-w-[200px] bg-white">
            <img src="uploads/courses/<?php echo $course['image']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="w-full aspect-video object-cover">
            <div class="p-3">
                <h3 class="font-semibold text-gray-800 text-sm line-clamp-2"><?php echo htmlspecialchars($course['title']); ?></h3>
                <div class="mt-2">
                    <span class="text-green-600 font-bold text-sm">FREE</span>
                </div>
            </div>
        </a>
        <?php 
            endwhile;
        else:
        ?>
        <p class="text-gray-500">No courses available yet.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Top Courses -->
<section class="mt-6 px-4">
    <h2 class="text-lg font-bold text-gray-800 mb-3">Popular Courses</h2>
    <div class="grid grid-cols-2 gap-4">
        <?php 
        if ($top_courses->num_rows > 0):
            while ($course = $top_courses->fetch_assoc()): 
        ?>
        <a href="course_detail.php?id=<?php echo $course['id']; ?>" class="bg-white">
            <img src="uploads/courses/<?php echo $course['image']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="w-full aspect-video object-cover">
            <div class="p-3">
                <h3 class="font-semibold text-gray-800 text-sm line-clamp-2"><?php echo htmlspecialchars($course['title']); ?></h3>
                <div class="mt-2">
                    <span class="text-green-600 font-bold text-sm">FREE</span>
                </div>
            </div>
        </a>
        <?php 
            endwhile;
        else:
        ?>
        <p class="text-gray-500 col-span-2">No courses available yet.</p>
        <?php endif; ?>
    </div>
</section>

<div class="h-8"></div>

<?php include 'common/bottom.php'; ?>
