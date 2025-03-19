<?php
require_once __DIR__ . '/../models/waste_listing.php';
require_once __DIR__ . '/../utils/validator.php';
require_once __DIR__ . '/../utils/response.php';

class WasteListingController {
    private $db;
    private $waste_listing;
    private $validator;
    private $response;
    
    public function __construct($db) {
        $this->db = $db;
        $this->waste_listing = new WasteListing($db);
        $this->response = new Response();
    }
    
    // Create a new waste listing
    public function create($user_id) {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('waste_type_id')->numeric('waste_type_id')
                        ->required('weight')->numeric('weight')
                        ->required('pickup_date')->date('pickup_date')
                        ->required('pickup_time_slot')
                        ->required('pickup_address');
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set waste listing properties
        $this->waste_listing->user_id = $user_id;
        $this->waste_listing->waste_type_id = $data['waste_type_id'];
        $this->waste_listing->waste_subtype_id = $data['waste_subtype_id'] ?? null;
        $this->waste_listing->weight = $data['weight'];
        $this->waste_listing->quantity = $data['quantity'] ?? null;
        $this->waste_listing->description = $data['description'] ?? null;
        $this->waste_listing->pickup_date = $data['pickup_date'];
        $this->waste_listing->pickup_time_slot = $data['pickup_time_slot'];
        $this->waste_listing->pickup_address = $data['pickup_address'];
        $this->waste_listing->status = 'pending';
        
        // Create the waste listing
        if ($this->waste_listing->create()) {
            $this->response->send([
                'message' => 'Waste listing created successfully',
                'id' => $this->waste_listing->id
            ], 201);
        } else {
            $this->response->sendError('Creation Failed', 'Unable to create waste listing');
        }
    }
    
    // Get a single waste listing
    public function getOne($id, $user_id = null, $role = null) {
        // Set waste listing ID
        $this->waste_listing->id = $id;
        
        // Get waste listing data
        if ($this->waste_listing->readOne()) {
            // Check if user has permission to view this listing
            if ($user_id !== null && $role !== 'admin' && $role !== 'super_admin') {
                if ($this->waste_listing->user_id != $user_id && $role !== 'collector') {
                    $this->response->sendError('Access Denied', 'You do not have permission to view this listing', 403);
                    return;
                }
            }
            
            // Return waste listing data
            $this->response->send([
                'id' => $this->waste_listing->id,
                'user_id' => $this->waste_listing->user_id,
                'waste_type_id' => $this->waste_listing->waste_type_id,
                'waste_subtype_id' => $this->waste_listing->waste_subtype_id,
                'weight' => $this->waste_listing->weight,
                'quantity' => $this->waste_listing->quantity,
                'description' => $this->waste_listing->description,
                'pickup_date' => $this->waste_listing->pickup_date,
                'pickup_time_slot' => $this->waste_listing->pickup_time_slot,
                'pickup_address' => $this->waste_listing->pickup_address,
                'status' => $this->waste_listing->status,
                'created_at' => $this->waste_listing->created_at,
                'updated_at' => $this->waste_listing->updated_at
            ]);
        } else {
            $this->response->sendError('Not Found', 'Waste listing not found', 404);
        }
    }
    
    // Get all waste listings for a user
    public function getByUser($user_id) {
        // Set user ID
        $this->waste_listing->user_id = $user_id;
        
        // Get waste listings
        $stmt = $this->waste_listing->readByUser();
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $listings_arr = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $listings_arr[] = [
                    'id' => $row['id'],
                    'waste_type_id' => $row['waste_type_id'],
                    'waste_subtype_id' => $row['waste_subtype_id'],
                    'weight' => $row['weight'],
                    'quantity' => $row['quantity'],
                    'description' => $row['description'],
                    'pickup_date' => $row['pickup_date'],
                    'pickup_time_slot' => $row['pickup_time_slot'],
                    'pickup_address' => $row['pickup_address'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at']
                ];
            }
            
            $this->response->send($listings_arr);
        } else {
            $this->response->send([]);
        }
    }
    
    // Get active waste listings (for collectors)
    public function getActive() {
        // Get active listings
        $stmt = $this->waste_listing->readActive();
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $listings_arr = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $listings_arr[] = [
                    'id' => $row['id'],
                    'user_id' => $row['user_id'],
                    'user_name' => $row['user_name'],
                    'waste_type_id' => $row['waste_type_id'],
                    'waste_type_name' => $row['waste_type_name'],
                    'waste_subtype_id' => $row['waste_subtype_id'],
                    'waste_subtype_name' => $row['waste_subtype_name'],
                    'weight' => $row['weight'],
                    'pickup_date' => $row['pickup_date'],
                    'pickup_time_slot' => $row['pickup_time_slot'],
                    'pickup_address' => $row['pickup_address'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at']
                ];
            }
            
            $this->response->send($listings_arr);
        } else {
            $this->response->send([]);
        }
    }
    
    // Update a waste listing
    public function update($id, $user_id) {
        // Set waste listing ID
        $this->waste_listing->id = $id;
        
        // Check if listing exists and belongs to user
        if ($this->waste_listing->readOne()) {
            if ($this->waste_listing->user_id != $user_id) {
                $this->response->sendError('Access Denied', 'You do not have permission to update this listing', 403);
                return;
            }
            
            if ($this->waste_listing->status !== 'pending') {
                $this->response->sendError('Update Failed', 'Cannot update a listing that is not in pending status', 400);
                return;
            }
            
            // Get posted data
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validate input
            $this->validator = new Validator($data);
            $this->validator->required('waste_type_id')->numeric('waste_type_id')
                            ->required('weight')->numeric('weight')
                            ->required('pickup_date')->date('pickup_date')
                            ->required('pickup_time_slot')
                            ->required('pickup_address');
            
            if ($this->validator->fails()) {
                $this->response->sendValidationError($this->validator->getErrors());
                return;
            }
            
            // Set waste listing properties
            $this->waste_listing->waste_type_id = $data['waste_type_id'];
            $this->waste_listing->waste_subtype_id = $data['waste_subtype_id'] ?? null;
            $this->waste_listing->weight = $data['weight'];
            $this->waste_listing->quantity = $data['quantity'] ?? null;
            $this->waste_listing->description = $data['description'] ?? null;
            $this->waste_listing->pickup_date = $data['pickup_date'];
            $this->waste_listing->pickup_time_slot = $data['pickup_time_slot'];
            $this->waste_listing->pickup_address = $data['pickup_address'];
            
            // Update the waste listing
            if ($this->waste_listing->update()) {
                $this->response->send([
                    'message' => 'Waste listing updated successfully'
                ]);
            } else {
                $this->response->sendError('Update Failed', 'Unable to update waste listing');
            }
        } else {
            $this->response->sendError('Not Found', 'Waste listing not found', 404);
        }
    }
    
    // Delete a waste listing
    public function delete($id, $user_id) {
        // Set waste listing properties
        $this->waste_listing->id = $id;
        $this->waste_listing->user_id = $user_id;
        
        // Delete the waste listing
        if ($this->waste_listing->delete()) {
            $this->response->send([
                'message' => 'Waste listing deleted successfully'
            ]);
        } else {
            $this->response->sendError('Deletion Failed', 'Unable to delete waste listing. It may not exist, not belong to you, or not be in pending status.');
        }
    }
}
?>