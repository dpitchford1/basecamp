/* application wrapper
-------------------------------------------------------------------------------------------------------------------------- */
/**
 * Main Kaneshop application module
 * @namespace kaneshop
 * @description Core functionality for the Kaneshop theme, including header, menu, and UI management
 */
var kaneshop = kaneshop || {};
window.kaneshop = (function (window, document, kaneshopwrapper){
    "use strict";
    
    // Cache DOM elements once to improve performance
    const doc = document;
    const exitcanvashtml = doc.querySelector('#site-body');
    const exitcanvasbody = doc.querySelector('#page-body');
    const exitcanvas = doc.querySelector('#exit-off-canvas');
    const headerSS = doc.querySelector('#global-header--ss');
    const header = doc.querySelector('#global-header');
    const searchform = doc.querySelector('#searchform');

    // Create links and logo for small screen header using fragment (performance best practice)
    const fragment = doc.createDocumentFragment();
    const toggler = fragment.appendChild(doc.createElement('a'));
    const ssLogo = fragment.appendChild(doc.createElement('a'));
    const searchtoggle = fragment.appendChild(doc.createElement('a'));

    // Initialize media query
    const mediaQuery = window.matchMedia('(max-width: 767px)');
    
    // Feature detection for better browser support
    const supportsTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    const supportsPassiveEvents = (function() {
        let passiveSupported = false;
        try {
            const options = {
                get passive() { 
                    passiveSupported = true;
                    return true;
                }
            };
            window.addEventListener('test', null, options);
            window.removeEventListener('test', null, options);
        } catch (e) {
            passiveSupported = false;
        }
        return passiveSupported;
    })();

    /**
     * Enhanced event listener helper that handles touch and mouse events appropriately
     * @param {HTMLElement} element - The DOM element to attach the event to
     * @param {string} eventType - The event type (e.g., 'click', 'scroll')
     * @param {Function} handler - The event handler function
     * @param {Object} options - Additional options for addEventListener
     * @returns {void}
     */
    function addEventListenerWithOptions(element, eventType, handler, options = {}) {
        if (!elementExists(element)) return;
        
        try {
            // For touch devices, add touch events where appropriate
            if (supportsTouch && eventType === 'click') {
                element.addEventListener('touchend', function(e) {
                    // Prevent ghost clicks by stopping propagation
                    e.preventDefault();
                    e.stopPropagation();
                    handler(e);
                }, supportsPassiveEvents ? { passive: false, ...options } : false);
            }
            
            // Always add the original event for non-touch devices and as fallback
            element.addEventListener(
                eventType, 
                handler, 
                supportsPassiveEvents ? options : false
            );
        } catch (e) {
            console.error(`Error adding event listener for ${eventType}:`, e);
            // Fallback to basic event listener
            element.addEventListener(eventType, handler);
        }
    }

    // Scroll position management
    let scrollpos = window.scrollY;
    let ticking = false;

    /**
     * Checks if an element exists safely
     * @param {*} element - The element to check
     * @returns {boolean} - True if the element exists
     */
    function elementExists(element) {
        return element !== null && element !== undefined;
    }

    /**
     * Throttle function for better performance
     * @param {Function} callback - The function to throttle
     * @param {number} delay - Throttle delay in milliseconds
     * @returns {Function} - Throttled function
     */
    function throttle(callback, delay) {
        let lastCall = 0;
        return function(...args) {
            const now = new Date().getTime();
            if (now - lastCall < delay) {
                return;
            }
            lastCall = now;
            return callback(...args);
        };
    }
    
    /**
     * Debounce function for less frequent updates
     * @param {Function} callback - The function to debounce
     * @param {number} wait - Debounce wait time in milliseconds
     * @returns {Function} - Debounced function
     */
    function debounce(callback, wait) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => callback.apply(context, args), wait);
        };
    }

    /**
     * Manages the sticky header behavior
     * Uses IntersectionObserver when available, with fallback to scroll events
     * @returns {void}
     */
    function stickyheader(){
        // Only proceed if header element exists
        if (!elementExists(header)) {
            console.warn('Header element not found, skipping sticky header functionality');
            return;
        }
        
        // Use IntersectionObserver instead of scroll events for better performance
        if ('IntersectionObserver' in window) {
            // Create a sentinel element that will trigger our sticky header
            const sentinel = document.createElement('div');
            sentinel.classList.add('sticky-sentinel');
            sentinel.style.position = 'absolute';
            sentinel.style.top = '0';
            sentinel.style.height = '50px'; // Match the scroll threshold
            sentinel.style.width = '1px';
            sentinel.style.opacity = '0';
            sentinel.style.pointerEvents = 'none';
            
            // Insert the sentinel right before the header
            if (header.parentNode) {
                header.parentNode.insertBefore(sentinel, header);
            }
            
            // Create the observer
            const headerObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) {
                        add_class_on_scroll();
                    } else {
                        remove_class_on_scroll();
                    }
                });
            }, { 
                threshold: 0,
                rootMargin: '10px 0px 0px 0px' // Trigger when sentinel is 50px in/out of viewport
            });
            
            // Start observing the sentinel
            headerObserver.observe(sentinel);
        } else {
            // Fallback to scroll events for older browsers
            function onScroll() {
                scrollpos = window.scrollY;
                
                if (!ticking) {
                    window.requestAnimationFrame(() => {
                        if(scrollpos > 50){
                            add_class_on_scroll();
                        } else {
                            remove_class_on_scroll();
                        }
                        ticking = false;
                    });
                    
                    ticking = true;
                }
            }

            // Use passive event listener for better scroll performance
            window.addEventListener('scroll', throttle(onScroll, 100), 
                supportsPassiveEvents ? { passive: true } : false);
        }

        /**
         * Adds sticky class to header
         * @private
         */
        function add_class_on_scroll() {
            header.classList.add("is--sticky");
        }

        /**
         * Removes sticky class from header
         * @private
         */
        function remove_class_on_scroll() {
            header.classList.remove("is--sticky");
        }
    }

    /**
     * Sets multiple attributes on an element
     * @param {HTMLElement} el - Element to set attributes on
     * @param {Object} attrs - Object of attribute name/value pairs
     * @returns {void}
     */
    function setAttributes(el, attrs) {
        // Guard clause to prevent errors with null elements
        if (!elementExists(el)) {
            console.warn('Cannot set attributes on non-existent element');
            return;
        }
        
        // Make sure attrs is an object
        if (!attrs || typeof attrs !== 'object') {
            console.warn('Invalid attributes object');
            return;
        }
        
        try {
            Object.keys(attrs).forEach(key => {
                if (attrs[key] !== undefined && attrs[key] !== null) {
                    el.setAttribute(key, attrs[key]);
                }
            });
        } catch (e) {
            console.error('Error setting attributes:', e);
        }
    }

    /**
     * Sets up the small screen header with menu and search toggles
     * @returns {void}
     */
    function setupSmallScreenHeader() {
        // Check if required elements exist
        if (!elementExists(headerSS)) {
            console.warn('Small screen header element not found, skipping setup');
            return;
        }
        
        // Create links for small screen header
        headerSS.appendChild(fragment);

        // Set small screen html Content
        toggler.innerHTML = 'Menu';
        ssLogo.innerHTML = 'Kaneism';
        searchtoggle.innerHTML = 'Search';

        // set attributes on SS header items
        // toggler link
        setAttributes(toggler, {
            "aria-controls": "global-header",
            "href": "#global-header",
            "id": "menu-trigger",
            "role": "button",
            "class": "menu-trigger ico i--menu",
            "aria-expanded": "false",
            "aria-label": "Toggle menu"
        });

        // create ss logo
        setAttributes(ssLogo, {
            "class": "brand brand-ss",
            "href": "/",
            "id": "menu-ss"
        });

        // search toggler link
        setAttributes(searchtoggle, {
            "aria-controls": "searchform",
            "href": "#searchform",
            "id": "search-trigger",
            "role": "button",
            "class": "search-trigger ico i--search",
            "aria-expanded": "false",
            "aria-label": "Toggle search"
        });
    }

    /**
     * Sets up the global header menu and search functionality
     * @returns {Object} UIState - The state manager for external access
     */
    function globalheadermenu(){
        // Safe event binding - verify all required elements exist
        if (!elementExists(toggler) || !elementExists(searchtoggle) || !elementExists(exitcanvas) || 
            !elementExists(header) || !elementExists(searchform)) {
            console.warn('Required menu elements not found, skipping menu functionality');
            return;
        }

        // Store the element that had focus before a menu was opened
        let lastFocusedElement = null;

        /**
         * Manages focus when opening modal/dialog-like components
         * @param {HTMLElement} container - The container to trap focus within
         * @returns {void}
         */
        function manageFocus(container) {
            if (!elementExists(container)) return;
            
            // Save last focused element to return to later
            lastFocusedElement = document.activeElement;
            
            // Find all focusable elements in the container
            const focusableElements = container.querySelectorAll('a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"])');
            
            // Focus the first element if it exists
            if (focusableElements.length > 0) {
                setTimeout(() => {
                    focusableElements[0].focus();
                }, 100);
            }
        }

        /**
         * Restores focus to the element that had focus before the modal was opened
         * @returns {void}
         */
        function restoreFocus() {
            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                setTimeout(() => {
                    lastFocusedElement.focus();
                }, 100);
            }
        }
        
        /**
         * Create a focus trap to keep focus within a specific container when it's open
         * @param {HTMLElement} container - The container to trap focus within
         */
        function createFocusTrap(container) {
            if (!elementExists(container)) return;
            
            // Get all focusable elements
            const focusableElements = Array.from(
                container.querySelectorAll(
                    'a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"])'
                )
            );
            
            if (focusableElements.length === 0) return;
            
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];
            
            // Handle Tab key presses
            container.addEventListener('keydown', function(e) {
                // Only handle tab key
                if (e.key !== 'Tab') return;
                
                // If shift + tab and on first element, move to last element
                if (e.shiftKey && document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                } 
                // If tab and on last element, move to first element
                else if (!e.shiftKey && document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            });
        }
        
        // Create focus traps for both menu and search once they exist
        function setupFocusTraps() {
            createFocusTrap(header);
            createFocusTrap(searchform);
        }
        
        // Set up focus traps after a short delay to ensure the DOM is ready
        setTimeout(setupFocusTraps, 500);

        /**
         * Unified state manager for toggle elements
         * @type {Object}
         */
        const UIState = {
            // Track the state of UI elements
            menuVisible: false,
            searchVisible: false,
            
            /**
             * Toggle menu state and update UI
             * @returns {boolean} New menu visibility state
             */
            toggleMenu: function() {
                // Close search if it's open
                if (this.searchVisible) {
                    this.closeSearch();
                }
                
                // Toggle menu state
                this.menuVisible = !this.menuVisible;
                
                // Update UI based on new state
                this.updateUI();
                
                // Manage focus when opening/closing
                if (this.menuVisible) {
                    // Just track the last focused element without changing focus
                    lastFocusedElement = document.activeElement;
                    
                    // No longer automatically focusing any element in menu
                    // This keeps behavior consistent with search and avoids jarring focus shifts
                } else {
                    restoreFocus();
                }
                
                return this.menuVisible;
            },
            
            /**
             * Toggle search state and update UI
             * @returns {boolean} New search visibility state
             */
            toggleSearch: function() {
                // Close menu if it's open
                if (this.menuVisible) {
                    this.closeMenu();
                }
                
                // Toggle search state
                this.searchVisible = !this.searchVisible;
                
                // Update UI based on new state
                this.updateUI();
                
                // Manage focus when opening/closing
                if (this.searchVisible) {
                    // Just track the last focused element without auto-focusing search
                    lastFocusedElement = document.activeElement;
                    
                    // No longer automatically focusing any element in search
                    // This prevents keyboard pop-up on mobile and avoids jarring focus shifts on desktop
                } else {
                    restoreFocus();
                }
                
                return this.searchVisible;
            },
            
            /**
             * Close both menu and search
             * @returns {void}
             */
            closeAll: function() {
                const wasOpen = this.menuVisible || this.searchVisible;
                this.menuVisible = false;
                this.searchVisible = false;
                this.updateUI();
                
                // Only restore focus if something was actually closed
                if (wasOpen) {
                    restoreFocus();
                }
            },
            
            /**
             * Close only the menu
             * @returns {void}
             */
            closeMenu: function() {
                const wasOpen = this.menuVisible;
                this.menuVisible = false;
                this.updateUI();
                
                // Only restore focus if the menu was actually open
                if (wasOpen) {
                    restoreFocus();
                }
            },
            
            /**
             * Close only the search
             * @returns {void}
             */
            closeSearch: function() {
                const wasOpen = this.searchVisible;
                this.searchVisible = false;
                this.updateUI();
                
                // Only restore focus if the search was actually open
                if (wasOpen) {
                    restoreFocus();
                }
            },
            
            /**
             * Update all UI elements based on current state
             * @returns {void}
             */
            updateUI: function() {
                try {
                    // Update menu elements
                    setAttributes(header, { 
                        "data-nav-slide": this.menuVisible ? "slide visible" : "slide hidden"
                        //"aria-hidden": this.menuVisible ? "false" : "true"
                    });
                    
                    toggler.className = this.menuVisible ? 
                        'menu-trigger open ico i--close' : 
                        'menu-trigger ico i--menu';
                    
                    // Update ARIA expanded state for menu
                    setAttributes(toggler, {
                        "aria-expanded": this.menuVisible ? "true" : "false"
                    });
                    
                    // Update search elements
                    setAttributes(searchform, { 
                        "data-search-slide": this.searchVisible ? "slide visible" : "slide hidden"
                        //"aria-hidden": this.searchVisible ? "false" : "true"
                    });
                    
                    searchtoggle.className = this.searchVisible ? 
                        'search-trigger open ico i--close' : 
                        'search-trigger ico i--search';
                    
                    // Update ARIA expanded state for search
                    setAttributes(searchtoggle, {
                        "aria-expanded": this.searchVisible ? "true" : "false"
                    });
                    
                    // Update background elements
                    setAttributes(exitcanvasbody, { 
                        "data-off-screen": this.menuVisible ? "visible" : "hidden" 
                    });
                    
                    setAttributes(exitcanvashtml, { 
                        "data-off-canvas": this.searchVisible ? "visible" : "hidden" 
                    });
                    
                    // Ensure proper tabindex for content outside menus
                    // This prevents the user from tabbing into the background content
                    const mainContent = doc.querySelector('#main');
                    if (elementExists(mainContent)) {
                        if (this.menuVisible || this.searchVisible) {
                            mainContent.setAttribute('tabindex', '-1');
                            mainContent.setAttribute('aria-hidden', 'true');
                        } else {
                            mainContent.removeAttribute('tabindex');
                            mainContent.removeAttribute('aria-hidden');
                        }
                    }
                } catch (e) {
                    console.error('Error updating UI:', e);
                }
            },
            
            /**
             * Initialize state based on current DOM
             * @returns {void}
             */
            init: function() {
                try {
                    // Set initial state based on media query
                    this.menuVisible = header.getAttribute('data-nav-slide') === 'slide visible';
                    this.searchVisible = searchform.getAttribute('data-search-slide') === 'slide visible';
                    
                    // Ensure backgrounds are in correct initial state
                    setAttributes(exitcanvashtml, { "data-off-canvas": "hidden" });
                    setAttributes(exitcanvasbody, { "data-off-screen": "hidden" });
                    
                    // Ensure ARIA states are set correctly on init
                    setAttributes(toggler, {
                        "aria-expanded": this.menuVisible ? "true" : "false"
                    });
                    
                    setAttributes(searchtoggle, {
                        "aria-expanded": this.searchVisible ? "true" : "false"
                    });
                    
                    // Add initial ARIA hidden states
                    // setAttributes(header, {
                    //     "aria-hidden": this.menuVisible ? "false" : "true"
                    // });
                    
                    // setAttributes(searchform, {
                    //     "aria-hidden": this.searchVisible ? "false" : "true"
                    // });
                    
                } catch (e) {
                    console.error('Error initializing UI state:', e);
                    // Set safe defaults
                    this.menuVisible = false;
                    this.searchVisible = false;
                }
            }
        };
        
        // Initialize the state manager
        UIState.init();
        
        /* On menu click do this */
        addEventListenerWithOptions(toggler, 'click', function (e) {
            e.preventDefault();
            UIState.toggleMenu();
        });

        /* On search click do this */
        addEventListenerWithOptions(searchtoggle, 'click', function (e) {
            e.preventDefault();
            UIState.toggleSearch();
        });

        /* On exit canvas BG click do this */
        addEventListenerWithOptions(exitcanvas, 'click', function (e) {
            e.preventDefault();
            UIState.closeAll();
        });
        
        /* Add enhanced keyboard accessibility - expand beyond Escape key */
        document.addEventListener('keydown', function(e) {
            // Escape key closes all menus
            if (e.key === 'Escape') {
                UIState.closeAll();
                return;
            }
            
            // Handle menu toggle with Alt+M
            if (e.key === 'm' && e.altKey) {
                e.preventDefault();
                UIState.toggleMenu();
                return;
            }
            
            // Handle search toggle with Alt+S
            if (e.key === 's' && e.altKey) {
                e.preventDefault();
                UIState.toggleSearch();
                return;
            }
            
            // Handle menu navigation with arrow keys when menu is open
            if (UIState.menuVisible) {
                const menuLinks = header.querySelectorAll('a[href], button');
                if (menuLinks.length === 0) return;
                
                // Get the current focused element index
                let currentIndex = Array.from(menuLinks).indexOf(document.activeElement);
                
                // If no menu item is focused, default to -1
                if (currentIndex === -1) return;
                
                // Navigate with arrow keys
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const nextIndex = (currentIndex + 1) % menuLinks.length;
                    menuLinks[nextIndex].focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prevIndex = (currentIndex - 1 + menuLinks.length) % menuLinks.length;
                    menuLinks[prevIndex].focus();
                }
            }
        });

        /**
         * Handle media query changes for header visibility
         * @param {MediaQueryList} mq - The media query list
         * @returns {void}
         */
        function headerHide(mq) {    
            // Update the UI based on media query
            UIState.menuVisible = !mq.matches;
            UIState.searchVisible = false;
            UIState.updateUI();
            
            // Ensure canvas is always properly hidden initially, regardless of screen size
            setAttributes(exitcanvashtml, { "data-off-canvas": "hidden" });
            setAttributes(exitcanvasbody, { "data-off-screen": "hidden" });
            
            // Only apply these attributes in mobile view
            if (mq.matches) {
                // Apply aria labelledby for global header
                setAttributes(header, { 
                    "aria-labelledby": "menu-trigger",
                    "role": "navigation"
                });
                
                // Set search attributes
                setAttributes(searchform, { 
                    "aria-labelledby": "search-trigger",
                    "role": "search"
                });
            } else {
                // Remove role attributes on large screens as they're visually integrated
                setAttributes(header, {
                    "aria-labelledby": null,
                    "role": "navigation"  // Always keep navigation role
                });
                
                setAttributes(searchform, {
                    "aria-labelledby": null,
                    "role": "search"  // Always keep search role
                });
            }
        }

        // Add a listener for media query changes
        mediaQuery.addEventListener('change', headerHide);
        
        // Initialize on load
        headerHide(mediaQuery);
        
        // Return the state manager for external access if needed
        return UIState;
    }

    /**
     * Sets up the scroll to top button functionality
     * @returns {void}
     */
    function setupScrollToTop() {
        const toTopButton = doc.querySelector('.js-BackToTop');
        if (!elementExists(toTopButton)) {
            console.warn('Back to top button not found, skipping functionality');
            return;
        }

        // Use our enhanced event listener for better touch support
        addEventListenerWithOptions(toTopButton, 'click', function(e) {
            e.preventDefault();
            
            try {
                // Modern smooth scroll with fallback
                if ('scrollBehavior' in document.documentElement.style) {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                } else {
                    // Fallback for browsers that don't support smooth scrolling
                    function _scrollToTop() {
                        if (doc.body.scrollTop !== 0 || doc.documentElement.scrollTop !== 0) {
                            window.scrollBy(0, -50);
                            setTimeout(_scrollToTop, 10);
                        }
                    }
                    _scrollToTop();
                }
            } catch (e) {
                console.error('Error during scroll to top:', e);
                // Simplest fallback
                window.scrollTo(0, 0);
            }
        });
    }

    /**
     * Initializes all functionality
     * @returns {void}
     */
    function init() {
        setupSmallScreenHeader();
        stickyheader();
        globalheadermenu();
        setupScrollToTop();
        // themeSwitcher() removed as it's now in themer.js
    }

    // Safe load event handling
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        // Document already loaded, run init
        setTimeout(init, 1);
    } else {
        // Use modern event listener
        document.addEventListener('DOMContentLoaded', init);
    }

    // Return public methods if needed for external access
    return {
        // Public API
        elementExists: elementExists,
        setAttributes: setAttributes,
        throttle: throttle,
        debounce: debounce,
        addEventListenerWithOptions: addEventListenerWithOptions
    };

})(window, document);

