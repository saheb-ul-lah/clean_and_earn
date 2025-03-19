<?php
$page_title = "My Orders";

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

$stmt = $pdo->prepare("
    SELECT 
        wp.id,
        wp.storage_id,
        wp.inventory_id,
        wp.weight,
        wp.amount,
        wp.status,
        wp.payment_method,
        wp.payment_reference,
        wp.pickup_date,
        wp.pickup_time,
        wp.created_at,
        si.waste_type_id,
        si.waste_subtype_id,
        wt.name as waste_type_name,
        ws.name as waste_subtype_name,
        u.name as storage_name,
        u.city,
        u.state,
        u.phone as storage_phone
    FROM waste_purchases wp
    JOIN storage_inventory si ON wp.inventory_id = si.id
    JOIN waste_types wt ON si.waste_type_id = wt.id
    LEFT JOIN waste_subtypes ws ON si.waste_subtype_id = ws.id
    JOIN users u ON wp.storage_id = u.id
    WHERE wp.buyer_id = ? AND wp.status IN ('pending', 'paid')
    ORDER BY wp.created_at DESC
");
$stmt->execute([$user_id]);
$pending_orders = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT 
        wp.id,
        wp.storage_id,
        wp.inventory_id,
        wp.weight,
        wp.amount,
        wp.status,
        wp.payment_method,
        wp.payment_reference,
        wp.pickup_date,
        wp.pickup_time,
        wp.created_at,
        si.waste_type_id,
        si.waste_subtype_id,
        wt.name as waste_type_name,
        ws.name as waste_subtype_name,
        u.name as storage_name
    FROM waste_purchases wp
    JOIN storage_inventory si ON wp.inventory_id = si.id
    JOIN waste_types wt ON si.waste_type_id = wt.id
    LEFT JOIN waste_subtypes ws ON si.waste_subtype_id = ws.id
    JOIN users u ON wp.storage_id = u.id
    WHERE wp.buyer_id = ? AND wp.status = 'completed'
    ORDER BY wp.created_at DESC
");
$stmt->execute([$user_id]);
$completed_orders = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancel_order'])) {
        $order_id = $_POST['order_id'] ?? '';
        
        if (empty($order_id)) {
            $error_message = 'Invalid request';
        } else {
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("
                    SELECT wp.id, wp.inventory_id 
                    FROM waste_purchases wp 
                    WHERE wp.id = ? AND wp.buyer_id = ? AND wp.status = 'pending'
                ");
                $stmt->execute([$order_id, $user_id]);
                $order = $stmt->fetch();
                
                if (!$order) {
                    throw new Exception('Order not found or cannot be cancelled');
                }
                
                $stmt = $pdo->prepare("UPDATE waste_purchases SET status = 'cancelled' WHERE id = ?");
                $stmt->execute([$order_id]);
                
                $stmt = $pdo->prepare("UPDATE storage_inventory SET status = 'available' WHERE id = ?");
                $stmt->execute([$order['inventory_id']]);
                
                $pdo->commit();
                $success_message = 'Order cancelled successfully';
                
                header('Location: /dashboard/orders/index.php');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error_message = 'Failed to cancel order: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['pay_order'])) {
        $order_id = $_POST['order_id'] ?? '';
        $payment_method = $_POST['payment_method'] ?? '';
        $payment_reference = $_POST['payment_reference'] ?? '';
        
        if (empty($order_id) || empty($payment_method) || empty($payment_reference)) {
            $error_message = 'All payment details are required';
        } else {
            try {
                $stmt = $pdo->prepare("
                    SELECT id FROM waste_purchases 
                    WHERE id = ? AND buyer_id = ? AND status = 'pending'
                ");
                $stmt->execute([$order_id, $user_id]);
                $order = $stmt->fetch();
                
                if (!$order) {
                    throw new Exception('Order not found or cannot be paid');
                }
                
                $stmt = $pdo->prepare("
                    UPDATE waste_purchases 
                    SET status = 'paid', payment_method = ?, payment_reference = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$payment_method, $payment_reference, $order_id]);
                
                $success_message = 'Payment recorded successfully';
                
                header('Location: /dashboard/orders/index.php');
                exit;
            } catch (Exception $e) {
                $error_message = 'Failed to process payment: ' . $e->getMessage();
            }
        }
    }
}

include_once '../../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">My Orders</h1>
        <div>
            <a href="/dashboard/marketplace/index.php" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                <i class="fas fa-shopping-cart mr-2"></i> Browse Marketplace
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
    
    <div class="rounded-lg border bg-white shadow-sm">
        <div class="border-b">
            <nav class="flex" aria-label="Tabs">
                <button type="button" class="active-tab-button border-b-2 border-primary-600 px-4 py-4 text-sm font-medium text-primary-600" data-tab="pending-tab">
                    Pending Orders
                </button>
                <button type="button" class="tab-button border-b-2 border-transparent px-4 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700" data-tab="completed-tab">
                    Completed Orders
                </button>
            </nav>
        </div>
        
        <div id="pending-tab" class="tab-content p-6">
            <h2 class="text-lg font-bold">Pending Orders</h2>
            <p class="text-sm text-gray-500">
                Orders that are awaiting payment or pickup
            </p>
            
            <?php if (empty($pending_orders)): ?>
            <div class="mt-6 flex flex-col items-center justify-center py-10">
                <p class="text-gray-500 mb-4">You don't have any pending orders</p>
                <a href="/dashboard/marketplace/index.php" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                    Browse Marketplace
                </a>
            </div>
            <?php else: ?>
            <div class="mt-6 space-y-6">
                <?php foreach ($pending_orders as $order): ?>
                <div class="rounded-lg border p-4 transition-all hover:shadow-md">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-medium"><?php echo htmlspecialchars($order['waste_type_name']); ?></h3>
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 <?php echo getStatusBadgeClass($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            <?php if ($order['waste_subtype_name']): ?>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($order['waste_subtype_name']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2 md:mt-0 text-sm">
                            <div class="flex items-center gap-1">
                                <i class="fas fa-calendar text-gray-400"></i>
                                <span>Order Date: <?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="fas fa-tag text-gray-400"></i>
                                <span>Order #<?php echo $order['id']; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <h4 class="text-sm font-medium">Order Details</h4>
                            <div class="mt-2 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Weight:</span>
                                    <span class="text-sm font-medium"><?php echo $order['weight']; ?> kg</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Amount:</span>
                                    <span class="text-sm font-medium">₹<?php echo number_format($order['amount'], 2); ?></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Pickup Date:</span>
                                    <span class="text-sm font-medium"><?php echo date('M j, Y', strtotime($order['pickup_date'])); ?></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Pickup Time:</span>
                                    <span class="text-sm font-medium"><?php echo $order['pickup_time']; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium">Storage Details</h4>
                            <div class="mt-2 space-y-2">
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-store text-gray-400 mt-1 flex-shrink-0"></i>
                                    <span class="text-sm"><?php echo htmlspecialchars($order['storage_name']); ?></span>
                                </div>
                                <?php if ($order['city'] && $order['state']): ?>
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-map-marker-alt text-gray-400 mt-1 flex-shrink-0"></i>
                                    <span class="text-sm"><?php echo htmlspecialchars($order['city'] . ', ' . $order['state']); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($order['storage_phone']): ?>
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-phone text-gray-400 mt-1 flex-shrink-0"></i>
                                    <span class="text-sm"><?php echo htmlspecialchars($order['storage_phone']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium">Payment Status</h4>
                            <div class="mt-2 space-y-2">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-<?php echo $order['status'] === 'pending' ? 'clock' : 'check-circle'; ?> text-<?php echo $order['status'] === 'pending' ? 'yellow' : 'green'; ?>-500"></i>
                                    <span class="text-sm font-medium">
                                        <?php echo $order['status'] === 'pending' ? 'Payment Pending' : 'Payment Completed'; ?>
                                    </span>
                                </div>
                                <?php if ($order['status'] === 'paid'): ?>
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-credit-card text-gray-400 mt-1 flex-shrink-0"></i>
                                    <div>
                                        <span class="text-sm"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                                        <p class="text-xs text-gray-500">Ref: <?php echo htmlspecialchars($order['payment_reference']); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 border-t pt-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <?php if ($order['status'] === 'pending'): ?>
                            <div class="flex items-center gap-2">
                                <button type="button" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-700" onclick="openPaymentModal('<?php echo $order['id']; ?>', '<?php echo $order['amount']; ?>')">
                                    <i class="fas fa-credit-card mr-1"></i> Pay Now
                                </button>
                                <form method="POST" action="" class="inline-block" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" name="cancel_order" class="inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100">
                                        <i class="fas fa-times mr-1"></i> Cancel Order
                                    </button>
                                </form>
                            </div>
                            <?php elseif ($order['status'] === 'paid'): ?>
                            <div class="flex items-center gap-2">
                                <a href="https://maps.google.com/?q=<?php echo urlencode($order['city'] . ', ' . $order['state']); ?>" target="_blank" class="inline-flex items-center rounded-md bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100">
                                    <i class="fas fa-directions mr-1"></i> Get Directions
                                </a>
                                <?php if ($order['storage_phone']): ?>
                                <a href="tel:<?php echo $order['storage_phone']; ?>" class="inline-flex items-center rounded-md bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100">
                                    <i class="fas fa-phone mr-1"></i> Call Storage
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div>
                                <a href="/dashboard/orders/view.php?id=<?php echo $order['id']; ?>" class="inline-flex items-center rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-eye mr-1"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div id="completed-tab" class="tab-content p-6 hidden">
            <h2 class="text-lg font-bold">Completed Orders</h2>
            <p class="text-sm text-gray-500">
                Orders that have been completed
            </p>
            
            <?php if (empty($completed_orders)): ?>
            <div class="mt-6 flex flex-col items-center justify-center py-10">
                <p class="text-gray-500 mb-4">You don't have any completed orders</p>
            </div>
            <?php else: ?>
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Storage</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($completed_orders as $order): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">
                                    <?php echo htmlspecialchars($order['waste_type_name']); ?>
                                </div>
                                <?php if ($order['waste_subtype_name']): ?>
                                <div class="text-xs text-gray-500">
                                    <?php echo htmlspecialchars($order['waste_subtype_name']); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($order['storage_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $order['weight']; ?> kg
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₹<?php echo number_format($order['amount'], 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="/dashboard/orders/view.php?id=<?php echo $order['id']; ?>" class="text-primary-600 hover:text-primary-900">
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

<!-- Payment Modal -->
<div id="payment-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">Make Payment</h3>
            <form method="POST" action="" id="payment-form">
                <input type="hidden" name="order_id" id="payment-order-id">
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Amount</p>
                        <p class="text-lg font-bold text-primary-600" id="payment-amount"></p>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="payment-method" class="text-sm font-medium">Payment Method</label>
                        <select id="payment-method" name="payment_method" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                            <option value="">Select Payment Method</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="UPI">UPI</option>
                            <option value="Credit/Debit Card">Credit/Debit Card</option>
                            <option value="Cash">Cash</option>
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="payment-reference" class="text-sm font-medium">Payment Reference</label>
                        <input type="text" id="payment-reference" name="payment_reference" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" required>
                        <p class="text-xs text-gray-500">Enter transaction ID, UPI reference, or other payment identifier</p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" onclick="closePaymentModal()">
                        Cancel
                    </button>
                    <button type="submit" name="pay_order" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                        Submit Payment
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
    
    function openPaymentModal(orderId, amount) {
        document.getElementById('payment-order-id').value = orderId;
        document.getElementById('payment-amount').textContent = `₹${parseFloat(amount).toFixed(2)}`;
        document.getElementById('payment-modal').classList.remove('hidden');
    }
    
    function closePaymentModal() {
        document.getElementById('payment-modal').classList.add('hidden');
    }
</script>

<?php
include_once '../../includes/dashboard_footer.php';
?>