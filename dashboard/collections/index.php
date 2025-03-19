<?php
//
$page_title = "My Collections";


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
        wc.id,
        wc.listing_id,
        wc.status,
        wc.collection_date,
        wc.created_at,
        wl.waste_type_id,
        wl.waste_subtype_id,
        wl.weight,
        wl.pickup_date,
        wl.pickup_time_slot,
        wl.pickup_address,
        u.name as household_name,
        u.phone as household_phone
    FROM waste_collections wc
    JOIN waste_listings wl ON wc.listing_id = wl.id
    JOIN users u ON wl.user_id = u.id
    WHERE wc.collector_id = ? AND wc.status IN ('assigned', 'in_progress')
    ORDER BY wl.pickup_date ASC, wl.pickup_time_slot ASC
");
$stmt->execute([$user_id]);
$assigned_collections = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT 
        wc.id,
        wc.listing_id,
        wc.status,
        wc.actual_weight,
        wc.collection_date,
        wc.created_at,
        wl.waste_type_id,
        wl.waste_subtype_id,
        wl.weight,
        wl.pickup_address,
        u.name as household_name
    FROM waste_collections wc
    JOIN waste_listings wl ON wc.listing_id = wl.id
    JOIN users u ON wl.user_id = u.id
    WHERE wc.collector_id = ? AND wc.status = 'collected'
    ORDER BY wc.collection_date DESC
    LIMIT 20
");
$stmt->execute([$user_id]);
$completed_collections = $stmt->fetchAll();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $collection_id = $_POST['collection_id'] ?? '';
    $new_status = $_POST['new_status'] ?? '';
    $actual_weight = $_POST['actual_weight'] ?? '';
    
    if (empty($collection_id) || empty($new_status)) {
        $error_message = 'Invalid request';
    } else {
        
        $stmt = $pdo->prepare("SELECT id FROM waste_collections WHERE id = ? AND collector_id = ?");
        $stmt->execute([$collection_id, $user_id]);
        $collection = $stmt->fetch();
        
        if (!$collection) {
            $error_message = 'Collection not found or you do not have permission to update it';
        } else {
            
            if ($new_status === 'collected' && empty($actual_weight)) {
                $error_message = 'Actual weight is required for completed collections';
            } else {
                try {
                    $pdo->beginTransaction();
                    
                    if ($new_status === 'collected') {
                        
                        $stmt = $pdo->prepare("
                            UPDATE waste_collections 
                            SET status = ?, actual_weight = ?, collection_date = NOW() 
                            WHERE id = ?
                        ");
                        $stmt->execute([$new_status, $actual_weight, $collection_id]);
                        
                        $stmt = $pdo->prepare("
                            SELECT wl.id, wl.user_id, wl.waste_type_id
                            FROM waste_collections wc
                            JOIN waste_listings wl ON wc.listing_id = wl.id
                            WHERE wc.id = ?
                        ");
                        $stmt->execute([$collection_id]);
                        $listing = $stmt->fetch();
                        
                        // Update listing status
                        $stmt = $pdo->prepare("UPDATE waste_listings SET status = 'completed' WHERE id = ?");
                        $stmt->execute([$listing['id']]);
                        
                        // Calculate points
                        $points = calculatePoints($listing['waste_type_id'], $actual_weight, $pdo);
                        
                        addPointsToUser(
                            $listing['user_id'],
                            $points,
                            'earned',
                            $listing['id'],
                            'listing',
                            'Points earned for recycling waste',
                            $pdo
                        );
                    } else {
                        $stmt = $pdo->prepare("UPDATE waste_collections SET status = ? WHERE id = ?");
                        $stmt->execute([$new_status, $collection_id]);
                    }
                    
                    $pdo->commit();
                    $success_message = 'Collection status updated successfully';
                    
                    header('Location: /dashboard/collections/index.php');
                    exit;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error_message = 'Failed to update collection status: ' . $e->getMessage();
                }
            }
        }
    }
}

include_once '../../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">My Collections</h1>
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
    
    <div class="rounded-lg border bg-white shadow-sm">
        <div class="border-b">
            <nav class="flex" aria-label="Tabs">
                <button type="button" class="active-tab-button border-b-2 border-primary-600 px-4 py-4 text-sm font-medium text-primary-600" data-tab="assigned-tab">
                    Assigned Collections
                </button>
                <button type="button" class="tab-button border-b-2 border-transparent px-4 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700" data-tab="completed-tab">
                    Completed Collections
                </button>
            </nav>
        </div>
        
        <div id="assigned-tab" class="tab-content p-6">
            <h2 class="text-lg font-bold">Assigned Collections</h2>
            <p class="text-sm text-gray-500">
                Manage your assigned waste collections
            </p>
            
            <?php if (empty($assigned_collections)): ?>
            <div class="mt-6 flex flex-col items-center justify-center py-10">
                <p class="text-gray-500 mb-4">You don't have any assigned collections</p>
                <p class="text-sm text-gray-500">
                    New collections will appear here when they are assigned to you
                </p>
            </div>
            <?php else: ?>
            <div class="mt-6 space-y-6">
                <?php foreach ($assigned_collections as $collection): ?>
                <div class="rounded-lg border p-4 transition-all hover:shadow-md">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-medium"><?php echo getWasteTypeName($collection['waste_type_id'], $pdo); ?></h3>
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 <?php echo getStatusBadgeClass($collection['status']); ?>">
                                    <?php echo ucfirst($collection['status']); ?>
                                </span>
                            </div>
                            <?php if ($collection['waste_subtype_id']): ?>
                            <p class="text-sm text-gray-500"><?php echo getWasteSubtypeName($collection['waste_subtype_id'], $pdo); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2 md:mt-0 text-sm">
                            <div class="flex items-center gap-1">
                                <i class="fas fa-calendar text-gray-400"></i>
                                <span><?php echo date('M j, Y', strtotime($collection['pickup_date'])); ?></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="fas fa-clock text-gray-400"></i>
                                <span><?php echo $collection['pickup_time_slot']; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <h4 class="text-sm font-medium">Household Details</h4>
                            <div class="mt-2 space-y-2">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user text-gray-400"></i>
                                    <span class="text-sm"><?php echo htmlspecialchars($collection['household_name']); ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-phone text-gray-400"></i>
                                    <span class="text-sm"><?php echo htmlspecialchars($collection['household_phone']); ?></span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-map-marker-alt text-gray-400 mt-1"></i>
                                    <span class="text-sm"><?php echo htmlspecialchars($collection['pickup_address']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium">Waste Details</h4>
                            <div class="mt-2 space-y-2">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-weight text-gray-400"></i>
                                    <span class="text-sm">Estimated Weight: <?php echo $collection['weight']; ?> kg</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-info-circle text-gray-400"></i>
                                    <span class="text-sm">Collection ID: #<?php echo $collection['id']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 border-t pt-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <a href="https://maps.google.com/?q=<?php echo urlencode($collection['pickup_address']); ?>" target="_blank" class="inline-flex items-center rounded-md bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100">
                                    <i class="fas fa-directions mr-1"></i> Get Directions
                                </a>
                                <a href="tel:<?php echo $collection['household_phone']; ?>" class="inline-flex items-center rounded-md bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100">
                                    <i class="fas fa-phone mr-1"></i> Call Household
                                </a>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <?php if ($collection['status'] === 'assigned'): ?>
                                <form method="POST" action="" class="inline-block">
                                    <input type="hidden" name="collection_id" value="<?php echo $collection['id']; ?>">
                                    <input type="hidden" name="new_status" value="in_progress">
                                    <button type="submit" name="update_status" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-700">
                                        <i class="fas fa-truck mr-1"></i> Start Collection
                                    </button>
                                </form>
                                <?php elseif ($collection['status'] === 'in_progress'): ?>
                                <button type="button" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-700" onclick="openCollectionModal('<?php echo $collection['id']; ?>')">
                                    <i class="fas fa-check-circle mr-1"></i> Complete Collection
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div id="completed-tab" class="tab-content p-6 hidden">
            <h2 class="text-lg font-bold">Completed Collections</h2>
            <p class="text-sm text-gray-500">
                History of your completed waste collections
            </p>
            
            <?php if (empty($completed_collections)): ?>
            <div class="mt-6 flex flex-col items-center justify-center py-10">
                <p class="text-gray-500 mb-4">You don't have any completed collections</p>
            </div>
            <?php else: ?>
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Collection Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Household</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($completed_collections as $collection): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M j, Y', strtotime($collection['collection_date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">
                                    <?php echo getWasteTypeName($collection['waste_type_id'], $pdo); ?>
                                </div>
                                <?php if ($collection['waste_subtype_id']): ?>
                                <div class="text-xs text-gray-500">
                                    <?php echo getWasteSubtypeName($collection['waste_subtype_id'], $pdo); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($collection['household_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $collection['actual_weight']; ?> kg
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 <?php echo getStatusBadgeClass($collection['status']); ?>">
                                    <?php echo ucfirst($collection['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="/dashboard/collections/view.php?id=<?php echo $collection['id']; ?>" class="text-primary-600 hover:text-primary-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="collection-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">Complete Collection</h3>
            <form method="POST" action="" id="complete-collection-form">
                <input type="hidden" name="collection_id" id="modal-collection-id">
                <input type="hidden" name="new_status" value="collected">
                
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label for="actual-weight" class="text-sm font-medium">Actual Weight (kg)</label>
                        <input type="number" id="actual-weight" name="actual_weight" step="0.01" min="0.1" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                        <p class="text-xs text-gray-500">Enter the actual weight of the collected waste</p>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="collection-notes" class="text-sm font-medium">Notes (Optional)</label>
                        <textarea id="collection-notes" name="notes" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"></textarea>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" onclick="closeCollectionModal()">
                        Cancel
                    </button>
                    <button type="submit" name="update_status" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                        Complete Collection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const tabButtons = document.querySelectorAll('.tab-button, .active-tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                
                tabButtons.forEach(btn => {
                    btn.classList.remove('border-primary-600', 'text-primary-600');
                    btn.classList.add('border-transparent', 'text-gray-500', 'hover:border-gray-300', 'hover:text-gray-700');
                });
                
                
                this.classList.remove('border-transparent', 'text-gray-500', 'hover:border-gray-300', 'hover:text-gray-700');
                this.classList.add('border-primary-600', 'text-primary-600');
                
                
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });
                
                
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.remove('hidden');
            });
        });
    });
    
    // Collection modal functions
    function openCollectionModal(collectionId) {
        document.getElementById('modal-collection-id').value = collectionId;
        document.getElementById('collection-modal').classList.remove('hidden');
    }
    
    function closeCollectionModal() {
        document.getElementById('collection-modal').classList.add('hidden');
    }
</script>

<?php
include_once '../../includes/dashboard_footer.php';
?>