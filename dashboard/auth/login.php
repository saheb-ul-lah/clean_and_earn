<?php
include_once __DIR__ . '/../../config.php';
?>
<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard/index.php');
    exit;
}

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$email = '';
$password = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email)) {
        $error = 'Email is required';
    } elseif (empty($password)) {
        $error = 'Password is required';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'active') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                header('Location: ' . BASE_URL . 'dashboard/index.php');
                exit;
            } else {
                $error = 'Your account is not active. Please contact support.';
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}

$page_title = "Login";
$show_header = false;
$show_footer = false;

include_once '../includes/header.php';
?>

<div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 p-4">
    <a href="../../index.php" class="absolute left-8 top-8 flex items-center gap-2">
        <i class="fas fa-leaf text-primary-600 text-xl"></i>
        <span class="text-xl font-bold">CleanAndEarnIndia</span>
    </a>
    
    <div class="w-full max-w-md rounded-lg border bg-white p-6 shadow-sm">
        <div class="space-y-2 text-center">
            <h1 class="text-2xl font-bold">Login</h1>
            <p class="text-sm text-gray-500">
                Enter your credentials to access your account
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
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="mt-6 space-y-4">
            <div class="space-y-2">
                <label for="email" class="text-sm font-medium">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" placeholder="m@example.com" required>
            </div>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label for="password" class="text-sm font-medium">Password</label>
                    <a href="./forgot-password.php" class="text-xs text-primary-600 hover:underline">
                        Forgot password?
                    </a>
                </div>
                <input type="password" id="password" name="password" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
            </div>
            <button type="submit" class="w-full rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                Login
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm">
            Don't have an account ?
            <a href="register.php" class="text-primary-600 hover:underline">
                Register
            </a>
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
    </div>
</div>

<?php
include_once '../includes/footer.php';
?>

