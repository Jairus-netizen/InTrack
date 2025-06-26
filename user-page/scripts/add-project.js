document.addEventListener('DOMContentLoaded', function() {
    // Check for success message from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success') && urlParams.get('success') === 'true') {
        Swal.fire({
            title: 'Success!',
            text: 'Project created successfully',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(() => {
            // Clean the URL by removing the parameter
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
            // Redirect to projects page
            window.location.href = 'projects.php';
        });
        return; // Stop further execution to prevent form interaction
    }

    // Form elements
    const projectForm = document.querySelector('form.project-form-card');
    
    if (!projectForm) {
        console.error('Project form not found!');
        return;
    }

    const imageUpload = document.getElementById('project-image-upload');
    const imagePreview = document.querySelector('.image-upload-preview');
    const submitBtn = document.querySelector('.submit-btn');
    const resetBtn = document.querySelector('.reset-btn');
    const cancelBtn = document.querySelector('.cancel-btn');

    // Image upload handling
    if (imageUpload && imagePreview) {
        imageUpload.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const file = e.target.files[0];
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                
                if (!validTypes.includes(file.type)) {
                    Swal.fire({
                        title: 'Invalid File Type',
                        text: 'Only JPG, PNG, and GIF images are allowed',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    e.target.value = '';
                    return;
                }
                
                if (file.size > 10 * 1024 * 1024) {
                    Swal.fire({
                        title: 'File Too Large',
                        text: 'Maximum image size is 2MB',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    e.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    imagePreview.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.style.maxWidth = '100%';
                    img.style.maxHeight = '100%';
                    imagePreview.appendChild(img);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    // Form submission
    projectForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        let isValid = true;
        const requiredFields = projectForm.querySelectorAll('[required]');
        const errorFields = [];
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#f44336';
                isValid = false;
                errorFields.push(field);
            } else {
                field.style.borderColor = '#ddd';
            }
        });
        
        if (!isValid) {
            if (errorFields.length > 0) {
                errorFields[0].focus();
            }
            
            Swal.fire({
                title: 'Missing Information',
                text: 'Please fill in all required fields',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Show confirmation dialog before submitting
        Swal.fire({
            title: 'Create Project?',
            text: "Are you sure you want to create this project?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, create it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit the form normally
                projectForm.submit();
            }
        });
    });

    // Form reset
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Reset Form?',
                text: "Are you sure you want to reset all fields?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, reset it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    projectForm.reset();
                    if (imagePreview) {
                        imagePreview.innerHTML = '<i class="bx bx-image-add"></i>';
                    }
                    if (imageUpload) {
                        imageUpload.value = '';
                    }
                    
                    document.querySelectorAll('.form-input, .form-textarea').forEach(input => {
                        input.style.borderColor = '#ddd';
                    });
                }
            });
        });
    }

    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Cancel Project Creation?',
                text: "Any unsaved changes will be lost",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'projects.php';
                }
            });
        });
    }
});