document.querySelector('.signup-form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault(); // Stop form submission
        
        // Remove any existing error messages
        const existingError = document.querySelector('.password-error');
        if (existingError) existingError.remove();
        
        // Create and display error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message password-error';
        errorDiv.textContent = 'Passwords do not match!';
        
        // Insert after password fields
        const passwordField = document.getElementById('confirm-password');
        passwordField.parentNode.insertBefore(errorDiv, passwordField.nextSibling);
        
        // Highlight fields
        document.getElementById('password').style.borderColor = 'red';
        document.getElementById('confirm-password').style.borderColor = 'red';
        
        return false;
    }
    
    return true;
});

// Real-time password matching check
document.getElementById('confirm-password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword && confirmPassword.length > 0) {
        this.style.borderColor = 'red';
        document.getElementById('password').style.borderColor = 'red';
    } else {
        this.style.borderColor = '';
        document.getElementById('password').style.borderColor = '';
    }
});
