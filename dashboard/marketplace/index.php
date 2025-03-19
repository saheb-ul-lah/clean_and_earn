<?php
$page_title = "Marketplace";

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireLogin();

if ($_SESSION['user_role'] !== 'buyer') {
    header('Location: /dashboard/unauthorized.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$waste_types = $pdo->query("SELECT id, name FROM waste_types ORDER BY name")->fetchAll();

$filter_waste_type = $_GET['waste_type'] ?? '';
$filter_min_weight = $_GET['min_weight'] ?? '';
$filter_max_weight = $_GET['max_weight'] ?? '';
$filter_sort = $_GET['sort'] ?? 'created_at_desc';

$query = "
    SELECT 
        wl.id,
        wl.weight,
        wl.quantity,
        wl.description,
        wl.pickup_date,
        wl.pickup_time_slot,
        wl.pickup_address,
        wl.status,
        wl.created_at,
        wt.name as waste_type_name,
        ws.name as waste_subtype_name,
        u.name as seller_name,
        u.city,
        u.state,
        wt.rate_per_kg
    FROM waste_listings wl
    LEFT JOIN waste_types wt ON wl.waste_type_id = wt.id
    LEFT JOIN waste_subtypes ws ON wl.waste_subtype_id = ws.id
    LEFT JOIN users u ON wl.user_id = u.id
    WHERE wl.status = 'available'
";

$params = [];

if (!empty($filter_waste_type)) {
    $query .= " AND wl.waste_type_id = ?";
    $params[] = $filter_waste_type;
}

if (!empty($filter_min_weight)) {
    $query .= " AND wl.weight >= ?";
    $params[] = $filter_min_weight;
}

if (!empty($filter_max_weight)) {
    $query .= " AND wl.weight <= ?";
    $params[] = $filter_max_weight;
}

switch ($filter_sort) {
    case 'weight_asc':
        $query .= " ORDER BY wl.weight ASC";
        break;
    case 'weight_desc':
        $query .= " ORDER BY wl.weight DESC";
        break;
    case 'price_asc':
        $query .= " ORDER BY wt.rate_per_kg ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY wt.rate_per_kg DESC";
        break;
    case 'created_at_asc':
        $query .= " ORDER BY wl.created_at ASC";
        break;
    case 'created_at_desc':
    default:
        $query .= " ORDER BY wl.created_at DESC";
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$listings = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_request'])) {
    $listing_id = $_POST['listing_id'] ?? '';
    $quantity = floatval($_POST['quantity'] ?? 0);
    $pickup_date = $_POST['pickup_date'] ?? '';
    $pickup_time = $_POST['pickup_time'] ?? '';

    if (empty($listing_id) || empty($quantity) || empty($pickup_date) || empty($pickup_time)) {
        $error_message = 'All fields are required';
    } elseif ($quantity <= 0) {
        $error_message = 'Quantity must be greater than 0';
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT wl.*, wt.rate_per_kg 
                FROM waste_listings wl
                LEFT JOIN waste_types wt ON wl.waste_type_id = wt.id
                WHERE wl.id = ? AND wl.status = 'available'
            ");
            $stmt->execute([$listing_id]);
            $listing = $stmt->fetch();

            if (!$listing) {
                $error_message = 'Listing not found or no longer available';
            } elseif ($quantity > $listing['quantity']) {
                $error_message = 'Requested quantity exceeds available quantity';
            } else {
                $total_amount = $quantity * $listing['rate_per_kg'];

                $stmt = $pdo->prepare("
                    INSERT INTO waste_purchases 
                    (buyer_id, listing_id, quantity, total_amount, pickup_date, pickup_time, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([$user_id, $listing_id, $quantity, $total_amount, $pickup_date, $pickup_time]);

                $stmt = $pdo->prepare("
                    UPDATE waste_listings 
                    SET quantity = quantity - ? 
                    WHERE id = ?
                ");
                $stmt->execute([$quantity, $listing_id]);

                $success_message = 'Purchase request submitted successfully';
                header('Location: /dashboard/orders/index.php');
                exit;
            }
        } catch (Exception $e) {
            $error_message = 'Failed to submit purchase request: ' . $e->getMessage();
        }
    }
}

include_once '../../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Marketplace</h1>
        <a href="/dashboard/orders/index.php" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            <i class="fas fa-shopping-cart mr-2"></i> View Orders
        </a>
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
        <h2 class="text-lg font-medium">Filter Listings</h2>
        <form method="GET" action="" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-5">
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
                <label for="min-weight" class="block text-sm font-medium text-gray-700">Min Weight (kg)</label>
                <input type="number" id="min-weight" name="min_weight" value="<?php echo htmlspecialchars($filter_min_weight); ?>" min="0" step="0.1" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
            </div>
            <div>
                <label for="max-weight" class="block text-sm font-medium text-gray-700">Max Weight (kg)</label>
                <input type="number" id="max-weight" name="max_weight" value="<?php echo htmlspecialchars($filter_max_weight); ?>" min="0" step="0.1" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
            </div>
            <div>
                <label for="sort" class="block text-sm font-medium text-gray-700">Sort By</label>
                <select id="sort" name="sort" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                    <option value="created_at_desc" <?php echo $filter_sort === 'created_at_desc' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="created_at_asc" <?php echo $filter_sort === 'created_at_asc' ? 'selected' : ''; ?>>Oldest First</option>
                    <option value="weight_asc" <?php echo $filter_sort === 'weight_asc' ? 'selected' : ''; ?>>Weight: Low to High</option>
                    <option value="weight_desc" <?php echo $filter_sort === 'weight_desc' ? 'selected' : ''; ?>>Weight: High to Low</option>
                    <option value="price_asc" <?php echo $filter_sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo $filter_sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                </select>
            </div>
            <div class="self-end">
                <button type="submit" class="w-full rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Listings -->
    <div class="rounded-lg border bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight (kg)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price per kg</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($listings as $listing): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($listing['waste_type_name']); ?></div>
                            <?php if (!empty($listing['waste_subtype_name'])): ?>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($listing['waste_subtype_name']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($listing['seller_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo number_format($listing['weight'], 2); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">₹<?php echo number_format($listing['rate_per_kg'], 2); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($listing['city']); ?></div>
                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($listing['state']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="openPurchaseModal(<?php echo $listing['id']; ?>, <?php echo $listing['weight']; ?>, <?php echo $listing['rate_per_kg']; ?>)" class="text-primary-600 hover:text-primary-900">
                                Purchase
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Purchase Modal -->
<div id="purchase-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50" onclick="closePurchaseModal()"></div>
        <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md">
            <div class="p-6">
                <h2 class="text-lg font-bold">Purchase Waste</h2>
                <form method="POST" action="" class="mt-4 space-y-4">
                    <input type="hidden" name="listing_id" id="modal-listing-id">
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity (kg)</label>
                        <input type="number" name="quantity" id="modal-quantity" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                        <p class="text-xs text-gray-500 mt-1">Available: <span id="modal-available-quantity"></span> kg</p>
                    </div>
                    <div>
                        <label for="pickup_date" class="block text-sm font-medium text-gray-700">Pickup Date</label>
                        <input type="date" name="pickup_date" id="modal-pickup-date" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                    </div>
                    <div>
                        <label for="pickup_time" class="block text-sm font-medium text-gray-700">Pickup Time</label>
                        <input type="time" name="pickup_time" id="modal-pickup-time" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                    </div>
                    <div class="pt-4">
                        <button type="submit" name="purchase_request" class="w-full rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            Submit Purchase Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openPurchaseModal(listingId, availableQuantity, ratePerKg) {
    const modal = document.getElementById('purchase-modal');
    const listingIdInput = document.getElementById('modal-listing-id');
    const quantityInput = document.getElementById('modal-quantity');
    const availableQuantitySpan = document.getElementById('modal-available-quantity');

    listingIdInput.value = listingId;
    quantityInput.setAttribute('max', availableQuantity);
    availableQuantitySpan.textContent = availableQuantity;

    modal.classList.remove('hidden');
}

function closePurchaseModal() {
    const modal = document.getElementById('purchase-modal');
    modal.classList.add('hidden');
}
</script>

<?php
include_once '../../includes/dashboard_footer.php';
?>