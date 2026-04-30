/**
 * Theme switching functionality
 * @namespace kaneshop.themer
 */
(function(window, document) {
    "use strict";

    const doc = document;

    /**
     * Safely retrieves an item from localStorage
     * @param {string} key - The key to retrieve
     * @returns {string|null} Retrieved value or null on error
     */
    function getLocalStorageItem(key) {
        try {
            return localStorage.getItem(key);
        } catch (e) {
            console.warn('Unable to access localStorage:', e);
            return null;
        }
    }
    
    /**
     * Safely sets an item in localStorage
     * @param {string} key - The key to set
     * @param {string} value - The value to store
     * @returns {boolean} True if successful, false otherwise
     */
    function setLocalStorageItem(key, value) {
        try {
            localStorage.setItem(key, value);
            return true;
        } catch (e) {
            console.warn('Unable to save to localStorage:', e);
            return false;
        }
    }

    /**
     * Handles theme toggle events
     * @param {Event} e - The change event
     * @returns {void}
     */
    function toggleTheme(e) {
        const isChecked = e.target.checked;
        
        if(isChecked) {
            doc.body.setAttribute('data-theme', 'dark');
            doc.documentElement.classList.add("dark");
            doc.documentElement.classList.remove("light");
            setLocalStorageItem('theme', 'dark');
        } else {
            doc.body.setAttribute('data-theme', 'light');
            doc.documentElement.classList.remove("dark");
            doc.documentElement.classList.add("light");
            setLocalStorageItem('theme', 'light');
        }
    }

    /**
     * Initializes theme switching functionality
     * @returns {void}
     */
    function init() {
        const checkbox = doc.querySelector('.theme-checkbox');
        if (!checkbox) {
            console.warn('Theme switcher checkbox not found, skipping theme switching functionality');
            return;
        }

        // Apply stored theme preference or default to dark
        let currentTheme = getLocalStorageItem('theme');
        if (!currentTheme) {
            currentTheme = 'dark';
            setLocalStorageItem('theme', 'dark');
        }

        doc.body.setAttribute('data-theme', currentTheme);

        if (currentTheme === 'dark') {
            checkbox.checked = true;
            doc.documentElement.classList.add("dark");
            doc.documentElement.classList.remove("light");
        } else {
            checkbox.checked = false;
            doc.documentElement.classList.add("light");
            doc.documentElement.classList.remove("dark");
        }

        // Set up event listeners
        checkbox.addEventListener('change', toggleTheme);

        // Support for touch devices
        const supportsTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        if (supportsTouch) {
            const checkboxLabel = doc.querySelector('.theme-checkbox-label');
            if (checkboxLabel) {
                checkboxLabel.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        }
    }

    // Initialize on DOM load
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(init, 1);
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }

    // Export public API if needed
    window.kaneshop = window.kaneshop || {};
    window.kaneshop.themer = {
        init: init,
        toggleTheme: toggleTheme
    };

})(window, document);
