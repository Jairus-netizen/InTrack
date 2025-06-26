document.addEventListener('DOMContentLoaded', function() {
    // Notification bell functionality
    const notificationBell = document.querySelector('.notification-bell');
    const notificationDropdown = document.querySelector('.notification-dropdown');
    
    // Toggle dropdown
    notificationBell.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown.style.display = notificationDropdown.style.display === 'block' ? 'none' : 'block';
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        notificationDropdown.style.display = 'none';
    });

        // Notification item click handler
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            const notificationId = this.getAttribute('data-id');
            
            // Mark as read via AJAX
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `notification_id=${notificationId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the notification badge count
                    updateNotificationBadge();
                }
            });
        });
    });
    
    // Function to update notification badge
    function updateNotificationBadge() {
        fetch('get_notification_count.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.notification-badge');
                if (data.count > 0) {
                    if (!badge) {
                        // Create badge if it doesn't exist
                        const newBadge = document.createElement('span');
                        newBadge.className = 'notification-badge';
                        newBadge.textContent = data.count;
                        document.querySelector('.notification-bell').appendChild(newBadge);
                    } else {
                        // Update existing badge
                        badge.textContent = data.count;
                    }
                } else if (badge) {
                    // Remove badge if no notifications
                    badge.remove();
                }
            });
    }
    
    // Call this initially to set the correct count
    updateNotificationBadge();
    
    // Settings item clicks
    const settingsItems = document.querySelectorAll('.settings-item');
    settingsItems.forEach(item => {
        item.addEventListener('click', function() {
            const section = this.getAttribute('data-section');
            window.location.href = `settings.php?section=${section}`;
        });
    });

    // Project item clicks
    const projectItems = document.querySelectorAll('.project-item');
    projectItems.forEach(item => {
        item.addEventListener('click', function() {
            const projectId = this.getAttribute('data-project-id');
            if (projectId) {
                window.location.href = `view-project.php?id=${projectId}`;
            }
        });
    });

    // Clock in/out functionality
    const clockButton = document.getElementById('clockButton');
    if (clockButton) {
        clockButton.addEventListener('click', function() {
            const currentStatus = this.getAttribute('data-status');
            const action = currentStatus === 'in' ? 'clock_out' : 'clock_in';
            
            fetch('/intrack-cathy/user-page/scripts/clock_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    const newStatus = data.action === 'in' ? 'in' : 'out';
                    const statusIndicator = document.querySelector('.status-indicator');
                    const statusText = document.querySelector('.status span');
                    
                    // Update button
                    clockButton.setAttribute('data-status', newStatus);
                    clockButton.textContent = newStatus === 'in' ? 'Clock Out' : 'Clock In';
                    
                    // Update status indicator
                    statusIndicator.classList.remove('clocked-in', 'clocked-out');
                    statusIndicator.classList.add(newStatus === 'in' ? 'clocked-in' : 'clocked-out');
                    
                    // Update status text
                    statusText.textContent = `Currently ${newStatus === 'in' ? 'Clocked In' : 'Clocked Out'}`;
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message,
                        timer: 2000
                    });

                    // Update time stats if provided
                    if (data.stats) {
                        document.querySelector('.time-stat:nth-child(1) p').textContent = data.stats.today;
                        document.querySelector('.time-stat:nth-child(2) p').textContent = data.stats.week;
                        document.querySelector('.time-stat:nth-child(3) p').textContent = data.stats.month;
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing your request'
                });
            });
        });
    }
});