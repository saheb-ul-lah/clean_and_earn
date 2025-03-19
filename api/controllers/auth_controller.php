<?php
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../utils/validator.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/jwt.php';

class AuthController {
    private $db;
    private $user;
    private $validator;
    private $response;
    private $jwt;
    
    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
        $this->response = new Response();
        $this->jwt = new JWT();
    }
    
    // Register a new user
    public function register() {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('name')
                        ->required('email')->email('email')
                        ->required('password')->min('password', 8)
                        ->required('phone')
                        ->required('role')->in('role', ['household', 'collector', 'storage', 'buyer']);
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Check if email already exists
        $this->user->email = $data['email'];
        if ($this->user->emailExists()) {
            $this->response->sendError('Registration Failed', 'Email already exists');
            return;
        }
        
        // Set user properties
        $this->user->name = $data['name'];
        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        $this->user->phone = $data['phone'];
        $this->user->role = $data['role'];
        $this->user->status = 'active'; // Set to 'pending' if email verification is required
        
        // Create the user
        if ($this->user->create()) {
            // Generate JWT token
            $token = $this->jwt->generate([
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'role' => $this->user->role
            ]);
            
            // Return success response with token
            $this->response->send([
                'message' => 'User registered successfully',
                'token' => $token,
                'user' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                    'role' => $this->user->role
                ]
            ]);
        } else {
            $this->response->sendError('Registration Failed', 'Unable to register user');
        }
    }
    
    // Login user
    public function login() {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('email')->email('email')
                        ->required('password');
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set user properties
        $this->user->email = $data['email'];
        
        // Check if user exists
        if ($this->user->getByEmail()) {
            // Verify password
            if (password_verify($data['password'], $this->user->password)) {
                // Check if user is active
                if ($this->user->status !== 'active') {
                    $this->response->sendError('Login Failed', 'Your account is not active. Please contact support.', 403);
                    return;
                }
                
                // Generate JWT token
                $token = $this->jwt->generate([
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'role' => $this->user->role
                ]);
                
                // Return success response with token
                $this->response->send([
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'phone' => $this->user->phone,
                        'role' => $this->user->role,
                        'total_points' => $this->user->total_points
                    ]
                ]);
            } else {
                $this->response->sendError('Login Failed', 'Invalid credentials', 401);
            }
        } else {
            $this->response->sendError('Login Failed', 'Invalid credentials', 401);
        }
    }
    
    // Forgot password
    public function forgotPassword() {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('email')->email('email');
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set user properties
        $this->user->email = $data['email'];
        
        // Check if user exists
        if ($this->user->getByEmail()) {
            // Generate reset token
            if ($this->user->createResetToken()) {
                // In a real application, send an email with the reset link
                // For this API, we'll just return the token
                
                $this->response->send([
                    'message' => 'Password reset instructions have been sent to your email',
                    'token' => $this->user->reset_token // Remove this in production
                ]);
            } else {
                $this->response->sendError('Forgot Password Failed', 'Unable to process your request');
            }
        } else {
            // Don't reveal that the user doesn't exist for security reasons
            $this->response->send([
                'message' => 'If your email is registered, you will receive password reset instructions'
            ]);
        }
    }
    
    // Reset password
    public function resetPassword() {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('token')
                        ->required('password')->min('password', 8)
                        ->required('confirm_password')
                        ->matches('password', 'confirm_password');
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set user properties
        $this->user->reset_token = $data['token'];
        
        // Verify token
        if ($this->user->verifyResetToken()) {
            // Set new password
            $this->user->password = $data['password'];
            
            // Reset password
            if ($this->user->resetPassword()) {
                $this->response->send([
                    'message' => 'Password has been reset successfully'
                ]);
            } else {
                $this->response->sendError('Reset Password Failed', 'Unable to reset password');
            }
        } else {
            $this->response->sendError('Reset Password Failed', 'Invalid or expired token');
        }
    }
    
    // Change password (authenticated)
    public function changePassword($user_id) {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('current_password')
                        ->required('new_password')->min('new_password', 8)
                        ->required('confirm_password')
                        ->matches('new_password', 'confirm_password');
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set user properties
        $this->user->id = $user_id;
        
        // Get user data
        if ($this->user->readOne()) {
            // Verify current password
            if (password_verify($data['current_password'], $this->user->password)) {
                // Set new password
                $this->user->password = $data['new_password'];
                
                // Update password
                if ($this->user->updatePassword()) {
                    $this->response->send([
                        'message' => 'Password changed successfully'
                    ]);
                } else {
                    $this->response->sendError('Change Password Failed', 'Unable to change password');
                }
            } else {
                $this->response->sendError('Change Password Failed', 'Current password is incorrect');
            }
        } else {
            $this->response->sendError('Change Password Failed', 'User not found');
        }
    }
}
?>