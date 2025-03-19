<?php
require_once __DIR__ . '/../models/points_transaction.php';
require_once __DIR__ . '/../utils/validator.php';
require_once __DIR__ . '/../utils/response.php';

class PointsController {
    private $db;
    private $points_transaction;
    private $validator;
    private $response;
    
    public function __construct($db) {
        $this->db = $db;
        $this->points_transaction = new PointsTransaction($db);
        $this->response = new Response();
    }
    
    // Get user's total points
    public function getTotalPoints($user_id) {
        // Set user ID
        $this->points_transaction->user_id = $user_id;
        
        // Get total points
        $total_points = $this->points_transaction->getUserPoints();
        
        $this->response->send([
            'total_points' => $total_points
        ]);
    }
    
    // Get user's points transactions
    public function getTransactions($user_id) {
        // Set user ID
        $this->points_transaction->user_id = $user_id;
        
        // Get points transactions
        $stmt = $this->points_transaction->readByUser();
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $transactions_arr = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $transactions_arr[] = [
                    'id' => $row['id'],
                    'points' => $row['points'],
                    'transaction_type' => $row['transaction_type'],
                    'reference_id' => $row['reference_id'],
                    'reference_type' => $row['reference_type'],
                    'description' => $row['description'],
                    'created_at' => $row['created_at']
                ];
            }
            
            $this->response->send($transactions_arr);
        } else {
            $this->response->send([]);
        }
    }
    
    // Create a points transaction (admin only)
    public function createTransaction() {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate input
        $this->validator = new Validator($data);
        $this->validator->required('user_id')->numeric('user_id')
                        ->required('points')->numeric('points')
                        ->required('transaction_type')->in('transaction_type', ['earned', 'redeemed', 'expired', 'adjusted'])
                        ->required('reference_type')->in('reference_type', ['listing', 'collection', 'purchase', 'manual'])
                        ->required('description');
        
        if ($this->validator->fails()) {
            $this->response->sendValidationError($this->validator->getErrors());
            return;
        }
        
        // Set points transaction properties
        $this->points_transaction->user_id = $data['user_id'];
        $this->points_transaction->points = $data['points'];
        $this->points_transaction->transaction_type = $data['transaction_type'];
        $this->points_transaction->reference_id = $data['reference_id'] ?? null;
        $this->points_transaction->reference_type = $data['reference_type'];
        $this->points_transaction->description = $data['description'];
        
        // Create the points transaction
        if ($this->points_transaction->create()) {
            $this->response->send([
                'message' => 'Points transaction created successfully',
                'id' => $this->points_transaction->id
            ], 201);
        } else {
            $this->response->sendError('Creation Failed', 'Unable to create points transaction');
        }
    }
}
?>