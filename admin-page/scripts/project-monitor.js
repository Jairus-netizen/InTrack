document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchButton = document.getElementById('search-button');
    const form = searchInput.closest('form');

    // Toggle between search and clear functionality
    searchInput.addEventListener('input', function() {
        const hasText = this.value.trim().length > 0;
        searchButton.innerHTML = `<i class='bx ${hasText ? 'bx-x' : 'bx-search'}'></i>`;
        searchButton.type = hasText ? 'button' : 'submit';
    });

    // Handle clear action
    searchButton.addEventListener('click', function() {
        if (searchInput.value.trim().length > 0) {
            // Clear the search
            searchInput.value = '';
            form.submit(); // Submit the form without search term
        }
    });
});