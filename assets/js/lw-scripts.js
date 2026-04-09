/**
 * Pearl Weather - Frontend JavaScript
 *
 * @package    PearlWeather
 * @subpackage Frontend
 * @since      1.0.0
 * @version    1.0.0
 */

(function($) {
    'use strict';

    /**
     * Pearl Weather Frontend Handler
     */
    const PearlWeatherFrontend = {

        /**
         * Initialize all weather widgets.
         */
        init: function() {
            $('.pearl-weather-main-wrapper').each(function() {
                const $wrapper = $(this);
                const wrapperId = $wrapper.attr('id');
                const shortcodeId = $wrapper.data('shortcode-id');

                // Initialize all modules
                this.removePreloader($wrapper);
                this.handleAjaxReload($wrapper, shortcodeId);
                this.initForecastSelect($wrapper);
                this.initForecastTabs($wrapper);
                this.initLocationSearch($wrapper);
                this.initUnitToggle($wrapper);
            }.bind(this));
        },

        /**
         * Remove preloader with fade animation.
         *
         * @param {jQuery} $wrapper - Main wrapper element.
         */
        removePreloader: function($wrapper) {
            const $preloaderWrapper = $wrapper.find('.pearl-weather-wrapper.pearl-weather-preloader-wrapper');
            const $preloader = $wrapper.find('.pearl-weather-preloader');

            if ($preloaderWrapper.length && $preloader.length) {
                $preloader.animate({ opacity: 0 }, 600, function() {
                    $(this).remove();
                    $preloaderWrapper.removeClass('pearl-weather-preloader-wrapper');
                });
            }
        },

        /**
         * Handle AJAX reload when skip cache is enabled.
         *
         * @param {jQuery} $wrapper - Main wrapper element.
         * @param {string} shortcodeId - Shortcode ID.
         */
        handleAjaxReload: function($wrapper, shortcodeId) {
            // Check if AJAX already loaded and conditions are met
            if (
                $wrapper.hasClass('pearl-weather-ajax-loaded') ||
                typeof pearlWeatherAjax === 'undefined' ||
                !pearlWeatherAjax.skipCache ||
                !shortcodeId
            ) {
                return;
            }

            const requestData = {
                nonce: pearlWeatherAjax.nonce,
                action: 'pearl_weather_ajax_get_weather',
                shortcode_id: shortcodeId,
                skip_cache: true
            };

            $.ajax({
                url: pearlWeatherAjax.ajaxUrl,
                type: 'POST',
                data: requestData,
                dataType: 'json',
                beforeSend: function() {
                    this.showLoadingState($wrapper);
                }.bind(this),
                success: function(response) {
                    if (response && response.success && response.data) {
                        this.updateWeatherContent($wrapper, response.data);
                        $wrapper.addClass('pearl-weather-ajax-loaded');
                    } else {
                        this.handleAjaxError($wrapper, response);
                    }
                }.bind(this),
                error: function(xhr, status, error) {
                    this.handleAjaxError($wrapper, { message: error });
                }.bind(this)
            });
        },

        /**
         * Show loading state during AJAX request.
         *
         * @param {jQuery} $wrapper - Main wrapper element.
         */
        showLoadingState: function($wrapper) {
            const $loadingOverlay = $('<div class="pearl-weather-loading-overlay"><div class="pearl-weather-loading-spinner"></div></div>');
            $wrapper.css('position', 'relative').append($loadingOverlay);
        },

        /**
         * Hide loading state after AJAX request.
         *
         * @param {jQuery} $wrapper - Main wrapper element.
         */
        hideLoadingState: function($wrapper) {
            $wrapper.find('.pearl-weather-loading-overlay').remove();
            $wrapper.css('position', '');
        },

        /**
         * Update weather content with new data.
         *
         * @param {jQuery} $wrapper - Main wrapper element.
         * @param {object} data - New weather data.
         */
        updateWeatherContent: function($wrapper, data) {
            // Update temperature
            if (data.temperature) {
                $wrapper.find('.pearl-weather-temperature').text(data.temperature);
            }

            // Update condition
            if (data.condition) {
                $wrapper.find('.pearl-weather-condition').text(data.condition);
            }

            // Update location
            if (data.location) {
                $wrapper.find('.pearl-weather-location').text(data.location);
            }

            // Update date
            if (data.date) {
                $wrapper.find('.pearl-weather-date').text(data.date);
            }

            // Update weather icon
            if (data.icon_url) {
                $wrapper.find('.pearl-weather-icon img').attr('src', data.icon_url);
            }

            // Update additional details
            if (data.details) {
                $.each(data.details, function(key, value) {
                    $wrapper.find('.pearl-weather-detail-' + key).text(value);
                });
            }

            this.hideLoadingState($wrapper);
        },

        /**
         * Handle AJAX errors.
         *
         * @param {jQuery} $wrapper - Main wrapper element.
         * @param {object} response - Error response.
         */
        handleAjaxError: function($wrapper, response) {
            this.hideLoadingState($wrapper);
            
            const errorMessage = response.message || pearlWeatherAjax.strings.errorMessage;
            const $errorDiv = $('<div class="pearl-weather-error">' + errorMessage + '</div>');
            
            $wrapper.find('.pearl-weather-error').remove();
            $wrapper.append($errorDiv);
            
            setTimeout(function() {
                $errorDiv.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Initialize forecast select dropdown for changing weather metrics.
         *
         * @param {jQuery} $wrapper - Main wrapper element.
         */
        initForecastSelect: function($wrapper) {
            const $select = $wrapper.find('#pearl-weather-forecast-select');

            if (!$select.length) {
                return;
            }

            // Map of select values to CSS selectors
            const metricMap = {
                temperature: '.pearl-weather-metric-temperature',
                wind: '.pearl-weather-metric-wind',
                humidity: '.pearl-weather-metric-humidity',
                pressure: '.pearl-weather-metric-pressure',
                precipitation: '.pearl-weather-metric-precipitation',
                rain_chance: '.pearl-weather-metric-rain-chance',
                snow: '.pearl-weather-metric-snow'
            };

            const $forecastItems = $wrapper.find('.pearl-weather-advanced-forecast .pearl-weather-forecast-item');

            $select.on('change', function() {
                const selectedValue = $(this).val();
                const targetSelector = metricMap[selectedValue];

                if (targetSelector) {
                    $forecastItems.find(targetSelector)
                        .addClass('active')
                        .closest('.pearl-weather-forecast-item')
                        .find(targetSelector)
                        .siblings()
                        .removeClass('active');
                }
            });
        },

        /**
         * Initialize forecast tabs for switching between forecast periods.
         *
         * @param {jQuery} $wrapper - Main wrapper element.
         */
        initForecastTabs: function($wrapper) {
            $wrapper.find('.pearl-weather-forecast-tabs-container').each(function() {
                const $container = $(this);
                const $tabs = $container.find('[data-tab-target]');
                const $tabContents = $container.find('[data-tab-content]');

                if (!$tabs.length || !$tabContents.length) {
                    return;
                }

                // Remove any existing handlers and attach new ones
                $tabs.off('click.pearlWeather').on('click.pearlWeather', function(e) {
                    e.preventDefault();

                    const $clickedTab = $(this);
                    const targetSelector = $clickedTab.data('tab-target');

                    if (!targetSelector) {
                        return;
                    }

                    // Update active tab state
                    $tabs.removeClass('active');
                    $clickedTab.addClass('active');

                    // Update active content
                    $tabContents.removeClass('active');
                    $tabContents.filter(targetSelector).addClass('active');

                    // Trigger custom event for other components
                    $container.trigger('pearl-weather:tab-changed', [targetSelector, $clickedTab]);
                });

                // Activate first tab by default if none active
                if (!$tabs.filter('.active').length) {
                    $tabs.first().trigger('click.pearlWeather');
                }
            });
        },

        /**
         * Initialize location search functionality.
         *
         * @param {jQuery} $wrapper - Main wrapper element.
         */
        initLocationSearch: function($wrapper) {
            const $searchInput = $wrapper.find('.pearl-weather-location-search');
            const $searchButton = $wrapper.find('.pearl-weather-location-search-btn');

            if (!$searchInput.length) {
                return;
            }

            const searchHandler = function() {
                const location = $searchInput.val().trim();
                
                if (!location) {
                    return;
                }

                this.fetchWeatherByLocation($wrapper, location);
            }.bind(this);

            $searchButton.on('click', searchHandler);
            $searchInput.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    searchHandler();
                }
            });
        },

        /**
         * Fetch weather data by location.
         *
         * @param {jQuery} $wrapper - Main wrapper element.
         * @param {string} location - Location name.
         */
        fetchWeatherByLocation: function($wrapper, location) {
            const requestData = {
                nonce: pearlWeatherAjax.nonce,
                action: 'pearl_weather_ajax_get_weather',
                location: location
            };

            $.ajax({
                url: pearlWeatherAjax.ajaxUrl,
                type: 'POST',
                data: requestData,
                dataType: 'json',
                beforeSend: function() {
                    this.showLoadingState($wrapper);
                }.bind(this),
                success: function(response) {
                    if (response && response.success && response.data) {
                        this.updateWeatherContent($wrapper, response.data);
                    } else {
                        this.handleAjaxError($wrapper, response);
                    }
                }.bind(this),
                error: function(xhr, status, error) {
                    this.handleAjaxError($wrapper, { message: error });
                }.bind(this)
            });
        },

        /**
         * Initialize unit toggle (Celsius/Fahrenheit).
         *
         * @param {jQuery} $wrapper - Main wrapper element.
         */
        initUnitToggle: function($wrapper) {
            const $unitToggle = $wrapper.find('.pearl-weather-unit-toggle');

            if (!$unitToggle.length) {
                return;
            }

            $unitToggle.on('change', function() {
                const selectedUnit = $(this).val();
                
                $.ajax({
                    url: pearlWeatherAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        nonce: pearlWeatherAjax.nonce,
                        action: 'pearl_weather_ajax_change_unit',
                        unit: selectedUnit
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        this.showLoadingState($wrapper);
                    }.bind(this),
                    success: function(response) {
                        if (response && response.success && response.data) {
                            this.updateWeatherContent($wrapper, response.data);
                        }
                    }.bind(this),
                    error: function(xhr, status, error) {
                        this.handleAjaxError($wrapper, { message: error });
                    }.bind(this)
                });
            }.bind(this));
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        PearlWeatherFrontend.init();
    });

    // Re-initialize after AJAX content loads (for widgets/shortcodes loaded dynamically)
    $(document).on('pearl-weather:refresh', function() {
        PearlWeatherFrontend.init();
    });

    // Also listen for WordPress heartbeats if needed
    $(document).on('heartbeat-tick', function() {
        $('.pearl-weather-main-wrapper.pearl-weather-auto-refresh').each(function() {
            const $wrapper = $(this);
            const shortcodeId = $wrapper.data('shortcode-id');
            if (shortcodeId) {
                PearlWeatherFrontend.handleAjaxReload($wrapper, shortcodeId);
            }
        });
    });

})(jQuery);