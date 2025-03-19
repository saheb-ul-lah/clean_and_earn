<?php
include_once __DIR__ . '/../../config.php';
?>

<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

function isActive($page, $dir = null) {
    global $current_page, $current_dir;
    
    if ($dir !== null) {
        return $current_dir === $dir;
    }
    
    return $current_page === $page;
}

$user_role = $_SESSION['user_role'] ?? '';
?>

<div id="sidebar" class="fixed left-0 top-0 z-40 h-screen w-64 transform transition-transform duration-300 ease-in-out bg-white border-r shadow-sm md:translate-x-0 -translate-x-full">
    <div class="flex h-16 items-center justify-between border-b px-4">
        <div class="flex items-center gap-2">
            <i class="fas fa-leaf text-primary-600 text-xl"></i>
            <span class="text-xl font-bold">CAEIndia</span>
        </div>
        <button id="close-sidebar" class="md:hidden text-gray-500 hover:text-gray-700">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="overflow-y-auto h-[calc(100vh-4rem)]">
        <nav class="px-4 py-4">
            <ul class="space-y-1">
                <li>
                    <a href="<?php echo BASE_PATH; ?>dashboard/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-chart-bar w-5 h-5"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <?php if (hasRole(['household'])): ?>
                <li>
                    <a href="<?php echo BASE_PATH; ?>dashboard/waste-listings/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php', 'waste-listings') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-list-check w-5 h-5"></i>
                        <span>My Waste Listings</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>dashboard/rewards/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php', 'rewards') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-wallet w-5 h-5"></i>
                        <span>Rewards & Points</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasRole(['collector'])): ?>
                <li>
                    <a href="<?php echo BASE_PATH; ?>dashboard/collections/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php', 'collections') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-truck w-5 h-5"></i>
                        <span>My Collections</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>dashboard/earnings/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php', 'earnings') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-wallet w-5 h-5"></i>
                        <span>Earnings</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasRole(['storage'])): ?>
                <li>
                    <a href="<?php echo BASE_PATH; ?>dashboard/storage/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php', 'storage') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-store w-5 h-5"></i>
                        <span>Storage Management</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>dashboard/inventory/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php', 'inventory') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-box w-5 h-5"></i>
                        <span>Inventory</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasRole(['buyer'])): ?>
                <li>
                    <a href="<?php echo BASE_PATH; ?>dashboard/marketplace/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php', 'marketplace') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-shopping-cart w-5 h-5"></i>
                        <span>Marketplace</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>dashboard/orders/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php', 'orders') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-box w-5 h-5"></i>
                        <span>My Orders</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (hasRole(['admin', 'super_admin'])): ?>
                <li>
                    <a href="<?php echo BASE_PATH; ?>dashboard/users/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php', 'users') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-users w-5 h-5"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>dashboard/waste-types/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php', 'waste-types') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-recycle w-5 h-5"></i>
                        <span>Waste Types</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>dashboard/reports/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php', 'reports') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-chart-line w-5 h-5"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="mt-4 pt-4 border-t">
                    <a href="<?php echo BASE_PATH; ?>dashboard/profile/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php', 'profile') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-user w-5 h-5"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_PATH; ?>dashboard/settings/index.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium <?php echo isActive('index.php', 'settings') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                        <i class="fas fa-cog w-5 h-5"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li>
                    <a href="../auth/logout.php" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt w-5 h-5"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<div class="fixed bottom-4 right-4 md:hidden z-50">
    <button id="open-sidebar" class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-600 text-white shadow-lg">
        <i class="fas fa-bars"></i>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const openSidebar = document.getElementById('open-sidebar');
        const closeSidebar = document.getElementById('close-sidebar');
        const sidebar = document.getElementById('sidebar');
        
        if (openSidebar && closeSidebar && sidebar) {
            openSidebar.addEventListener('click', function() {
                sidebar.classList.remove('-translate-x-full');
            });
            
            closeSidebar.addEventListener('click', function() {
                sidebar.classList.add('-translate-x-full');
            });
        }
    });
</script>

