<?php
class PointsTransaction {
    // Database connection and table name
    private $conn;
    private $table_name = "points_transactions";

    // Object properties
    public $id;
    public $user_id;
    public $points;
    public $transaction_type;
    public $reference_id;
    public $reference_type;
    public $description;
    public $created_at;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read points transactions by user
    public function readByUser() {
        // Query to read records
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE user_id = ?
                  ORDER BY created_at DESC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind user_id
        $stmt->bindParam(1, $this->user_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Create points transaction (admin only)
    public function create() {
        // Sanitize inputs
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->points = htmlspecialchars(strip_tags($this->points));
        $this->transaction_type = htmlspecialchars(strip_tags($this->transaction_type));
        $this->reference_type = htmlspecialchars(strip_tags($this->reference_type));
        $this->description = htmlspecialchars(strip_tags($this->description));
        
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Insert points transaction
            $query = "INSERT INTO " . $this->table_name . "
                      SET user_id=:user_id, points=:points, transaction_type=:transaction_type,
                          reference_id=:reference_id, reference_type=:reference_type, description=:description";
            
            // Prepare query
            $stmt = $this->conn->prepare($query);
            
            // Bind values
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":points", $this->points);
            $stmt->bindParam(":transaction_type", $this->transaction_type);
            $stmt->bindParam(":reference_id", $this->reference_id);
            $stmt->bindParam(":reference_type", $this->reference_type);
            $stmt->bindParam(":description", $this->description);
            
            // Execute query
            $stmt->execute();
            
            $this->id = $this->conn->lastInsertId();
            
            // Update user's total points
            if ($this->transaction_type === 'earned') {
                $query = "UPDATE users SET total_points = total_points + :points WHERE id=:user_id";
            } else {
                $query = "UPDATE users SET total_points = total_points - :points WHERE id=:user_id";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":points", $this->points);
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->execute();
            
            // Commit transaction
            $this->conn->commit();
            
            return true;
        } catch (Exception $e) {
            // Rollback transaction
            $this->conn->rollBack();
            return false;
        }
    }
    
    // Get user's total points
    public function getUserPoints() {
        // Query to get total points
        $query = "SELECT total_points FROM users WHERE id = ? LIMIT 0,1";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind user_id
        $stmt->bindParam(1, $this->user_id);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return $row['total_points'];
        }
        
        return 0;
    }
}
?>