document.addEventListener('DOMContentLoaded', function() {
    const profile = document.querySelector('.profile');
    const profileDropdown = document.getElementById('profile-dropdown');
    const notificationDropdown = document.getElementById('notification-dropdown');

    if (profile && profileDropdown) {
        // Toggle profile dropdown
        profile.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close notification dropdown if open
            if (notificationDropdown && notificationDropdown.classList.contains('active')) {
                notificationDropdown.classList.remove('active');
            }
            
            // Toggle profile dropdown
            profileDropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profile.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('show');
            }
        });
        
        // Prevent dropdown from closing when clicking inside it
        profileDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});