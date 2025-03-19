<?php
$page_title = "Reports";

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireLogin();

if (!hasRole(['admin', 'super_admin'])) {
    header('Location: /dashboard/unauthorized.php');
    exit;
}

$total_users = $pdo->query("SELECT COUNT(*) as total_users FROM users")->fetch()['total_users'];
$total_waste_collected = $pdo->query("SELECT SUM(actual_weight) as total_weight FROM waste_collections WHERE status = 'collected'")->fetch()['total_weight'];
$total_listings = $pdo->query("SELECT COUNT(*) as total_listings FROM waste_listings")->fetch()['total_listings'];
$total_points_earned = $pdo->query("SELECT SUM(points) as total_points FROM points_transactions")->fetch()['total_points'];

$waste_trends = $pdo->query("
    SELECT DATE(wc.collection_date) as collection_day, SUM(wc.actual_weight) as total_weight
    FROM waste_collections wc
    WHERE wc.collection_date >= NOW() - INTERVAL 30 DAY
    GROUP BY collection_day
    ORDER BY collection_day ASC
")->fetchAll();

$recent_activities = $pdo->query("
    SELECT 
        u.name as user_name,
        u.role,
        wl.weight,
        wt.name as waste_type,
        wc.collection_date,
        wc.status as collection_status
    FROM waste_collections wc
    JOIN waste_listings wl ON wc.listing_id = wl.id
    JOIN waste_types wt ON wl.waste_type_id = wt.id
    JOIN users u ON wc.collector_id = u.id
    ORDER BY wc.collection_date DESC
    LIMIT 10
")->fetchAll();

$inventory_overview = $pdo->query("
    SELECT 
        wt.name as waste_type,
        SUM(si.weight) as total_weight,
        si.status
    FROM storage_inventory si
    JOIN waste_types wt ON si.waste_type_id = wt.id
    GROUP BY wt.name, si.status
")->fetchAll();

include_once '../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <!-- System-Wide Stats -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Users</h3>
                <i class="fas fa-users text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($total_users); ?></div>
            <p class="text-xs text-gray-500">Registered platform users</p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Waste Collected</h3>
                <i class="fas fa-recycle text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($total_waste_collected, 1); ?> kg</div>
            <p class="text-xs text-gray-500">Total waste recycled</p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Listings</h3>
                <i class="fas fa-list text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($total_listings); ?></div>
            <p class="text-xs text-gray-500">Waste listings created</p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Points Earned</h3>
                <i class="fas fa-wallet text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($total_points_earned); ?></div>
            <p class="text-xs text-gray-500">Points earned by users</p>
        </div>
    </div>

    <!-- Waste Collection Trends -->
    <div class="rounded-lg border bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold">Waste Collection Trends (Last 30 Days)</h3>
        <p class="text-sm text-gray-500">Daily waste collection trends</p>
        <div class="mt-4 h-80">
            <canvas id="wasteTrendsChart"></canvas>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="rounded-lg border bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold">Recent Activities</h3>
        <p class="text-sm text-gray-500">Latest waste collection activities</p>
        <div class="mt-4 space-y-4">
            <?php if (empty($recent_activities)): ?>
            <p class="text-center text-gray-500 py-4">No recent activities found.</p>
            <?php else: ?>
            <?php foreach ($recent_activities as $activity): ?>
            <div class="flex items-start gap-4">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100">
                    <i class="fas fa-truck text-primary-600"></i>
                </div>
                <div class="flex-1 space-y-1">
                    <p class="text-sm font-medium">
                        <?php echo htmlspecialchars($activity['user_name']); ?> (<?php echo ucfirst($activity['role']); ?>)
                    </p>
                    <p class="text-xs text-gray-500">
                        Collected <?php echo htmlspecialchars($activity['waste_type']); ?> - <?php echo $activity['weight']; ?> kg
                    </p>
                    <p class="text-xs text-gray-500">
                        <?php echo date('M j, Y, g:i a', strtotime($activity['collection_date'])); ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Inventory Overview -->
    <div class="rounded-lg border bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold">Inventory Overview</h3>
        <p class="text-sm text-gray-500">Distribution of waste in storage facilities</p>
        <div class="mt-4 space-y-4">
            <?php foreach ($inventory_overview as $inventory): ?>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium"><?php echo htmlspecialchars($inventory['waste_type']); ?> (<?php echo ucfirst($inventory['status']); ?>)</span>
                    <span class="text-sm text-gray-500"><?php echo number_format($inventory['total_weight'], 1); ?> kg</span>
                </div>
                <div class="h-2 w-full rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-primary-600" style="width: <?php echo ($inventory['total_weight'] / $total_waste_collected) * 100; ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('wasteTrendsChart').getContext('2d');
        const labels = <?php echo json_encode(array_column($waste_trends, 'collection_day')); ?>;
        const data = <?php echo json_encode(array_column($waste_trends, 'total_weight')); ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Waste Collected (kg)',
                    data: data,
                    backgroundColor: 'rgba(34, 197, 94, 0.2)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
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