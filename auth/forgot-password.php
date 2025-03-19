<?php
//
session_start();


if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard/index.php');
    exit;
}


require_once '../includes/db_connect.php';
require_once '../includes/functions.php';


$email = '';
$error = '';
$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = sanitize($_POST['email'] ?? '');
    
    
    if (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
            $result = $stmt->execute([$token, $expires, $user['id']]);
            
            if ($result) {
                
                
                $reset_link = "http://{$_SERVER['HTTP_HOST']}/auth/reset-password.php?token=$token";
                
                $success = 'Password reset instructions have been sent to your email.';
                
                //
                $email = '';
            } else {
                $error = 'Failed to process your request. Please try again.';
            }
        } else {
            
            $success = 'If your email is registered, you will receive password reset instructions.';
            $email = '';
        }
    }
}


$page_title = "Forgot Password";
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
            <h1 class="text-2xl font-bold">Forgot Password</h1>
            <p class="text-sm text-gray-500">
                Enter your email address and we'll send you instructions to reset your password
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
            
            <?php if (isset($reset_link)): ?>
            <p class="mt-2">
                <strong>Demo Reset Link:</strong> <a href="<?php echo $reset_link; ?>" class="underline"><?php echo $reset_link; ?></a>
            </p>
            <?php endif; ?>
            
            <p class="mt-4">
                <a href="login.php" class="font-medium text-green-700 hover:underline">Back to login</a>
            </p>
        </div>
        <?php else: ?>
        <form method="POST" action="" class="mt-6 space-y-4">
            <div class="space-y-2">
                <label for="email" class="text-sm font-medium">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" placeholder="m@example.com" required>
            </div>
            <button type="submit" class="w-full rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                Send Reset Instructions
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm">
            Remember your password?
            <a href="login.php" class="text-primary-600 hover:underline">
                Login
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php

include_once '../includes/footer.php';
?>

