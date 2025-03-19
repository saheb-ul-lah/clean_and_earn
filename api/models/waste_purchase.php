<?php
class WastePurchase {
    // Database connection and table name
    private $conn;
    private $table_name = "waste_purchases";

    // Object properties
    public $id;
    public $buyer_id;
    public $storage_id;
    public $inventory_id;
    public $weight;
    public $amount;
    public $status;
    public $payment_method;
    public $payment_reference;
    public $pickup_date;
    public $pickup_time;
    public $created_at;
    public $updated_at;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create purchase
    public function create() {
        // Sanitize inputs
        $this->buyer_id = htmlspecialchars(strip_tags($this->buyer_id));
        $this->storage_id = htmlspecialchars(strip_tags($this->storage_id));
        $this->inventory_id = htmlspecialchars(strip_tags($this->inventory_id));
        $this->weight = htmlspecialchars(strip_tags($this->weight));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->pickup_date = htmlspecialchars(strip_tags($this->pickup_date));
        $this->pickup_time = htmlspecialchars(strip_tags($this->pickup_time));
        
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Check if inventory is available
            $query = "SELECT status, weight FROM storage_inventory 
                      WHERE id=:inventory_id AND storage_id=:storage_id AND status='available'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":inventory_id", $this->inventory_id);
            $stmt->bindParam(":storage_id", $this->storage_id);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return false; // Inventory not available
            }
            
            $inventory = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if requested weight is available
            if ($this->weight > $inventory['weight']) {
                return false; // Not enough weight available
            }
            
            // Insert purchase record
            $query = "INSERT INTO " . $this->table_name . "
                      SET buyer_id=:buyer_id, storage_id=:storage_id, inventory_id=:inventory_id,
                          weight=:weight, amount=:amount, status=:status,
                          pickup_date=:pickup_date, pickup_time=:pickup_time";
            
            // Prepare query
            $stmt = $this->conn->prepare($query);
            
            // Bind values
            $stmt->bindParam(":buyer_id", $this->buyer_id);
            $stmt->bindParam(":storage_id", $this->storage_id);
            $stmt->bindParam(":inventory_id", $this->inventory_  $this->storage_id);
            $stmt->bindParam(":inventory_id", $this->inventory_id);
            $stmt->bindParam(":weight", $this->weight);
            $stmt->bindParam(":amount", $this->amount);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":pickup_date", $this->pickup_date);
            $stmt->bindParam(":pickup_time", $this->pickup_time);
            
            // Execute query
            $stmt->execute();
            
            $this->id = $this->conn->lastInsertId();
            
            // Update inventory status to 'reserved'
            if ($this->weight == $inventory['weight']) {
                // If entire inventory is purchased, mark as reserved
                $query = "UPDATE storage_inventory SET status='reserved' WHERE id=:inventory_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":inventory_id", $this->inventory_id);
                $stmt->execute();
            } else {
                // If partial purchase, reduce weight and create new inventory item for the purchase
                $remaining_weight = $inventory['weight'] - $this->weight;
                
                // Update existing inventory with remaining weight
                $query = "UPDATE storage_inventory SET weight=:weight WHERE id=:inventory_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":weight", $remaining_weight);
                $stmt->bindParam(":inventory_id", $this->inventory_id);
                $stmt->execute();
                
                // Create new inventory item for the purchased amount with reserved status
                $query = "INSERT INTO storage_inventory (storage_id, waste_type_id, waste_subtype_id, weight, status)
                          SELECT storage_id, waste_type_id, waste_subtype_id, :weight, 'reserved'
                          FROM storage_inventory WHERE id=:inventory_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":weight", $this->weight);
                $stmt->bindParam(":inventory_id", $this->inventory_id);
                $stmt->execute();
                
                // Update purchase with new inventory id
                $new_inventory_id = $this->conn->lastInsertId();
                $query = "UPDATE " . $this->table_name . " SET inventory_id=:new_inventory_id WHERE id=:id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":new_inventory_id", $new_inventory_id);
                $stmt->bindParam(":id", $this->id);
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
    
    // Read single purchase
    public function readOne() {
        // Query to read single record
        $query = "SELECT wp.*, u1.name as buyer_name, u2.name as storage_name,
                         si.waste_type_id, si.waste_subtype_id,
                         wt.name as waste_type_name, ws.name as waste_subtype_name
                  FROM " . $this->table_name . " wp
                  JOIN users u1 ON wp.buyer_id = u1.id
                  JOIN users u2 ON wp.storage_id = u2.id
                  JOIN storage_inventory si ON wp.inventory_id = si.id
                  JOIN waste_types wt ON si.waste_type_id = wt.id
                  LEFT JOIN waste_subtypes ws ON si.waste_subtype_id = ws.id
                  WHERE wp.id = ? LIMIT 0,1";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind id of purchase to be read
        $stmt->bindParam(1, $this->id);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Set values to object properties
        if ($row) {
            $this->id = $row['id'];
            $this->buyer_id = $row['buyer_id'];
            $this->storage_id = $row['storage_id'];
            $this->inventory_id = $row['inventory_id'];
            $this->weight = $row['weight'];
            $this->amount = $row['amount'];
            $this->status = $row['status'];
            $this->payment_method = $row['payment_method'];
            $this->payment_reference = $row['payment_reference'];
            $this->pickup_date = $row['pickup_date'];
            $this->pickup_time = $row['pickup_time'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            // Additional data
            $this->buyer_name = $row['buyer_name'];
            $this->storage_name = $row['storage_name'];
            $this->waste_type_id = $row['waste_type_id'];
            $this->waste_type_name = $row['waste_type_name'];
            $this->waste_subtype_id = $row['waste_subtype_id'];
            $this->waste_subtype_name = $row['waste_subtype_name'];
            
            return true;
        }
        
        return false;
    }
    
    // Read purchases by buyer
    public function readByBuyer() {
        // Query to read records
        $query = "SELECT wp.*, u.name as storage_name,
                         si.waste_type_id, si.waste_subtype_id,
                         wt.name as waste_type_name, ws.name as waste_subtype_name
                  FROM " . $this->table_name . " wp
                  JOIN users u ON wp.storage_id = u.id
                  JOIN storage_inventory si ON wp.inventory_id = si.id
                  JOIN waste_types wt ON si.waste_type_id = wt.id
                  LEFT JOIN waste_subtypes ws ON si.waste_subtype_id = ws.id
                  WHERE wp.buyer_id = ?
                  ORDER BY wp.created_at DESC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind buyer_id
        $stmt->bindParam(1, $this->buyer_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Read purchases by storage
    public function readByStorage() {
        // Query to read records
        $query = "SELECT wp.*, u.name as buyer_name,
                         si.waste_type_id, si.waste_subtype_id,
                         wt.name as waste_type_name, ws.name as waste_subtype_name
                  FROM " . $this->table_name . " wp
                  JOIN users u ON wp.buyer_id = u.id
                  JOIN storage_inventory si ON wp.inventory_id = si.id
                  JOIN waste_types wt ON si.waste_type_id = wt.id
                  LEFT JOIN waste_subtypes ws ON si.waste_subtype_id = ws.id
                  WHERE wp.storage_id = ?
                  ORDER BY wp.created_at DESC";
        
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // Bind storage_id
        $stmt->bindParam(1, $this->storage_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Update purchase status
    public function updateStatus() {
        // Sanitize inputs
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->payment_method = $this->payment_method ? htmlspecialchars(strip_tags($this->payment_method)) : null;
        $this->payment_reference = $this->payment_reference ? htmlspecialchars(strip_tags($this->payment_reference)) : null;
        
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Update purchase status
            $query = "UPDATE " . $this->table_name . "
                      SET status=:status, payment_method=:payment_method, payment_reference=:payment_reference
                      WHERE id=:id";
            
            if ($this->status === 'completed') {
                $query .= " AND (buyer_id=:user_id OR storage_id=:user_id)";
            } else {
                $query .= " AND buyer_id=:user_id";
            }
            
            // Prepare query
            $stmt = $this->conn->prepare($query);
            
            // Bind values
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":payment_method", $this->payment_method);
            $stmt->bindParam(":payment_reference", $this->payment_reference);
            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":user_id", $this->user_id);
            
            // Execute query
            $stmt->execute();
            
            // If status is 'completed', update inventory status to 'sold'
            if ($this->status === 'completed') {
                // Get inventory_id
                $query = "SELECT inventory_id FROM " . $this->table_name . " WHERE id=:id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":id", $this->id);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $inventory_id = $row['inventory_id'];
                
                // Update inventory status
                $query = "UPDATE storage_inventory SET status='sold' WHERE id=:inventory_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":inventory_id", $inventory_id);
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
    
    // Cancel purchase
    public function cancel() {
        // Sanitize input
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Get purchase details
            $query = "SELECT inventory_id, status FROM " . $this->table_name . "
                      WHERE id=:id AND buyer_id=:buyer_id AND status IN ('pending', 'paid')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":buyer_id", $this->buyer_id);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return false; // Purchase not found or cannot be cancelled
            }
            
            $purchase = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Update purchase status to cancelled
            $query = "UPDATE " . $this->table_name . " SET status='cancelled' WHERE id=:id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id);
            $stmt->execute();
            
            // Update inventory status back to available
            $query = "UPDATE storage_inventory SET status='available' WHERE id=:inventory_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":inventory_id", $purchase['inventory_id']);
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
}
?>