<?php
require_once 'common/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getUser($conn);
$message = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($name) || empty($phone) || empty($email)) {
        $error = 'Name, phone and email are required.';
    } else {
        // Check if email is taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email is already taken by another user.';
        } else {
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $name, $phone, $email, $hashed, $_SESSION['user_id']);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, email = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $phone, $email, $_SESSION['user_id']);
            }
            
            if ($stmt->execute()) {
                $message = 'Profile updated successfully!';
                $user = getUser($conn);
                $_SESSION['user_name'] = $user['name'];
            } else {
                $error = 'Failed to update profile.';
            }
        }
    }
}

include 'common/header.php';
include 'common/sidebar.php';
?>

<!-- Page Title -->
<div class="p-4 bg-white border-b">
    <h1 class="text-xl font-bold text-gray-800">Profile</h1>
</div>

<div class="p-4">
    <?php if ($message): ?>
    <div class="bg-green-100 text-green-700 p-4 mb-4">
        <i class="fas fa-check-circle mr-2"></i><?php echo $message; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="bg-red-100 text-red-700 p-4 mb-4">
        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <!-- Profile Avatar -->
    <div class="text-center mb-6">
        <div class="w-20 h-20 bg-sky-600 text-white flex items-center justify-center text-3xl font-bold mx-auto">
            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
        </div>
        <p class="mt-2 text-gray-600">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
    </div>
    
    <!-- Profile Form -->
    <form method="POST" class="bg-white p-4 space-y-4">
        <div>
            <label class="block text-gray-700 text-sm mb-1">Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-sky-600 focus:outline-none">
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Phone</label>
            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-sky-600 focus:outline-none">
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-sky-600 focus:outline-none">
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">New Password (leave blank to keep current)</label>
            <input type="password" name="password" minlength="6" class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-sky-600 focus:outline-none">
        </div>
        <button type="submit" class="w-full bg-sky-600 text-white py-3 font-semibold">
            <i class="fas fa-save mr-2"></i>Save Changes
        </button>
    </form>
    
    <!-- Logout -->
    <a href="login.php?logout=1" class="block w-full mt-4 bg-red-500 text-white text-center py-3 font-semibold">
        <i class="fas fa-sign-out-alt mr-2"></i>Logout
    </a>
</div>

<?php include 'common/bottom.php'; ?>
