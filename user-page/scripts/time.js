

    // Notes modal functionality
    const modal = document.getElementById('notesModal');
    const notesText = document.getElementById('notesText');
    let currentEntryId = null;
    
    // Edit Notes
    document.querySelectorAll('.edit-notes').forEach(btn => {
        btn.addEventListener('click', function() {
            currentEntryId = this.dataset.id;
            const row = this.closest('tr');
            const notes = row.querySelector('.notes-cell').textContent;
            notesText.value = notes.trim() === '--' ? '' : notes;
            modal.style.display = 'block';
        });
    });
    
    // Archive Entry
    document.querySelectorAll('.archive-entry').forEach(btn => {
        btn.addEventListener('click', function() {
            Swal.fire({
                title: 'Archive Confirmation',
                text: 'Are you sure you want to archive this entry?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, archive it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const entryId = this.dataset.id;
                    fetch('scripts/archive_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=archive&entry_id=${entryId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Archived!',
                                text: 'The entry has been archived.',
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    });
                }
            });
        });
    });
    
    // Modal actions
    document.querySelector('.close-modal').addEventListener('click', () => {
        modal.style.display = 'none';
    });
    
    document.getElementById('cancelNotes').addEventListener('click', () => {
        modal.style.display = 'none';
    });
    
    document.getElementById('saveNotes').addEventListener('click', () => {
        fetch('scripts/archive_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_notes&entry_id=${currentEntryId}&notes=${encodeURIComponent(notesText.value)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: 'Notes have been updated.',
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });