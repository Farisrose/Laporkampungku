/**
 * Navbar Session Management
 * Handles conditional rendering of login/logout buttons based on session state
 */

/**
 * Check if user is logged in by verifying session
 * @returns {Promise<{isLoggedIn: boolean, user: Object|null}>}
 */
async function checkLoginStatus() {
    try {
        const fd = new FormData();
        fd.append('action', 'check');

        const resp = await fetch('../backend/auth.php', {
            method: 'POST',
            body: fd
        });

        const result = await resp.json();
        
        if (result.success) {
            return {
                isLoggedIn: true,
                user: result.user
            };
        } else {
            return {
                isLoggedIn: false,
                user: null
            };
        }
    } catch (err) {
        console.error('Error checking login status:', err);
        return {
            isLoggedIn: false,
            user: null
        };
    }
}

/**
 * Update navbar based on login status
 * @param {boolean} isLoggedIn - Is user logged in
 * @param {Object|null} user - User object if logged in
 */
function updateNavbar(isLoggedIn, user = null) {
    // Get navbar containers
    const desktopNav = document.querySelector('.hidden.md\\:flex.items-center.gap-6');
    const mobileNav = document.getElementById('mobileMenu');

    if (!desktopNav) {
        console.warn('Desktop navbar container not found');
        return;
    }

    if (isLoggedIn && user) {
        // User is logged in - show Dashboard and Logout
        const loginBtn = desktopNav.querySelector('a[href="login.html"]');
        const registerBtn = desktopNav.querySelector('a[href="register.html"]');
        
        if (loginBtn) loginBtn.style.display = 'none';
        if (registerBtn) registerBtn.style.display = 'none';

        // Create dashboard and logout buttons if they don't exist
        let dashboardBtn = desktopNav.querySelector('[data-action="dashboard"]');
        let logoutBtn = desktopNav.querySelector('[data-action="logout"]');

        if (!dashboardBtn) {
            dashboardBtn = document.createElement('a');
            dashboardBtn.href = 'user_dashboard.html';
            dashboardBtn.className = 'btn btn-primary w-full';
            dashboardBtn.dataset.action = 'dashboard';
            dashboardBtn.textContent = 'Dashboard Saya';
            desktopNav.appendChild(dashboardBtn);
        } else {
            dashboardBtn.style.display = 'inline-block';
        }

        if (!logoutBtn) {
            logoutBtn = document.createElement('button');
            logoutBtn.className = 'btn btn-outline';
            logoutBtn.dataset.action = 'logout';
            logoutBtn.textContent = 'Keluar';
            logoutBtn.onclick = handleLogout;
            desktopNav.appendChild(logoutBtn);
        } else {
            logoutBtn.style.display = 'inline-block';
        }

        // Update mobile menu
        if (mobileNav) {
            updateMobileNavbar(mobileNav, isLoggedIn, user);
        }

    } else {
        // User is not logged in - show Login and Register
        const loginBtn = desktopNav.querySelector('a[href="login.html"]');
        const registerBtn = desktopNav.querySelector('a[href="register.html"]');
        
        if (loginBtn) loginBtn.style.display = 'inline-block';
        if (registerBtn) registerBtn.style.display = 'inline-block';

        // Hide dashboard and logout buttons if they exist
        const dashboardBtn = desktopNav.querySelector('[data-action="dashboard"]');
        const logoutBtn = desktopNav.querySelector('[data-action="logout"]');

        if (dashboardBtn) dashboardBtn.style.display = 'none';
        if (logoutBtn) logoutBtn.style.display = 'none';

        // Update mobile menu
        if (mobileNav) {
            updateMobileNavbar(mobileNav, isLoggedIn, null);
        }
    }
}

/**
 * Update mobile navbar menu
 * @param {HTMLElement} mobileNav - Mobile menu container
 * @param {boolean} isLoggedIn - Is user logged in
 * @param {Object|null} user - User object if logged in
 */
function updateMobileNavbar(mobileNav, isLoggedIn, user) {
    // Get or create mobile action buttons container
    let mobileActions = mobileNav.querySelector('[data-mobile-actions]');
    
    if (!mobileActions) {
        mobileActions = document.createElement('div');
        mobileActions.setAttribute('data-mobile-actions', 'true');
        mobileActions.className = 'flex flex-col gap-3 pt-4 border-t border-border-light';
        mobileNav.appendChild(mobileActions);
    }

    if (isLoggedIn && user) {
        // Hide login/register links
        const loginLink = mobileNav.querySelector('a[href="login.html"]');
        const registerLink = mobileNav.querySelector('a[href="register.html"]');
        
        if (loginLink) loginLink.style.display = 'none';
        if (registerLink) registerLink.style.display = 'none';

        // Update mobile actions
        mobileActions.innerHTML = `
            <a href="user_dashboard.html" class="btn btn-primary w-full">Dashboard Saya</a>
            <button onclick="handleLogout()" class="btn btn-outline w-full">Keluar</button>
        `;
    } else {
        // Show login/register links
        const loginLink = mobileNav.querySelector('a[href="login.html"]');
        const registerLink = mobileNav.querySelector('a[href="register.html"]');
        
        if (loginLink) loginLink.style.display = 'block';
        if (registerLink) registerLink.style.display = 'block';

        // Hide mobile actions
        mobileActions.innerHTML = '';
    }
}

/**
 * Handle logout
 */
async function handleLogout() {
    try {
        const fd = new FormData();
        fd.append('action', 'logout');

        await fetch('../backend/auth.php', {
            method: 'POST',
            body: fd
        });

        // Redirect to login page
        window.location.href = 'login.html';
    } catch (err) {
        console.error('Logout error:', err);
        alert('Gagal melakukan logout');
    }
}

/**
 * Initialize navbar on page load
 * Run this function when page loads
 */
async function initializeNavbar() {
    try {
        const { isLoggedIn, user } = await checkLoginStatus();
        updateNavbar(isLoggedIn, user);
    } catch (err) {
        console.error('Error initializing navbar:', err);
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', initializeNavbar);

/**
 * ALTERNATIVE: Check session from localStorage (for faster response)
 * Use this if you want to avoid the extra API call on every page load
 */
function checkLoginStatusFromStorage() {
    try {
        const userDataJson = localStorage.getItem('userData');
        if (userDataJson) {
            const userData = JSON.parse(userDataJson);
            return {
                isLoggedIn: true,
                user: userData
            };
        }
    } catch (err) {
        console.error('Error reading from localStorage:', err);
    }
    
    return {
        isLoggedIn: false,
        user: null
    };
}

/**
 * Store user data in localStorage on login
 * Call this function after successful login
 */
function storeUserData(user) {
    try {
        localStorage.setItem('userData', JSON.stringify(user));
    } catch (err) {
        console.error('Error storing user data:', err);
    }
}

/**
 * Clear user data from localStorage on logout
 * Call this when user logs out
 */
function clearUserData() {
    try {
        localStorage.removeItem('userData');
    } catch (err) {
        console.error('Error clearing user data:', err);
    }
}
