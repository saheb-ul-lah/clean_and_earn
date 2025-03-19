<?php
//
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . '/db_connect.php';


require_once __DIR__ . '/functions.php';


requireLogin();


$user_data = getUserData($_SESSION['user_id'], $pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Clean and Earn India' : 'Dashboard - Clean and Earn India'; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                            950: '#052e16',
                        },
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                        'montserrat': ['Montserrat', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
        }
        
        .gradient-primary {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        }
        
        .gradient-secondary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        
        .gradient-background {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }
        
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .animate-slide-in {
            animation: slideIn 0.5s ease-in-out;
        }
        
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include_once __DIR__ . '/sidebar.php'; ?>
    
    <div class="md:ml-64 min-h-screen">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b bg-white px-4 md:px-6 shadow-sm">
            <div class="flex flex-1 items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <button id="notifications-button" class="flex h-9 w-9 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                            <i class="fas fa-bell"></i>
                        </button>
                        <div id="notifications-dropdown" class="absolute right-0 mt-2 w-80 rounded-md bg-white p-2 shadow-lg ring-1 ring-black ring-opacity-5 hidden">
                            <div class="px-4 py-2 text-sm font-medium text-gray-700 border-b">
                                Notifications
                            </div>
                            <div class="max-h-60 overflow-y-auto py-2">
                                <div class="px-4 py-2 hover:bg-gray-50 rounded-md">
                                    <p class="text-sm font-medium text-gray-900">New pickup request</p>
                                    <p class="text-xs text-gray-500">2 hours ago</p>
                                </div>
                                <div class="px-4 py-2 hover:bg-gray-50 rounded-md">
                                    <p class="text-sm font-medium text-gray-900">Points earned</p>
                                    <p class="text-xs text-gray-500">Yesterday</p>
                                </div>
                            </div>
                            <a href="#" class="block px-4 py-2 text-center text-xs font-medium text-primary-600 hover:text-primary-700">
                                View all notifications
                            </a>
                        </div>
                    </div>
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-primary-700">
                                <?php echo strtoupper(substr($user_data['name'] ?? 'U', 0, 1)); ?>
                            </div>
                            <span class="hidden md:inline-block"><?php echo $user_data['name'] ?? 'User'; ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div id="user-menu-dropdown" class="absolute right-0 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 hidden">
                            <a href="../dashboard/profile/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Profile
                            </a>
                            <a href="../dashboard/settings/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Settings
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <a href="../auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <main class="p-4 md:p-6 animate-fade-in">

