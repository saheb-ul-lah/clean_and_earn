<?php
//
session_start();


if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard/index.php');
    exit;
}


require_once '../includes/db_connect.php';
require_once '../includes/functions.php';


$name = '';
$email = '';
$phone = '';
$role = '';
$error = '';
$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']) ? true : false;
    
    //
    if (empty($name)) {
        $error = 'Name is required';
    } elseif (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (empty($phone)) {
        $error = 'Phone number is required';
    } elseif (empty($role)) {
        $error = 'Role is required';
    } elseif (empty($password)) {
        $error = 'Password is required';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!$terms) {
        $error = 'You must agree to the Terms of Service and Privacy Policy';
    } else {
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existing_user = $stmt->fetch();
        
        if ($existing_user) {
            $error = 'Email already exists';
        } else {
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $result = $stmt->execute([$name, $email, $hashed_password, $phone, $role]);
            
            if ($result) {
                $success = 'Registration successful! You can now login.';
                
                
                $name = '';
                $email = '';
                $phone = '';
                $role = '';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}


$page_title = "Register";
$show_header = false;
$show_footer = false;


include_once '../includes/header.php';
?>

<div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 p-4">
    <a href="../index.php" class="absolute left-8 top-8 flex items-center gap-2">
        <i class="fas fa-leaf text-primary-600 text-xl"></i>
        <span class="text-xl font-bold">CleanAndEarnIndia</span>
    </a>
    
    <div class="w-full max-w-md rounded-lg border bg-white p-6 shadow-sm">
        <div class="space-y-2 text-center">
            <h1 class="text-2xl font-bold">Create an account</h1>
            <p class="text-sm text-gray-500">
                Join Clean and Earn India to start recycling and earning rewards
            </p>
        </div>
        
        <?php if ($error): ?>
        <div class="mt-4 rounded-md bg-red-50 p-4 text-sm text-red-600">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="mt-4 rounded-md bg-green-50 p-4 text-sm text-green-600">
            <?php echo $success; ?>
            <p class="mt-2">
                <a href="./login.php" class="font-medium text-green-700 hover:underline">Login now</a>
            </p>
        </div>
        <?php else: ?>
        <form method="POST" action="" class="mt-6 space-y-4">
            <div class="space-y-2">
                <label for="name" class="text-sm font-medium">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" placeholder="Enter your full name" required>
            </div>
            <div class="space-y-2">
                <label for="email" class="text-sm font-medium">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" placeholder="m@example.com" required>
            </div>
            <div class="space-y-2">
                <label for="phone" class="text-sm font-medium">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" placeholder="Enter your phone number" required>
            </div>
            <div class="space-y-2">
                <label for="role" class="text-sm font-medium">I am a</label>
                <select id="role" name="role" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                    <option value="" <?php echo empty($role) ? 'selected' : ''; ?>>Select your role</option>
                    <option value="household" <?php echo $role === 'household' ? 'selected' : ''; ?>>Household User</option>
                    <option value="collector" <?php echo $role === 'collector' ? 'selected' : ''; ?>>Waste Collector</option>
                    <option value="storage" <?php echo $role === 'storage' ? 'selected' : ''; ?>>Storage House</option>
                    <option value="buyer" <?php echo $role === 'buyer' ? 'selected' : ''; ?>>Buyer</option>
                </select>
            </div>
            <div class="space-y-2">
                <label for="password" class="text-sm font-medium">Password</label>
                <input type="password" id="password" name="password" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" placeholder="Min. 8 characters" required>
            </div>
            <div class="space-y-2">
                <label for="confirm-password" class="text-sm font-medium">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm_password" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
            </div>
            <div class="flex items-center space-x-2">
                <input type="checkbox" id="terms" name="terms" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <label for="terms" class="text-sm text-gray-600">
                    I agree to the
                    <a href="./terms.php" class="text-primary-600 hover:underline">Terms of Service</a>
                    and
                    <a href="./privacy.php" class="text-primary-600 hover:underline">Privacy Policy</a>
                </label>
            </div>
            <button type="submit" class="w-full rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                Register
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm">
            Already have an account &nbsp;&nbsp;&nbsp;
            <a href="login.php" class="text-lg font-bold text-primary-600 hover:underline hover:text-primary-800 transition duration-200 ease-in-out">Login</a>
        </div>
        
        <div class="relative mt-6">
            <div class="absolute inset-0 flex items-center">
                <span class="w-full border-t"></span>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-white px-2 text-gray-500">Or continue with</span>
            </div>
        </div>
        
        <div class="mt-6 grid grid-cols-2 gap-4">
            <a href="#" class="flex items-center justify-center gap-2 rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="fab fa-google"></i>
                Google
            </a>
            <a href="#" class="flex items-center justify-center gap-2 rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="fab fa-facebook-f"></i>
                Facebook
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
include_once '../includes/footer.php';
?>

