/**
 * API Authentication Helper
 * Automatically adds JWT token to all fetch requests
 */
(function() {
    // Store the original fetch function
    const originalFetch = window.fetch;
    
    // Override fetch with our version that adds the Authorization header
    window.fetch = function(url, options = {}) {
        // Get the token from session
        const token = '<?php echo isset($_SESSION["token"]) ? $_SESSION["token"] : ""; ?>';
        
        // Set up headers if they don't exist
        if (!options.headers) {
            options.headers = {};
        }
        
        // Only add Authorization if we have a token
        if (token) {
            options.headers['Authorization'] = 'Bearer ' + token;
        }
        
        // Call the original fetch with our enhanced options
        return originalFetch(url, options);
    };
})();