<?php
require_once __DIR__ . '/../models/waste_purchase.php';
require_once __DIR__ . '/../utils/validator.php';
require_once __DIR__ . '/../utils/response.php';

class PurchaseController {
    private $db;
    private $waste_purchase;
    private $validator;
    private $response;
    
    public function __construct($db) {
        $this->db = $db;
        $this->waste_purchase = new WastePurchase($db);
        $this->response = new Response();
    }
    
    // Create a new purchase
    public function create($buyer_id) {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('storage_id')->numeric('storage_id')
                        ->required('inventory_id')->numeric('inventory_id')
                        ->required('weight')->numeric('weight')
                        ->required('amount')->numeric('amount')
                        ->required('pickup_date')->date('pickup_date')
                        ->required('pickup_time');
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set purchase properties
        $this->waste_purchase->buyer_id = $buyer_id;
        $this->waste_purchase->storage_id = $data['storage_id'];
        $this->waste_purchase->inventory_id = $data['inventory_id'];
        $this->waste_purchase->weight = $data['weight'];
        $this->waste_purchase->amount = $data['amount'];
        $this->waste_purchase->status = 'pending';
        $this->waste_purchase->pickup_date = $data['pickup_date'];
        $this->waste_purchase->pickup_time = $data['pickup_time'];
        
        // Create the purchase
        if ($this->waste_purchase->create()) {
            $this->response->send([
                'message' => 'Purchase created successfully',
                'id' => $this->waste_purchase->id
            ], 201);
        } else {
            $this->response->sendError('Creation Failed', 'Unable to create purchase. The inventory may not be available or insufficient quantity.');
        }
    }
    
    // Get a single purchase
    public function getOne($id, $user_id, $role) {
        // Set purchase ID
        $this->waste_purchase->id = $id;
        
        // Get purchase data
        if ($this->waste_purchase->readOne()) {
            // Check if user has permission to view this purchase
            if ($role !== 'admin' && $role !== 'super_admin') {
                if ($this->waste_purchase->buyer_id != $user_id && 
                    $this->waste_purchase->storage_id != $user_id) {
                    $this->response->sendError('Access Denied', 'You do not have permission to view this purchase', 403);
                    return;
                }
            }
            
            // Return purchase data
            $this->response->send([
                'id' => $this->waste_purchase->id,
                'buyer_id' => $this->waste_purchase->buyer_id,
                'buyer_name' => $this->waste_purchase->buyer_name,
                'storage_id' => $this->waste_purchase->storage_id,
                'storage_name' => $this->waste_purchase->storage_name,
                'inventory_id' => $this->waste_purchase->inventory_id,
                'waste_type_id' => $this->waste_purchase->waste_type_id,
                'waste_type_name' => $this->waste_purchase->waste_type_name,
                'waste_subtype_id' => $this->waste_purchase->waste_subtype_id,
                'waste_subtype_name' => $this->waste_purchase->waste_subtype_name,
                'weight' => $this->waste_purchase->weight,
                'amount' => $this->waste_purchase->amount,
                'status' => $this->waste_purchase->status,
                'payment_method' => $this->waste_purchase->payment_method,
                'payment_reference' => $this->waste_purchase->payment_reference,
                'pickup_date' => $this->waste_purchase->pickup_date,
                'pickup_time' => $this->waste_purchase->pickup_time,
                'created_at' => $this->waste_purchase->created_at,
                'updated_at' => $this->waste_purchase->updated_at
            ]);
        } else {
            $this->response->sendError('Not Found', 'Purchase not found', 404);
        }
    }
    
    // Get all purchases for a buyer
    public function getByBuyer($buyer_id) {
        // Set buyer ID
        $this->waste_purchase->buyer_id = $buyer_id;
        
        // Get purchases
        $stmt = $this->waste_purchase->readByBuyer();
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $purchases_arr = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $purchases_arr[] = [
                    'id' => $row['id'],
                    'storage_id' => $row['storage_id'],
                    'storage_name' => $row['storage_name'],
                    'inventory_id' => $row['inventory_id'],
                    'waste_type_id' => $row['waste_type_id'],
                    'waste_type_name' => $row['waste_type_name'],
                    'waste_subtype_id' => $row['waste_subtype_id'],
                    'waste_subtype_name' => $row['waste_subtype_name'],
                    'weight' => $row['weight'],
                    'amount' => $row['amount'],
                    'status' => $row['status'],
                    'payment_method' => $row['payment_method'],
                    'payment_reference' => $row['payment_reference'],
                    'pickup_date' => $row['pickup_date'],
                    'pickup_time' => $row['pickup_time'],
                    'created_at' => $row['created_at']
                ];
            }
            
            $this->response->send($purchases_arr);
        } else {
            $this->response->send([]);
        }
    }
    
    // Get all purchases for a storage
    public function getByStorage($storage_id) {
        // Set storage ID
        $this->waste_purchase->storage_id = $storage_id;
        
        // Get purchases
        $stmt = $this->waste_purchase->readByStorage();
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $purchases_arr = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $purchases_arr[] = [
                    'id' => $row['id'],
                    'buyer_id' => $row['buyer_id'],
                    'buyer_name' => $row['buyer_name'],
                    'inventory_id' => $row['inventory_id'],
                    'waste_type_id' => $row['waste_type_id'],
                    'waste_type_name' => $row['waste_type_name'],
                    'waste_subtype_id' => $row['waste_subtype_id'],
                    'waste_subtype_name' => $row['waste_subtype_name'],
                    'weight' => $row['weight'],
                    'amount' => $row['amount'],
                    'status' => $row['status'],
                    'payment_method' => $row['payment_method'],
                    'payment_reference' => $row['payment_reference'],
                    'pickup_date' => $row['pickup_date'],
                    'pickup_time' => $row['pickup_time'],
                    'created_at' => $row['created_at']
                ];
            }
            
            $this->response->send($purchases_arr);
        } else {
            $this->response->send([]);
        }
    }
    
    // Update purchase status
    public function updateStatus($id, $user_id, $role) {
        // Set purchase ID
        $this->waste_purchase->id = $id;
        $this->waste_purchase->user_id = $user_id;
        
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('status')->in('status', ['paid', 'completed', 'cancelled']);
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set purchase properties
        $this->waste_purchase->status = $data['status'];
        $this->waste_purchase->payment_method = $data['payment_method'] ?? null;
        $this->waste_purchase->payment_reference = $data['payment_reference'] ?? null;
        
        // Update the purchase status
        if ($this->waste_purchase->updateStatus()) {
            $this->response->send([
                'message' => 'Purchase status updated successfully'
            ]);
        } else {
            $this->response->sendError('Update Failed', 'Unable to update purchase status');
        }
    }
    
    // Cancel a purchase
    public function cancel($id, $buyer_id) {
        // Set purchase properties
        $this->waste_purchase->id = $id;
        $this->waste_purchase->buyer_id = $buyer_id;
        
        // Cancel the purchase
        if ($this->waste_purchase->cancel()) {
            $this->response->send([
                'message' => 'Purchase cancelled successfully'
            ]);
        } else {
            $this->response->sendError('Cancellation Failed', 'Unable to cancel purchase. It may not exist, not belong to you, or be in a status that cannot be cancelled.');
        }
    }
}
?>