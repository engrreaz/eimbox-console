function showToast(type, message, title = '') {
    const types = {
        success: 'bg-success text-white',
        info: 'bg-info text-dark',
        primary: 'bg-primary text-white',
        warning: 'bg-warning text-dark',
        danger: 'bg-danger text-white',
        error: 'bg-danger text-white'
    };

    const toastId = 'toast-' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center border-0 mb-2 ${types[type] || 'bg-secondary text-white'}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${title ? `<strong>${title}</strong><br>` : ''}
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    const container = document.getElementById('toastContainer');
    container.insertAdjacentHTML('beforeend', toastHTML);

    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();

    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

function setCookie(name, value, days = 30){
    let d = new Date();
    d.setTime(d.getTime() + (days*24*60*60*1000));
    document.cookie = name + "=" + encodeURIComponent(value) +
        ";expires=" + d.toUTCString() + ";path=/";
}


function getCookie(name){
    let cname = name + "=";
    let ca = document.cookie.split(';');
    for(let i=0;i<ca.length;i++){
        let c = ca[i].trim();
        if(c.indexOf(cname) === 0){
            return decodeURIComponent(c.substring(cname.length));
        }
    }
    return "";
}


function deleteCookie(name){
    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
}

/**
 * =========================================================
 * EIMBox Common Light / Dark Mode System
 * Centralized theme detection, synchronization & reactive UI
 * =========================================================
 */
const EimboxTheme = {
    _listeners: [],

    /**
     * Check if dark mode is currently active
     * @returns {boolean}
     */
    isDark() {
        const doc = document.documentElement;
        return doc.classList.contains('dark-style') || 
               doc.getAttribute('data-bs-theme') === 'dark' || 
               document.body.classList.contains('dark-style');
    },

    /**
     * Get current theme name ('dark' | 'light')
     * @returns {string}
     */
    getTheme() {
        return this.isDark() ? 'dark' : 'light';
    },

    /**
     * Set theme explicitly ('light' | 'dark' | 'system')
     * @param {string} theme 
     */
    setTheme(theme) {
        let isDark = false;
        if (theme === 'system') {
            isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        } else {
            isDark = (theme === 'dark');
        }

        const doc = document.documentElement;
        if (isDark) {
            doc.classList.add('dark-style');
            doc.classList.remove('light-style');
            doc.setAttribute('data-bs-theme', 'dark');
        } else {
            doc.classList.add('light-style');
            doc.classList.remove('dark-style');
            doc.setAttribute('data-bs-theme', 'light');
        }

        // Persist theme preference in localStorage and Cookie
        try {
            localStorage.setItem('templateCustomizer-vertical-menu-template--Theme', theme);
        } catch (e) {}
        setCookie('site_theme', isDark ? 'dark' : 'light', 365);

        this.syncUI();
    },

    /**
     * Toggle between light and dark mode
     */
    toggle() {
        this.setTheme(this.isDark() ? 'light' : 'dark');
    },

    /**
     * Register a callback to be called whenever theme changes
     * @param {function(boolean, string): void} callback 
     */
    onChange(callback) {
        if (typeof callback === 'function') {
            this._listeners.push(callback);
        }
    },

    /**
     * Synchronize dynamic UI components (SVG circles, stat cards, charts, etc.)
     */
    syncUI() {
        const isDark = this.isDark();
        const theme = this.getTheme();

        // 1. Sync SVG Circular KPI Tracks
        const circleTracks = document.querySelectorAll('.kpi-circle-track-svg, #kpi-circle-track');
        circleTracks.forEach(track => {
            track.setAttribute('stroke', isDark ? '#35364e' : '#edf2f7');
        });

        // 2. Synchronize Platform Filter Pills if present
        const platformPills = document.querySelectorAll('#platformFilterPills .nav-link');
        platformPills.forEach(pill => {
            if (!pill.classList.contains('active')) {
                pill.classList.remove('text-dark');
            }
        });

        // 3. Dispatch global DOM event so any widget/page can respond
        window.dispatchEvent(new CustomEvent('eimboxThemeChanged', {
            detail: { isDark, theme }
        }));

        // 4. Notify registered listeners
        this._listeners.forEach(fn => {
            try {
                fn(isDark, theme);
            } catch (e) {
                console.error('Error in EimboxTheme listener:', e);
            }
        });
    },

    /**
     * Initialize theme observer on page load
     */
    init() {
        // Observe attribute & class changes on document.documentElement
        const observer = new MutationObserver(mutations => {
            for (const mutation of mutations) {
                if (mutation.attributeName === 'class' || mutation.attributeName === 'data-bs-theme') {
                    this.syncUI();
                    break;
                }
            }
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class', 'data-bs-theme']
        });

        // Listen for clicks on navbar style switcher buttons
        document.addEventListener('click', e => {
            const btn = e.target.closest('[data-bs-theme-value]');
            if (btn) {
                setTimeout(() => {
                    this.syncUI();
                }, 50);
            }
        });

        // Initial sync on startup
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.syncUI());
        } else {
            this.syncUI();
        }
    }
};

// Auto initialize and expose to global window
window.EimboxTheme = EimboxTheme;
EimboxTheme.init();

