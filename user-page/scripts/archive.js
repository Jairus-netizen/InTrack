document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('deleteModal');
        let currentCard = null;
        
        // Restore button functionality
        document.querySelectorAll('.restore-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.notification-card');
                card.style.transform = 'scale(0.95)';
                card.style.opacity = '0.7';
                
                // Simulate restore action (would be AJAX in real implementation)
                setTimeout(() => {
                    card.remove();
                    checkEmptyState();
                }, 300);
            });
        });
        
        // Delete button functionality
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                currentCard = this.closest('.notification-card');
                deleteModal.style.display = 'block';
            });
        });
        
        // Confirm delete
        document.getElementById('confirmDelete').addEventListener('click', function() {
            deleteModal.style.display = 'none';
            
            if (currentCard) {
                currentCard.style.transform = 'scale(0.95)';
                currentCard.style.opacity = '0.7';
                
                // Simulate delete action (would be AJAX in real implementation)
                setTimeout(() => {
                    currentCard.remove();
                    checkEmptyState();
                    currentCard = null;
                }, 300);
            }
        });
        
        // Cancel delete
        document.getElementById('cancelDelete').addEventListener('click', function() {
            deleteModal.style.display = 'none';
            currentCard = null;
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
                currentCard = null;
            }
        });
        
        // Check if we should show empty state
        function checkEmptyState() {
            const notificationList = document.querySelector('.notification-list');
            const emptyState = document.querySelector('.empty-state');
            
            if (notificationList.querySelectorAll('.notification-card').length === 0) {
                emptyState.style.display = 'block';
            } else {
                emptyState.style.display = 'none';
            }
        }
    });