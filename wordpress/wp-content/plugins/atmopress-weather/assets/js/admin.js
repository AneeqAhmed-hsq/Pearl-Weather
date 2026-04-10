/**
 * AtmoPress Weather – Admin JavaScript
 * Handles API key testing, cache flushing, and shortcode generator.
 */

(function ($) {
  'use strict';

  var REST_URL = (typeof AtmoPressAdmin !== 'undefined') ? AtmoPressAdmin.restUrl : '';
  var NONCE    = (typeof AtmoPressAdmin !== 'undefined') ? AtmoPressAdmin.nonce  : '';
  var i18n     = (typeof AtmoPressAdmin !== 'undefined') ? AtmoPressAdmin.i18n   : {};

  function restPost(endpoint, data, cb) {
    $.ajax({
      url:         REST_URL + endpoint,
      method:      'POST',
      contentType: 'application/json',
      data:        JSON.stringify(data),
      beforeSend:  function (xhr) { xhr.setRequestHeader('X-WP-Nonce', NONCE); },
      success:     function (res) { cb(res, null); },
      error:       function (xhr) {
        var msg = 'Request failed';
        try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
        cb(null, msg);
      },
    });
  }

  function restGet(endpoint, params, cb) {
    $.ajax({
      url:        REST_URL + endpoint,
      method:     'GET',
      data:       params,
      beforeSend: function (xhr) { xhr.setRequestHeader('X-WP-Nonce', NONCE); },
      success:    function (res) { cb(res, null); },
      error:      function (xhr) {
        var msg = 'Request failed';
        try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
        cb(null, msg);
      },
    });
  }

  /* ------------------------------------------------------------------
   * Test API Key
   * ------------------------------------------------------------------ */
  $(document).on('click', '#atmopress-test-key', function () {
    var $btn    = $(this);
    var apiKey  = $('#api_key').val().trim();
    var provider = $('#api_provider').val();
    var $result = $('#atmopress-test-result');

    if (!apiKey) {
      $result.html('<span style="color:#dc2626;">Please enter an API key first.</span>');
      return;
    }

    $btn.prop('disabled', true).text('Testing…');
    $result.html('<span style="color:#64748b;">Connecting to API…</span>');

    restGet('test-api', { api_key: apiKey, provider: provider }, function (res, err) {
      $btn.prop('disabled', false).text('Test Key');
      if (err || !res) {
        $result.html('<span style="color:#dc2626;">✗ ' + (i18n.testFail || 'Test failed.') + '</span>');
        return;
      }
      if (res.ok) {
        $result.html('<span style="color:#16a34a;">✓ ' + (i18n.testSuccess || 'API key valid!') + ' (' + (res.city || '') + ')</span>');
      } else {
        $result.html('<span style="color:#dc2626;">✗ ' + (res.message || i18n.testFail) + '</span>');
      }
    });
  });

  /* ------------------------------------------------------------------
   * Flush Cache
   * ------------------------------------------------------------------ */
  $(document).on('click', '#atmopress-flush-cache', function () {
    var $btn    = $(this);
    var $result = $('#atmopress-flush-result');

    $btn.prop('disabled', true).text('Flushing…');

    restPost('flush-cache', {}, function (res, err) {
      $btn.prop('disabled', false).text('Flush Weather Cache');
      if (res && res.flushed) {
        $result.html('<span style="color:#16a34a;">✓ Cache flushed successfully.</span>');
      } else {
        $result.html('<span style="color:#dc2626;">✗ ' + (err || 'Failed to flush.') + '</span>');
      }
    });
  });

  /* ------------------------------------------------------------------
   * Shortcode Generator
   * ------------------------------------------------------------------ */
  function generateShortcode() {
    var template = $('#sc-template').val() || 'card';
    var location = $('#sc-location').val() || 'London';
    var units    = $('#sc-units').val()    || 'metric';
    var color    = $('#sc-color').val()    || '#2563eb';
    var days     = $('#sc-days').val()     || '7';
    var search   = $('#sc-search').is(':checked') ? 'true' : 'false';
    var geo      = $('#sc-geo').is(':checked')    ? 'true' : 'false';
    var humidity = $('#sc-humidity').is(':checked') ? 'true' : 'false';
    var wind     = $('#sc-wind').is(':checked')     ? 'true' : 'false';
    var pressure = $('#sc-pressure').is(':checked') ? 'true' : 'false';
    var hourly   = $('#sc-hourly').is(':checked')   ? 'true' : 'false';
    var daily    = $('#sc-daily').is(':checked')    ? 'true' : 'false';

    var sc = '[atmopress';
    sc += ' template="' + template + '"';
    sc += ' location="' + location + '"';
    sc += ' units="' + units + '"';
    sc += ' color_primary="' + color + '"';
    sc += ' forecast_days="' + days + '"';
    if (search   === 'false')  sc += ' show_search="false"';
    if (geo      === 'false')  sc += ' show_geolocation="false"';
    if (humidity === 'false')  sc += ' show_humidity="false"';
    if (wind     === 'false')  sc += ' show_wind="false"';
    if (pressure === 'false')  sc += ' show_pressure="false"';
    if (hourly   === 'false')  sc += ' show_hourly="false"';
    if (daily    === 'false')  sc += ' show_daily="false"';
    sc += ']';

    $('#sc-output').text(sc);
  }

  $('#sc-template, #sc-location, #sc-units, #sc-color, #sc-days').on('input change', generateShortcode);
  $('#sc-search, #sc-geo, #sc-humidity, #sc-wind, #sc-pressure, #sc-hourly, #sc-daily').on('change', generateShortcode);

  $(document).on('click', '#sc-copy', function () {
    var text = $('#sc-output').text();
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text).then(function () {
        var $btn = $('#sc-copy');
        $btn.text('Copied!');
        setTimeout(function () { $btn.text('Copy'); }, 2000);
      });
    }
  });

  // Init
  generateShortcode();

}(jQuery));
