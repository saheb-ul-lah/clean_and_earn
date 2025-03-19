<?php
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../utils/validator.php';
require_once __DIR__ . '/../utils/response.php';

class UserController {
    private $db;
    private $user;
    private $validator;
    private $response;
    
    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
        $this->response = new Response();
    }
    
    // Get user profile
    public function getProfile($user_id) {
        // Set user properties
        $this->user->id = $user_id;
        
        // Get user data
        if ($this->user->readOne()) {
            // Return user data
            $this->response->send([
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'address' => $this->user->address,
                'city' => $this->user->city,
                'state' => $this->user->state,
                'pincode' => $this->user->pincode,
                'role' => $this->user->role,
                'profile_image' => $this->user->profile_image,
                'total_points' => $this->user->total_points,
                'created_at' => $this->user->created_at
            ]);
        } else {
            $this->response->sendError('User Not Found', 'User with the given ID was not found', 404);
        }
    }
    
    // Update user profile
    public function updateProfile($user_id) {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('name')
                        ->required('phone');
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set user properties
        $this->user->id = $user_id;
        $this->user->name = $data['name'];
        $this->user->phone = $data['phone'];
        $this->user->address = $data['address'] ?? null;
        $this->user->city = $data['city'] ?? null;
        $this->user->state = $data['state'] ?? null;
        $this->user->pincode = $data['pincode'] ?? null;
        
        // Update user
        if ($this->user->update()) {
            $this->response->send([
                'message' => 'Profile updated successfully'
            ]);
        } else {
            $this->response->sendError('Update Failed', 'Unable to update profile');
        }
    }
}
?>