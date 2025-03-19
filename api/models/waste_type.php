<?php
class WasteType {
    // Database connection and table name
    private $conn;
    private $table_name = "waste_types";

    // Object properties
    public $id;
    public $name;
    public $description;
    public $rate_per_kg;
    public $created_at;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all waste types
    public function read() {
        // Query to read all records
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read single waste type
    public function readOne() {
        // Query to read single record
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind id of waste type to be read
        $stmt->bindParam(1, $this->id);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Set values to object properties
        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->rate_per_kg = $row['rate_per_kg'];
            $this->created_at = $row['created_at'];
            return true;
        }
        
        return false;
    }
    
    // Read waste subtypes by type
    public function readSubtypes() {
        // Query to read subtypes
        $query = "SELECT * FROM waste_subtypes WHERE waste_type_id = ? ORDER BY name ASC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind waste_type_id
        $stmt->bindParam(1, $this->id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Create waste type (admin only)
    public function create() {
        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->rate_per_kg = htmlspecialchars(strip_tags($this->rate_per_kg));
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET name=:name, description=:description, rate_per_kg=:rate_per_kg";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":rate_per_kg", $this->rate_per_kg);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Update waste type (admin only)
    public function update() {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->rate_per_kg = htmlspecialchars(strip_tags($this->rate_per_kg));
        
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                  SET name=:name, description=:description, rate_per_kg=:rate_per_kg
                  WHERE id=:id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":rate_per_kg", $this->rate_per_kg);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Create waste subtype (admin only)
    public function createSubtype($name, $description, $rate_per_kg) {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $rate_per_kg = htmlspecialchars(strip_tags($rate_per_kg));
        
        // Query to insert record
        $query = "INSERT INTO waste_subtypes
                  SET waste_type_id=:waste_type_id, name=:name, description=:description, rate_per_kg=:rate_per_kg";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":waste_type_id", $this->id);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":rate_per_kg", $rate_per_kg);
        
        // Execute query
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
}
?>