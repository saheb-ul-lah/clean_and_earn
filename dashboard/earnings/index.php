<?php
//
$page_title = "Earnings";


require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireLogin();


if ($_SESSION['user_role'] !== 'collector') {
    header('Location: /dashboard/unauthorized.php');
    exit;
}


$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_collections,
        SUM(actual_weight) as total_weight,
        SUM(actual_weight * 5) as total_earnings
    FROM waste_collections
    WHERE collector_id = ? AND status = 'collected'
");
$stmt->execute([$user_id]);
$earnings_summary = $stmt->fetch();

//
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(collection_date, '%Y-%m') as month,
        COUNT(*) as collections,
        SUM(actual_weight) as weight,
        SUM(actual_weight * 5) as earnings
    FROM waste_collections
    WHERE collector_id = ? AND status = 'collected'
    AND collection_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(collection_date, '%Y-%m')
    ORDER BY month DESC
");
$stmt->execute([$user_id]);
$monthly_earnings = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT 
        wt.id,
        wt.name,
        COUNT(*) as collections,
        SUM(wc.actual_weight) as weight,
        SUM(wc.actual_weight * 5) as earnings
    FROM waste_collections wc
    JOIN waste_listings wl ON wc.listing_id = wl.id
    JOIN waste_types wt ON wl.waste_type_id = wt.id
    WHERE wc.collector_id = ? AND wc.status = 'collected'
    GROUP BY wt.id, wt.name
    ORDER BY earnings DESC
");
$stmt->execute([$user_id]);
$earnings_by_type = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT 
        wc.id,
        wc.actual_weight,
        wc.collection_date,
        wl.waste_type_id,
        wl.waste_subtype_id,
        u.name as household_name
    FROM waste_collections wc
    JOIN waste_listings wl ON wc.listing_id = wl.id
    JOIN users u ON wl.user_id = u.id
    WHERE wc.collector_id = ? AND wc.status = 'collected'
    ORDER BY wc.collection_date DESC
    LIMIT 10
");
$stmt->execute([$user_id]);
$recent_earnings = $stmt->fetchAll();


include_once '../../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Earnings</h1>
        <div>
            <button type="button" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                <i class="fas fa-download mr-2"></i> Download Report
            </button>
        </div>
    </div>
    
    <!-- Earnings Summary -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Earnings</h3>
                <i class="fas fa-rupee-sign text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold">₹<?php echo number_format($earnings_summary['total_earnings'] ?? 0, 2); ?></div>
            <p class="text-xs text-gray-500">
                From <?php echo number_format($earnings_summary['total_collections'] ?? 0); ?> collections
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Weight Collected</h3>
                <i class="fas fa-weight text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($earnings_summary['total_weight'] ?? 0, 1); ?> kg</div>
            <p class="text-xs text-gray-500">
                Average of <?php echo $earnings_summary['total_collections'] > 0 ? number_format(($earnings_summary['total_weight'] ?? 0) / $earnings_summary['total_collections'], 1) : 0; ?> kg per collection
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">This Month's Earnings</h3>
                <i class="fas fa-calendar text-gray-400"></i>
            </div>
            <?php
            $current_month = date('Y-m');
            $this_month_earnings = 0;
            foreach ($monthly_earnings as $month) {
                if ($month['month'] === $current_month) {
                    $this_month_earnings = $month['earnings'];
                    break;
                }
            }
            ?>
            <div class="text-2xl font-bold">₹<?php echo number_format($this_month_earnings, 2); ?></div>
            <p class="text-xs text-gray-500">
                <?php echo date('F Y'); ?>
            </p>
        </div>
    </div>
    
    <!-- Earnings Charts -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Monthly Earnings Chart -->
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-medium">Monthly Earnings</h2>
            <p class="text-sm text-gray-500">
                Your earnings over the past 6 months
            </p>
            <div class="mt-4 h-80">
                <canvas id="monthlyEarningsChart"></canvas>
            </div>
        </div>
        
        <!-- Earnings by Waste Type Chart -->
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-medium">Earnings by Waste Type</h2>
            <p class="text-sm text-gray-500">
                Distribution of your earnings by waste type
            </p>
            <div class="mt-4 h-80">
                <canvas id="wasteTypeEarningsChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Recent Earnings -->
    <div class="rounded-lg border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-medium">Recent Earnings</h2>
        <p class="text-sm text-gray-500">
            Your most recent collection earnings
        </p>
        
        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Household</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Earnings</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($recent_earnings)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                            No earnings found
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recent_earnings as $earning): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($earning['collection_date'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">
                                <?php echo getWasteTypeName($earning['waste_type_id'], $pdo); ?>
                            </div>
                            <?php if ($earning['waste_subtype_id']): ?>
                            <div class="text-xs text-gray-500">
                                <?php echo getWasteSubtypeName($earning['waste_subtype_id'], $pdo); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($earning['household_name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $earning['actual_weight']; ?> kg
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-green-600">
                            ₹<?php echo number_format($earning['actual_weight'] * 5, 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Payment Information -->
    <div class="rounded-lg border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-medium">Payment Information</h2>
        <p class="text-sm text-gray-500">
            Your payment details and schedule
        </p>
        
        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <h3 class="text-sm font-medium">Payment Method</h3>
                <div class="mt-4 rounded-md border p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100">
                                <i class="fas fa-university text-primary-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium">Bank Transfer</p>
                                <p class="text-xs text-gray-500">XXXX XXXX XXXX 1234</p>
                            </div>
                        </div>
                        <button type="button" class="text-sm text-primary-600 hover:text-primary-700">
                            Change
                        </button>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-sm font-medium">Payment Schedule</h3>
                <div class="mt-4 rounded-md border p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100">
                                <i class="fas fa-calendar-alt text-primary-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium">Weekly Payments</p>
                                <p class="text-xs text-gray-500">Every Monday</p>
                            </div>
                        </div>
                        <button type="button" class="text-sm text-primary-600 hover:text-primary-700">
                            Change
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-6">
            <h3 class="text-sm font-medium">Upcoming Payments</h3>
            <div class="mt-4 rounded-md border p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium">Next Payment</p>
                        <p class="text-xs text-gray-500"><?php echo date('F j, Y', strtotime('next Monday')); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-green-600">₹<?php echo number_format($this_month_earnings, 2); ?></p>
                        <p class="text-xs text-gray-500">Estimated</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const monthlyCtx = document.getElementById('monthlyEarningsChart').getContext('2d');
        
        const months = [];
        const earnings = [];
        const collections = [];
        
        <?php foreach (array_reverse($monthly_earnings) as $month): ?>
        months.push('<?php echo date("M Y", strtotime($month["month"] . "-01")); ?>');
        earnings.push(<?php echo $month['earnings']; ?>);
        collections.push(<?php echo $month['collections']; ?>);
        <?php endforeach; ?>
        
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Earnings (₹)',
                        data: earnings,
                        backgroundColor: 'rgba(34, 197, 94, 0.2)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 2
                    },
                    {
                        label: 'Collections',
                        data: collections,
                        type: 'line',
                        fill: false,
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Earnings (₹)'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: 'Collections'
                        }
                    }
                }
            }
        });
        
        //
        const wasteTypeCtx = document.getElementById('wasteTypeEarningsChart').getContext('2d');
        
        
        const wasteTypes = [];
        const wasteTypeEarnings = [];
        const backgroundColors = [
            'rgba(34, 197, 94, 0.2)',
            'rgba(59, 130, 246, 0.2)',
            'rgba(245, 158, 11, 0.2)',
            'rgba(239, 68, 68, 0.2)',
            'rgba(139, 92, 246, 0.2)',
            'rgba(20, 184, 166, 0.2)'
        ];
        const borderColors = [
            'rgba(34, 197, 94, 1)',
            'rgba(59, 130, 246, 1)',
            'rgba(245, 158, 11, 1)',
            'rgba(239, 68, 68, 1)',
            'rgba(139, 92, 246, 1)',
            'rgba(20, 184, 166, 1)'
        ];
        
        <?php foreach ($earnings_by_type as $index => $type): ?>
        wasteTypes.push('<?php echo $type['name']; ?>');
        wasteTypeEarnings.push(<?php echo $type['earnings']; ?>);
        <?php endforeach; ?>
        
        new Chart(wasteTypeCtx, {
            type: 'doughnut',
            data: {
                labels: wasteTypes,
                datasets: [{
                    data: wasteTypeEarnings,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    });
</script>

<?php

include_once '../../includes/dashboard_footer.php';
?>