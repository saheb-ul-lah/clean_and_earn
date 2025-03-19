<?php
class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";

    // Object properties
    public $id;
    public $name;
    public $email;
    public $password;
    public $phone;
    public $address;
    public $city;
    public $state;
    public $pincode;
    public $role;
    public $status;
    public $profile_image;
    public $total_points;
    public $reset_token;
    public $reset_token_expires;
    public $created_at;
    public $updated_at;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create user
    public function create() {
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Hash the password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET name=:name, email=:email, password=:password, phone=:phone, 
                      role=:role, status=:status";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":status", $this->status);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Read single user
    public function readOne() {
        // Query to read single record
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind id of user to be read
        $stmt->bindParam(1, $this->id);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Set values to object properties
        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->city = $row['city'];
            $this->state = $row['state'];
            $this->pincode = $row['pincode'];
            $this->role = $row['role'];
            $this->status = $row['status'];
            $this->profile_image = $row['profile_image'];
            $this->total_points = $row['total_points'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Get user by email
    public function getByEmail() {
        // Query to read single record
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Bind email of user
        $stmt->bindParam(1, $this->email);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Set values to object properties
        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->city = $row['city'];
            $this->state = $row['state'];
            $this->pincode = $row['pincode'];
            $this->role = $row['role'];
            $this->status = $row['status'];
            $this->profile_image = $row['profile_image'];
            $this->total_points = $row['total_points'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Update user
    public function update() {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->state = htmlspecialchars(strip_tags($this->state));
        $this->pincode = htmlspecialchars(strip_tags($this->pincode));
        
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                  SET name=:name, phone=:phone, address=:address, 
                      city=:city, state=:state, pincode=:pincode
                  WHERE id=:id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":state", $this->state);
        $stmt->bindParam(":pincode", $this->pincode);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Update password
    public function updatePassword() {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Hash the password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Query to update password
        $query = "UPDATE " . $this->table_name . "
                  SET password=:password
                  WHERE id=:id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Create password reset token
    public function createResetToken() {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Query to update token
        $query = "UPDATE " . $this->table_name . "
                  SET reset_token=:token, reset_token_expires=:expires
                  WHERE email=:email";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":expires", $expires);
        $stmt->bindParam(":email", $this->email);
        
        // Execute query
        if ($stmt->execute()) {
            $this->reset_token = $token;
            $this->reset_token_expires = $expires;
            return true;
        }
        
        return false;
    }
    
    // Verify reset token
    public function verifyResetToken() {
        // Query to check token
        $query = "SELECT id, reset_token_expires FROM " . $this->table_name . "
                  WHERE reset_token=:token LIMIT 0,1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":token", $this->reset_token);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            // Check if token is expired
            $expires = strtotime($row['reset_token_expires']);
            if ($expires > time()) {
                $this->id = $row['id'];
                return true;
            }
        }
        
        return false;
    }
    
    // Reset password using token
    public function resetPassword() {
        // Hash the password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Query to update password and clear token
        $query = "UPDATE " . $this->table_name . "
                  SET password=:password, reset_token=NULL, reset_token_expires=NULL
                  WHERE id=:id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Check if email exists
    public function emailExists() {
        // Query to check if email exists
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Bind email
        $stmt->bindParam(1, $this->email);
        
        // Execute query
        $stmt->execute();
        
        // Get number of rows
        $num = $stmt->rowCount();
        
        // If email exists, return true
        if ($num > 0) {
            return true;
        }
        
        return false;
    }
}
?>