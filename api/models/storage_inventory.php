<?php
class StorageInventory {
    // Database connection and table name
    private $conn;
    private $table_name = "storage_inventory";

    // Object properties
    public $id;
    public $storage_id;
    public $waste_type_id;
    public $waste_subtype_id;
    public $weight;
    public $collection_id;
    public $status;
    public $created_at;
    public $updated_at;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create inventory item
    public function create() {
        // Sanitize inputs
        $this->storage_id = htmlspecialchars(strip_tags($this->storage_id));
        $this->waste_type_id = htmlspecialchars(strip_tags($this->waste_type_id));
        $this->waste_subtype_id = $this->waste_subtype_id ? htmlspecialchars(strip_tags($this->waste_subtype_id)) : null;
        $this->weight = htmlspecialchars(strip_tags($this->weight));
        $this->collection_id = $this->collection_id ? htmlspecialchars(strip_tags($this->collection_id)) : null;
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET storage_id=:storage_id, waste_type_id=:waste_type_id, 
                      waste_subtype_id=:waste_subtype_id, weight=:weight,
                      collection_id=:collection_id, status=:status";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":storage_id", $this->storage_id);
        $stmt->bindParam(":waste_type_id", $this->waste_type_id);
        $stmt->bindParam(":waste_subtype_id", $this->waste_subtype_id);
        $stmt->bindParam(":weight", $this->weight);
        $stmt->bindParam(":collection_id", $this->collection_id);
        $stmt->bindParam(":status", $this->status);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            // If from collection, update collection status
            if ($this->collection_id) {
                $query = "UPDATE waste_collections SET status='delivered' WHERE id=:collection_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":collection_id", $this->collection_id);
                $stmt->execute();
            }
            
            return true;
        }
        
        return false;
    }
    
    // Read single inventory item
    public function readOne() {
        // Query to read single record
        $query = "SELECT si.*, wt.name as waste_type_name, ws.name as waste_subtype_name
                  FROM " . $this->table_name . " si
                  JOIN waste_types wt ON si.waste_type_id = wt.id
                  LEFT JOIN waste_subtypes ws ON si.waste_subtype_id = ws.id
                  WHERE si.id = ? LIMIT 0,1";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind id of inventory to be read
        $stmt->bindParam(1, $this->id);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Set values to object properties
        if ($row) {
            $this->id = $row['id'];
            $this->storage_id = $row['storage_id'];
            $this->waste_type_id = $row['waste_type_id'];
            $this->waste_subtype_id = $row['waste_subtype_id'];
            $this->weight = $row['weight'];
            $this->collection_id = $row['collection_id'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            // Additional data
            $this->waste_type_name = $row['waste_type_name'];
            $this->waste_subtype_name = $row['waste_subtype_name'];
            
            return true;
        }
        
        return false;
    }
    
    // Read inventory by storage
    public function readByStorage() {
        // Query to read records
        $query = "SELECT si.*, wt.name as waste_type_name, ws.name as waste_subtype_name
                  FROM " . $this->table_name . " si
                  JOIN waste_types wt ON si.waste_type_id = wt.id
                  LEFT JOIN waste_subtypes ws ON si.waste_subtype_id = ws.id
                  WHERE si.storage_id = ?
                  ORDER BY si.created_at DESC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind storage_id
        $stmt->bindParam(1, $this->storage_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read available inventory
    public function readAvailable() {
        // Query to read records
        $query = "SELECT si.*, wt.name as waste_type_name, ws.name as waste_subtype_name,
                         u.name as storage_name, u.city, u.state
                  FROM " . $this->table_name . " si
                  JOIN waste_types wt ON si.waste_type_id = wt.id
                  LEFT JOIN waste_subtypes ws ON si.waste_subtype_id = ws.id
                  JOIN users u ON si.storage_id = u.id
                  WHERE si.status = 'available'
                  ORDER BY si.created_at DESC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Update inventory status
    public function updateStatus() {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Query to update status
        $query = "UPDATE " . $this->table_name . "
                  SET status=:status
                  WHERE id=:id AND storage_id=:storage_id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":storage_id", $this->storage_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>