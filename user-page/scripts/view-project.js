document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const editBtn = document.querySelector('.project-edit-btn');
    const saveBtn = document.querySelector('.project-save-btn');
    const cancelBtn = document.querySelector('.project-cancel-btn');
    const backBtn = document.querySelector('.project-back-btn');
    const completeBtn = document.querySelector('.project-complete-btn');
    const inputs = document.querySelectorAll('.view-project-input:not([readonly])');
    const textarea = document.querySelector('.view-project-textarea');
    const editImageBtn = document.querySelector('.edit-image-btn');
    const imageUpload = document.getElementById('project-image-upload');
    const projectImage = document.querySelector('.project-image');
    const viewProjectCard = document.querySelector('.view-project-card');
    
    // Store original values
    let originalValues = {};
    
    // Edit button click handler
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            // Store original values
            inputs.forEach(input => {
                originalValues[input.name] = input.value;
            });
            originalValues['project_progress'] = textarea.value;
            
            // Enable editing
            viewProjectCard.classList.add('editing');
            
            // Remove readonly attributes
            inputs.forEach(input => {
                input.removeAttribute('readonly');
            });
            textarea.removeAttribute('readonly');
            
            // Show/hide buttons
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-block';
            cancelBtn.style.display = 'inline-block';
            if (completeBtn) completeBtn.style.display = 'none';
        });
    }
    
    // Save button click handler
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            // Collect all updated data
            const formData = new FormData();
            formData.append('project_id', document.querySelector('.view-project-input[readonly]').value);
            
            inputs.forEach(input => {
                formData.append(input.name, input.value);
            });
            
            formData.append('project_progress', textarea.value);
            
            if (imageUpload.files[0]) {
                formData.append('project_image', imageUpload.files[0]);
            }
            
            // Show loading state
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
            
            // Send AJAX request
            fetch('update-project.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Project details updated successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to update project',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while updating the project',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Changes';
            });
        });
    }
    
    // Cancel button click handler
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            // Restore original values
            inputs.forEach(input => {
                if (originalValues[input.name]) {
                    input.value = originalValues[input.name];
                }
            });
            
            if (originalValues['project_progress']) {
                textarea.value = originalValues['project_progress'];
            }
            
            // Reset image
            if (imageUpload) {
                imageUpload.value = '';
            }
            
            exitEditMode();
        });
    }
    
    // Complete button click handler
    if (completeBtn) {
        completeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const projectId = document.querySelector('.view-project-input[readonly]').value;
            
            // Get original expected completion date
            fetch(`get-project-date.php?id=${projectId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const currentDate = new Date().toISOString().split('T')[0];
                        const isOverdue = data.expected_completion < currentDate;

                        Swal.fire({
                            title: 'Complete Project',
                            html: isOverdue 
                                ? 'This project is overdue! Are you sure you want to mark it as completed?' 
                                : 'Are you sure you want to mark this project as completed?',
                            icon: isOverdue ? 'warning' : 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#00a651',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, complete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.getElementById('completeProjectForm').submit();
                            }
                        });
                    } else {
                        Swal.fire('Error', 'Failed to check project status', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'An error occurred while checking project status', 'error');
                });
        });
    }

    
    // Back button click handler
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            window.location.href = 'projects.php';
        });
    }
    
    // Image upload handler
    if (editImageBtn) {
        editImageBtn.addEventListener('click', function() {
            imageUpload.click();
        });
    }
    
    if (imageUpload) {
        imageUpload.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    projectImage.src = event.target.result;
                };
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }
    
    // Helper function to exit edit mode
    function exitEditMode() {
        viewProjectCard.classList.remove('editing');
        
        // Set readonly attributes
        inputs.forEach(input => {
            if (!input.classList.contains('view-project-date')) {
                input.setAttribute('readonly', true);
            }
        });
        textarea.setAttribute('readonly', true);
        
        if (editBtn) editBtn.style.display = 'inline-block';
        if (saveBtn) saveBtn.style.display = 'none';
        if (cancelBtn) cancelBtn.style.display = 'none';
        if (completeBtn) completeBtn.style.display = 'inline-block';
    }
    
    // Initialize - make sure inputs are read-only by default
    inputs.forEach(input => {
        if (!input.classList.contains('view-project-date')) {
            input.setAttribute('readonly', true);
        }
    });
    if (textarea) textarea.setAttribute('readonly', true);
});