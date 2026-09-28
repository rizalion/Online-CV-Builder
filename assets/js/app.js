/**
 * CV Builder — Global JavaScript
 * 
 * Dark mode toggle, theme persistence, and shared utilities.
 */

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initAnimations();
});

// ── Dark Mode ────────────────────────────────────

function initThemeToggle() {
    const toggle = document.getElementById('themeToggle');
    if (!toggle) return;

    const icon = toggle.querySelector('i');
    const updateIcon = (theme) => {
        if (icon) {
            if (theme === 'dark') {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon';
            }
        }
    };

    // Apply saved theme immediately (also handled inline to prevent flash)
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateIcon(savedTheme);

    toggle.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';

        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        updateIcon(next);

        // Sync with server session (optional, for SSR consistency)
        fetch(`${getBaseUrl()}/api/save_profile.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': getCsrfToken(),
            },
            body: `action=save_theme&theme=${next}`,
        }).catch(() => {}); // Silent fail — theme is primarily client-side
    });
}

// ── Scroll Animations ────────────────────────────

function initAnimations() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    if (!elements.length) return;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
    );

    elements.forEach((el) => observer.observe(el));
}

// ── Utilities ────────────────────────────────────

/**
 * Get the base URL from the meta tag or page.
 */
function getBaseUrl() {
    const meta = document.querySelector('meta[name="base-url"]');
    if (meta) return meta.content;
    // Fallback: derive from current URL
    return window.location.origin + '/cv-builder';
}

/**
 * Get the CSRF token from the page.
 */
function getCsrfToken() {
    const input = document.querySelector('input[name="csrf_token"]');
    if (input) return input.value;
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.content;
    return '';
}

/**
 * Show a toast notification.
 */
function showToast(message, type = 'info', duration = 4000) {
    // Remove existing toasts
    document.querySelectorAll('.app-toast').forEach((t) => t.remove());

    const toast = document.createElement('div');
    toast.className = `app-toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas ${getToastIcon(type)}"></i>
            <span>${escapeHtml(message)}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Styles
    Object.assign(toast.style, {
        position: 'fixed',
        top: '80px',
        right: '20px',
        zIndex: '9999',
        padding: '14px 20px',
        borderRadius: '12px',
        background: type === 'success' ? '#059669' :
                    type === 'danger' ? '#dc2626' :
                    type === 'warning' ? '#d97706' : '#6366f1',
        color: 'white',
        fontSize: '0.9rem',
        fontWeight: '500',
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        boxShadow: '0 10px 25px rgba(0,0,0,0.2)',
        animation: 'fadeInUp 0.3s ease-out',
        maxWidth: '400px',
    });

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'fadeIn 0.3s ease-out reverse';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function getToastIcon(type) {
    return {
        success: 'fa-check-circle',
        danger: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle',
    }[type] || 'fa-info-circle';
}

/**
 * Escape HTML entities.
 */
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Debounce utility.
 */
function debounce(fn, delay = 300) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

/**
 * Format a date string for display.
 */
function formatDate(dateStr) {
    if (!dateStr) return 'Present';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
}

/**
 * Make an AJAX POST request with CSRF token.
 */
async function apiPost(url, data = {}) {
    const formData = new FormData();
    formData.append('csrf_token', getCsrfToken());
    for (const [key, val] of Object.entries(data)) {
        formData.append(key, val);
    }

    const response = await fetch(url, {
        method: 'POST',
        body: formData,
    });

    return response.json();
}

/**
 * Make an AJAX GET request.
 */
async function apiGet(url) {
    const response = await fetch(url);
    return response.json();
}
