<?php
require_once __DIR__ . '/../models/storage_inventory.php';
require_once __DIR__ . '/../utils/validator.php';
require_once __DIR__ . '/../utils/response.php';

class StorageController {
    private $db;
    private $storage_inventory;
    private $validator;
    private $response;
    
    public function __construct($db) {
        $this->db = $db;
        $this->storage_inventory = new StorageInventory($db);
        $this->response = new Response();
    }
    
    // Add new inventory item
    public function addInventory($storage_id) {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('waste_type_id')->numeric('waste_type_id')
                        ->required('weight')->numeric('weight')
                        ->required('status')->in('status', ['available', 'reserved', 'sold']);
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set storage inventory properties
        $this->storage_inventory->storage_id = $storage_id;
        $this->storage_inventory->waste_type_id = $data['waste_type_id'];
        $this->storage_inventory->waste_subtype_id = $data['waste_subtype_id'] ?? null;
        $this->storage_inventory->weight = $data['weight'];
        $this->storage_inventory->collection_id = $data['collection_id'] ?? null;
        $this->storage_inventory->status = $data['status'];
        
        // Create the inventory item
        if ($this->storage_inventory->create()) {
            $this->response->send([
                'message' => 'Inventory item added successfully',
                'id' => $this->storage_inventory->id
            ], 201);
        } else {
            $this->response->sendError('Creation Failed', 'Unable to add inventory item');
        }
    }
    
    // Get a single inventory item
    public function getOne($id) {
        // Set inventory ID
        $this->storage_inventory->id = $id;
        
        // Get inventory data
        if ($this->storage_inventory->readOne()) {
            // Return inventory data
            $this->response->send([
                'id' => $this->storage_inventory->id,
                'storage_id' => $this->storage_inventory->storage_id,
                'waste_type_id' => $this->storage_inventory->waste_type_id,
                'waste_type_name' => $this->storage_inventory->waste_type_name,
                'waste_subtype_id' => $this->storage_inventory->waste_subtype_id,
                'waste_subtype_name' => $this->storage_inventory->waste_subtype_name,
                'weight' => $this->storage_inventory->weight,
                'collection_id' => $this->storage_inventory->collection_id,
                'status' => $this->storage_inventory->status,
                'created_at' => $this->storage_inventory->created_at,
                'updated_at' => $this->storage_inventory->updated_at
            ]);
        } else {
            $this->response->sendError('Not Found', 'Inventory item not found', 404);
        }
    }
    
    // Get all inventory items for a storage
    public function getByStorage($storage_id) {
        // Set storage ID
        $this->storage_inventory->storage_id = $storage_id;
        
        // Get inventory items
        $stmt = $this->storage_inventory->readByStorage();
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $inventory_arr = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $inventory_arr[] = [
                    'id' => $row['id'],
                    'waste_type_id' => $row['waste_type_id'],
                    'waste_type_name' => $row['waste_type_name'],
                    'waste_subtype_id' => $row['waste_subtype_id'],
                    'waste_subtype_name' => $row['waste_subtype_name'],
                    'weight' => $row['weight'],
                    'collection_id' => $row['collection_id'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at']
                ];
            }
            
            $this->response->send($inventory_arr);
        } else {
            $this->response->send([]);
        }
    }
    
    // Get all available inventory items (for buyers)
    public function getAvailable() {
        // Get available inventory items
        $stmt = $this->storage_inventory->readAvailable();
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $inventory_arr = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $inventory_arr[] = [
                    'id' => $row['id'],
                    'storage_id' => $row['storage_id'],
                    'storage_name' => $row['storage_name'],
                    'city' => $row['city'],
                    'state' => $row['state'],
                    'waste_type_id' => $row['waste_type_id'],
                    'waste_type_name' => $row['waste_type_name'],
                    'waste_subtype_id' => $row['waste_subtype_id'],
                    'waste_subtype_name' => $row['waste_subtype_name'],
                    'weight' => $row['weight'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at']
                ];
            }
            
            $this->response->send($inventory_arr);
        } else {
            $this->response->send([]);
        }
    }
    
    // Update inventory status
    public function updateStatus($id, $storage_id) {
        // Set inventory ID
        $this->storage_inventory->id = $id;
        $this->storage_inventory->storage_id = $storage_id;
        
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('status')->in('status', ['available', 'reserved', 'sold']);
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set inventory status
        $this->storage_inventory->status = $data['status'];
        
        // Update the inventory status
        if ($this->storage_inventory->updateStatus()) {
            $this->response->send([
                'message' => 'Inventory status updated successfully'
            ]);
        } else {
            $this->response->sendError('Update Failed', 'Unable to update inventory status');
        }
    }
}
?>