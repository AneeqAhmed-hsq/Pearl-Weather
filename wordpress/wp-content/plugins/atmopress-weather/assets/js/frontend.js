/**
 * AtmoPress Weather – Frontend JavaScript
 * Handles widget hydration, search, geolocation, and unit toggling.
 */

(function () {
  'use strict';

  var REST_URL  = (typeof AtmoPressData !== 'undefined') ? AtmoPressData.restUrl : '';
  var NONCE     = (typeof AtmoPressData !== 'undefined') ? AtmoPressData.nonce  : '';
  var i18n      = (typeof AtmoPressData !== 'undefined') ? AtmoPressData.i18n   : {};

  /* ------------------------------------------------------------------
   * Utility: AJAX via REST API
   * ------------------------------------------------------------------ */
  function fetchWeather(location, config, callback) {
    if (!REST_URL) {
      callback(null, i18n.error || 'Error');
      return;
    }

    var params = new URLSearchParams({
      location: location,
      template: config.template || 'card',
      units:    config.units    || 'metric',
      config:   JSON.stringify(config),
    });

    var xhr = new XMLHttpRequest();
    xhr.open('GET', REST_URL + 'render?' + params.toString(), true);
    xhr.setRequestHeader('X-WP-Nonce', NONCE);
    xhr.onload = function () {
      if (xhr.status === 200) {
        try {
          var data = JSON.parse(xhr.responseText);
          callback(data, null);
        } catch (e) {
          callback(null, i18n.error || 'Parse error');
        }
      } else {
        callback(null, i18n.error || ('HTTP ' + xhr.status));
      }
    };
    xhr.onerror = function () {
      callback(null, i18n.error || 'Network error');
    };
    xhr.send();
  }

  /* ------------------------------------------------------------------
   * Show loading state inside widget
   * ------------------------------------------------------------------ */
  function showLoading(el, msg) {
    el.innerHTML = '<div class="atmopress-loading">' +
      '<span class="atmopress-spinner"></span>' +
      '<span>' + (msg || i18n.loading || 'Loading…') + '</span>' +
      '</div>';
  }

  function showError(el, msg) {
    el.innerHTML = '<div class="atmopress-error">' + (msg || i18n.error || 'Error') + '</div>';
  }

  /* ------------------------------------------------------------------
   * Hydrate a single widget
   * ------------------------------------------------------------------ */
  function hydrateWidget(el) {
    var rawConfig = el.getAttribute('data-config');
    if (!rawConfig) return;

    var config;
    try {
      config = JSON.parse(rawConfig);
    } catch (e) {
      showError(el, 'Invalid config');
      return;
    }

    var location = config.location || 'London';
    showLoading(el);

    fetchWeather(location, config, function (data, err) {
      if (err || !data) {
        showError(el, err);
        return;
      }

      el.innerHTML = data.html;

      // Re-attach interactive handlers after new HTML
      attachHandlers(el, config);
    });
  }

  /* ------------------------------------------------------------------
   * Attach event handlers to a hydrated widget
   * ------------------------------------------------------------------ */
  function attachHandlers(el, config) {
    // Search form
    var searchForm = el.querySelector('.ap-search-form');
    if (searchForm) {
      searchForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var input = searchForm.querySelector('.ap-search-input');
        if (input && input.value.trim()) {
          var newConfig = mergeConfig(config, { location: input.value.trim() });
          updateWidget(el, newConfig);
        }
      });
    }

    // Geolocation button
    var geoBtn = el.querySelector('.ap-geo-btn');
    if (geoBtn) {
      geoBtn.addEventListener('click', function () {
        if (!navigator.geolocation) {
          showError(el, 'Geolocation not supported');
          return;
        }
        showLoading(el, i18n.detecting || 'Detecting…');
        navigator.geolocation.getCurrentPosition(
          function (pos) {
            var loc = pos.coords.latitude + ',' + pos.coords.longitude;
            var newConfig = mergeConfig(config, { location: loc });
            updateWidget(el, newConfig);
          },
          function () {
            hydrateWidget(el);
          }
        );
      });
    }

    // Unit toggle
    var unitBtn = el.querySelector('.ap-unit-toggle');
    if (unitBtn) {
      unitBtn.addEventListener('click', function () {
        var currentUnit = config.units || 'metric';
        var newUnit     = currentUnit === 'metric' ? 'imperial' : 'metric';
        var newConfig   = mergeConfig(config, { units: newUnit });
        updateWidget(el, newConfig);
      });
    }
  }

  function mergeConfig(base, overrides) {
    var c = {};
    var keys = Object.keys(base);
    for (var i = 0; i < keys.length; i++) {
      c[keys[i]] = base[keys[i]];
    }
    var okeys = Object.keys(overrides);
    for (var j = 0; j < okeys.length; j++) {
      c[okeys[j]] = overrides[okeys[j]];
    }
    return c;
  }

  function updateWidget(el, newConfig) {
    el.setAttribute('data-config', JSON.stringify(newConfig));
    showLoading(el);
    fetchWeather(newConfig.location, newConfig, function (data, err) {
      if (err || !data) {
        showError(el, err);
        return;
      }
      el.innerHTML = data.html;
      attachHandlers(el, newConfig);
    });
  }

  /* ------------------------------------------------------------------
   * Init: find all .atmopress-widget containers and hydrate them
   * ------------------------------------------------------------------ */
  function init() {
    var widgets = document.querySelectorAll('.atmopress-widget[data-config]');
    for (var i = 0; i < widgets.length; i++) {
      hydrateWidget(widgets[i]);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
