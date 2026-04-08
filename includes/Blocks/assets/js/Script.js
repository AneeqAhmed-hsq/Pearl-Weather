/**
 * Pearl Weather - Frontend JavaScript
 *
 * Handles all frontend interactions including:
 * - Custom sliders for weather items
 * - Preloader animations
 * - Forecast select dropdowns
 * - Forecast data toggling (tabs & selects)
 * - Swiper carousel initialization
 * - Tabbed content switching
 *
 * @package    PearlWeather
 * @since      1.0.0
 * @author     Your Name
 */

( function( $ ) {
    'use strict';

    /**
     * Pearl Weather Frontend Handler
     */
    const PearlWeatherFrontend = {

        /**
         * Initialize all frontend components
         */
        init: function() {
            this.preloaderInitialize();
            this.initAllBlocks();
        },

        /**
         * Initialize all weather blocks on the page
         */
        initAllBlocks: function() {
            const blocks = document.querySelectorAll( '.pearl-weather-block-wrapper' );
            
            if ( ! blocks.length ) {
                return;
            }

            blocks.forEach( ( blockWrapper ) => {
                const blockId = blockWrapper.getAttribute( 'id' );
                const block = document.getElementById( blockId );
                
                if ( ! block ) {
                    return;
                }

                this.initBlockComponents( block );
            } );
        },

        /**
         * Initialize components for a single block
         *
         * @param {HTMLElement} block - The weather block element
         */
        initBlockComponents: function( block ) {
            // Initialize forecast select dropdowns
            this.initForecastSelect( block );
            
            // Initialize forecast data toggling
            this.initForecastDataToggle( block );
            
            // Initialize Swiper carousels
            this.initSwiperCarousels( block );
            
            // Initialize tabbed content
            this.initTabs( block );
            
            // Initialize custom sliders
            this.initCustomSliders( block );
        },

        /**
         * Initialize preloader animations
         */
        preloaderInitialize: function() {
            const preloaders = document.querySelectorAll( '.pearl-weather-preloader' );
            
            if ( ! preloaders.length ) {
                return;
            }

            preloaders.forEach( ( loader ) => {
                const loaderId = loader.getAttribute( 'data-preloader-id' );
                
                if ( ! loaderId ) {
                    return;
                }

                const mainWrapper = document.querySelector( 
                    `#${ loaderId } .pearl-weather-template-wrapper` 
                );
                
                if ( mainWrapper ) {
                    mainWrapper.style.opacity = '0';
                    mainWrapper.style.transition = 'opacity 0.6s ease';
                    
                    mainWrapper.addEventListener( 'transitionend', () => {
                        mainWrapper.style.opacity = '1';
                        if ( loader.parentNode ) {
                            loader.remove();
                        }
                    }, { once: true } );
                }
            } );
        },

        /**
         * Initialize forecast select dropdowns
         *
         * @param {HTMLElement} block - The weather block
         */
        initForecastSelect: function( block ) {
            const selects = block.querySelectorAll( '.pearl-weather-forecast-select' );
            
            if ( ! selects.length ) {
                return;
            }

            selects.forEach( ( select ) => {
                const trigger = select.querySelector( '.pearl-weather-select-trigger' );
                const dropdownList = select.querySelector( '.pearl-weather-forecast-select-list' );
                const selectedOption = select.querySelector( '.pearl-weather-selected-option' );
                const chevronIcon = select.querySelector( '.pearl-weather-select-chevron' );

                if ( ! trigger || ! dropdownList ) {
                    return;
                }

                // Toggle dropdown on trigger click
                trigger.addEventListener( 'click', ( event ) => {
                    event.stopPropagation();
                    const isOpen = dropdownList.classList.contains( 'is-open' );
                    
                    // Close all other dropdowns first
                    this.closeAllDropdowns( block, select );
                    
                    if ( ! isOpen ) {
                        dropdownList.classList.add( 'is-open' );
                        dropdownList.classList.remove( 'is-hidden' );
                        if ( chevronIcon ) {
                            chevronIcon.classList.add( 'is-rotated' );
                        }
                    } else {
                        dropdownList.classList.remove( 'is-open' );
                        dropdownList.classList.add( 'is-hidden' );
                        if ( chevronIcon ) {
                            chevronIcon.classList.remove( 'is-rotated' );
                        }
                    }
                } );

                // Handle option selection
                dropdownList.addEventListener( 'click', ( event ) => {
                    const option = event.target.closest( '.pearl-weather-forecast-select-option' );
                    
                    if ( ! option ) {
                        return;
                    }

                    // Update active state
                    const activeOption = dropdownList.querySelector( '.pearl-weather-forecast-select-option.active' );
                    if ( activeOption ) {
                        activeOption.classList.remove( 'active' );
                    }
                    option.classList.add( 'active' );

                    // Update selected display
                    if ( selectedOption ) {
                        selectedOption.textContent = option.textContent;
                        selectedOption.setAttribute( 'data-value', option.getAttribute( 'data-value' ) || '' );
                    }

                    // Close dropdown
                    dropdownList.classList.remove( 'is-open' );
                    dropdownList.classList.add( 'is-hidden' );
                    if ( chevronIcon ) {
                        chevronIcon.classList.remove( 'is-rotated' );
                    }

                    // Trigger change event for data filtering
                    const changeEvent = new CustomEvent( 'pearlWeather:forecastChanged', {
                        detail: { value: option.getAttribute( 'data-value' ) }
                    } );
                    select.dispatchEvent( changeEvent );
                } );
            } );

            // Close dropdowns when clicking outside
            document.addEventListener( 'click', () => {
                this.closeAllDropdowns( block );
            } );
        },

        /**
         * Close all forecast dropdowns in a block
         *
         * @param {HTMLElement} block - The weather block
         * @param {HTMLElement} exceptSelect - Optional select to exclude
         */
        closeAllDropdowns: function( block, exceptSelect = null ) {
            const selects = block.querySelectorAll( '.pearl-weather-forecast-select' );
            
            selects.forEach( ( select ) => {
                if ( exceptSelect === select ) {
                    return;
                }
                
                const dropdownList = select.querySelector( '.pearl-weather-forecast-select-list' );
                const chevronIcon = select.querySelector( '.pearl-weather-select-chevron' );
                
                if ( dropdownList ) {
                    dropdownList.classList.remove( 'is-open' );
                    dropdownList.classList.add( 'is-hidden' );
                }
                if ( chevronIcon ) {
                    chevronIcon.classList.remove( 'is-rotated' );
                }
            } );
        },

        /**
         * Initialize forecast data toggling (for selects and tabs)
         *
         * @param {HTMLElement} block - The weather block
         */
        initForecastDataToggle: function( block ) {
            const forecasts = block.querySelectorAll( '.pearl-weather-forecast' );
            
            if ( ! forecasts.length ) {
                return;
            }

            forecasts.forEach( ( forecast ) => {
                // Handle forecast select changes
                const forecastSelect = forecast.querySelector( '.pearl-weather-forecast-select' );
                if ( forecastSelect ) {
                    forecastSelect.addEventListener( 'pearlWeather:forecastChanged', ( event ) => {
                        const selectedValue = event.detail.value;
                        this.toggleForecastData( forecast, selectedValue );
                    } );
                }

                // Handle forecast tabs
                const forecastTabs = forecast.querySelector( '.pearl-weather-forecast-tabs' );
                if ( forecastTabs ) {
                    forecastTabs.addEventListener( 'click', ( event ) => {
                        const tab = event.target.closest( '.pearl-weather-forecast-tab' );
                        if ( ! tab ) {
                            return;
                        }

                        // Remove active class from all tabs
                        const allTabs = forecastTabs.querySelectorAll( '.pearl-weather-forecast-tab' );
                        allTabs.forEach( t => t.classList.remove( 'active' ) );
                        tab.classList.add( 'active' );

                        const selectedValue = tab.getAttribute( 'data-value' );
                        this.toggleForecastData( forecast, selectedValue );
                    } );
                }
            } );
        },

        /**
         * Toggle forecast data visibility based on selected value
         *
         * @param {HTMLElement} forecast - The forecast container
         * @param {string} selectedValue - The selected value (temperature, humidity, etc.)
         */
        toggleForecastData: function( forecast, selectedValue ) {
            const forecastItems = forecast.querySelectorAll( '.pearl-weather-forecast-item' );
            
            if ( ! forecastItems.length ) {
                return;
            }

            forecastItems.forEach( ( item ) => {
                const itemType = item.getAttribute( 'data-forecast-type' );
                const isMatch = ( itemType === selectedValue );
                
                if ( isMatch ) {
                    item.classList.remove( 'is-hidden' );
                    item.classList.add( 'is-visible' );
                } else {
                    item.classList.remove( 'is-visible' );
                    item.classList.add( 'is-hidden' );
                }
            } );
        },

        /**
         * Initialize Swiper carousels
         *
         * @param {HTMLElement} block - The weather block
         */
        initSwiperCarousels: function( block ) {
            // Check if Swiper is available globally
            if ( typeof Swiper === 'undefined' ) {
                console.warn( 'Pearl Weather: Swiper library not loaded.' );
                return;
            }

            const swiperContainers = block.querySelectorAll( '.pearl-weather-swiper' );
            
            if ( ! swiperContainers.length ) {
                return;
            }

            swiperContainers.forEach( ( container ) => {
                const optionsAttr = container.getAttribute( 'data-swiper-options' );
                
                if ( ! optionsAttr ) {
                    return;
                }

                let swiperOptions = {};
                
                try {
                    swiperOptions = JSON.parse( optionsAttr );
                } catch ( error ) {
                    console.error( 'Pearl Weather: Invalid Swiper options JSON', error );
                    return;
                }

                // Initialize Swiper instance
                new Swiper( container, swiperOptions );
            } );
        },

        /**
         * Initialize tabbed content
         *
         * @param {HTMLElement} block - The weather block
         */
        initTabs: function( block ) {
            const tabGroups = block.querySelectorAll( '.pearl-weather-tabs-group' );
            
            if ( ! tabGroups.length ) {
                return;
            }

            tabGroups.forEach( ( tabGroup ) => {
                const tabButtons = tabGroup.querySelectorAll( '.pearl-weather-tab-btn' );
                const tabPanes = block.querySelectorAll( '.pearl-weather-tab-pane' );
                
                if ( ! tabButtons.length ) {
                    return;
                }

                tabButtons.forEach( ( button ) => {
                    button.addEventListener( 'click', ( event ) => {
                        event.preventDefault();
                        
                        const targetTabId = button.getAttribute( 'data-tab' );
                        
                        if ( ! targetTabId ) {
                            return;
                        }

                        // Remove active class from all buttons in this group
                        tabButtons.forEach( btn => btn.classList.remove( 'active' ) );
                        
                        // Add active class to clicked button
                        button.classList.add( 'active' );
                        
                        // Hide all panes
                        tabPanes.forEach( pane => pane.classList.remove( 'active' ) );
                        
                        // Show target pane
                        const targetPane = block.querySelector( `.pearl-weather-tab-pane#${ targetTabId }` );
                        if ( targetPane ) {
                            targetPane.classList.add( 'active' );
                        }
                        
                        // Special handling for map tab - trigger resize
                        if ( targetTabId === 'map' ) {
                            setTimeout( () => {
                                window.dispatchEvent( new Event( 'resize' ) );
                            }, 100 );
                        }
                    } );
                } );
            } );
        },

        /**
         * Initialize custom horizontal sliders
         *
         * @param {HTMLElement} block - The weather block
         */
        initCustomSliders: function( block ) {
            const sliderContainers = block.querySelectorAll( '.pearl-weather-custom-slider' );
            
            if ( ! sliderContainers.length ) {
                return;
            }

            sliderContainers.forEach( ( container ) => {
                this.customSlider( container );
            } );
        },

        /**
         * Custom slider implementation for horizontal scrolling
         *
         * @param {HTMLElement} sliderContainer - The slider container element
         */
        customSlider: function( sliderContainer ) {
            const sliderItems = sliderContainer.querySelectorAll( '.pearl-weather-slider-item' );
            const prevButton = sliderContainer.querySelector( '.pearl-weather-slider-nav-prev' );
            const nextButton = sliderContainer.querySelector( '.pearl-weather-slider-nav-next' );
            
            if ( ! sliderItems.length ) {
                return;
            }

            let currentPosition = 0;
            
            // Get computed gap value
            const sliderStyles = window.getComputedStyle( sliderContainer );
            const gap = parseInt( sliderStyles.gap, 10 ) || 0;
            
            // Calculate item dimensions
            const firstItemWidth = sliderItems[0]?.offsetWidth || 0;
            const regularItemWidth = sliderItems[1]?.offsetWidth || firstItemWidth;
            const itemWidth = regularItemWidth;
            
            // Calculate visible items count
            const containerWidth = sliderContainer.offsetWidth;
            const visibleItems = Math.floor( containerWidth / ( itemWidth + gap ) );
            
            // Calculate scroll amount (scroll by visible items minus 1)
            const scrollAmount = Math.max( 1, visibleItems - 1 ) * ( itemWidth + gap );
            
            // Calculate max scroll position
            const totalWidth = ( sliderItems.length * ( itemWidth + gap ) ) - gap;
            const maxPosition = Math.max( 0, totalWidth - containerWidth );
            
            /**
             * Update scroll position
             *
             * @param {number} position - Target scroll position
             */
            const updateScrollPosition = ( position ) => {
                const clampedPosition = Math.min( maxPosition, Math.max( 0, position ) );
                sliderContainer.scrollTo( {
                    left: clampedPosition,
                    behavior: 'smooth'
                } );
                currentPosition = clampedPosition;
            };
            
            /**
             * Update button states (enable/disable)
             *
             * @param {number} position - Current scroll position
             */
            const updateButtonStates = ( position ) => {
                if ( prevButton ) {
                    const isAtStart = position <= 0;
                    prevButton.disabled = isAtStart;
                    prevButton.classList.toggle( 'is-disabled', isAtStart );
                }
                
                if ( nextButton ) {
                    const isAtEnd = position >= maxPosition - 1;
                    nextButton.disabled = isAtEnd;
                    nextButton.classList.toggle( 'is-disabled', isAtEnd );
                }
            };
            
            // Previous button click handler
            if ( prevButton ) {
                prevButton.addEventListener( 'click', () => {
                    const newPosition = currentPosition - scrollAmount;
                    updateScrollPosition( newPosition );
                    updateButtonStates( newPosition );
                } );
            }
            
            // Next button click handler
            if ( nextButton ) {
                nextButton.addEventListener( 'click', () => {
                    const newPosition = currentPosition + scrollAmount;
                    updateScrollPosition( newPosition );
                    updateButtonStates( newPosition );
                } );
            }
            
            // Initialize button states
            updateButtonStates( currentPosition );
            
            // Update button states on scroll
            sliderContainer.addEventListener( 'scroll', () => {
                const scrollLeft = sliderContainer.scrollLeft;
                updateButtonStates( scrollLeft );
            } );
            
            // Handle window resize
            let resizeTimeout;
            window.addEventListener( 'resize', () => {
                clearTimeout( resizeTimeout );
                resizeTimeout = setTimeout( () => {
                    // Recalculate and update
                    const newContainerWidth = sliderContainer.offsetWidth;
                    const newVisibleItems = Math.floor( newContainerWidth / ( itemWidth + gap ) );
                    const newScrollAmount = Math.max( 1, newVisibleItems - 1 ) * ( itemWidth + gap );
                    const newTotalWidth = ( sliderItems.length * ( itemWidth + gap ) ) - gap;
                    const newMaxPosition = Math.max( 0, newTotalWidth - newContainerWidth );
                    
                    // Recalculate scroll amount and max position
                    // Note: In production, you'd want to recalculate fully
                    updateButtonStates( sliderContainer.scrollLeft );
                }, 150 );
            } );
        }
    };

    // Initialize when DOM is ready
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', () => {
            PearlWeatherFrontend.init();
        } );
    } else {
        PearlWeatherFrontend.init();
    }

    // Export for potential external use
    window.PearlWeatherFrontend = PearlWeatherFrontend;

} )( jQuery );