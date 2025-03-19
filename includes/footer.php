</main>
    
    <?php if (isset($show_footer) && $show_footer): ?>
    <footer class="border-t py-12 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center gap-2 mb-6 md:mb-0">
                    <i class="fas fa-leaf text-primary-600 text-xl"></i>
                    <span class="text-xl font-bold">CleanAndEarnIndia</span>
                </div>
                <p class="text-sm text-gray-500">
                    &copy; <?php echo date('Y'); ?> Clean and Earn India. All rights reserved.
                </p>
                <p class="text-sm text-gray-500 mt-4 md:mt-0">
                    "Save Nature, Save World"
                </p>
            </div>
            
            <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">About Us</h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="#" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">Our Mission</a>
                        </li>
                        <li>
                            <a href="#" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">Our Team</a>
                        </li>
                        <li>
                            <a href="#" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">Careers</a>
                        </li>
                        <li>
                            <a href="#" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">Press</a>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Services</h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="#" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">Waste Collection</a>
                        </li>
                        <li>
                            <a href="#" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">Recycling</a>
                        </li>
                        <li>
                            <a href="#" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">Waste Management</a>
                        </li>
                        <li>
                            <a href="#" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">Corporate Solutions</a>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Resources</h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="#" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">Blog</a>
                        </li>
                        <li>
                            <a href="#" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">Guides</a>
                        </li>
                        <li>
                            <a href="#" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">FAQ</a>
                        </li>
                        <li>
                            <a href="#" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">Support</a>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Contact</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-map-marker-alt text-primary-600 mt-1"></i>
                            <span class="text-sm text-gray-600">123 Green Street, New Delhi, India</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-phone text-primary-600 mt-1"></i>
                            <span class="text-sm text-gray-600">+91 123 456 7890</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-envelope text-primary-600 mt-1"></i>
                            <span class="text-sm text-gray-600">info@cleanearnindia.com</span>
                        </li>
                        <li class="flex items-center gap-4 mt-4">
                            <a href="#" class="text-gray-600 hover:text-primary-600 transition-colors">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="text-gray-600 hover:text-primary-600 transition-colors">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="text-gray-600 hover:text-primary-600 transition-colors">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="text-gray-600 hover:text-primary-600 transition-colors">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <?php endif; ?>
    
    <script>
        // mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>

