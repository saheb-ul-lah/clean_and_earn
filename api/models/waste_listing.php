<?php
class WasteListing {
    // Database connection and table name
    private $conn;
    private $table_name = "waste_listings";

    // Object properties
    public $id;
    public $user_id;
    public $waste_type_id;
    public $waste_subtype_id;
    public $weight;
    public $quantity;
    public $description;
    public $pickup_date;
    public $pickup_time_slot;
    public $pickup_address;
    public $status;
    public $created_at;
    public $updated_at;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create waste listing
    public function create() {
        // Sanitize inputs
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->waste_type_id = htmlspecialchars(strip_tags($this->waste_type_id));
        $this->waste_subtype_id = $this->waste_subtype_id ? htmlspecialchars(strip_tags($this->waste_subtype_id)) : null;
        $this->weight = htmlspecialchars(strip_tags($this->weight));
        $this->quantity = $this->quantity ? htmlspecialchars(strip_tags($this->quantity)) : null;
        $this->description = $this->description ? htmlspecialchars(strip_tags($this->description)) : null;
        $this->pickup_date = htmlspecialchars(strip_tags($this->pickup_date));
        $this->pickup_time_slot = htmlspecialchars(strip_tags($this->pickup_time_slot));
        $this->pickup_address = htmlspecialchars(strip_tags($this->pickup_address));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET user_id=:user_id, waste_type_id=:waste_type_id, waste_subtype_id=:waste_subtype_id,
                      weight=:weight, quantity=:quantity, description=:description,
                      pickup_date=:pickup_date, pickup_time_slot=:pickup_time_slot,
                      pickup_address=:pickup_address, status=:status";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":waste_type_id", $this->waste_type_id);
        $stmt->bindParam(":waste_subtype_id", $this->waste_subtype_id);
        $stmt->bindParam(":weight", $this->weight);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":pickup_date", $this->pickup_date);
        $stmt->bindParam(":pickup_time_slot", $this->pickup_time_slot);
        $stmt->bindParam(":pickup_address", $this->pickup_address);
        $stmt->bindParam(":status", $this->status);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Read single waste listing
    public function readOne() {
        // Query to read single record
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind id of listing to be read
        $stmt->bindParam(1, $this->id);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Set values to object properties
        if ($row) {
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->waste_type_id = $row['waste_type_id'];
            $this->waste_subtype_id = $row['waste_subtype_id'];
            $this->weight = $row['weight'];
            $this->quantity = $row['quantity'];
            $this->description = $row['description'];
            $this->pickup_date = $row['pickup_date'];
            $this->pickup_time_slot = $row['pickup_time_slot'];
            $this->pickup_address = $row['pickup_address'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    // Read waste listings by user
    public function readByUser() {
        // Query to read records
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = ? ORDER BY created_at DESC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind user_id
        $stmt->bindParam(1, $this->user_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read active waste listings
    public function readActive() {
        // Query to read records
        $query = "SELECT wl.*, wt.name as waste_type_name, ws.name as waste_subtype_name, u.name as user_name
                  FROM " . $this->table_name . " wl
                  LEFT JOIN waste_types wt ON wl.waste_type_id = wt.id
                  LEFT JOIN waste_subtypes ws ON wl.waste_subtype_id = ws.id
                  LEFT JOIN users u ON wl.user_id = u.id
                  WHERE wl.status = 'pending'
                  ORDER BY wl.pickup_date ASC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Update waste listing
    public function update() {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->waste_type_id = htmlspecialchars(strip_tags($this->waste_type_id));
        $this->waste_subtype_id = $this->waste_subtype_id ? htmlspecialchars(strip_tags($this->waste_subtype_id)) : null;
        $this->weight = htmlspecialchars(strip_tags($this->weight));
        $this->quantity = $this->quantity ? htmlspecialchars(strip_tags($this->quantity)) : null;
        $this->description = $this->description ? htmlspecialchars(strip_tags($this->description)) : null;
        $this->pickup_date = htmlspecialchars(strip_tags($this->pickup_date));
        $this->pickup_time_slot = htmlspecialchars(strip_tags($this->pickup_time_slot));
        $this->pickup_address = htmlspecialchars(strip_tags($this->pickup_address));
        
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                  SET waste_type_id=:waste_type_id, waste_subtype_id=:waste_subtype_id,
                      weight=:weight, quantity=:quantity, description=:description,
                      pickup_date=:pickup_date, pickup_time_slot=:pickup_time_slot,
                      pickup_address=:pickup_address
                  WHERE id=:id AND status='pending'";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":waste_type_id", $this->waste_type_id);
        $stmt->bindParam(":waste_subtype_id", $this->waste_subtype_id);
        $stmt->bindParam(":weight", $this->weight);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":pickup_date", $this->pickup_date);
        $stmt->bindParam(":pickup_time_slot", $this->pickup_time_slot);
        $stmt->bindParam(":pickup_address", $this->pickup_address);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Update waste listing status
    public function updateStatus() {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Query to update status
        $query = "UPDATE " . $this->table_name . "
                  SET status=:status
                  WHERE id=:id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete waste listing
    public function delete() {
        // Sanitize input
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        
        // Query to delete record
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id AND user_id=:user_id AND status='pending'";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":user_id", $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>