/**
 * Pearl Weather - Frontend JavaScript
 * Handles extended forecast toggle, responsive behaviors, and interactive elements
 * Version: 1.0.0
 * Author: Aneeq Ahmed
 */

(function($, window, document, undefined) {
    'use strict';

    /**
     * Pearl Weather Namespace
     */
    var PW = PW || {};

    /**
     * Plugin Variables
     */
    PW.vars = {
        $window: $(window),
        $document: $(document),
        isExtendedVisible: false,
        animationDuration: 300,
        breakpoints: {
            mobile: 768,
            tablet: 1024,
            desktop: 1200
        }
    };

    /**
     * Helper Functions
     */
    PW.helper = {
        /**
         * Debounce function for performance optimization
         */
        debounce: function(callback, threshold, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) callback.apply(context, args);
                };
                var callNow = (immediate && !timeout);
                clearTimeout(timeout);
                timeout = setTimeout(later, threshold);
                if (callNow) callback.apply(context, args);
            };
        },

        /**
         * Get element visibility status
         */
        isElementInViewport: function(el) {
            var rect = el.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || PW.vars.$window.height()) &&
                rect.right <= (window.innerWidth || PW.vars.$window.width())
            );
        },

        /**
         * Format date for display
         */
        formatDate: function(dateString, format) {
            var date = new Date(dateString);
            var options = { weekday: 'short', month: 'short', day: 'numeric' };
            if (format === 'day') {
                options = { weekday: 'long' };
            } else if (format === 'date') {
                options = { month: 'short', day: 'numeric' };
            }
            return date.toLocaleDateString(undefined, options);
        },

        /**
         * Get weather icon URL from OpenWeatherMap
         */
        getWeatherIconUrl: function(iconCode, size) {
            size = size || '2x';
            var sizeMap = {
                '1x': '',
                '2x': '@2x',
                '4x': '@4x'
            };
            var sizeSuffix = sizeMap[size] || '@2x';
            return 'https://openweathermap.org/img/wn/' + iconCode + sizeSuffix + '.png';
        },

        /**
         * Convert temperature unit display
         */
        getTempUnit: function(unit) {
            if (unit === 'metric') return '°C';
            if (unit === 'imperial') return '°F';
            return 'K';
        }
    };

    /**
     * Extended Forecast Toggle Handler
     */
    PW.extendedForecast = {
        /**
         * Initialize extended forecast functionality
         */
        init: function() {
            var $toggleBtn = $('.pw-toggle-extended');
            var $extendedSection = $('.pw-extended-forecast');
            
            if (!$toggleBtn.length || !$extendedSection.length) {
                return;
            }

            // Initially hide extended forecast
            $extendedSection.hide();
            
            // Toggle button click handler
            $toggleBtn.on('click', function(e) {
                e.preventDefault();
                PW.extendedForecast.toggle($(this), $extendedSection);
            });
            
            // Store references
            PW.vars.$toggleBtn = $toggleBtn;
            PW.vars.$extendedSection = $extendedSection;
        },

        /**
         * Toggle extended forecast visibility
         */
        toggle: function($btn, $section) {
            if (PW.vars.isExtendedVisible) {
                this.hide($section);
                $btn.html('<i class="dashicons dashicons-arrow-down-alt2"></i> Show Extended Forecast');
            } else {
                this.show($section);
                $btn.html('<i class="dashicons dashicons-arrow-up-alt2"></i> Hide Extended Forecast');
            }
            PW.vars.isExtendedVisible = !PW.vars.isExtendedVisible;
        },

        /**
         * Show extended forecast with animation
         */
        show: function($section) {
            $section.slideDown(PW.vars.animationDuration, function() {
                $section.css('display', 'grid');
            });
        },

        /**
         * Hide extended forecast with animation
         */
        hide: function($section) {
            $section.slideUp(PW.vars.animationDuration);
        }
    };

    /**
     * Responsive Card Adjustments
     */
    PW.responsiveCards = {
        /**
         * Initialize responsive card behavior
         */
        init: function() {
            this.adjustCards();
            PW.vars.$window.on('resize', PW.helper.debounce(function() {
                PW.responsiveCards.adjustCards();
            }, 250));
        },

        /**
         * Adjust card layout based on screen size
         */
        adjustCards: function() {
            var windowWidth = PW.vars.$window.width();
            var $forecastGrid = $('.pw-forecast-grid');
            var $extendedGrid = $('.pw-extended-grid');
            
            if (windowWidth <= PW.vars.breakpoints.mobile) {
                $forecastGrid.css('grid-template-columns', 'repeat(auto-fit, minmax(120px, 1fr))');
                $extendedGrid.css('grid-template-columns', 'repeat(auto-fit, minmax(120px, 1fr))');
            } else if (windowWidth <= PW.vars.breakpoints.tablet) {
                $forecastGrid.css('grid-template-columns', 'repeat(auto-fit, minmax(140px, 1fr))');
                $extendedGrid.css('grid-template-columns', 'repeat(auto-fit, minmax(140px, 1fr))');
            } else {
                $forecastGrid.css('grid-template-columns', 'repeat(auto-fit, minmax(160px, 1fr))');
                $extendedGrid.css('grid-template-columns', 'repeat(auto-fit, minmax(160px, 1fr))');
            }
        }
    };

    /**
     * Hover Effects Enhancement
     */
    PW.hoverEffects = {
        /**
         * Initialize hover effects
         */
        init: function() {
            this.enhanceCardHover();
            this.addLoadingEffect();
        },

        /**
         * Enhance card hover with smooth transitions
         */
        enhanceCardHover: function() {
            $('.pw-forecast-card, .pw-extended-card').on({
                mouseenter: function() {
                    $(this).css({
                        'transition': 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
                    });
                },
                mouseleave: function() {
                    $(this).css({
                        'transform': 'translateY(0)'
                    });
                }
            });
        },

        /**
         * Add loading effect for async operations
         */
        addLoadingEffect: function() {
            $(document).on('pw-weather-loading', function() {
                $('.pearl-weather-container').css('opacity', '0.6');
            });
            
            $(document).on('pw-weather-loaded', function() {
                $('.pearl-weather-container').css('opacity', '1');
            });
        }
    };

    /**
     * Temperature Unit Toggle (if multiple units supported)
     */
    PW.unitToggle = {
        /**
         * Initialize unit toggle if available
         */
        init: function() {
            var $unitToggle = $('.pw-unit-toggle');
            
            if (!$unitToggle.length) {
                return;
            }
            
            $unitToggle.on('click', function(e) {
                e.preventDefault();
                var currentUnit = $(this).data('unit');
                var newUnit = (currentUnit === 'metric') ? 'imperial' : 'metric';
                
                $(this).data('unit', newUnit);
                $(this).find('.current-unit').text(newUnit === 'metric' ? '°C' : '°F');
                
                // Trigger event for temperature conversion
                $(document).trigger('pw-unit-change', [newUnit]);
            });
        }
    };

    /**
     * Accessibility Enhancements
     */
    PW.accessibility = {
        /**
         * Initialize accessibility features
         */
        init: function() {
            this.addAriaLabels();
            this.enableKeyboardNavigation();
        },

        /**
         * Add ARIA labels for screen readers
         */
        addAriaLabels: function() {
            $('.pw-current-card').attr('aria-label', 'Current weather conditions');
            $('.pw-forecast-card').each(function(index) {
                $(this).attr('aria-label', 'Forecast for day ' + (index + 1));
            });
            $('.pw-toggle-extended').attr({
                'aria-expanded': 'false',
                'role': 'button',
                'tabindex': '0'
            });
        },

        /**
         * Enable keyboard navigation
         */
        enableKeyboardNavigation: function() {
            $(document).on('keydown', '.pw-toggle-extended', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).trigger('click');
                }
            });
        }
    };

    /**
     * Error Handling and Retry Logic
     */
    PW.errorHandler = {
        /**
         * Initialize error handling
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind error handling events
         */
        bindEvents: function() {
            $(document).on('pw-api-error', function(e, error) {
                PW.errorHandler.displayError(error);
            });
            
            $('.pw-retry-btn').on('click', function() {
                PW.errorHandler.retryLoad();
            });
        },

        /**
         * Display user-friendly error message
         */
        displayError: function(error) {
            var $container = $('.pearl-weather-container');
            var errorHtml = '<div class="pw-error-message" style="text-align:center;padding:40px;background:#f8d7da;color:#721c24;border-radius:12px;margin:20px;">' +
                '<i class="dashicons dashicons-warning" style="font-size:48px;margin-bottom:15px;display:block;"></i>' +
                '<strong>Unable to load weather data</strong><br>' +
                '<span style="font-size:14px;">' + (error.message || 'Please check your API key or try again later.') + '</span>' +
                '<br><br><button class="pw-retry-btn button">Retry</button>' +
                '</div>';
            
            if ($container.find('.pw-current-card').length) {
                $container.find('.pw-current-card').after(errorHtml);
            } else {
                $container.html(errorHtml);
            }
        },

        /**
         * Retry loading weather data
         */
        retryLoad: function() {
            $('.pw-error-message').remove();
            $(document).trigger('pw-weather-retry');
            // Reload the page or re-fetch data via AJAX
            location.reload();
        }
    };

    /**
     * Initialize all modules when document is ready
     */
    $(document).ready(function() {
        // Initialize all components
        PW.extendedForecast.init();
        PW.responsiveCards.init();
        PW.hoverEffects.init();
        PW.unitToggle.init();
        PW.accessibility.init();
        PW.errorHandler.init();

        // Add animation class to container
        $('.pearl-weather-container').addClass('pw-initialized');

        // Trigger custom event for any external integrations
        $(document).trigger('pw-initialized');

        // Log initialization (for debugging - removed in production)
        if (window.console && window.location.hostname === 'localhost') {
            console.log('Pearl Weather: Plugin initialized successfully');
        }
    });

    /**
     * Handle AJAX completion for dynamic content
     */
    $(document).ajaxComplete(function(event, xhr, settings) {
        if (settings.url && settings.url.indexOf('pearl_weather') !== -1) {
            // Reinitialize components after AJAX load
            setTimeout(function() {
                PW.extendedForecast.init();
                PW.responsiveCards.init();
                PW.hoverEffects.init();
                PW.accessibility.init();
            }, 100);
        }
    });

    /**
     * Export PW namespace for external use
     */
    window.PearlWeather = PW;

})(jQuery, window, document);