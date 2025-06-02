/**
 * Session Timeout Handler - Improved Version
 */
(function() {
    // Configuration
    const warningTime = 60 * 1000; // 1 minute
    const countdownDuration = 30; // 30 seconds
    const logoutTime = warningTime + (countdownDuration * 1000); // 1 min + 30 sec
    
    let warningTimer;
    let logoutTimer;
    let countdownInterval;
    let warningShown = false;
    let modalCreated = false;
    
    // Create warning modal if it doesn't exist
    function createWarningModal() {
        // First, remove any existing modal with this ID
        const existingModal = document.getElementById('session-timeout-modal');
        if (existingModal) {
            existingModal.remove();
        }
        
        console.log("Creating new timeout modal");
        const modal = document.createElement('div');
        modal.id = 'session-timeout-modal';
        modal.className = 'modal-backdrop';
        modal.style.display = 'none'; // Use inline style to ensure it's hidden
        modal.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Session Timeout Warning</h3>
                </div>
                <div class="modal-body">
                    <p>Your session will expire in <span id="timeout-countdown">${countdownDuration}</span> seconds due to inactivity.</p>
                    <p>Do you want to continue?</p>
                </div>
                <div class="modal-footer">
                    <button id="session-logout-btn" class="btn btn-secondary">Log Out</button>
                    <button id="session-continue-btn" class="btn btn-primary">Continue Session</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        console.log("Modal added to DOM:", document.getElementById('session-timeout-modal'));
        
        // Add event listeners
        document.getElementById('session-continue-btn').addEventListener('click', function() {
            console.log("Continue button clicked");
            continueSession();
        });
        
        document.getElementById('session-logout-btn').addEventListener('click', function() {
            console.log("Logout button clicked");
            window.location.href = 'logout.php';
        });
        
        modalCreated = true;
    }
    
    // Show warning modal
    function showWarning() {
        console.log("Showing warning");
        if (!modalCreated) {
            createWarningModal();
        }
        warningShown = true;
        
        // Stop tracking activity temporarily
        stopTrackingActivity();
        
        const modal = document.getElementById('session-timeout-modal');
        if (modal) {
            modal.style.display = 'flex'; // Use inline style for maximum compatibility
        } else {
            console.error("Modal not found when showing");
            return;
        }
        
        // Start countdown
        let countdown = countdownDuration;
        const countdownEl = document.getElementById('timeout-countdown');
        if (countdownEl) {
            countdownEl.textContent = countdown;
            
            // Clear any existing interval
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
            
            countdownInterval = setInterval(function() {
                countdown--;
                countdownEl.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    // Don't automatically logout - the logoutTimer will handle that
                }
            }, 1000);
        }
    }
    
    // Hide warning modal
    function hideWarning() {
        console.log("Hiding warning");
        warningShown = false;
        
        // Clear countdown interval
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
        
        const modal = document.getElementById('session-timeout-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    // Continue session (user clicked "Continue")
    function continueSession() {
        console.log("Continuing session");
        hideWarning();
        resetTimers();
        startTrackingActivity();
    }
    
    // Reset timers
    function resetTimers() {
        console.log("Resetting timers");
        clearTimeout(warningTimer);
        clearTimeout(logoutTimer);
        
        warningTimer = setTimeout(function() {
            console.log("Warning timer triggered");
            showWarning();
            
            // Set logout timer after warning is shown
            logoutTimer = setTimeout(function() {
                console.log("Logout timer triggered");
                window.location.href = 'logout.php';
            }, countdownDuration * 1000);
            
        }, warningTime);
    }
    
    // Track user activity
    function handleUserActivity() {
        // Only reset timers if warning is not shown
        if (!warningShown) {
            resetTimers();
        }
    }
    
    // Start tracking user activity
    function startTrackingActivity() {
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(function(event) {
            document.addEventListener(event, handleUserActivity);
        });
    }
    
    // Stop tracking user activity
    function stopTrackingActivity() {
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(function(event) {
            document.removeEventListener(event, handleUserActivity);
        });
    }
    
    // Initialize
    function init() {
        console.log("Initializing session timeout");
        resetTimers(); // Start the timers
        startTrackingActivity(); // Start tracking activity
    }
    
    // Check if user is logged in before initializing
    if (document.querySelector('.logout-btn')) {
        console.log("User is logged in, initializing timeout");
        // Wait a moment for other scripts to load
        setTimeout(init, 1000);
    }
})();