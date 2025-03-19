<?php
$page_title = "Rewards & Points";

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
$stmt = $pdo->prepare("SELECT total_points FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$total_points = $user['total_points'] ?? 0;

$stmt = $pdo->prepare("
    SELECT 
        pt.*,
        wl.waste_type_id,
        wl.waste_subtype_id,
        wl.weight
    FROM points_transactions pt
    LEFT JOIN waste_listings wl ON pt.reference_id = wl.id AND pt.reference_type = 'listing'
    WHERE pt.user_id = ?
    ORDER BY pt.created_at DESC
    LIMIT 20
");
$stmt->execute([$user_id]);
$points_history = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT 1 as id, 'Amazon Gift Card' as name, 'Rs. 100 Amazon Gift Card' as description, 500 as points_required, 'amazon.jpg' as image
    UNION SELECT 2, 'Flipkart Voucher', 'Rs. 200 Flipkart Voucher', 1000, 'flipkart.jpg'
    UNION SELECT 3, 'Movie Tickets', 'PVR Cinema Ticket', 750, 'movie.jpg'
    UNION SELECT 4, 'Food Delivery', 'Rs. 150 Zomato Voucher', 800, 'food.jpg'
    UNION SELECT 5, 'Grocery Discount', '10% off on BigBasket', 300, 'grocery.jpg'
");
$rewards = $stmt->fetchAll();

include_once '../../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Rewards & Points</h1>
    </div>
    
    <!-- Points Overview -->
    <div class="rounded-lg border bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="flex flex-col items-center justify-center space-y-2 rounded-lg bg-primary-50 p-6">
                <div class="text-3xl font-bold text-primary-600"><?php echo number_format($total_points); ?></div>
                <div class="text-sm font-medium text-gray-500">Total Points</div>
            </div>
            <div class="col-span-2">
                <h2 class="text-lg font-medium">Your Points</h2>
                <p class="mt-2 text-sm text-gray-500">
                    Earn points by recycling waste and redeem them for exciting rewards. The more you recycle, the more you earn!
                </p>
                <div class="mt-4 flex space-x-4">
                    <a href="#rewards" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        Redeem Points
                    </a>
                    <a href="#history" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        View History
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Points Earning Guide -->
    <div class="rounded-lg border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-medium">How to Earn Points</h2>
        <p class="mt-2 text-sm text-gray-500">
            Learn how to maximize your points by recycling different types of waste
        </p>
        
        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-lg border p-4">
                <div class="flex items-center space-x-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                        <i class="fas fa-newspaper text-green-600"></i>
                    </div>
                    <h3 class="text-sm font-medium">Paper Waste</h3>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    Earn 22 points per kg of paper waste including newspapers, magazines, and cardboard
                </p>
            </div>
            <div class="rounded-lg border p-4">
                <div class="flex items-center space-x-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                        <i class="fas fa-wine-bottle text-blue-600"></i>
                    </div>
                    <h3 class="text-sm font-medium">Plastic Waste</h3>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    Earn 15 points per kg of plastic waste including bottles, containers, and packaging
                </p>
            </div>
            <div class="rounded-lg border p-4">
                <div class="flex items-center space-x-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-100">
                        <i class="fas fa-cog text-yellow-600"></i>
                    </div>
                    <h3 class="text-sm font-medium">Metal Waste</h3>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    Earn 90 points per kg of metal waste including aluminum, iron, and steel
                </p>
            </div>
            <div class="rounded-lg border p-4">
                <div class="flex items-center space-x-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100">
                        <i class="fas fa-laptop text-red-600"></i>
                    </div>
                    <h3 class="text-sm font-medium">Electronic Waste</h3>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    Earn 200 points per kg of electronic waste including computers, phones, and appliances
                </p>
            </div>
            <div class="rounded-lg border p-4">
                <div class="flex items-center space-x-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100">
                        <i class="fas fa-glass-martini text-purple-600"></i>
                    </div>
                    <h3 class="text-sm font-medium">Glass Waste</h3>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    Earn 10 points per kg of glass waste including bottles and containers
                </p>
            </div>
            <div class="rounded-lg border p-4">
                <div class="flex items-center space-x-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                        <i class="fas fa-apple-alt text-green-600"></i>
                    </div>
                    <h3 class="text-sm font-medium">Organic Waste</h3>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    Earn 5 points per kg of organic waste including food scraps and garden waste
                </p>
            </div>
        </div>
    </div>
    
    <!-- Available Rewards -->
    <div id="rewards" class="rounded-lg border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-medium">Available Rewards</h2>
        <p class="mt-2 text-sm text-gray-500">
            Redeem your points for these exciting rewards
        </p>
        
        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($rewards as $reward): ?>
            <div class="rounded-lg border p-4 transition-all hover:shadow-md">
                <!-- <div class="aspect-w-16 aspect-h-9 mb-4">
                    <img src="/assets/images/rewards/<?php echo htmlspecialchars($reward['image']); ?>" alt="<?php echo htmlspecialchars($reward['name']); ?>" class="rounded-md object-cover" onerror="this.src='/placeholder.svg?height=150&width=300'">
                </div> -->
                <h3 class="text-sm font-medium"><?php echo htmlspecialchars($reward['name']); ?></h3>
                <p class="mt-1 text-sm text-gray-500"><?php echo htmlspecialchars($reward['description']); ?></p>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-sm font-medium text-primary-600"><?php echo number_format($reward['points_required']); ?> points</span>
                    <button type="button" class="rounded-md bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 <?php echo $total_points < $reward['points_required'] ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo $total_points < $reward['points_required'] ? 'disabled' : ''; ?>>
                        Redeem
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Points History -->
    <div id="history" class="rounded-lg border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-medium">Points History</h2>
        <p class="mt-2 text-sm text-gray-500">
            Track your points earning and redemption history
        </p>
        
        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($points_history)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                            No points history found
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($points_history as $transaction): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y, g:i a', strtotime($transaction['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?php 
                            if ($transaction['reference_type'] === 'listing') {
                                echo 'Waste Recycling: ' . getWasteTypeName($transaction['waste_type_id'], $pdo);
                                if ($transaction['waste_subtype_id']) {
                                    echo ' - ' . getWasteSubtypeName($transaction['waste_subtype_id'], $pdo);
                                }
                                echo ' (' . $transaction['weight'] . ' kg)';
                            } else {
                                echo htmlspecialchars($transaction['description'] ?? 'Points Transaction');
                            }
                            ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 <?php echo $transaction['transaction_type'] === 'earned' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo ucfirst($transaction['transaction_type']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium <?php echo $transaction['transaction_type'] === 'earned' ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $transaction['transaction_type'] === 'earned' ? '+' : '-'; ?><?php echo number_format($transaction['points']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include_once '../../includes/dashboard_footer.php';
?>