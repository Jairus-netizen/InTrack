document.querySelector('.project-back-btn').addEventListener('click', function () {
    window.history.back();
});

document.addEventListener('DOMContentLoaded', function() {
    // Get the image and lightbox elements
    const projectImage = document.querySelector('.project-details-group img');
    const lightbox = document.getElementById('imageLightbox');
    const lightboxImg = document.getElementById('lightboxImage');
    const closeBtn = document.querySelector('.close-btn');
    
    if (projectImage) {
        // Open lightbox when image is clicked
        projectImage.addEventListener('click', function() {
            lightbox.style.display = 'block';
            lightboxImg.src = this.src;
            lightboxImg.alt = this.alt;
            
            // Add caption if alt text exists
            const caption = this.alt;
            if (caption) {
                document.querySelector('.lightbox-caption').textContent = caption;
            }
        });
        
        // Close lightbox when X is clicked
        closeBtn.addEventListener('click', function() {
            lightbox.style.display = 'none';
        });
        
        // Close lightbox when clicking outside image
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                lightbox.style.display = 'none';
            }
        });
        
        // Close with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lightbox.style.display === 'block') {
                lightbox.style.display = 'none';
            }
        });
    }
});