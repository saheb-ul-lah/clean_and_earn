<?php
// Set headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include configuration files
require_once 'config/config.php';
require_once 'config/database.php';

// Include middleware
require_once 'middleware/auth.php';
require_once 'middleware/rate_limit.php';
require_once 'middleware/cors.php';

// Include utils
require_once 'utils/response.php';

// Initialize response utility
$response = new Response();

// Initialize CORS middleware
$cors = new CORS();
$cors->handle();

// Initialize rate limiting middleware
$rate_limit = new RateLimit();
$rate_limit->check();

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Parse the URI
$uri = parse_url($request_uri, PHP_URL_PATH);
$uri = explode('/', $uri);

// Remove empty segments and 'api' from the URI
$uri = array_values(array_filter($uri, function($segment) {
    return $segment !== '' && $segment !== 'api';
}));

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize auth middleware
$auth = new Auth();

// Route the request
try {
    // Public routes (no authentication required)
    if (count($uri) >= 1) {
        // Auth routes
        if ($uri[0] === 'auth') {
            require_once 'controllers/auth_controller.php';
            $auth_controller = new AuthController($db);
            
            if (count($uri) >= 2) {
                switch ($uri[1]) {
                    case 'register':
                        if ($method === 'POST') {
                            $auth_controller->register();
                            exit;
                        }
                        break;
                    case 'login':
                        if ($method === 'POST') {
                            $auth_controller->login();
                            exit;
                        }
                        break;
                    case 'forgot-password':
                        if ($method === 'POST') {
                            $auth_controller->forgotPassword();
                            exit;
                        }
                        break;
                    case 'reset-password':
                        if ($method === 'POST') {
                            $auth_controller->resetPassword();
                            exit;
                        }
                        break;
                }
            }
        }
        
        // Waste types (public read)
        if ($uri[0] === 'waste-types') {
            require_once 'controllers/waste_type_controller.php';
            $waste_type_controller = new WasteTypeController($db);
            
            if ($method === 'GET') {
                if (count($uri) === 1) {
                    // GET /api/waste-types
                    $waste_type_controller->getAll();
                    exit;
                } elseif (count($uri) === 2) {
                    // GET /api/waste-types/{id}
                    $waste_type_controller->getOne($uri[1]);
                    exit;
                } elseif (count($uri) === 3 && $uri[2] === 'subtypes') {
                    // GET /api/waste-types/{id}/subtypes
                    $waste_type_controller->getSubtypes($uri[1]);
                    exit;
                }
            }
        }
    }
    
    // Protected routes (authentication required)
    // Authenticate the user
    $user = $auth->authenticate();
    
    if (count($uri) >= 1) {
        // User profile
        if ($uri[0] === 'profile') {
            require_once 'controllers/user_controller.php';
            $user_controller = new UserController($db);
            
            if ($method === 'GET') {
                // GET /api/profile
                $user_controller->getProfile($user->id);
                exit;
            } elseif ($method === 'PUT') {
                // PUT /api/profile
                $user_controller->updateProfile($user->id);
                exit;
            }
        }
        
        // Change password
        if ($uri[0] === 'change-password' && $method === 'POST') {
            require_once 'controllers/auth_controller.php';
            $auth_controller = new AuthController($db);
            $auth_controller->changePassword($user->id);
            exit;
        }
        
        // Waste listings
        if ($uri[0] === 'waste-listings') {
            require_once 'controllers/waste_listing_controller.php';
            $waste_listing_controller = new WasteListingController($db);
            
            if (count($uri) === 1) {
                if ($method === 'GET') {
                    // GET /api/waste-listings
                    if ($user->role === 'household') {
                        $waste_listing_controller->getByUser($user->id);
                    } elseif ($user->role === 'collector') {
                        $waste_listing_controller->getActive();
                    } else {
                        $response->sendError('Access Denied', 'You do not have permission to access this resource', 403);
                    }
                    exit;
                } elseif ($method === 'POST' && $user->role === 'household') {
                    // POST /api/waste-listings
                    $waste_listing_controller->create($user->id);
                    exit;
                }
            } elseif (count($uri) === 2) {
                if ($method === 'GET') {
                    // GET /api/waste-listings/{id}
                    $waste_listing_controller->getOne($uri[1], $user->id, $user->role);
                    exit;
                } elseif ($method === 'PUT' && $user->role === 'household') {
                    // PUT /api/waste-listings/{id}
                    $waste_listing_controller->update($uri[1], $user->id);
                    exit;
                } elseif ($method === 'DELETE' && $user->role === 'household') {
                    // DELETE /api/waste-listings/{id}
                    $waste_listing_controller->delete($uri[1], $user->id);
                    exit;
                }
            }
        }
        
        // Waste collections
        if ($uri[0] === 'collections') {
            require_once 'controllers/waste_collection_controller.php';
            $waste_collection_controller = new WasteCollectionController($db);
            
            if (count($uri) === 1) {
                if ($method === 'GET') {
                    // GET /api/collections
                    if ($user->role === 'collector') {
                        $waste_collection_controller->getByCollector($user->id);
                    } elseif ($user->role === 'household') {
                        $waste_collection_controller->getByHousehold($user->id);
                    } else {
                        $response->sendError('Access Denied', 'You do not have permission to access this resource', 403);
                    }
                    exit;
                } elseif ($method === 'POST' && $user->role === 'collector') {
                    // POST /api/collections
                    $waste_collection_controller->create($user->id);
                    exit;
                }
            } elseif (count($uri) === 2) {
                if ($method === 'GET') {
                    // GET /api/collections/{id}
                    $waste_collection_controller->getOne($uri[1], $user->id, $user->role);
                    exit;
                } elseif ($method === 'PUT' && $user->role === 'collector') {
                    // PUT /api/collections/{id}
                    $waste_collection_controller->updateCollection($uri[1], $user->id);
                    exit;
                }
            }
        }
        
        // Storage inventory
        if ($uri[0] === 'inventory') {
            require_once 'controllers/storage_controller.php';
            $storage_controller = new StorageController($db);
            
            if (count($uri) === 1) {
                if ($method === 'GET') {
                    // GET /api/inventory
                    if ($user->role === 'storage') {
                        $storage_controller->getByStorage($user->id);
                    } elseif ($user->role === 'buyer') {
                        $storage_controller->getAvailable();
                    } else {
                        $response->sendError('Access Denied', 'You do not have permission to access this resource', 403);
                    }
                    exit;
                } elseif ($method === 'POST' && $user->role === 'storage') {
                    // POST /api/inventory
                    $storage_controller->addInventory($user->id);
                    exit;
                }
            } elseif (count($uri) === 2) {
                if ($method === 'GET') {
                    // GET /api/inventory/{id}
                    $storage_controller->getOne($uri[1]);
                    exit;
                } elseif ($method === 'PUT' && $user->role === 'storage') {
                    // PUT /api/inventory/{id}
                    $storage_controller->updateStatus($uri[1], $user->id);
                    exit;
                }
            }
        }
        
        // Purchases
        if ($uri[0] === 'purchases') {
            require_once 'controllers/purchase_controller.php';
            $purchase_controller = new PurchaseController($db);
            
            if (count($uri) === 1) {
                if ($method === 'GET') {
                    // GET /api/purchases
                    if ($user->role === 'buyer') {
                        $purchase_controller->getByBuyer($user->id);
                    } elseif ($user->role === 'storage') {
                        $purchase_controller->getByStorage($user->id);
                    } else {
                        $response->sendError('Access Denied', 'You do not have permission to access this resource', 403);
                    }
                    exit;
                } elseif ($method === 'POST' && $user->role === 'buyer') {
                    // POST /api/purchases
                    $purchase_controller->create($user->id);
                    exit;
                }
            } elseif (count($uri) === 2) {
                if ($method === 'GET') {
                    // GET /api/purchases/{id}
                    $purchase_controller->getOne($uri[1], $user->id, $user->role);
                    exit;
                } elseif ($method === 'PUT') {
                    // PUT /api/purchases/{id}
                    $purchase_controller->updateStatus($uri[1], $user->id, $user->role);
                    exit;
                } elseif ($method === 'DELETE' && $user->role === 'buyer') {
                    // DELETE /api/purchases/{id}
                    $purchase_controller->cancel($uri[1], $user->id);
                    exit;
                }
            }
        }
        
        // Points
        if ($uri[0] === 'points') {
            require_once 'controllers/points_controller.php';
            $points_controller = new PointsController($db);
            
            if (count($uri) === 1) {
                if ($method === 'GET') {
                    // GET /api/points
                    $points_controller->getTotalPoints($user->id);
                    exit;
                }
            } elseif (count($uri) === 2 && $uri[1] === 'transactions') {
                if ($method === 'GET') {
                    // GET /api/points/transactions
                    $points_controller->getTransactions;
                    $points_controller->getTransactions($user->id);
                    exit;
                }
            }
        }
        
        // Admin routes
        if ($uri[0] === 'admin' && ($user->role === 'admin' || $user->role === 'super_admin')) {
            if (count($uri) >= 2) {
                // Admin waste types management
                if ($uri[1] === 'waste-types') {
                    require_once 'controllers/waste_type_controller.php';
                    $waste_type_controller = new WasteTypeController($db);
                    
                    if (count($uri) === 2) {
                        if ($method === 'POST') {
                            // POST /api/admin/waste-types
                            $waste_type_controller->create();
                            exit;
                        }
                    } elseif (count($uri) === 3) {
                        if ($method === 'PUT') {
                            // PUT /api/admin/waste-types/{id}
                            $waste_type_controller->update($uri[2]);
                            exit;
                        }
                    } elseif (count($uri) === 4 && $uri[3] === 'subtypes') {
                        if ($method === 'POST') {
                            // POST /api/admin/waste-types/{id}/subtypes
                            $waste_type_controller->createSubtype($uri[2]);
                            exit;
                        }
                    }
                }
                
                // Admin points management
                if ($uri[1] === 'points' && count($uri) === 3 && $uri[2] === 'transactions') {
                    require_once 'controllers/points_controller.php';
                    $points_controller = new PointsController($db);
                    
                    if ($method === 'POST') {
                        // POST /api/admin/points/transactions
                        $points_controller->createTransaction();
                        exit;
                    }
                }
            }
        }
    }
    
    // If we get here, the route was not found
    $response->sendError('Not Found', 'Endpoint not found', 404);
    
} catch (Exception $e) {
    // Handle any exceptions
    $response->sendError('Server Error', $e->getMessage(), 500);
}
?>