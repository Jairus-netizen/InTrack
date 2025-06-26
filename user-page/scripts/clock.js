// clock.js - Shared clock functionality for both dashboard and time pages
document.addEventListener('DOMContentLoaded', function() {
    const clockButton = document.getElementById('clockButton');
    if (clockButton) {
        clockButton.addEventListener('click', function() {
            const currentStatus = this.getAttribute('data-status');
            const action = currentStatus === 'in' ? 'clock_out' : 'clock_in';
            
            Swal.fire({
                title: `${currentStatus === 'in' ? 'Clock Out' : 'Clock In'} Confirmation`,
                text: `Are you sure you want to ${currentStatus === 'in' ? 'clock out' : 'clock in'}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('scripts/clock_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=${action}`,
                        credentials: 'include'
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => { throw new Error(text) });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Update UI
                            const newStatus = action === 'clock_in' ? 'in' : 'out';
                            clockButton.setAttribute('data-status', newStatus);
                            clockButton.textContent = newStatus === 'in' ? 'Clock Out' : 'Clock In';
                            
                            document.querySelector('.status-indicator').className = 
                                'status-indicator ' + (newStatus === 'in' ? 'clocked-in' : 'clocked-out');
                            
                            document.querySelector('.status span').textContent = 
                                `Currently ${newStatus === 'in' ? 'Clocked In' : 'Clocked Out'}`;
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                html: data.message.replace(/\n/g, '<br>'),
                                timer: 3000
                            });

                            // Update stats if available
                            if (data.stats) {
                                const statElements = document.querySelectorAll('.time-stat p');
                                if (statElements.length >= 3) {
                                    statElements[0].textContent = data.stats.today || '0h 0m';
                                    statElements[1].textContent = data.stats.week || '0h 0m';
                                    statElements[2].textContent = data.stats.month || '0h 0m';
                                }
                            }

                            // Refresh if clocked out
                            if (newStatus === 'out') {
                                setTimeout(() => location.reload(), 1000);
                            }
                        } else {
                            throw new Error(data.message || 'Action failed');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Failed to complete action'
                        });
                    });
                }
            });
        });
    }
});