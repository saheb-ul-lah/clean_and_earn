<?php
$page_title = "My Waste Listings";

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireLogin();

if ($_SESSION['user_role'] !== 'household') {
    header('Location: /dashboard/unauthorized.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$success_message = $error_message = '';
$listing_to_edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_listing'])) {
    $waste_type_id = $_POST['waste_type_id'];
    $waste_subtype_id = $_POST['waste_subtype_id'];
    $weight = $_POST['weight'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];
    $pickup_date = $_POST['pickup_date'];
    $pickup_time_slot = $_POST['pickup_time_slot'];
    $pickup_address = $_POST['pickup_address'];

    $stmt = $pdo->prepare("
        INSERT INTO waste_listings (user_id, waste_type_id, waste_subtype_id, weight, quantity, description, pickup_date, pickup_time_slot, pickup_address, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    if ($stmt->execute([$user_id, $waste_type_id, $waste_subtype_id, $weight, $quantity, $description, $pickup_date, $pickup_time_slot, $pickup_address])) {
        $success_message = "Listing added successfully!";
    } else {
        $error_message = "Failed to add listing.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_listing'])) {
    $listing_id = $_POST['listing_id'];
    $waste_type_id = $_POST['waste_type_id'];
    $waste_subtype_id = $_POST['waste_subtype_id'];
    $weight = $_POST['weight'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];
    $pickup_date = $_POST['pickup_date'];
    $pickup_time_slot = $_POST['pickup_time_slot'];
    $pickup_address = $_POST['pickup_address'];

    $stmt = $pdo->prepare("
        UPDATE waste_listings
        SET waste_type_id = ?, waste_subtype_id = ?, weight = ?, quantity = ?, description = ?, pickup_date = ?, pickup_time_slot = ?, pickup_address = ?
        WHERE id = ? AND user_id = ?
    ");
    if ($stmt->execute([$waste_type_id, $waste_subtype_id, $weight, $quantity, $description, $pickup_date, $pickup_time_slot, $pickup_address, $listing_id, $user_id])) {
        $success_message = "Listing updated successfully!";
    } else {
        $error_message = "Failed to update listing.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_listing'])) {
    $listing_id = $_POST['listing_id'];

    $stmt = $pdo->prepare("DELETE FROM waste_listings WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$listing_id, $user_id])) {
        $success_message = "Listing deleted successfully!";
    } else {
        $error_message = "Failed to delete listing.";
    }
}

if (isset($_GET['edit'])) {
    $listing_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM waste_listings WHERE id = ? AND user_id = ?");
    $stmt->execute([$listing_id, $user_id]);
    $listing_to_edit = $stmt->fetch();
}

$stmt = $pdo->prepare("
    SELECT 
        wl.*,
        wt.name as waste_type_name,
        ws.name as waste_subtype_name
    FROM waste_listings wl
    LEFT JOIN waste_types wt ON wl.waste_type_id = wt.id
    LEFT JOIN waste_subtypes ws ON wl.waste_subtype_id = ws.id
    WHERE wl.user_id = ?
    ORDER BY wl.pickup_date ASC
");
$stmt->execute([$user_id]);
$listings = $stmt->fetchAll();

$waste_types = $pdo->query("SELECT * FROM waste_types")->fetchAll();
$waste_subtypes = $pdo->query("SELECT * FROM waste_subtypes")->fetchAll();

include_once '../../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">My Waste Listings</h1>
        <button onclick="toggleForm('add')" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            <i class="fas fa-plus mr-2"></i> Add New Listing
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
    <div id="listing-form" class="hidden rounded-lg border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold"><?php echo $listing_to_edit ? 'Edit Listing' : 'Add New Listing'; ?></h2>
        <form method="POST" action="">
            <?php if ($listing_to_edit): ?>
            <input type="hidden" name="listing_id" value="<?php echo $listing_to_edit['id']; ?>">
            <?php endif; ?>
            <div class="mt-4 space-y-4">
                <div>
                    <label for="waste_type_id" class="block text-sm font-medium text-gray-700">Waste Type</label>
                    <select name="waste_type_id" id="waste_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <?php foreach ($waste_types as $type): ?>
                        <option value="<?php echo $type['id']; ?>" <?php echo ($listing_to_edit && $listing_to_edit['waste_type_id'] == $type['id']) ? 'selected' : ''; ?>>
                            <?php echo $type['name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="waste_subtype_id" class="block text-sm font-medium text-gray-700">Waste Subtype</label>
                    <select name="waste_subtype_id" id="waste_subtype_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <?php foreach ($waste_subtypes as $subtype): ?>
                        <option value="<?php echo $subtype['id']; ?>" <?php echo ($listing_to_edit && $listing_to_edit['waste_subtype_id'] == $subtype['id']) ? 'selected' : ''; ?>>
                            <?php echo $subtype['name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="weight" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                    <input type="number" name="weight" id="weight" step="0.1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" value="<?php echo $listing_to_edit ? $listing_to_edit['weight'] : ''; ?>" required>
                </div>
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" value="<?php echo $listing_to_edit ? $listing_to_edit['quantity'] : ''; ?>" required>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"><?php echo $listing_to_edit ? $listing_to_edit['description'] : ''; ?></textarea>
                </div>
                <div>
                    <label for="pickup_date" class="block text-sm font-medium text-gray-700">Pickup Date</label>
                    <input type="date" name="pickup_date" id="pickup_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" value="<?php echo $listing_to_edit ? $listing_to_edit['pickup_date'] : ''; ?>" required>
                </div>
                <div>
                    <label for="pickup_time_slot" class="block text-sm font-medium text-gray-700">Pickup Time Slot</label>
                    <input type="time" name="pickup_time_slot" id="pickup_time_slot" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" value="<?php echo $listing_to_edit ? $listing_to_edit['pickup_time_slot'] : ''; ?>" required>
                </div>
                <div>
                    <label for="pickup_address" class="block text-sm font-medium text-gray-700">Pickup Address</label>
                    <input type="text" name="pickup_address" id="pickup_address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" value="<?php echo $listing_to_edit ? $listing_to_edit['pickup_address'] : ''; ?>" required>
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="<?php echo $listing_to_edit ? 'edit_listing' : 'add_listing'; ?>" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        <?php echo $listing_to_edit ? 'Update Listing' : 'Add Listing'; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Listings Table -->
    <div class="rounded-lg border bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtype</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pickup Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($listings as $listing): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $listing['waste_type_name']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $listing['waste_subtype_name']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $listing['weight']; ?> kg</td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $listing['quantity']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y', strtotime($listing['pickup_date'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 <?php echo getStatusBadgeClass($listing['status']); ?>">
                                <?php echo ucfirst($listing['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="?edit=<?php echo $listing['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                            <form method="POST" action="" class="inline-block">
                                <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                <button type="submit" name="delete_listing" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this listing?')">Delete</button>
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
    const form = document.getElementById('listing-form');
    if (action === 'add') {
        form.classList.remove('hidden');
        form.querySelector('h2').textContent = 'Add New Listing';
        form.querySelector('button[type="submit"]').textContent = 'Add Listing';
        form.querySelector('button[type="submit"]').name = 'add_listing';
    } else if (action === 'edit') {
        form.classList.remove('hidden');
        form.querySelector('h2').textContent = 'Edit Listing';
        form.querySelector('button[type="submit"]').textContent = 'Update Listing';
        form.querySelector('button[type="submit"]').name = 'edit_listing';
    }
}

<?php if (isset($_GET['edit'])): ?>
toggleForm('edit');
<?php endif; ?>
</script>

<?php
include_once '../../includes/dashboard_footer.php';
?>