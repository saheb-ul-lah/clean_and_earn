</main>
    </div>
    
    <script>
        
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenuDropdown = document.getElementById('user-menu-dropdown');
            
            if (userMenuButton && userMenuDropdown) {
                userMenuButton.addEventListener('click', function() {
                    userMenuDropdown.classList.toggle('hidden');
                });
                
                
                document.addEventListener('click', function(event) {
                    if (!userMenuButton.contains(event.target) && !userMenuDropdown.contains(event.target)) {
                        userMenuDropdown.classList.add('hidden');
                    }
                });
            }
            
            
            const notificationsButton = document.getElementById('notifications-button');
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            
            if (notificationsButton && notificationsDropdown) {
                notificationsButton.addEventListener('click', function() {
                    notificationsDropdown.classList.toggle('hidden');
                });
                
                
                document.addEventListener('click', function(event) {
                    if (!notificationsButton.contains(event.target) && !notificationsDropdown.contains(event.target)) {
                        notificationsDropdown.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>
</html>

