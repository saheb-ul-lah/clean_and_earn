<?php
$page_title = "My Profile";

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireLogin();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $city = sanitize($_POST['city'] ?? '');
        $state = sanitize($_POST['state'] ?? '');
        $pincode = sanitize($_POST['pincode'] ?? '');
        
        if (empty($name)) {
            $error_message = 'Name is required';
        } elseif (empty($phone)) {
            $error_message = 'Phone number is required';
        } else {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, phone = ?, address = ?, city = ?, state = ?, pincode = ?
                WHERE id = ?
            ");
            $result = $stmt->execute([$name, $phone, $address, $city, $state, $pincode, $user_id]);
            
            if ($result) {
                $success_message = 'Profile updated successfully';
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
                $_SESSION['user_name'] = $user['name'];
            } else {
                $error_message = 'Failed to update profile';
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password)) {
            $error_message = 'Current password is required';
        } elseif (empty($new_password)) {
            $error_message = 'New password is required';
        } elseif (strlen($new_password) < 8) {
            $error_message = 'New password must be at least 8 characters long';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'New passwords do not match';
        } elseif (!password_verify($current_password, $user['password'])) {
            $error_message = 'Current password is incorrect';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $result = $stmt->execute([$hashed_password, $user_id]);
            
            if ($result) {
                $success_message = 'Password changed successfully';
            } else {
                $error_message = 'Failed to change password';
            }
        }
    }
}

include_once '../../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">My Profile</h1>
    </div>
    
    <?php if ($success_message): ?>
    <div class="rounded-md bg-green-50 p-4 animate-fade-in">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800"><?php echo $success_message; ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    <div class="rounded-md bg-red-50 p-4 animate-fade-in">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-times-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800"><?php echo $error_message; ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Profile Information -->
        <div class="rounded-lg border bg-white shadow-sm">
            <div class="border-b px-6 py-4">
                <h2 class="text-lg font-medium">Profile Information</h2>
                <p class="text-sm text-gray-500">Update your personal information</p>
            </div>
            <div class="p-6">
                <form method="POST" action="" class="space-y-4">
                    <div class="space-y-2">
                        <label for="name" class="text-sm font-medium">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                    </div>
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium">Email</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm" readonly>
                        <p class="text-xs text-gray-500">Email cannot be changed</p>
                    </div>
                    <div class="space-y-2">
                        <label for="phone" class="text-sm font-medium">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                    </div>
                    <div class="space-y-2">
                        <label for="address" class="text-sm font-medium">Address</label>
                        <textarea id="address" name="address" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="city" class="text-sm font-medium">City</label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                        </div>
                        <div class="space-y-2">
                            <label for="state" class="text-sm font-medium">State</label>
                            <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="pincode" class="text-sm font-medium">Pincode</label>
                        <input type="text" id="pincode" name="pincode" value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                    </div>
                    <div class="pt-4">
                        <button type="submit" name="update_profile" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Account Information -->
        <div class="space-y-6">
            <div class="rounded-lg border bg-white shadow-sm">
                <div class="border-b px-6 py-4">
                    <h2 class="text-lg font-medium">Account Information</h2>
                    <p class="text-sm text-gray-500">View your account details</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Account Type</p>
                            <p class="text-sm"><?php echo ucfirst($user['role'] ?? ''); ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Account Status</p>
                            <p class="text-sm">
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo ucfirst($user['status'] ?? ''); ?>
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Member Since</p>
                            <p class="text-sm"><?php echo date('F j, Y', strtotime($user['created_at'] ?? 'now')); ?></p>
                        </div>
                        <?php if ($user['role'] === 'household'): ?>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Points</p>
                            <p class="text-sm font-bold text-primary-600"><?php echo number_format($user['total_points'] ?? 0); ?> points</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="rounded-lg border bg-white shadow-sm">
                <div class="border-b px-6 py-4">
                    <h2 class="text-lg font-medium">Change Password</h2>
                    <p class="text-sm text-gray-500">Update your account password</p>
                </div>
                <div class="p-6">
                    <form method="POST" action="" class="space-y-4">
                        <div class="space-y-2">
                            <label for="current-password" class="text-sm font-medium">Current Password</label>
                            <input type="password" id="current-password" name="current_password" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                        </div>
                        <div class="space-y-2">
                            <label for="new-password" class="text-sm font-medium">New Password</label>
                            <input type="password" id="new-password" name="new_password" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                            <p class="text-xs text-gray-500">Minimum 8 characters</p>
                        </div>
                        <div class="space-y-2">
                            <label for="confirm-password" class="text-sm font-medium">Confirm New Password</label>
                            <input type="password" id="confirm-password" name="confirm_password" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                        </div>
                        <div class="pt-4">
                            <button type="submit" name="change_password" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once '../../includes/dashboard_footer.php';
?>