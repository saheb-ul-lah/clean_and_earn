<?php
require_once __DIR__ . '/../models/waste_type.php';
require_once __DIR__ . '/../utils/validator.php';
require_once __DIR__ . '/../utils/response.php';

class WasteTypeController {
    private $db;
    private $waste_type;
    private $validator;
    private $response;
    
    public function __construct($db) {
        $this->db = $db;
        $this->waste_type = new WasteType($db);
        $this->response = new Response();
    }
    
    // Get all waste types
    public function getAll() {
        // Get waste types
        $stmt = $this->waste_type->read();
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $types_arr = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $types_arr[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'rate_per_kg' => $row['rate_per_kg'],
                    'created_at' => $row['created_at']
                ];
            }
            
            $this->response->send($types_arr);
        } else {
            $this->response->send([]);
        }
    }
    
    // Get a single waste type
    public function getOne($id) {
        // Set waste type ID
        $this->waste_type->id = $id;
        
        // Get waste type data
        if ($this->waste_type->readOne()) {
            // Return waste type data
            $this->response->send([
                'id' => $this->waste_type->id,
                'name' => $this->waste_type->name,
                'description' => $this->waste_type->description,
                'rate_per_kg' => $this->waste_type->rate_per_kg,
                'created_at' => $this->waste_type->created_at
            ]);
        } else {
            $this->response->sendError('Not Found', 'Waste type not found', 404);
        }
    }
    
    // Get subtypes for a waste type
    public function getSubtypes($id) {
        // Set waste type ID
        $this->waste_type->id = $id;
        
        // Get waste subtypes
        $stmt = $this->waste_type->readSubtypes();
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $subtypes_arr = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $subtypes_arr[] = [
                    'id' => $row['id'],
                    'waste_type_id' => $row['waste_type_id'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'rate_per_kg' => $row['rate_per_kg'],
                    'created_at' => $row['created_at']
                ];
            }
            
            $this->response->send($subtypes_arr);
        } else {
            $this->response->send([]);
        }
    }
    
    // Create a new waste type (admin only)
    public function create() {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('name')
                        ->required('rate_per_kg')->numeric('rate_per_kg');
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set waste type properties
        $this->waste_type->name = $data['name'];
        $this->waste_type->description = $data['description'] ?? null;
        $this->waste_type->rate_per_kg = $data['rate_per_kg'];
        
        // Create the waste type
        if ($this->waste_type->create()) {
            $this->response->send([
                'message' => 'Waste type created successfully',
                'id' => $this->waste_type->id
            ], 201);
        } else {
            $this->response->sendError('Creation Failed', 'Unable to create waste type');
        }
    }
    
    // Update a waste type (admin only)
    public function update($id) {
        // Set waste type ID
        $this->waste_type->id = $id;
        
        // Check if waste type exists
        if ($this->waste_type->readOne()) {
            // Get posted data
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validate input
            $this->validator = new Validator($data);
            $this->validator->required('name')
                            ->required('rate_per_kg')->numeric('rate_per_kg');
            
            if ($this->validator->fails()) {
                $this->response->sendValidationError($this->validator->getErrors());
                return;
            }
            
            // Set waste type properties
            $this->waste_type->name = $data['name'];
            $this->waste_type->description = $data['description'] ?? null;
            $this->waste_type->rate_per_kg = $data['rate_per_kg'];
            
            // Update the waste type
            if ($this->waste_type->update()) {
                $this->response->send([
                    'message' => 'Waste type updated successfully'
                ]);
            } else {
                $this->response->sendError('Update Failed', 'Unable to update waste type');
            }
        } else {
            $this->response->sendError('Not Found', 'Waste type not found', 404);
        }
    }
    
    // Create a new waste subtype (admin only)
    public function createSubtype($id) {
        // Set waste type ID
        $this->waste_type->id = $id;
        
        // Check if waste type exists
        if ($this->waste_type->readOne()) {
            // Get posted data
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validate input
            $this->validator = new Validator($data);
            $this->validator->required('name')
                            ->required('rate_per_kg')->numeric('rate_per_kg');
            
            if ($this->validator->fails()) {
                $this->response->sendValidationError($this->validator->getErrors());
                return;
            }
            
            // Create the waste subtype
            $subtype_id = $this->waste_type->createSubtype(
                $data['name'],
                $data['description'] ?? null,
                $data['rate_per_kg']
            );
            
            if ($subtype_id) {
                $this->response->send([
                    'message' => 'Waste subtype created successfully',
                    'id' => $subtype_id
                ], 201);
            } else {
                $this->response->sendError('Creation Failed', 'Unable to create waste subtype');
            }
        } else {
            $this->response->sendError('Not Found', 'Waste type not found', 404);
        }
    }
}
?>