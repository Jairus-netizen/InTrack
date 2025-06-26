// Auto-focus and move between OTP inputs
const otpInputs = document.querySelectorAll('.form-groups input');

otpInputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
        if (e.target.value.length === 1) {
            if (index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        }
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && e.target.value.length === 0) {
            if (index > 0) {
                otpInputs[index - 1].focus();
            }
        }
    });
});

// Timer countdown
let timeLeft = 120; // 2 minutes
const timer = document.getElementById('timer');
const resendLink = document.getElementById('resendOtp');

resendLink.classList.add('disabled');

const countdown = setInterval(() => {
    timeLeft--;
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;

    timer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

    if (timeLeft <= 0) {
        clearInterval(countdown);
        timer.style.display = 'none';
        resendLink.classList.remove('disabled');
    }
}, 1000);

// Resend OTP functionality
resendLink.addEventListener('click', (e) => {
    e.preventDefault();
    if (!resendLink.classList.contains('disabled')) {
        // Implement AJAX call to resend OTP
        fetch('/resend-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: 'user@example.com' }) // Replace with actual email
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('New OTP sent successfully!');
                    // Reset timer
                    timeLeft = 120;
                    resendLink.classList.add('disabled');
                    timer.style.display = 'inline';
                    otpInputs.forEach(input => input.value = '');
                    otpInputs[0].focus();
                } else {
                    alert('Failed to resend OTP. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
    }
});