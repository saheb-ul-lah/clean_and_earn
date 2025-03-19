<?php
$page_title = "Waste Types";

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

$success_message = $error_message = '';
$waste_type_to_edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_waste_type'])) {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $rate_per_kg = floatval($_POST['rate_per_kg'] ?? 0);

    if (empty($name)) {
        $error_message = 'Name is required';
    } elseif ($rate_per_kg < 0) {
        $error_message = 'Rate per kg must be a positive number';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO waste_types (name, description, rate_per_kg) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$name, $description, $rate_per_kg]);

            $success_message = 'Waste type added successfully';
            header('Location: /dashboard/waste-types/index.php');
            exit;
        } catch (Exception $e) {
            $error_message = 'Failed to add waste type: ' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_waste_type'])) {
    $waste_type_id = $_POST['waste_type_id'] ?? '';
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $rate_per_kg = floatval($_POST['rate_per_kg'] ?? 0);

    if (empty($waste_type_id) || empty($name)) {
        $error_message = 'Waste type ID and name are required';
    } elseif ($rate_per_kg < 0) {
        $error_message = 'Rate per kg must be a positive number';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE waste_types 
                SET name = ?, description = ?, rate_per_kg = ? 
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $rate_per_kg, $waste_type_id]);

            $success_message = 'Waste type updated successfully';
            header('Location: /dashboard/waste-types/index.php');
            exit;
        } catch (Exception $e) {
            $error_message = 'Failed to update waste type: ' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_waste_type'])) {
    $waste_type_id = $_POST['waste_type_id'] ?? '';

    if (empty($waste_type_id)) {
        $error_message = 'Waste type ID is required';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM waste_subtypes WHERE waste_type_id = ?");
            $stmt->execute([$waste_type_id]);
            $result = $stmt->fetch();

            if ($result['count'] > 0) {
                $error_message = 'Cannot delete waste type: It has associated subtypes';
            } else {
                $stmt = $pdo->prepare("DELETE FROM waste_types WHERE id = ?");
                $stmt->execute([$waste_type_id]);

                $success_message = 'Waste type deleted successfully';
                header('Location: /dashboard/waste-types/index.php');
                exit;
            }
        } catch (Exception $e) {
            $error_message = 'Failed to delete waste type: ' . $e->getMessage();
        }
    }
}

if (isset($_GET['edit'])) {
    $waste_type_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM waste_types WHERE id = ?");
    $stmt->execute([$waste_type_id]);
    $waste_type_to_edit = $stmt->fetch();
}

$stmt = $pdo->query("
    SELECT 
        wt.id,
        wt.name,
        wt.description,
        wt.rate_per_kg,
        COUNT(ws.id) as subtypes_count
    FROM waste_types wt
    LEFT JOIN waste_subtypes ws ON wt.id = ws.waste_type_id
    GROUP BY wt.id
    ORDER BY wt.name ASC
");
$waste_types = $stmt->fetchAll();

include_once '../../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Waste Types</h1>
        <button onclick="toggleForm('add')" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            <i class="fas fa-plus mr-2"></i> Add Waste Type
        </button>
    </div>

    <?php if ($success_message): ?>
    <div class="rounded-md bg-green-50 p-4">
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
    <div class="rounded-md bg-red-50 p-4">
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

    <!-- Add/Edit Form -->
    <div id="waste-type-form" class="hidden rounded-lg border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold"><?php echo $waste_type_to_edit ? 'Edit Waste Type' : 'Add Waste Type'; ?></h2>
        <form method="POST" action="">
            <?php if ($waste_type_to_edit): ?>
            <input type="hidden" name="waste_type_id" value="<?php echo $waste_type_to_edit['id']; ?>">
            <?php endif; ?>
            <div class="mt-4 space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" value="<?php echo $waste_type_to_edit ? $waste_type_to_edit['name'] : ''; ?>" required>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"><?php echo $waste_type_to_edit ? $waste_type_to_edit['description'] : ''; ?></textarea>
                </div>
                <div>
                    <label for="rate_per_kg" class="block text-sm font-medium text-gray-700">Rate per kg</label>
                    <input type="number" name="rate_per_kg" id="rate_per_kg" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" value="<?php echo $waste_type_to_edit ? $waste_type_to_edit['rate_per_kg'] : ''; ?>" required>
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="<?php echo $waste_type_to_edit ? 'update_waste_type' : 'add_waste_type'; ?>" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        <?php echo $waste_type_to_edit ? 'Update Waste Type' : 'Add Waste Type'; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Waste Types Table -->
    <div class="rounded-lg border bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate per kg</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtypes</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($waste_types as $waste_type): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $waste_type['name']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $waste_type['description']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">₹<?php echo number_format($waste_type['rate_per_kg'], 2); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $waste_type['subtypes_count']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="?edit=<?php echo $waste_type['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                            <form method="POST" action="" class="inline-block">
                                <input type="hidden" name="waste_type_id" value="<?php echo $waste_type['id']; ?>">
                                <button type="submit" name="delete_waste_type" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this waste type?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleForm(action) {
    const form = document.getElementById('waste-type-form');
    if (action === 'add') {
        form.classList.remove('hidden');
        form.querySelector('h2').textContent = 'Add Waste Type';
        form.querySelector('button[type="submit"]').textContent = 'Add Waste Type';
        form.querySelector('button[type="submit"]').name = 'add_waste_type';
    } else if (action === 'edit') {
        form.classList.remove('hidden');
        form.querySelector('h2').textContent = 'Edit Waste Type';
        form.querySelector('button[type="submit"]').textContent = 'Update Waste Type';
        form.querySelector('button[type="submit"]').name = 'update_waste_type';
    }
}

<?php if (isset($_GET['edit'])): ?>
toggleForm('edit');
<?php endif; ?>
</script>

<?php
include_once '../../includes/dashboard_footer.php';
?>