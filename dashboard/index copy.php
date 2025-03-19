<?php
// Set page variables
$page_title = "Dashboard";

// Include database connection and functions
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Start session and check if user is logged in
// Start session only if it's not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireLogin();

// Get user data
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Get user stats based on role
$stats = [];

if ($user_role === 'household') {
    // Get total points
    $stmt = $pdo->prepare("SELECT total_points FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $stats['total_points'] = $user['total_points'] ?? 0;
    
    // Get waste collected
    $stmt = $pdo->prepare("
        SELECT SUM(wc.actual_weight) as total_weight
        FROM waste_collections wc
        JOIN waste_listings wl ON wc.listing_id = wl.id
        WHERE wl.user_id = ? AND wc.status = 'collected'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $stats['waste_collected'] = $result['total_weight'] ?? 0;
    
    // Get pickups completed
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_pickups
        FROM waste_collections wc
        JOIN waste_listings wl ON wc.listing_id = wl.id
        WHERE wl.user_id = ? AND wc.status = 'collected'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $stats['pickups_completed'] = $result['total_pickups'] ?? 0;
    
    // Get active listings
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as active_listings
        FROM waste_listings
        WHERE user_id = ? AND status IN ('pending', 'assigned')
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $stats['active_listings'] = $result['active_listings'] ?? 0;
    
    // Get waste by category
    $stmt = $pdo->prepare("
        SELECT wt.name, SUM(wc.actual_weight) as total_weight
        FROM waste_collections wc
        JOIN waste_listings wl ON wc.listing_id = wl.id
        JOIN waste_types wt ON wl.waste_type_id = wt.id
        WHERE wl.user_id = ? AND wc.status = 'collected'
        GROUP BY wt.name
    ");
    $stmt->execute([$user_id]);
    $waste_by_category = $stmt->fetchAll();
    
    // Get recent activities
    $stmt = $pdo->prepare("
        SELECT 
            wl.id,
            wl.waste_type_id,
            wl.weight,
            wl.status,
            wl.created_at,
            wc.collection_date,
            wc.status as collection_status
        FROM waste_listings wl
        LEFT JOIN waste_collections wc ON wl.id = wc.listing_id
        WHERE wl.user_id = ?
        ORDER BY wl.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_activities = $stmt->fetchAll();
} elseif ($user_role === 'collector') {
    // Get total collections
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_collections
        FROM waste_collections
        WHERE collector_id = ? AND status = 'collected'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $stats['total_collections'] = $result['total_collections'] ?? 0;
    
    // Get total weight collected
    $stmt = $pdo->prepare("
        SELECT SUM(actual_weight) as total_weight
        FROM waste_collections
        WHERE collector_id = ? AND status = 'collected'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $stats['total_weight'] = $result['total_weight'] ?? 0;
    
    // Get pending collections
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as pending_collections
        FROM waste_collections
        WHERE collector_id = ? AND status = 'assigned'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $stats['pending_collections'] = $result['pending_collections'] ?? 0;
    
    // Get recent collections
    $stmt = $pdo->prepare("
        SELECT 
            wc.id,
            wc.actual_weight,
            wc.status,
            wc.collection_date,
            wl.waste_type_id,
            u.name as household_name
        FROM waste_collections wc
        JOIN waste_listings wl ON wc.listing_id = wl.id
        JOIN users u ON wl.user_id = u.id
        WHERE wc.collector_id = ?
        ORDER BY wc.collection_date DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_activities = $stmt->fetchAll();
} elseif ($user_role === 'storage') {
    // Get total inventory
    $stmt = $pdo->prepare("
        SELECT SUM(weight) as total_weight
        FROM storage_inventory
        WHERE storage_id = ?
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $stats['total_inventory'] = $result['total_weight'] ?? 0;
    
    // Get available inventory
    $stmt = $pdo->prepare("
        SELECT SUM(weight) as available_weight
        FROM storage_inventory
        WHERE storage_id = ? AND status = 'available'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $stats['available_inventory'] = $result['available_weight'] ?? 0;
    
    // Get reserved inventory
    $stmt = $pdo->prepare("
        SELECT SUM(weight) as reserved_weight
        FROM storage_inventory
        WHERE storage_id = ? AND status = 'reserved'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $stats['reserved_inventory'] = $result['reserved_weight'] ?? 0;
    
    // Get recent inventory changes
    $stmt = $pdo->prepare("
        SELECT 
            si.id,
            si.waste_type_id,
            si.weight,
            si.status,
            si.created_at,
            wt.name as waste_type_name
        FROM storage_inventory si
        JOIN waste_types wt ON si.waste_type_id = wt.id
        WHERE si.storage_id = ?
        ORDER BY si.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_activities = $stmt->fetchAll();
} elseif ($user_role === 'buyer') {
    // Get total purchases
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_purchases
        FROM waste_purchases
        WHERE buyer_id = ? AND status = 'completed'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $stats['total_purchases'] = $result['total_purchases'] ?? 0;
    
    // Get total weight purchased
    $stmt = $pdo->prepare("
        SELECT SUM(weight) as total_weight
        FROM waste_purchases
        WHERE buyer_id = ? AND status = 'completed'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $stats['total_weight'] = $result['total_weight'] ?? 0;
    
    // Get pending purchases
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as pending_purchases
        FROM waste_purchases
        WHERE buyer_id = ? AND status = 'pending'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $stats['pending_purchases'] = $result['pending_purchases'] ?? 0;
    
    // Get recent purchases
    $stmt = $pdo->prepare("
        SELECT 
            wp.id,
            wp.weight,
            wp.amount,
            wp.status,
            wp.created_at,
            si.waste_type_id,
            u.name as storage_name
        FROM waste_purchases wp
        JOIN storage_inventory si ON wp.inventory_id = si.id
        JOIN users u ON wp.storage_id = u.id
        WHERE wp.buyer_id = ?
        ORDER BY wp.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_activities = $stmt->fetchAll();
} elseif ($user_role === 'admin' || $user_role === 'super_admin') {
    // Get total users
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $result = $stmt->fetch();
    $stats['total_users'] = $result['total_users'] ?? 0;
    
    // Get total waste collected
    $stmt = $pdo->query("
        SELECT SUM(actual_weight) as total_weight
        FROM waste_collections
        WHERE status = 'collected'
    ");
    $result = $stmt->fetch();
    $stats['total_weight'] = $result['total_weight'] ?? 0;
    
    // Get total listings
    $stmt = $pdo->query("SELECT COUNT(*) as total_listings FROM waste_listings");
    $result = $stmt->fetch();
    $stats['total_listings'] = $result['total_listings'] ?? 0;
    
    // Get recent activities (listings)
    $stmt = $pdo->query("
        SELECT 
            wl.id,
            wl.waste_type_id,
            wl.weight,
            wl.status,
            wl.created_at,
            u.name as user_name
        FROM waste_listings wl
        JOIN users u ON wl.user_id = u.id
        ORDER BY wl.created_at DESC
        LIMIT 5
    ");
    $recent_activities = $stmt->fetchAll();
}

include_once '../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <?php if ($user_role === 'household'): ?>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Points</h3>
                <i class="fas fa-dollar-sign text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['total_points']); ?></div>
            <p class="text-xs text-gray-500">
                Earn more by recycling waste
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Waste Collected</h3>
                <i class="fas fa-recycle text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['waste_collected'], 1); ?> kg</div>
            <p class="text-xs text-gray-500">
                Total waste recycled
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Pickups Completed</h3>
                <i class="fas fa-truck text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['pickups_completed']); ?></div>
            <p class="text-xs text-gray-500">
                Successful waste collections
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Active Listings</h3>
                <i class="fas fa-box text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['active_listings']); ?></div>
            <p class="text-xs text-gray-500">
                Pending waste pickups
            </p>
        </div>
        <?php elseif ($user_role === 'collector'): ?>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Collections</h3>
                <i class="fas fa-truck text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['total_collections']); ?></div>
            <p class="text-xs text-gray-500">
                Completed waste pickups
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Weight Collected</h3>
                <i class="fas fa-weight text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['total_weight'], 1); ?> kg</div>
            <p class="text-xs text-gray-500">
                Total waste collected
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Pending Collections</h3>
                <i class="fas fa-clock text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['pending_collections']); ?></div>
            <p class="text-xs text-gray-500">
                Assigned but not collected
            </p>
        </div>
        <?php elseif ($user_role === 'storage'): ?>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Inventory</h3>
                <i class="fas fa-warehouse text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['total_inventory'], 1); ?> kg</div>
            <p class="text-xs text-gray-500">
                Total waste in storage
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Available Inventory</h3>
                <i class="fas fa-box-open text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['available_inventory'], 1); ?> kg</div>
            <p class="text-xs text-gray-500">
                Available for purchase
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Reserved Inventory</h3>
                <i class="fas fa-lock text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['reserved_inventory'], 1); ?> kg</div>
            <p class="text-xs text-gray-500">
                Reserved by buyers
            </p>
        </div>
        <?php elseif ($user_role === 'buyer'): ?>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Purchases</h3>
                <i class="fas fa-shopping-cart text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['total_purchases']); ?></div>
            <p class="text-xs text-gray-500">
                Completed waste purchases
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Weight Purchased</h3>
                <i class="fas fa-weight text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['total_weight'], 1); ?> kg</div>
            <p class="text-xs text-gray-500">
                Total waste purchased
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Pending Purchases</h3>
                <i class="fas fa-clock text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['pending_purchases']); ?></div>
            <p class="text-xs text-gray-500">
                Awaiting completion
            </p>
        </div>
        <?php elseif ($user_role === 'admin' || $user_role === 'super_admin'): ?>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Users</h3>
                <i class="fas fa-users text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['total_users']); ?></div>
            <p class="text-xs text-gray-500">
                Registered platform users
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Waste Collected</h3>
                <i class="fas fa-recycle text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['total_weight'], 1); ?> kg</div>
            <p class="text-xs text-gray-500">
                Total waste recycled
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Listings</h3>
                <i class="fas fa-list text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($stats['total_listings']); ?></div>
            <p class="text-xs text-gray-500">
                Waste listings created
            </p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Dashboard Content -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
        <!-- Chart -->
        <div class="rounded-lg border bg-white p-6 shadow-sm md:col-span-4">
            <h3 class="text-lg font-bold">Waste Collection Overview</h3>
            <p class="text-sm text-gray-500">
                Your waste collection history for the past 30 days
            </p>
            <div class="mt-4 h-80">
                <canvas id="wasteChart"></canvas>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="rounded-lg border bg-white p-6 shadow-sm md:col-span-3">
            <h3 class="text-lg font-bold">Recent Activities</h3>
            <p class="text-sm text-gray-500">
                Your recent waste management activities
            </p>
            <div class="mt-4 space-y-4">
                <?php if (empty($recent_activities)): ?>
                <p class="text-center text-gray-500 py-4">No recent activities found.</p>
                <?php else: ?>
                <?php foreach ($recent_activities as $activity): ?>
                <div class="flex items-start gap-4">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100">
                        <?php if ($user_role === 'household'): ?>
                        <i class="fas fa-list-check text-primary-600"></i>
                        <?php elseif ($user_role === 'collector'): ?>
                        <i class="fas fa-truck text-primary-600"></i>
                        <?php elseif ($user_role === 'storage'): ?>
                        <i class="fas fa-box text-primary-600"></i>
                        <?php elseif ($user_role === 'buyer'): ?>
                        <i class="fas fa-shopping-cart text-primary-600"></i>
                        <?php else: ?>
                        <i class="fas fa-clipboard-list text-primary-600"></i>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 space-y-1">
                        <?php if ($user_role === 'household'): ?>
                        <p class="text-sm font-medium">
                            <?php echo $activity['status'] === 'pending' ? 'New Waste Listing' : ($activity['collection_status'] === 'collected' ? 'Waste Pickup Completed' : 'Waste Pickup Scheduled'); ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            <?php echo getWasteTypeName($activity['waste_type_id'], $pdo); ?> - <?php echo $activity['weight']; ?> kg
                        </p>
                        <p class="text-xs text-gray-500">
                            <?php echo date('M j, Y, g:i a', strtotime($activity['created_at'])); ?>
                        </p>
                        <?php elseif ($user_role === 'collector'): ?>
                        <p class="text-sm font-medium">
                            Collection from <?php echo $activity['household_name']; ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            <?php echo getWasteTypeName($activity['waste_type_id'], $pdo); ?> - <?php echo $activity['actual_weight']; ?> kg
                        </p>
                        <p class="text-xs text-gray-500">
                            <?php echo date('M j, Y, g:i a', strtotime($activity['collection_date'] ?? $activity['created_at'])); ?>
                        </p>
                        <?php elseif ($user_role === 'storage'): ?>
                        <p class="text-sm font-medium">
                            <?php echo $activity['status'] === 'available' ? 'New Inventory Added' : ($activity['status'] === 'reserved' ? 'Inventory Reserved' : 'Inventory Sold'); ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            <?php echo $activity['waste_type_name']; ?> - <?php echo $activity['weight']; ?> kg
                        </p>
                        <p class="text-xs text-gray-500">
                            <?php echo date('M j, Y, g:i a', strtotime($activity['created_at'])); ?>
                        </p>
                        <?php elseif ($user_role === 'buyer'): ?>
                        <p class="text-sm font-medium">
                            <?php echo $activity['status'] === 'pending' ? 'New Purchase Request' : ($activity['status'] === 'completed' ? 'Purchase Completed' : 'Purchase Paid'); ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            <?php echo getWasteTypeName($activity['waste_type_id'], $pdo); ?> - <?php echo $activity['weight']; ?> kg (₹<?php echo number_format($activity['amount'], 2); ?>)
                        </p>
                        <p class="text-xs text-gray-500">
                            <?php echo date('M j, Y, g:i a', strtotime($activity['created_at'])); ?>
                        </p>
                        <?php else: ?>
                        <p class="text-sm font-medium">
                            New listing by <?php echo $activity['user_name']; ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            <?php echo getWasteTypeName($activity['waste_type_id'], $pdo); ?> - <?php echo $activity['weight']; ?> kg
                        </p>
                        <p class="text-xs text-gray-500">
                            <?php echo date('M j, Y, g:i a', strtotime($activity['created_at'])); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Additional Sections -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <!-- Quick Actions -->
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between">
                <h3 class="text-lg font-bold">Quick Actions</h3>
                <i class="fas fa-chart-bar text-gray-400"></i>
            </div>
            <div class="mt-4 space-y-2">
                <?php if ($user_role === 'household'): ?>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-box text-primary-600"></i>
                        <span class="text-sm">Add New Waste Listing</span>
                    </div>
                    <a href="/dashboard/waste-listings/create.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar text-primary-600"></i>
                        <span class="text-sm">Schedule Pickup</span>
                    </div>
                    <a href="/dashboard/waste-listings/create.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-users text-primary-600"></i>
                        <span class="text-sm">Invite Friends</span>
                    </div>
                    <a href="/dashboard/invite.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <?php elseif ($user_role === 'collector'): ?>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-truck text-primary-600"></i>
                        <span class="text-sm">View Assigned Collections</span>
                    </div>
                    <a href="/dashboard/collections/index.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-primary-600"></i>
                        <span class="text-sm">View Collection Map</span>
                    </div>
                    <a href="/dashboard/collections/map.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-wallet text-primary-600"></i>
                        <span class="text-sm">View Earnings</span>
                    </div>
                    <a href="/dashboard/earnings/index.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <?php elseif ($user_role === 'storage'): ?>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-box text-primary-600"></i>
                        <span class="text-sm">Manage Inventory</span>
                    </div>
                    <a href="/dashboard/inventory/index.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-truck text-primary-600"></i>
                        <span class="text-sm">Receive Collections</span>
                    </div>
                    <a href="/dashboard/storage/receive.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-chart-line text-primary-600"></i>
                        <span class="text-sm">View Reports</span>
                    </div>
                    <a href="/dashboard/storage/reports.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <?php elseif ($user_role === 'buyer'): ?>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-shopping-cart text-primary-600"></i>
                        <span class="text-sm">Browse Marketplace</span>
                    </div>
                    <a href="/dashboard/marketplace/index.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-box text-primary-600"></i>
                        <span class="text-sm">View Orders</span>
                    </div>
                    <a href="/dashboard/orders/index.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-history text-primary-600"></i>
                        <span class="text-sm">Purchase History</span>
                    </div>
                    <a href="/dashboard/orders/history.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <?php elseif ($user_role === 'admin' || $user_role === 'super_admin'): ?>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-users text-primary-600"></i>
                        <span class="text-sm">Manage Users</span>
                    </div>
                    <a href="/dashboard/users/index.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-recycle text-primary-600"></i>
                        <span class="text-sm">Manage Waste Types</span>
                    </div>
                    <a href="/dashboard/waste-types/index.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <div class="flex items-center justify-between rounded-md border p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-chart-line text-primary-600"></i>
                        <span class="text-sm">View Reports</span>
                    </div>
                    <a href="/dashboard/reports/index.php" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-arrow-up-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($user_role === 'household' && !empty($waste_by_category)): ?>
        <!-- Waste Categories -->
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold">Waste Categories</h3>
            <p class="text-sm text-gray-500 mt-2">
                Your contribution by waste category
            </p>
            <div class="mt-4 space-y-4">
                <?php 
                $total_weight = array_sum(array_column($waste_by_category, 'total_weight'));
                foreach ($waste_by_category as $category): 
                    $percentage = $total_weight > 0 ? round(($category['total_weight'] / $total_weight) * 100) : 0;
                ?>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium"><?php echo $category['name']; ?></span>
                        <span class="text-sm text-gray-500"><?php echo $percentage; ?>%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-gray-100">
                        <div class="h-2 rounded-full bg-primary-600" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Environmental Impact -->
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold">Environmental Impact</h3>
            <p class="text-sm text-gray-500 mt-2">
                Your contribution to the environment
            </p>
            <div class="mt-4 space-y-4">
                <div class="flex items-center gap-4 rounded-md border p-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100">
                        <i class="fas fa-recycle text-primary-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium">CO2 Reduction</p>
                        <p class="text-xs text-gray-500"><?php echo round($stats['waste_collected'] * 2.5); ?> kg saved</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 rounded-md border p-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100">
                        <i class="fas fa-tree text-primary-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium">Trees Saved</p>
                        <p class="text-xs text-gray-500"><?php echo round($stats['waste_collected'] / 15); ?> trees</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 rounded-md border p-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100">
                        <i class="fas fa-tint text-primary-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium">Water Saved</p>
                        <p class="text-xs text-gray-500"><?php echo round($stats['waste_collected'] * 100); ?> liters</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        //  data for the chart
        const ctx = document.getElementById('wasteChart').getContext('2d');
        
        const data = {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [
                {
                    label: 'Paper',
                    data: [4, 3, 5, 7],
                    backgroundColor: 'rgba(34, 197, 94, 0.2)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 2,
                    tension: 0.4
                },
                {
                    label: 'Plastic',
                    data: [3, 2, 4, 5],
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.4
                },
                {
                    label: 'Metal',
                    data: [2, 1, 2, 3],
                    backgroundColor: 'rgba(245, 158, 11, 0.2)',
                    borderColor: 'rgba(245, 158, 11, 1)',
                    borderWidth: 2,
                    tension: 0.4
                },
                {
                    label: 'Electronic',
                    data: [1, 0.5, 1, 2],
                    backgroundColor: 'rgba(239, 68, 68, 0.2)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }
            ]
        };
        
        new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Weight (kg)'
                        }
                    }
                }
            }
        });
    });
</script>

<?php
include_once '../includes/dashboard_footer.php';
?>

