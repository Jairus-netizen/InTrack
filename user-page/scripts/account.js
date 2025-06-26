document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editProfileBtn');
    const profileImageContainer = document.getElementById('profileImageContainer');
    const editFormContainer = document.getElementById('editFormContainer');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const profileImage = document.querySelector('.profile-image');
    const profileForm = document.getElementById('profileForm');
    const profileImageInput = document.getElementById('profileImage');

    // Toggle edit mode
    editBtn.addEventListener('click', function() {
        profileImageContainer.style.display = 'none';
        editFormContainer.style.display = 'block';
    });

    // Cancel edit
    cancelEditBtn.addEventListener('click', function() {
        profileImageContainer.style.display = 'flex';
        editFormContainer.style.display = 'none';
        profileForm.reset();
        const preview = document.querySelector('.profile-image-preview');
        if (preview) preview.remove();
        const inputs = profileForm.querySelectorAll('input');
        inputs.forEach(input => input.style.borderColor = '');
    });

    // Preview selected image
    profileImageInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!validTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: 'Only JPG, PNG, and GIF images are allowed'
                });
                e.target.value = '';
                return;
            }
            
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: 'Maximum image size is 2MB'
                });
                e.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.createElement('img');
                preview.src = event.target.result;
                preview.className = 'profile-image-preview';
                preview.style.maxWidth = '200px';
                preview.style.maxHeight = '200px';
                preview.style.borderRadius = '50%';
                preview.style.marginTop = '10px';
                
                const existingPreview = document.querySelector('.profile-image-preview');
                if (existingPreview) existingPreview.remove();
                
                const container = e.target.closest('.form-group');
                container.appendChild(preview);
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // Handle form submission
    profileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        const allInputs = profileForm.querySelectorAll('input');
        allInputs.forEach(input => input.style.borderColor = '');
        
        // Validate form
        let isValid = true;
        const errorFields = [];
        const errorMessages = [];
        
        // Required fields
        const requiredFields = ['first_name', 'last_name', 'email'];
        requiredFields.forEach(field => {
            const input = profileForm.querySelector(`[name="${field}"]`);
            if (!input.value.trim()) {
                input.style.borderColor = '#ff4444';
                isValid = false;
                errorFields.push(input);
                errorMessages.push(`${input.previousElementSibling.textContent} is required`);
            }
        });
        
        // Email format
        const emailInput = profileForm.querySelector('[name="email"]');
        if (emailInput.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
            emailInput.style.borderColor = '#ff4444';
            isValid = false;
            if (!errorFields.includes(emailInput)) errorFields.push(emailInput);
            errorMessages.push('Please enter a valid email address');
        }
        
        // Phone format (optional)
        const phoneInput = profileForm.querySelector('[name="phone"]');
        if (phoneInput.value.trim() && !/^[\d\s\-()+]{10,20}$/.test(phoneInput.value)) {
            phoneInput.style.borderColor = '#ff4444';
            isValid = false;
            if (!errorFields.includes(phoneInput)) errorFields.push(phoneInput);
            errorMessages.push('Please enter a valid phone number');
        }
        
        // Show errors if any
        if (!isValid) {
            if (errorFields.length > 0) errorFields[0].focus();
            
            Swal.fire({
                icon: 'error',
                title: 'Form Errors',
                html: errorMessages.join('<br>'),
                didClose: () => {
                    if (errorFields.length > 0) errorFields[0].focus();
                }
            });
            return;
        }
        
        // Confirmation dialog
        Swal.fire({
            title: 'Save Changes?',
            text: 'Are you sure you want to update your profile?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, save changes'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(profileForm);
                const saveBtn = document.getElementById('saveProfileBtn');
                
                // Show loading state
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="bx bx-loader bx-spin"></i> Saving...';
                
                // Submit form
                fetch('update-profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(data => {
                    if (!data.success) throw new Error(data.message || 'Update failed');
                    
                    // Show success and reload
                    return Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        if (data.newImage) {
                            profileImage.src = data.newImage + '?t=' + new Date().getTime();
                        }
                        window.location.reload();
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to update profile'
                    });
                })
                .finally(() => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Changes';
                });
            }
        });
    });

    // Real-time validation
    const validateInput = (input) => {
        const value = input.value.trim();
        
        if (input.name === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            input.style.borderColor = '#ff4444';
            return false;
        }
        
        if (input.name === 'phone' && value && !/^[\d\s\-()+]{10,20}$/.test(value)) {
            input.style.borderColor = '#ff4444';
            return false;
        }
        
        input.style.borderColor = '';
        return true;
    };

    // Add real-time validation listeners
    ['email', 'phone'].forEach(field => {
        const input = profileForm.querySelector(`[name="${field}"]`);
        if (input) {
            input.addEventListener('input', () => validateInput(input));
            input.addEventListener('blur', () => validateInput(input));
        }
    });
});