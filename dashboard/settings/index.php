<?php
$page_title = "Settings";

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireLogin();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_notifications'])) {
        $success_message = 'Notification settings updated successfully';
    } elseif (isset($_POST['update_privacy'])) {
        $success_message = 'Privacy settings updated successfully';
    }
}

include_once '../../includes/dashboard_layout.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Settings</h1>
    </div>
    
    <?php if ($success_message): ?>
    <div class="rounded-md bg-green-50 p-4 animate-fade-in">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800"><?php echo $success_message; ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    <div class="rounded-md bg-red-50 p-4 animate-fade-in">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-times-circle text-red-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800"><?php echo $error_message; ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <!-- Settings Navigation -->
        <div class="rounded-lg border bg-white shadow-sm">
            <div class="border-b px-6 py-4">
                <h2 class="text-lg font-medium">Settings</h2>
            </div>
            <div class="p-4">
                <nav class="flex flex-col space-y-1">
                    <a href="#notifications" class="settings-nav-item rounded-md px-3 py-2 text-sm font-medium text-primary-600 bg-primary-50" data-target="notifications-section">
                        <i class="fas fa-bell mr-2"></i> Notifications
                    </a>
                    <a href="#privacy" class="settings-nav-item rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100" data-target="privacy-section">
                        <i class="fas fa-lock mr-2"></i> Privacy
                    </a>
                    <a href="#appearance" class="settings-nav-item rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100" data-target="appearance-section">
                        <i class="fas fa-palette mr-2"></i> Appearance
                    </a>
                    <a href="#language" class="settings-nav-item rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100" data-target="language-section">
                        <i class="fas fa-language mr-2"></i> Language
                    </a>
                    <a href="#data" class="settings-nav-item rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100" data-target="data-section">
                        <i class="fas fa-database mr-2"></i> Data & Storage
                    </a>
                    <a href="#help" class="settings-nav-item rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100" data-target="help-section">
                        <i class="fas fa-question-circle mr-2"></i> Help & Support
                    </a>
                </nav>
            </div>
        </div>
        
        <!-- Settings Content -->
        <div class="md:col-span-2 space-y-6">
            <!-- Notifications Settings -->
            <div id="notifications-section" class="settings-section rounded-lg border bg-white shadow-sm">
                <div class="border-b px-6 py-4">
                    <h2 class="text-lg font-medium">Notification Settings</h2>
                    <p class="text-sm text-gray-500">Manage how you receive notifications</p>
                </div>
                <div class="p-6">
                    <form method="POST" action="" class="space-y-6">
                        <div>
                            <h3 class="text-sm font-medium">Email Notifications</h3>
                            <div class="mt-4 space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="email-pickup" name="email_pickup" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="email-pickup" class="font-medium text-gray-700">Pickup Notifications</label>
                                        <p class="text-gray-500">Receive email notifications about waste pickups</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="email-points" name="email_points" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="email-points" class="font-medium text-gray-700">Points Notifications</label>
                                        <p class="text-gray-500">Receive email notifications about points earned</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="email-marketing" name="email_marketing" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="email-marketing" class="font-medium text-gray-700">Marketing Emails</label>
                                        <p class="text-gray-500">Receive marketing emails and newsletters</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium">SMS Notifications</h3>
                            <div class="mt-4 space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="sms-pickup" name="sms_pickup" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="sms-pickup" class="font-medium text-gray-700">Pickup Notifications</label>
                                        <p class="text-gray-500">Receive SMS notifications about waste pickups</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="sms-points" name="sms_points" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="sms-points" class="font-medium text-gray-700">Points Notifications</label>
                                        <p class="text-gray-500">Receive SMS notifications about points earned</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" name="update_notifications" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                Save Notification Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Privacy Settings -->
            <div id="privacy-section" class="settings-section rounded-lg border bg-white shadow-sm hidden">
                <div class="border-b px-6 py-4">
                    <h2 class="text-lg font-medium">Privacy Settings</h2>
                    <p class="text-sm text-gray-500">Manage your privacy preferences</p>
                </div>
                <div class="p-6">
                    <form method="POST" action="" class="space-y-6">
                        <div>
                            <h3 class="text-sm font-medium">Profile Visibility</h3>
                            <div class="mt-4 space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="show-profile" name="show_profile" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="show-profile" class="font-medium text-gray-700">Show Profile to Other Users</label>
                                        <p class="text-gray-500">Allow other users to see your profile information</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="show-activity" name="show_activity" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="show-activity" class="font-medium text-gray-700">Show Activity</label>
                                        <p class="text-gray-500">Allow others to see your waste management activity</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium">Data Usage</h3>
                            <div class="mt-4 space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="data-analytics" name="data_analytics" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="data-analytics" class="font-medium text-gray-700">Analytics</label>
                                        <p class="text-gray-500">Allow us to collect anonymous usage data to improve our services</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="data-personalization" name="data_personalization" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="data-personalization" class="font-medium text-gray-700">Personalization</label>
                                        <p class="text-gray-500">Allow us to use your data to personalize your experience</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" name="update_privacy" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                Save Privacy Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Appearance Settings -->
            <div id="appearance-section" class="settings-section rounded-lg border bg-white shadow-sm hidden">
                <div class="border-b px-6 py-4">
                    <h2 class="text-lg font-medium">Appearance Settings</h2>
                    <p class="text-sm text-gray-500">Customize how the application looks</p>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-sm font-medium">Theme</h3>
                            <div class="mt-4 grid grid-cols-3 gap-4">
                                <div class="relative">
                                    <input type="radio" id="theme-light" name="theme" value="light" class="sr-only" checked>
                                    <label for="theme-light" class="block cursor-pointer rounded-lg border border-gray-300 p-2 hover:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <div class="h-20 rounded bg-white"></div>
                                        <div class="mt-2 text-center text-sm font-medium">Light</div>
                                    </label>
                                </div>
                                <div class="relative">
                                    <input type="radio" id="theme-dark" name="theme" value="dark" class="sr-only">
                                    <label for="theme-dark" class="block cursor-pointer rounded-lg border border-gray-300 p-2 hover:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <div class="h-20 rounded bg-gray-900"></div>
                                        <div class="mt-2 text-center text-sm font-medium">Dark</div>
                                    </label>
                                </div>
                                <div class="relative">
                                    <input type="radio" id="theme-system" name="theme" value="system" class="sr-only">
                                    <label for="theme-system" class="block cursor-pointer rounded-lg border border-gray-300 p-2 hover:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <div class="h-20 rounded bg-gradient-to-b from-white to-gray-900"></div>
                                        <div class="mt-2 text-center text-sm font-medium">System</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium">Font Size</h3>
                            <div class="mt-4">
                                <input type="range" min="1" max="5" value="3" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                                <div class="flex justify-between text-xs text-gray-500 mt-2">
                                    <span>Small</span>
                                    <span>Medium</span>
                                    <span>Large</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="pt-4">
                            <button type="button" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                Save Appearance Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Language Settings -->
            <div id="language-section" class="settings-section rounded-lg border bg-white shadow-sm hidden">
                <div class="border-b px-6 py-4">
                    <h2 class="text-lg font-medium">Language Settings</h2>
                    <p class="text-sm text-gray-500">Choose your preferred language</p>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        <div>
                            <label for="language" class="text-sm font-medium">Language</label>
                            <select id="language" name="language" class="mt-2 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                                <option value="en" selected>English</option>
                                <option value="hi">Hindi</option>
                                <option value="bn">Bengali</option>
                                <option value="te">Telugu</option>
                                <option value="mr">Marathi</option>
                                <option value="ta">Tamil</option>
                                <option value="ur">Urdu</option>
                                <option value="gu">Gujarati</option>
                                <option value="kn">Kannada</option>
                                <option value="ml">Malayalam</option>
                            </select>
                        </div>
                        
                        <div class="pt-4">
                            <button type="button" class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                Save Language Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Data & Storage Settings -->
            <div id="data-section" class="settings-section rounded-lg border bg-white shadow-sm hidden">
                <div class="border-b px-6 py-4">
                    <h2 class="text-lg font-medium">Data & Storage</h2>
                    <p class="text-sm text-gray-500">Manage your data and storage preferences</p>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-sm font-medium">Data Export</h3>
                            <p class="text-sm text-gray-500 mt-1">Download a copy of your data</p>
                            <button type="button" class="mt-4 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                <i class="fas fa-download mr-2"></i> Export Data
                            </button>
                        </div>
                        
                        <div class="border-t pt-6">
                            <h3 class="text-sm font-medium text-red-600">Danger Zone</h3>
                            <p class="text-sm text-gray-500 mt-1">Permanently delete your account and all data</p>
                            <button type="button" class="mt-4 inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <i class="fas fa-trash-alt mr-2"></i> Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Help & Support Settings -->
            <div id="help-section" class="settings-section rounded-lg border bg-white shadow-sm hidden">
                <div class="border-b px-6 py-4">
                    <h2 class="text-lg font-medium">Help & Support</h2>
                    <p class="text-sm text-gray-500">Get help with Clean and Earn India</p>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-sm font-medium">Frequently Asked Questions</h3>
                            <div class="mt-4 space-y-4">
                                <div class="rounded-md border border-gray-200">
                                    <button type="button" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-medium text-gray-900 focus:outline-none">
                                        <span>How do I schedule a waste pickup?</span>
                                        <i class="fas fa-chevron-down text-gray-500"></i>
                                    </button>
                                </div>
                                <div class="rounded-md border border-gray-200">
                                    <button type="button" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-medium text-gray-900 focus:outline-none">
                                        <span>How are points calculated?</span>
                                        <i class="fas fa-chevron-down text-gray-500"></i>
                                    </button>
                                </div>
                                <div class="rounded-md border border-gray-200">
                                    <button type="button" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-medium text-gray-900 focus:outline-none">
                                        <span>How do I redeem my points?</span>
                                        <i class="fas fa-chevron-down text-gray-500"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t pt-6">
                            <h3 class="text-sm font-medium">Contact Support</h3>
                            <p class="text-sm text-gray-500 mt-1">Need more help? Contact our support team</p>
                            <div class="mt-4 space-y-4">
                                <div class="flex items-center">
                                    <i class="fas fa-envelope text-gray-400 mr-3"></i>
                                    <span class="text-sm">support@cleanearnindia.com</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-phone text-gray-400 mr-3"></i>
                                    <span class="text-sm">+91 123 456 7890</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-comment-alt text-gray-400 mr-3"></i>
                                    <button type="button" class="text-sm text-primary-600 hover:text-primary-700">
                                        Start Live Chat
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const navItems = document.querySelectorAll('.settings-nav-item');
        const sections = document.querySelectorAll('.settings-section');
        
        navItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                navItems.forEach(navItem => {
                    navItem.classList.remove('bg-primary-50', 'text-primary-600');
                    navItem.classList.add('text-gray-700', 'hover:bg-gray-100');
                });
                
                this.classList.remove('text-gray-700', 'hover:bg-gray-100');
                this.classList.add('bg-primary-50', 'text-primary-600');
                
                sections.forEach(section => {
                    section.classList.add('hidden');
                });
                
                const targetId = this.getAttribute('data-target');
                document.getElementById(targetId).classList.remove('hidden');
            });
        });
    });
</script>

<?php
include_once '../../includes/dashboard_footer.php';
?>