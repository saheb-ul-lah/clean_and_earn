<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Clean and Earn India' : 'Clean and Earn India'; ?></title>
    
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
<body class="bg-gray-50 min-h-screen flex flex-col">
    <?php if (isset($show_header) && $show_header): ?>
    <header class="sticky top-0 z-50 w-full border-b bg-white shadow-sm">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-leaf text-primary-600 text-xl"></i>
                    <span class="text-xl font-bold">CleanAndEarnIndia</span>
                </div>
                
                <nav class="hidden md:flex items-center gap-6">
                    <a href="./#how-it-works" class="text-sm font-medium hover:text-primary-600 transition-colors">
                        How It Works
                    </a>
                    <a href="./#waste-types" class="text-sm font-medium hover:text-primary-600 transition-colors">
                        Waste Types
                    </a>
                    <a href="./#testimonials" class="text-sm font-medium hover:text-primary-600 transition-colors">
                        Testimonials
                    </a>
                    <a href="./#contact" class="text-sm font-medium hover:text-primary-600 transition-colors">
                        Contact
                    </a>
                </nav>
                
                <div class="flex items-center gap-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="./dashboard/" class="text-sm font-medium px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50 transition-colors">
                            Dashboard
                        </a>
                        <a href="./auth/logout.php" class="text-sm font-medium px-4 py-2 rounded-md bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                            Logout
                        </a>
                    <?php else: ?>
                        <a href="./auth/login.php" class="text-sm font-medium px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50 transition-colors">
                            Log in
                        </a>
                        <a href="./auth/register.php" class="text-sm font-medium px-4 py-2 rounded-md bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                            Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <?php endif; ?>
    
    <main class="flex-1">

