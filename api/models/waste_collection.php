<?php
class WasteCollection {
    // Database connection and table name
    private $conn;
    private $table_name = "waste_collections";

    // Object properties
    public $id;
    public $listing_id;
    public $collector_id;
    public $actual_weight;
    public $collection_date;
    public $status;
    public $notes;
    public $created_at;
    public $updated_at;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create waste collection
    public function create() {
        // Sanitize inputs
        $this->listing_id = htmlspecialchars(strip_tags($this->listing_id));
        $this->collector_id = htmlspecialchars(strip_tags($this->collector_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->notes = $this->notes ? htmlspecialchars(strip_tags($this->notes)) : null;
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET listing_id=:listing_id, collector_id=:collector_id,
                      status=:status, notes=:notes";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":listing_id", $this->listing_id);
        $stmt->bindParam(":collector_id", $this->collector_id);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes", $this->notes);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            // Update waste listing status to assigned
            $query = "UPDATE waste_listings SET status='assigned' WHERE id=:listing_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":listing_id", $this->listing_id);
            $stmt->execute();
            
            return true;
        }
        
        return false;
    }
    
    // Read single waste collection
    public function readOne() {
        // Query to read single record
        $query = "SELECT wc.*, wl.user_id as household_id, wl.waste_type_id, wl.waste_subtype_id, 
                         wl.weight as estimated_weight, wl.pickup_date, wl.pickup_time_slot, 
                         wl.pickup_address, wl.status as listing_status,
                         u1.name as household_name, u2.name as collector_name,
                         wt.name as waste_type_name, ws.name as waste_subtype_name
                  FROM " . $this->table_name . " wc
                  JOIN waste_listings wl ON wc.listing_id = wl.id
                  JOIN users u1 ON wl.user_id = u1.id
                  JOIN users u2 ON wc.collector_id = u2.id
                  JOIN waste_types wt ON wl.waste_type_id = wt.id
                  LEFT JOIN waste_subtypes ws ON wl.waste_subtype_id = ws.id
                  WHERE wc.id = ? LIMIT 0,1";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind id of collection to be read
        $stmt->bindParam(1, $this->id);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Set values to object properties
        if ($row) {
            $this->id = $row['id'];
            $this->listing_id = $row['listing_id'];
            $this->collector_id = $row['collector_id'];
            $this->actual_weight = $row['actual_weight'];
            $this->collection_date = $row['collection_date'];
            $this->status = $row['status'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            // Additional data
            $this->household_id = $row['household_id'];
            $this->household_name = $row['household_name'];
            $this->collector_name = $row['collector_name'];
            $this->waste_type_id = $row['waste_type_id'];
            $this->waste_type_name = $row['waste_type_name'];
            $this->waste_subtype_id = $row['waste_subtype_id'];
            $this->waste_subtype_name = $row['waste_subtype_name'];
            $this->estimated_weight = $row['estimated_weight'];
            $this->pickup_date = $row['pickup_date'];
            $this->pickup_time_slot = $row['pickup_time_slot'];
            $this->pickup_address = $row['pickup_address'];
            $this->listing_status = $row['listing_status'];
            
            return true;
        }
        
        return false;
    }
    
    // Read waste collections by collector
    public function readByCollector() {
        // Query to read records
        $query = "SELECT wc.*, wl.user_id as household_id, wl.waste_type_id, wl.waste_subtype_id, 
                         wl.weight as estimated_weight, wl.pickup_date, wl.pickup_time_slot, 
                         wl.pickup_address, wl.status as listing_status,
                         u.name as household_name,
                         wt.name as waste_type_name, ws.name as waste_subtype_name
                  FROM " . $this->table_name . " wc
                  JOIN waste_listings wl ON wc.listing_id = wl.id
                  JOIN users u ON wl.user_id = u.id
                  JOIN waste_types wt ON wl.waste_type_id = wt.id
                  LEFT JOIN waste_subtypes ws ON wl.waste_subtype_id = ws.id
                  WHERE wc.collector_id = ?
                  ORDER BY wc.created_at DESC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind collector_id
        $stmt->bindParam(1, $this->collector_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read waste collections by household
    public function readByHousehold() {
        // Query to read records
        $query = "SELECT wc.*, wl.waste_type_id, wl.waste_subtype_id, 
                         wl.weight as estimated_weight, wl.pickup_date, wl.pickup_time_slot, 
                         wl.pickup_address, wl.status as listing_status,
                         u.name as collector_name,
                         wt.name as waste_type_name, ws.name as waste_subtype_name
                  FROM " . $this->table_name . " wc
                  JOIN waste_listings wl ON wc.listing_id = wl.id
                  JOIN users u ON wc.collector_id = u.id
                  JOIN waste_types wt ON wl.waste_type_id = wt.id
                  LEFT JOIN waste_subtypes ws ON wl.waste_subtype_id = ws.id
                  WHERE wl.user_id = ?
                  ORDER BY wc.created_at DESC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind household_id
        $stmt->bindParam(1, $this->household_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Update waste collection status and actual weight
    public function updateCollection() {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->actual_weight = htmlspecialchars(strip_tags($this->actual_weight));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->notes = $this->notes ? htmlspecialchars(strip_tags($this->notes)) : null;
        
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Query to update collection
            $query = "UPDATE " . $this->table_name . "
                      SET actual_weight=:actual_weight, status=:status, 
                          notes=:notes, collection_date=NOW()
                      WHERE id=:id AND collector_id=:collector_id";
            
            // Prepare query
            $stmt = $this->conn->prepare($query);
            
            // Bind values
            $stmt->bindParam(":actual_weight", $this->actual_weight);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":notes", $this->notes);
            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":collector_id", $this->collector_id);
            
            // Execute query
            $stmt->execute();
            
            // If status is 'collected', update waste listing status
            if ($this->status === 'collected') {
                // Get listing_id
                $query = "SELECT listing_id FROM " . $this->table_name . " WHERE id=:id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":id", $this->id);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $listing_id = $row['listing_id'];
                
                // Update waste listing status
                $query = "UPDATE waste_listings SET status='completed' WHERE id=:listing_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":listing_id", $listing_id);
                $stmt->execute();
                
                // Get household_id and waste_type_id
                $query = "SELECT user_id, waste_type_id FROM waste_listings WHERE id=:listing_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":listing_id", $listing_id);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $household_id = $row['user_id'];
                $waste_type_id = $row['waste_type_id'];
                
                // Calculate points
                $query = "SELECT rate_per_kg FROM waste_types WHERE id=:waste_type_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":waste_type_id", $waste_type_id);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $rate_per_kg = $row['rate_per_kg'];
                
                $points = round($this->actual_weight * $rate_per_kg);
                
                // Add points transaction
                $query = "INSERT INTO points_transactions (user_id, points, transaction_type, reference_id, reference_type, description)
                          VALUES (:user_id, :points, 'earned', :reference_id, 'listing', 'Points earned for waste collection')";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $household_id);
                $stmt->bindParam(":points", $points);
                $stmt->bindParam(":reference_id", $listing_id);
                $stmt->execute();
                
                // Update user's total points
                $query = "UPDATE users SET total_points = total_points + :points WHERE id=:user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":points", $points);
                $stmt->bindParam(":user_id", $household_id);
                $stmt->execute();
            }
            
            // Commit transaction
            $this->conn->commit();
            
            return true;
        } catch (Exception $e) {
            // Rollback transaction
            $this->conn->rollBack();
            return false;
        }
    }
}
?>