# Fixing the Transaction Error in Collections

The error "Failed to update collection status: There is no active transaction" occurs because there's a conflict between transactions in your `addPointsToUser` function and the main transaction in your collection update code. Here's how to fix it:

## 1. Modify the Collection Update Code

Replace your current form handling code with this version:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $collection_id = $_POST['collection_id'] ?? '';
    $new_status = $_POST['new_status'] ?? '';
    $actual_weight = $_POST['actual_weight'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($collection_id)) {
        $error_message = 'Invalid request - missing collection ID';
    } elseif ($new_status === 'collected' && empty($actual_weight)) {
        $error_message = 'Actual weight is required for completed collections';
    } else {
        try {
            // Verify the collector owns this collection
            $stmt = $pdo->prepare("SELECT id FROM waste_collections WHERE id = ? AND collector_id = ?");
            $stmt->execute([$collection_id, $user_id]);
            $collection = $stmt->fetch();
            
            if (!$collection) {
                throw new Exception('Collection not found or you do not have permission to update it');
            }
            
            if ($new_status === 'collected') {
                // Update collection record with actual weight and notes
                $stmt = $pdo->prepare("
                    UPDATE waste_collections 
                    SET status = ?, 
                        actual_weight = ?, 
                        collection_date = NOW(), 
                        notes = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$new_status, $actual_weight, $notes, $collection_id]);
                
                // Get listing details for points calculation
                $stmt = $pdo->prepare("
                    SELECT wl.id, wl.user_id, wl.waste_type_id, wl.waste_subtype_id
                    FROM waste_collections wc
                    JOIN waste_listings wl ON wc.listing_id = wl.id
                    WHERE wc.id = ?
                ");
                $stmt->execute([$collection_id]);
                $listing = $stmt->fetch();
                
                if (!$listing) {
                    throw new Exception('Associated listing not found');
                }
                
                // Update listing status
                $stmt = $pdo->prepare("UPDATE waste_listings SET status = 'completed' WHERE id = ?");
                $stmt->execute([$listing['id']]);
                
                // Calculate points (without transaction)
                $points = calculatePoints($listing['waste_type_id'], $actual_weight, $pdo);
                
                // Add points (modified version without transaction)
                $stmt = $pdo->prepare("
                    INSERT INTO points_transactions 
                    (user_id, points, transaction_type, reference_id, reference_type, description) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $listing['user_id'],
                    $points,
                    'earned',
                    $listing['id'],
                    'listing',
                    'Points earned for recycling waste'
                ]);
                
                // Update user's total points
                $stmt = $pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE id = ?");
                $stmt->execute([$points, $listing['user_id']]);
                
                // Create inventory record for storage
                $stmt = $pdo->prepare("
                    SELECT id FROM users WHERE role = 'storage' LIMIT 1
                ");
                $stmt->execute();
                $storage = $stmt->fetch();
                
                if (!$storage) {
                    throw new Exception('No storage facility found in the system');
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO storage_inventory 
                    (storage_id, waste_type_id, waste_subtype_id, weight, collection_id, status) 
                    VALUES (?, ?, ?, ?, ?, 'available')
                ");
                $stmt->execute([
                    $storage['id'],
                    $listing['waste_type_id'],
                    $listing['waste_subtype_id'],
                    $actual_weight,
                    $collection_id
                ]);
            } else {
                // For status updates other than 'collected'
                $stmt = $pdo->prepare("
                    UPDATE waste_collections 
                    SET status = ?, 
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$new_status, $collection_id]);
            }
            
            $_SESSION['success_message'] = 'Collection status updated successfully';
            header('Location: /dashboard/collections/index.php');
            exit;
            
        } catch (Exception $e) {
            $error_message = 'Failed to update collection status: ' . $e->getMessage();
            error_log('Collection update error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
```

## 2. Key Changes Made:

1. **Removed Transactions**: Since `addPointsToUser` has its own transaction, we removed the outer transaction to prevent conflicts.

2. **Simplified Points Handling**: Moved the points logic directly into the main function rather than calling `addPointsToUser`.

3. **Storage ID Lookup**: Added direct storage ID lookup instead of using a helper function.

4. **Better Error Handling**: Improved error logging with stack traces.

## 3. Alternative Solution (If You Prefer Transactions)

If you want to keep using transactions, modify your `addPointsToUser` function to accept an optional PDO transaction parameter:

```php
function addPointsToUser($userId, $points, $transactionType, $referenceId, $referenceType, $description, $pdo, $inTransaction = false) {
    try {
        if (!$inTransaction) {
            $pdo->beginTransaction();
        }
        
        $stmt = $pdo->prepare("INSERT INTO points_transactions (user_id, points, transaction_type, reference_id, reference_type, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $points, $transactionType, $referenceId, $referenceType, $description]);
        
        $stmt = $pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE id = ?");
        $stmt->execute([$points, $userId]);
        
        if (!$inTransaction) {
            $pdo->commit();
        }
        return true;
    } catch (Exception $e) {
        if (!$inTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
}
```

Then call it with the additional parameter:
```php
addPointsToUser(
    $listing['user_id'],
    $points,
    'earned',
    $listing['id'],
    'listing',
    'Points earned for recycling waste',
    $pdo,
    true  // Indicates we're already in a transaction
);
```

Choose either solution based on your preference. The first solution (without transactions) is simpler and less prone to errors, while the second gives you more control if you need transactions for other reasons.# Fixing the Transaction Error in Collections

The error "Failed to update collection status: There is no active transaction" occurs because there's a conflict between transactions in your `addPointsToUser` function and the main transaction in your collection update code. Here's how to fix it:

## 1. Modify the Collection Update Code

Replace your current form handling code with this version:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $collection_id = $_POST['collection_id'] ?? '';
    $new_status = $_POST['new_status'] ?? '';
    $actual_weight = $_POST['actual_weight'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($collection_id)) {
        $error_message = 'Invalid request - missing collection ID';
    } elseif ($new_status === 'collected' && empty($actual_weight)) {
        $error_message = 'Actual weight is required for completed collections';
    } else {
        try {
            // Verify the collector owns this collection
            $stmt = $pdo->prepare("SELECT id FROM waste_collections WHERE id = ? AND collector_id = ?");
            $stmt->execute([$collection_id, $user_id]);
            $collection = $stmt->fetch();
            
            if (!$collection) {
                throw new Exception('Collection not found or you do not have permission to update it');
            }
            
            if ($new_status === 'collected') {
                // Update collection record with actual weight and notes
                $stmt = $pdo->prepare("
                    UPDATE waste_collections 
                    SET status = ?, 
                        actual_weight = ?, 
                        collection_date = NOW(), 
                        notes = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$new_status, $actual_weight, $notes, $collection_id]);
                
                // Get listing details for points calculation
                $stmt = $pdo->prepare("
                    SELECT wl.id, wl.user_id, wl.waste_type_id, wl.waste_subtype_id
                    FROM waste_collections wc
                    JOIN waste_listings wl ON wc.listing_id = wl.id
                    WHERE wc.id = ?
                ");
                $stmt->execute([$collection_id]);
                $listing = $stmt->fetch();
                
                if (!$listing) {
                    throw new Exception('Associated listing not found');
                }
                
                // Update listing status
                $stmt = $pdo->prepare("UPDATE waste_listings SET status = 'completed' WHERE id = ?");
                $stmt->execute([$listing['id']]);
                
                // Calculate points (without transaction)
                $points = calculatePoints($listing['waste_type_id'], $actual_weight, $pdo);
                
                // Add points (modified version without transaction)
                $stmt = $pdo->prepare("
                    INSERT INTO points_transactions 
                    (user_id, points, transaction_type, reference_id, reference_type, description) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $listing['user_id'],
                    $points,
                    'earned',
                    $listing['id'],
                    'listing',
                    'Points earned for recycling waste'
                ]);
                
                // Update user's total points
                $stmt = $pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE id = ?");
                $stmt->execute([$points, $listing['user_id']]);
                
                // Create inventory record for storage
                $stmt = $pdo->prepare("
                    SELECT id FROM users WHERE role = 'storage' LIMIT 1
                ");
                $stmt->execute();
                $storage = $stmt->fetch();
                
                if (!$storage) {
                    throw new Exception('No storage facility found in the system');
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO storage_inventory 
                    (storage_id, waste_type_id, waste_subtype_id, weight, collection_id, status) 
                    VALUES (?, ?, ?, ?, ?, 'available')
                ");
                $stmt->execute([
                    $storage['id'],
                    $listing['waste_type_id'],
                    $listing['waste_subtype_id'],
                    $actual_weight,
                    $collection_id
                ]);
            } else {
                // For status updates other than 'collected'
                $stmt = $pdo->prepare("
                    UPDATE waste_collections 
                    SET status = ?, 
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$new_status, $collection_id]);
            }
            
            $_SESSION['success_message'] = 'Collection status updated successfully';
            header('Location: /dashboard/collections/index.php');
            exit;
            
        } catch (Exception $e) {
            $error_message = 'Failed to update collection status: ' . $e->getMessage();
            error_log('Collection update error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
```

## 2. Key Changes Made:

1. **Removed Transactions**: Since `addPointsToUser` has its own transaction, we removed the outer transaction to prevent conflicts.

2. **Simplified Points Handling**: Moved the points logic directly into the main function rather than calling `addPointsToUser`.

3. **Storage ID Lookup**: Added direct storage ID lookup instead of using a helper function.

4. **Better Error Handling**: Improved error logging with stack traces.

## 3. Alternative Solution (If You Prefer Transactions)

If you want to keep using transactions, modify your `addPointsToUser` function to accept an optional PDO transaction parameter:

```php
function addPointsToUser($userId, $points, $transactionType, $referenceId, $referenceType, $description, $pdo, $inTransaction = false) {
    try {
        if (!$inTransaction) {
            $pdo->beginTransaction();
        }
        
        $stmt = $pdo->prepare("INSERT INTO points_transactions (user_id, points, transaction_type, reference_id, reference_type, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $points, $transactionType, $referenceId, $referenceType, $description]);
        
        $stmt = $pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE id = ?");
        $stmt->execute([$points, $userId]);
        
        if (!$inTransaction) {
            $pdo->commit();
        }
        return true;
    } catch (Exception $e) {
        if (!$inTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
}
```

Then call it with the additional parameter:
```php
addPointsToUser(
    $listing['user_id'],
    $points,
    'earned',
    $listing['id'],
    'listing',
    'Points earned for recycling waste',
    $pdo,
    true  // Indicates we're already in a transaction
);
```

Choose either solution based on your preference. The first solution (without transactions) is simpler and less prone to errors, while the second gives you more control if you need transactions for other reasons.