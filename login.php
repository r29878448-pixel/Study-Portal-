<?php
require_once 'common/config.php';

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Redirect if logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// AJAX Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'login') {
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
        
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                echo json_encode(['success' => true, 'message' => 'Login successful']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid password']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Email not found']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'signup') {
        $name = sanitize($_POST['name']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($name) || empty($phone) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            exit;
        }
        
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $phone, $email, $hashed);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['user_name'] = $name;
            echo json_encode(['success' => true, 'message' => 'Account created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed']);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - <?php echo $app_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { -webkit-user-select: none; -moz-user-select: none; user-select: none; }
        input { -webkit-user-select: text; user-select: text; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <div class="bg-sky-600 text-white py-8 text-center">
            <h1 class="text-2xl font-bold"><?php echo $app_name; ?></h1>
            <p class="text-sky-200 mt-1">Learn. Grow. Succeed.</p>
        </div>
        
        <!-- Tabs -->
        <div class="flex bg-white border-b">
            <button id="loginTab" onclick="switchTab('login')" class="flex-1 py-4 font-semibold text-sky-600 border-b-2 border-sky-600">Login</button>
            <button id="signupTab" onclick="switchTab('signup')" class="flex-1 py-4 font-semibold text-gray-500">Sign Up</button>
        </div>
        
        <!-- Forms -->
        <div class="flex-1 p-4">
            <!-- Login Form -->
            <form id="loginForm" class="space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-sky-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-sky-600 focus:outline-none">
                </div>
                <button type="submit" class="w-full bg-sky-600 text-white py-3 font-semibold">Login</button>
            </form>
            
            <!-- Signup Form -->
            <form id="signupForm" class="space-y-4 hidden">
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Full Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-sky-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Phone</label>
                    <input type="tel" name="phone" required class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-sky-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-sky-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Password</label>
                    <input type="password" name="password" required minlength="6" class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-sky-600 focus:outline-none">
                </div>
                <button type="submit" class="w-full bg-sky-600 text-white py-3 font-semibold">Create Account</button>
            </form>
            
            <!-- Message -->
            <div id="message" class="mt-4 p-3 text-center hidden"></div>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            const loginTab = document.getElementById('loginTab');
            const signupTab = document.getElementById('signupTab');
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            
            if (tab === 'login') {
                loginTab.classList.add('text-sky-600', 'border-b-2', 'border-sky-600');
                loginTab.classList.remove('text-gray-500');
                signupTab.classList.remove('text-sky-600', 'border-b-2', 'border-sky-600');
                signupTab.classList.add('text-gray-500');
                loginForm.classList.remove('hidden');
                signupForm.classList.add('hidden');
            } else {
                signupTab.classList.add('text-sky-600', 'border-b-2', 'border-sky-600');
                signupTab.classList.remove('text-gray-500');
                loginTab.classList.remove('text-sky-600', 'border-b-2', 'border-sky-600');
                loginTab.classList.add('text-gray-500');
                signupForm.classList.remove('hidden');
                loginForm.classList.add('hidden');
            }
            document.getElementById('message').classList.add('hidden');
        }
        
        function showMessage(msg, isError) {
            const el = document.getElementById('message');
            el.textContent = msg;
            el.className = 'mt-4 p-3 text-center ' + (isError ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700');
            el.classList.remove('hidden');
        }
        
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'login');
            
            fetch('login.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'index.php';
                    } else {
                        showMessage(data.message, true);
                    }
                });
        });
        
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'signup');
            
            fetch('login.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'index.php';
                    } else {
                        showMessage(data.message, true);
                    }
                });
        });
    </script>
</body>
</html>
