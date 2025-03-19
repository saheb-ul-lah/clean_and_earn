<?php
$page_title = "User Management";

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireLogin();

if (!in_array($_SESSION['user_role'], ['admin', 'super_admin'])) {
    header('Location: /dashboard/unauthorized.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$filter_role = $_GET['role'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_search = $_GET['search'] ?? '';

$query = "
    SELECT 
        id,
        name,
        email,
        phone,
        role,
        status,
        city,
        state,
        created_at,
        total_points
    FROM users
    WHERE 1=1
";

$params = [];

if (!empty($filter_role)) {
    $query .= " AND role = ?";
    $params[] = $filter_role;
}

if (!empty($filter_status)) {
    $query .= " AND status = ?";
    $params[] = $filter_status;
}

if (!empty($filter_search)) {
    $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_term = "%$filter_search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $user_id_to_update = $_POST['user_id'] ?? '';
        $new_status = $_POST['new_status'] ?? '';
        
        if (empty($user_id_to_update) || empty($new_status)) {
            $error_message = 'Invalid request';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $user_id_to_update]);
                
                $success_message = 'User status updated successfully';
                
                header('Location: /dashboard/users/index.php');
                exit;
            } catch (Exception $e) {
                $error_message = 'Failed to update user status: ' . $e->getMessage();
            }
        }
    }
}

include_once '../../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">User Management</h1>
        <div>
            <a href="/dashboard/users/add.php" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                <i class="fas fa-user-plus mr-2"></i> Add User
            </a>
        </div>
    </div>
    
    <?php if (isset($success_message)): ?>
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
    
    <?php if (isset($error_message)): ?>
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
    
    <!-- Filters -->
    <div class="rounded-lg border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-medium">Filter Users</h2>
        <form method="GET" action="" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                <select id="role" name="role" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                    <option value="">All Roles</option>
                    <option value="admin" <?php echo $filter_role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="household" <?php echo $filter_role === 'household' ? 'selected' : ''; ?>>Household</option>
                    <option value="collector" <?php echo $filter_role === 'collector' ? 'selected' : ''; ?>>Collector</option>
                    <option value="storage" <?php echo $filter_role === 'storage' ? 'selected' : ''; ?>>Storage</option>
                    <option value="buyer" <?php echo $filter_role === 'buyer' ? 'selected' : ''; ?>>Buyer</option>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $filter_status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="suspended" <?php echo $filter_status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                </select>
            </div>
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($filter_search); ?>" placeholder="Name, Email, or Phone" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
    
    <!-- Users List -->
    <div class="rounded-lg border bg-white shadow-sm">
        <div class="p-6">
            <h2 class="text-lg font-medium">Users</h2>
            <p class="text-sm text-gray-500">
                Manage users of the Clean and Earn India platform
            </p>
        </div>
        
        <?php if (empty($users)): ?>
        <div class="flex flex-col items-center justify-center py-10">
            <p class="text-gray-500 mb-4">No users found matching your criteria</p>
            <a href="/dashboard/users/index.php" class="text-primary-600 hover:text-primary-700">
                Clear filters and try again
            </a>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-500 font-medium"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></div>
                                    <?php if ($user['role'] === 'household'): ?>
                                    <div class="text-xs text-gray-500"><?php echo number_format($user['total_points']); ?> points</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['phone'] ?: 'No phone'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 <?php echo getRoleBadgeClass($user['role']); ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 <?php echo getStatusBadgeClass($user['status']); ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $user['city'] && $user['state'] ? htmlspecialchars($user['city'] . ', ' . $user['state']) : 'Not specified'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="/dashboard/users/view.php?id=<?php echo $user['id']; ?>" class="text-primary-600 hover:text-primary-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/dashboard/users/edit.php?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="text-red-600 hover:text-red-900" onclick="openStatusModal('<?php echo $user['id']; ?>', '<?php echo $user['status']; ?>')">
                                    <i class="fas fa-user-cog"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Status Modal -->
<div id="status-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">Update User Status</h3>
            <form method="POST" action="" id="status-form">
                <input type="hidden" name="user_id" id="status-user-id">
                
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label for="new-status" class="text-sm font-medium">Status</label>
                        <select id="new-status" name="new_status" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" onclick="closeStatusModal()">
                        Cancel
                    </button>
                    <button type="submit" name="update_status" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openStatusModal(userId, currentStatus) {
        document.getElementById('status-user-id').value = userId;
        document.getElementById('new-status').value = currentStatus;
        document.getElementById('status-modal').classList.remove('hidden');
    }
    
    function closeStatusModal() {
        document.getElementById('status-modal').classList.add('hidden');
    }
    
    function getRoleBadgeClass(role) {
        switch (role) {
            case 'admin':
                return 'bg-purple-100 text-purple-800';
            case 'household':
                return 'bg-green-100 text-green-800';
            case 'collector':
                return 'bg-blue-100 text-blue-800';
            case 'storage':
                return 'bg-yellow-100 text-yellow-800';
            case 'buyer':
                return 'bg-indigo-100 text-indigo-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
</script>

<?php
include_once '../../includes/dashboard_footer.php';
?>