<?php
$page_title = "Storage Management";

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireLogin();

if ($_SESSION['user_role'] !== 'storage') {
    header('Location: /dashboard/unauthorized.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT 
        SUM(weight) as total_weight,
        SUM(CASE WHEN status = 'available' THEN weight ELSE 0 END) as available_weight,
        SUM(CASE WHEN status = 'reserved' THEN weight ELSE 0 END) as reserved_weight,
        SUM(CASE WHEN status = 'sold' THEN weight ELSE 0 END) as sold_weight
    FROM storage_inventory
    WHERE storage_id = ?
");
$stmt->execute([$user_id]);
$storage_summary = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT 
        wt.id,
        wt.name,
        SUM(si.weight) as total_weight,
        SUM(CASE WHEN si.status = 'available' THEN si.weight ELSE 0 END) as available_weight,
        SUM(CASE WHEN si.status = 'reserved' THEN si.weight ELSE 0 END) as reserved_weight,
        SUM(CASE WHEN si.status = 'sold' THEN si.weight ELSE 0 END) as sold_weight
    FROM storage_inventory si
    JOIN waste_types wt ON si.waste_type_id = wt.id
    WHERE si.storage_id = ?
    GROUP BY wt.id, wt.name
    ORDER BY total_weight DESC
");
$stmt->execute([$user_id]);
$inventory_by_type = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT 
        wc.id,
        wc.listing_id,
        wc.collector_id,
        wc.actual_weight,
        wc.status,
        wc.collection_date,
        wl.waste_type_id,
        wl.waste_subtype_id,
        u_collector.name as collector_name,
        u_household.name as household_name
    FROM waste_collections wc
    JOIN waste_listings wl ON wc.listing_id = wl.id
    JOIN users u_collector ON wc.collector_id = u_collector.id
    JOIN users u_household ON wl.user_id = u_household.id
    WHERE wc.status = 'collected'
    ORDER BY wc.collection_date DESC
    LIMIT 10
");
$stmt->execute();
$pending_deliveries = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT 
        si.id,
        si.waste_type_id,
        si.waste_subtype_id,
        si.weight,
        si.status,
        si.created_at,
        wt.name as waste_type_name,
        ws.name as waste_subtype_name
    FROM storage_inventory si
    JOIN waste_types wt ON si.waste_type_id = wt.id
    LEFT JOIN waste_subtypes ws ON si.waste_subtype_id = ws.id
    WHERE si.storage_id = ?
    ORDER BY si.created_at DESC
    LIMIT 10
");
$stmt->execute([$user_id]);
$recent_inventory = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receive_delivery'])) {
    $collection_id = $_POST['collection_id'] ?? '';
    $waste_type_id = $_POST['waste_type_id'] ?? '';
    $waste_subtype_id = $_POST['waste_subtype_id'] ?? '';
    $weight = $_POST['weight'] ?? '';
    
    if (empty($collection_id) || empty($waste_type_id) || empty($weight)) {
        $error_message = 'Invalid request';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM waste_collections WHERE id = ? AND status = 'collected'");
        $stmt->execute([$collection_id]);
        $collection = $stmt->fetch();
        
        if (!$collection) {
            $error_message = 'Collection not found or already processed';
        } else {
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE waste_collections SET status = 'delivered' WHERE id = ?");
                $stmt->execute([$collection_id]);
                
                $stmt = $pdo->prepare("
                    INSERT INTO storage_inventory 
                    (storage_id, waste_type_id, waste_subtype_id, weight, collection_id, status) 
                    VALUES (?, ?, ?, ?, ?, 'available')
                ");
                $stmt->execute([$user_id, $waste_type_id, $waste_subtype_id ?: null, $weight, $collection_id]);
                
                $pdo->commit();
                $success_message = 'Delivery received and added to inventory successfully';
                
                header('Location: /dashboard/storage/index.php');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error_message = 'Failed to process delivery: ' . $e->getMessage();
            }
        }
    }
}

include_once '../../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Storage Management</h1>
        <div>
            <a href="../inventory/index.php" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                <i class="fas fa-box mr-2"></i> Manage Inventory
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
    
    <!-- Storage Summary -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Total Inventory</h3>
                <i class="fas fa-warehouse text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($storage_summary['total_weight'] ?? 0, 1); ?> kg</div>
            <p class="text-xs text-gray-500">
                Total waste in storage
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Available Inventory</h3>
                <i class="fas fa-box-open text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($storage_summary['available_weight'] ?? 0, 1); ?> kg</div>
            <p class="text-xs text-gray-500">
                Available for purchase
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Reserved Inventory</h3>
                <i class="fas fa-lock text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($storage_summary['reserved_weight'] ?? 0, 1); ?> kg</div>
            <p class="text-xs text-gray-500">
                Reserved by buyers
            </p>
        </div>
        <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-sm font-medium">Sold Inventory</h3>
                <i class="fas fa-shopping-cart text-gray-400"></i>
            </div>
            <div class="text-2xl font-bold"><?php echo number_format($storage_summary['sold_weight'] ?? 0, 1); ?> kg</div>
            <p class="text-xs text-gray-500">
                Sold to buyers
            </p>
        </div>
    </div>
    
    <!-- Inventory Chart -->
    <div class="rounded-lg border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-medium">Inventory by Waste Type</h2>
        <p class="text-sm text-gray-500">
            Distribution of your inventory by waste type
        </p>
        <div class="mt-4 h-80">
            <canvas id="inventoryChart"></canvas>
        </div>
    </div>
    
    <!-- Pending Deliveries -->
    <div class="rounded-lg border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-medium">Pending Deliveries</h2>
        <p class="text-sm text-gray-500">
            Waste collections ready to be received into storage
        </p>
        
        <?php if (empty($pending_deliveries)): ?>
        <div class="mt-6 flex flex-col items-center justify-center py-10">
            <p class="text-gray-500 mb-4">No pending deliveries</p>
            <p class="text-sm text-gray-500">
                New deliveries will appear here when collectors complete their collections
            </p>
        </div>
        <?php else: ?>
        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Collection Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Collector</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Household</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($pending_deliveries as $delivery): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($delivery['collection_date'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">
                                <?php echo getWasteTypeName($delivery['waste_type_id'], $pdo); ?>
                            </div>
                            <?php if ($delivery['waste_subtype_id']): ?>
                            <div class="text-xs text-gray-500">
                                <?php echo getWasteSubtypeName($delivery['waste_subtype_id'], $pdo); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $delivery['actual_weight']; ?> kg
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($delivery['collector_name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($delivery['household_name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button type="button" class="text-primary-600 hover:text-primary-900" onclick="openReceiveModal('<?php echo $delivery['id']; ?>', '<?php echo $delivery['waste_type_id']; ?>', '<?php echo $delivery['waste_subtype_id']; ?>', '<?php echo $delivery['actual_weight']; ?>')">
                                <i class="fas fa-check-circle mr-1"></i> Receive
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Recent Inventory Changes -->
    <div class="rounded-lg border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-medium">Recent Inventory Changes</h2>
        <p class="text-sm text-gray-500">
            Recent changes to your inventory
        </p>
        
        <?php if (empty($recent_inventory)): ?>
        <div class="mt-6 flex flex-col items-center justify-center py-10">
            <p class="text-gray-500 mb-4">No recent inventory changes</p>
        </div>
        <?php else: ?>
        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($recent_inventory as $inventory): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($inventory['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">
                                <?php echo htmlspecialchars($inventory['waste_type_name']); ?>
                            </div>
                            <?php if ($inventory['waste_subtype_name']): ?>
                            <div class="text-xs text-gray-500">
                                <?php echo htmlspecialchars($inventory['waste_subtype_name']); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $inventory['weight']; ?> kg
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 <?php echo getStatusBadgeClass($inventory['status']); ?>">
                                <?php echo ucfirst($inventory['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Receive Delivery Modal -->
<div id="receive-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">Receive Delivery</h3>
            <form method="POST" action="" id="receive-form">
                <input type="hidden" name="collection_id" id="modal-collection-id">
                <input type="hidden" name="waste_type_id" id="modal-waste-type-id">
                <input type="hidden" name="waste_subtype_id" id="modal-waste-subtype-id">
                
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label for="weight" class="text-sm font-medium">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" step="0.01" min="0.1" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                        <p class="text-xs text-gray-500">Enter the actual weight of the received waste</p>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="notes" class="text-sm font-medium">Notes (Optional)</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"></textarea>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" onclick="closeReceiveModal()">
                        Cancel
                    </button>
                    <button type="submit" name="receive_delivery" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                        Receive Delivery
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('inventoryChart').getContext('2d');
        
        const wasteTypes = [];
        const availableWeights = [];
        const reservedWeights = [];
        const soldWeights = [];
        
        <?php foreach ($inventory_by_type as $type): ?>
        wasteTypes.push('<?php echo $type['name']; ?>');
        availableWeights.push(<?php echo $type['available_weight']; ?>);
        reservedWeights.push(<?php echo $type['reserved_weight']; ?>);
        soldWeights.push(<?php echo $type['sold_weight']; ?>);
        <?php endforeach; ?>
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: wasteTypes,
                datasets: [
                    {
                        label: 'Available',
                        data: availableWeights,
                        backgroundColor: 'rgba(34, 197, 94, 0.2)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 2
                    },
                    {
                        label: 'Reserved',
                        data: reservedWeights,
                        backgroundColor: 'rgba(245, 158, 11, 0.2)',
                        borderColor: 'rgba(245, 158, 11, 1)',
                        borderWidth: 2
                    },
                    {
                        label: 'Sold',
                        data: soldWeights,
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true,
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
    
    function openReceiveModal(collectionId, wasteTypeId, wasteSubtypeId, weight) {
        document.getElementById('modal-collection-id').value = collectionId;
        document.getElementById('modal-waste-type-id').value = wasteTypeId;
        document.getElementById('modal-waste-subtype-id').value = wasteSubtypeId;
        document.getElementById('weight').value = weight;
        document.getElementById('receive-modal').classList.remove('hidden');
    }
    
    function closeReceiveModal() {
        document.getElementById('receive-modal').classList.add('hidden');
    }
</script>

<?php
include_once '../../includes/dashboard_footer.php';
?>