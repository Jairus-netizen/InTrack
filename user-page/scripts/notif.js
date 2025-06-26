document.addEventListener('DOMContentLoaded', function () {
    // Tab switching
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Show corresponding content
            const tabName = this.dataset.tab;
            tabContents.forEach(content => {
                content.style.display = 'none';
            });
            document.getElementById(`${tabName}-tab`).style.display = 'block';
        });
    });

    // Archive button - using event delegation
    document.addEventListener('submit', function (e) {
        if (e.target.closest('.notification-form') && e.target.querySelector('input[name="action"]').value === 'archive') {
            e.preventDefault();
            const form = e.target;
            const card = form.closest('.notification-card');

            // Add animation
            card.style.transform = 'scale(0.95)';
            card.style.opacity = '0.5';

            // Submit form after animation
            setTimeout(() => {
                form.submit();
            }, 300);
        }
    });

    // Add this to your notif.js
    function updateNotificationBadge() {
        fetch('../get_notification_count.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.notification-badge');
                if (data.count > 0) {
                    if (!badge) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'notification-badge';
                        newBadge.textContent = data.count;
                        document.querySelector('.notification-bell').appendChild(newBadge);
                    } else {
                        badge.textContent = data.count;
                    }
                } else if (badge) {
                    badge.remove();
                }
            });
    }

    // Call this after any action that affects notifications
    document.querySelectorAll('.notification-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            // Let the form submit normally
            setTimeout(updateNotificationBadge, 500); // Wait a bit for the action to complete
        });
    });

    // Restore button - using event delegation
    document.addEventListener('submit', function (e) {
        if (e.target.closest('.notification-form') && e.target.querySelector('input[name="action"]').value === 'restore') {
            e.preventDefault();
            const form = e.target;
            const card = form.closest('.notification-card');

            // Add animation
            card.style.transform = 'scale(0.95)';
            card.style.opacity = '0.5';

            // Submit form after animation
            setTimeout(() => {
                form.submit();
            }, 300);
        }
    });

    // Delete button - using event delegation
    document.addEventListener('click', function (e) {
        if (e.target.closest('.delete-btn')) {
            const deleteBtn = e.target.closest('.delete-btn');
            const form = deleteBtn.closest('.delete-form');
            const card = form.closest('.notification-card');

            // Show delete confirmation modal
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'block';

            // Confirm delete
            document.getElementById('confirmDelete').onclick = function () {
                modal.style.display = 'none';

                // Add animation
                card.style.transform = 'scale(0.95)';
                card.style.opacity = '0.5';

                // Submit form after animation
                setTimeout(() => {
                    form.submit();
                }, 300);
            };
        }
    });

    // Cancel button for modal
    document.getElementById('cancelDelete').addEventListener('click', function () {
        document.getElementById('deleteModal').style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function (event) {
        if (event.target.classList.contains('modal')) {
            document.getElementById('deleteModal').style.display = 'none';
        }
    });

    // Mark notifications as read when viewed
    const unreadNotifications = document.querySelectorAll('.notification-card:not([data-read="true"])');
    unreadNotifications.forEach(notification => {
        const notificationId = notification.dataset.id;
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `notification_id=${notificationId}`
        });
    });
});