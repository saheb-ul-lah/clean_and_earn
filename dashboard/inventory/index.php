<?php
$page_title = "Inventory Management";

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

$stmt = $pdo->query("SELECT id, name FROM waste_types ORDER BY name");
$waste_types = $stmt->fetchAll();

$filter_waste_type = $_GET['waste_type'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_sort = $_GET['sort'] ?? 'created_at_desc';

$query = "
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
";

$params = [$user_id];

if (!empty($filter_waste_type)) {
    $query .= " AND si.waste_type_id = ?";
    $params[] = $filter_waste_type;
}

if (!empty($filter_status)) {
    $query .= " AND si.status = ?";
    $params[] = $filter_status;
}

// sorting
switch ($filter_sort) {
    case 'weight_asc':
        $query .= " ORDER BY si.weight ASC";
        break;
    case 'weight_desc':
        $query .= " ORDER BY si.weight DESC";
        break;
    case 'created_at_asc':
        $query .= " ORDER BY si.created_at ASC";
        break;
    case 'created_at_desc':
    default:
        $query .= " ORDER BY si.created_at DESC";
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$inventory_items = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_inventory'])) {
        $waste_type_id = $_POST['waste_type_id'] ?? '';
        $waste_subtype_id = $_POST['waste_subtype_id'] ?? '';
        $weight = $_POST['weight'] ?? '';
        
        if (empty($waste_type_id) || empty($weight)) {
            $error_message = 'Waste type and weight are required';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO storage_inventory 
                    (storage_id, waste_type_id, waste_subtype_id, weight, status) 
                    VALUES (?, ?, ?, ?, 'available')
                ");
                $stmt->execute([$user_id, $waste_type_id, $waste_subtype_id ?: null, $weight]);
                
                $success_message = 'Inventory added successfully';
                
                header('Location: ./index.php');
                exit;
            } catch (Exception $e) {
                $error_message = 'Failed to add inventory: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['update_inventory'])) {
        $inventory_id = $_POST['inventory_id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        if (empty($inventory_id) || empty($status)) {
            $error_message = 'Invalid request';
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE storage_inventory 
                    SET status = ? 
                    WHERE id = ? AND storage_id = ?
                ");
                $stmt->execute([$status, $inventory_id, $user_id]);
                
                $success_message = 'Inventory status updated successfully';
                
                header('Location: ./index.php');
                exit;
            } catch (Exception $e) {
                $error_message = 'Failed to update inventory: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_inventory'])) {
        $inventory_id = $_POST['inventory_id'] ?? '';
        
        if (empty($inventory_id)) {
            $error_message = 'Invalid request';
        } else {
            try {
                $stmt = $pdo->prepare("
                    DELETE FROM storage_inventory 
                    WHERE id = ? AND storage_id = ? AND status = 'available'
                ");
                $stmt->execute([$inventory_id, $user_id]);
                
                if ($stmt->rowCount() > 0) {
                    $success_message = 'Inventory deleted successfully';
                } else {
                    $error_message = 'Cannot delete inventory that is not available';
                }
                
                header('Location: ./index.php');
                exit;
            } catch (Exception $e) {
                $error_message = 'Failed to delete inventory: ' . $e->getMessage();
            }
        }
    }
}

include_once '../../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Inventory Management</h1>
        <div>
            <button type="button" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700" onclick="openAddModal()">
                <i class="fas fa-plus mr-2"></i> Add Inventory
            </button>
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
    
    <div class="rounded-lg border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-medium">Filter Inventory</h2>
        <form method="GET" action="" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <label for="waste-type" class="block text-sm font-medium text-gray-700">Waste Type</label>
                <select id="waste-type" name="waste_type" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                    <option value="">All Types</option>
                    <?php foreach ($waste_types as $type): ?>
                    <option value="<?php echo $type['id']; ?>" <?php echo $filter_waste_type == $type['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                    <option value="">All Statuses</option>
                    <option value="available" <?php echo $filter_status === 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="reserved" <?php echo $filter_status === 'reserved' ? 'selected' : ''; ?>>Reserved</option>
                    <option value="sold" <?php echo $filter_status === 'sold' ? 'selected' : ''; ?>>Sold</option>
                </select>
            </div>
            <div>
                <label for="sort" class="block text-sm font-medium text-gray-700">Sort By</label>
                <select id="sort" name="sort" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                    <option value="created_at_desc" <?php echo $filter_sort === 'created_at_desc' ? 'selected' : ''; ?>>Date (Newest First)</option>
                    <option value="created_at_asc" <?php echo $filter_sort === 'created_at_asc' ? 'selected' : ''; ?>>Date (Oldest First)</option>
                    <option value="weight_desc" <?php echo $filter_sort === 'weight_desc' ? 'selected' : ''; ?>>Weight (Highest First)</option>
                    <option value="weight_asc" <?php echo $filter_sort === 'weight_asc' ? 'selected' : ''; ?>>Weight (Lowest First)</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
    
    <!-- Inventory List -->
    <div class="rounded-lg border bg-white shadow-sm">
        <div class="p-6">
            <h2 class="text-lg font-medium">Inventory Items</h2>
            <p class="text-sm text-gray-500">
                Manage your waste inventory
            </p>
        </div>
        
        <?php if (empty($inventory_items)): ?>
        <div class="flex flex-col items-center justify-center py-10">
            <p class="text-gray-500 mb-4">No inventory items found</p>
            <button type="button" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700" onclick="openAddModal()">
                <i class="fas fa-plus mr-2"></i> Add Inventory
            </button>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Added</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($inventory_items as $item): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">
                                <?php echo htmlspecialchars($item['waste_type_name']); ?>
                            </div>
                            <?php if ($item['waste_subtype_name']): ?>
                            <div class="text-xs text-gray-500">
                                <?php echo htmlspecialchars($item['waste_subtype_name']); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $item['weight']; ?> kg
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 <?php echo getStatusBadgeClass($item['status']); ?>">
                                <?php echo ucfirst($item['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <?php if ($item['status'] === 'available'): ?>
                            <button type="button" class="text-blue-600 hover:text-blue-900 mr-3" onclick="openUpdateModal('<?php echo $item['id']; ?>', '<?php echo $item['status']; ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="text-red-600 hover:text-red-900" onclick="openDeleteModal('<?php echo $item['id']; ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php else: ?>
                            <span class="text-gray-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Inventory Modal -->
<div id="add-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">Add Inventory</h3>
            <form method="POST" action="" id="add-form">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label for="add-waste-type" class="text-sm font-medium">Waste Type</label>
                        <select id="add-waste-type" name="waste_type_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                            <option value="">Select Waste Type</option>
                            <?php foreach ($waste_types as $type): ?>
                            <option value="<?php echo $type['id']; ?>">
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="add-waste-subtype" class="text-sm font-medium">Waste Subtype (Optional)</label>
                        <select id="add-waste-subtype" name="waste_subtype_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                            <option value="">Select Waste Subtype</option>
                            <!-- Subtypes will be loaded dynamically -->
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="add-weight" class="text-sm font-medium">Weight (kg)</label>
                        <input type="number" id="add-weight" name="weight" step="0.01" min="0.1" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" onclick="closeAddModal()">
                        Cancel
                    </button>
                    <button type="submit" name="add_inventory" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                        Add Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Inventory Modal -->
<div id="update-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">Update Inventory Status</h3>
            <form method="POST" action="" id="update-form">
                <input type="hidden" name="inventory_id" id="update-inventory-id">
                
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label for="update-status" class="text-sm font-medium">Status</label>
                        <select id="update-status" name="status" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" onclick="closeUpdateModal()">
                        Cancel
                    </button>
                    <button type="submit" name="update_inventory" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">Delete Inventory</h3>
            <p class="text-sm text-gray-500">
                Are you sure you want to delete this inventory item? This action cannot be undone.
            </p>
            
            <form method="POST" action="" id="delete-form">
                <input type="hidden" name="inventory_id" id="delete-inventory-id">
                
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" onclick="closeDeleteModal()">
                        Cancel
                    </button>
                    <button type="submit" name="delete_inventory" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('add-modal').classList.remove('hidden');
    }
    
    function closeAddModal() {
        document.getElementById('add-modal').classList.add('hidden');
    }
    
    function openUpdateModal(inventoryId, currentStatus) {
        document.getElementById('update-inventory-id').value = inventoryId;
        document.getElementById('update-status').value = currentStatus;
        document.getElementById('update-modal').classList.remove('hidden');
    }
    
    function closeUpdateModal() {
        document.getElementById('update-modal').classList.add('hidden');
    }
    
    function openDeleteModal(inventoryId) {
        document.getElementById('delete-inventory-id').value = inventoryId;
        document.getElementById('delete-modal').classList.remove('hidden');
    }
    
    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.add('hidden');
    }
    
    document.getElementById('add-waste-type').addEventListener('change', function() {
        const wasteTypeId = this.value;
        const wasteSubtypeSelect = document.getElementById('add-waste-subtype');
        
        // Clear current options
        wasteSubtypeSelect.innerHTML = '<option value="">Select Waste Subtype</option>';
        
        if (wasteTypeId) {
            fetch(`/inventory/fetch-subtypes.php?waste_type_id=${wasteTypeId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(subtype => {
                        const option = document.createElement('option');
                        option.value = subtype.id;
                        option.textContent = subtype.name;
                        wasteSubtypeSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching waste subtypes:', error));
        }
    });
</script>

<?php
include_once '../../includes/dashboard_footer.php';
?>