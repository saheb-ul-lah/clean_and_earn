<?php
require_once __DIR__ . '/../models/waste_collection.php';
require_once __DIR__ . '/../utils/validator.php';
require_once __DIR__ . '/../utils/response.php';

class WasteCollectionController {
    private $db;
    private $waste_collection;
    private $validator;
    private $response;
    
    public function __construct($db) {
        $this->db = $db;
        $this->waste_collection = new WasteCollection($db);
        $this->response = new Response();
    }
    
    // Create a new waste collection (assign collector to listing)
    public function create($collector_id) {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('listing_id')->numeric('listing_id');
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set waste collection properties
        $this->waste_collection->listing_id = $data['listing_id'];
        $this->waste_collection->collector_id = $collector_id;
        $this->waste_collection->status = 'assigned';
        $this->waste_collection->notes = $data['notes'] ?? null;
        
        // Create the waste collection
        if ($this->waste_collection->create()) {
            $this->response->send([
                'message' => 'Waste collection assigned successfully',
                'id' => $this->waste_collection->id
            ], 201);
        } else {
            $this->response->sendError('Assignment Failed', 'Unable to assign waste collection. The listing may already be assigned or completed.');
        }
    }
    
    // Get a single waste collection
    public function getOne($id, $user_id, $role) {
        // Set waste collection ID
        $this->waste_collection->id = $id;
        
        // Get waste collection data
        if ($this->waste_collection->readOne()) {
            // Check if user has permission to view this collection
            if ($role !== 'admin' && $role !== 'super_admin') {
                if ($this->waste_collection->collector_id != $user_id && 
                    $this->waste_collection->household_id != $user_id) {
                    $this->response->sendError('Access Denied', 'You do not have permission to view this collection', 403);
                    return;
                }
            }
            
            // Return waste collection data
            $this->response->send([
                'id' => $this->waste_collection->id,
                'listing_id' => $this->waste_collection->listing_id,
                'collector_id' => $this->waste_collection->collector_id,
                'collector_name' => $this->waste_collection->collector_name,
                'household_id' => $this->waste_collection->household_id,
                'household_name' => $this->waste_collection->household_name,
                'waste_type_id' => $this->waste_collection->waste_type_id,
                'waste_type_name' => $this->waste_collection->waste_type_name,
                'waste_subtype_id' => $this->waste_collection->waste_subtype_id,
                'waste_subtype_name' => $this->waste_collection->waste_subtype_name,
                'estimated_weight' => $this->waste_collection->estimated_weight,
                'actual_weight' => $this->waste_collection->actual_weight,
                'pickup_date' => $this->waste_collection->pickup_date,
                'pickup_time_slot' => $this->waste_collection->pickup_time_slot,
                'pickup_address' => $this->waste_collection->pickup_address,
                'collection_date' => $this->waste_collection->collection_date,
                'status' => $this->waste_collection->status,
                'listing_status' => $this->waste_collection->listing_status,
                'notes' => $this->waste_collection->notes,
                'created_at' => $this->waste_collection->created_at,
                'updated_at' => $this->waste_collection->updated_at
            ]);
        } else {
            $this->response->sendError('Not Found', 'Waste collection not found', 404);
        }
    }
    
    // Get all waste collections for a collector
    public function getByCollector($collector_id) {
        // Set collector ID
        $this->waste_collection->collector_id = $collector_id;
        
        // Get waste collections
        $stmt = $this->waste_collection->readByCollector();
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $collections_arr = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $collections_arr[] = [
                    'id' => $row['id'],
                    'listing_id' => $row['listing_id'],
                    'household_id' => $row['household_id'],
                    'household_name' => $row['household_name'],
                    'waste_type_id' => $row['waste_type_id'],
                    'waste_type_name' => $row['waste_type_name'],
                    'waste_subtype_id' => $row['waste_subtype_id'],
                    'waste_subtype_name' => $row['waste_subtype_name'],
                    'estimated_weight' => $row['estimated_weight'],
                    'actual_weight' => $row['actual_weight'],
                    'pickup_date' => $row['pickup_date'],
                    'pickup_time_slot' => $row['pickup_time_slot'],
                    'pickup_address' => $row['pickup_address'],
                    'collection_date' => $row['collection_date'],
                    'status' => $row['status'],
                    'listing_status' => $row['listing_status'],
                    'notes' => $row['notes'],
                    'created_at' => $row['created_at']
                ];
            }
            
            $this->response->send($collections_arr);
        } else {
            $this->response->send([]);
        }
    }
    
    // Get all waste collections for a household
    public function getByHousehold($household_id) {
        // Set household ID
        $this->waste_collection->household_id = $household_id;
        
        // Get waste collections
        $stmt = $this->waste_collection->readByHousehold();
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $collections_arr = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $collections_arr[] = [
                    'id' => $row['id'],
                    'listing_id' => $row['listing_id'],
                    'collector_id' => $row['collector_id'],
                    'collector_name' => $row['collector_name'],
                    'waste_type_id' => $row['waste_type_id'],
                    'waste_type_name' => $row['waste_type_name'],
                    'waste_subtype_id' => $row['waste_subtype_id'],
                    'waste_subtype_name' => $row['waste_subtype_name'],
                    'estimated_weight' => $row['estimated_weight'],
                    'actual_weight' => $row['actual_weight'],
                    'pickup_date' => $row['pickup_date'],
                    'pickup_time_slot' => $row['pickup_time_slot'],
                    'pickup_address' => $row['pickup_address'],
                    'collection_date' => $row['collection_date'],
                    'status' => $row['status'],
                    'listing_status' => $row['listing_status'],
                    'notes' => $row['notes'],
                    'created_at' => $row['created_at']
                ];
            }
            
            $this->response->send($collections_arr);
        } else {
            $this->response->send([]);
        }
    }
    
    // Update waste collection status (mark as collected)
    public function updateCollection($id, $collector_id) {
        // Set waste collection ID
        $this->waste_collection->id = $id;
        $this->waste_collection->collector_id = $collector_id;
        
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('actual_weight')->numeric('actual_weight')
                        ->required('status')->in('status', ['in_progress', 'collected']);
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set waste collection properties
        $this->waste_collection->actual_weight = $data['actual_weight'];
        $this->waste_collection->status = $data['status'];
        $this->waste_collection->notes = $data['notes'] ?? null;
        
        // Update the waste collection
        if ($this->waste_collection->updateCollection()) {
            $this->response->send([
                'message' => 'Waste collection updated successfully'
            ]);
        } else {
            $this->response->sendError('Update Failed', 'Unable to update waste collection');
        }
    }
}
?>