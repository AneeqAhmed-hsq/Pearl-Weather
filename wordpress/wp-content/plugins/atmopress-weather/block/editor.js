/**
 * AtmoPress Weather – Gutenberg Block Editor (No-build, createElement API)
 * Uses WordPress global wp.* objects — no transpilation required.
 */

(function () {
  'use strict';

  var el          = wp.element.createElement;
  var __          = wp.i18n.__;
  var registerBlockType = wp.blocks.registerBlockType;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var useBlockProps     = wp.blockEditor.useBlockProps;
  var PanelBody     = wp.components.PanelBody;
  var PanelRow      = wp.components.PanelRow;
  var TextControl   = wp.components.TextControl;
  var SelectControl = wp.components.SelectControl;
  var RangeControl  = wp.components.RangeControl;
  var ToggleControl = wp.components.ToggleControl;
  var ColorPicker   = wp.components.ColorPicker;
  var Button        = wp.components.Button;
  var Spinner       = wp.components.Spinner;
  var Notice        = wp.components.Notice;

  var blockData   = (typeof AtmoPressBlock !== 'undefined') ? AtmoPressBlock : {};
  var templates   = blockData.templates   || {};
  var defaults    = blockData.defaults    || {};
  var REST_URL    = blockData.restUrl     || '';
  var NONCE       = blockData.nonce       || '';

  var templateOptions = Object.keys(templates).map(function (key) {
    return { label: templates[key], value: key };
  });

  /* ------------------------------------------------------------------
   * Live Preview component
   * ------------------------------------------------------------------ */
  function LivePreview(props) {
    var attrs = props.attributes;
    var ref   = wp.element.useRef(null);
    var state = wp.element.useState({ html: '', loading: true, error: '' });
    var html  = state[0].html;
    var loading = state[0].loading;
    var error = state[0].error;
    var setState = state[1];

    wp.element.useEffect(function () {
      setState({ html: '', loading: true, error: '' });

      var params = new URLSearchParams({
        location: attrs.location || defaults.location,
        template: attrs.template || 'card',
        units:    attrs.units    || 'metric',
        config:   JSON.stringify(attrs),
      });

      var controller = new AbortController();
      var timer = setTimeout(function () {
        fetch(REST_URL + 'render?' + params.toString(), {
          headers: { 'X-WP-Nonce': NONCE },
          signal:  controller.signal,
        })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            setState({ html: data.html || '', loading: false, error: '' });
          })
          .catch(function (err) {
            if (err.name !== 'AbortError') {
              setState({ html: '', loading: false, error: 'Preview error' });
            }
          });
      }, 600);

      return function () {
        clearTimeout(timer);
        controller.abort();
      };
    }, [
      attrs.location, attrs.template, attrs.units,
      attrs.show_humidity, attrs.show_wind, attrs.show_pressure,
      attrs.show_daily, attrs.show_hourly, attrs.forecast_days,
      attrs.color_primary, attrs.color_bg, attrs.border_radius,
    ]);

    if (loading) {
      return el('div', { style: { padding: '20px', display: 'flex', alignItems: 'center', gap: '10px' } },
        el(Spinner),
        'Loading preview…'
      );
    }

    if (error) {
      return el('div', { style: { padding: '16px', color: '#dc2626' } }, error);
    }

    return el('div', {
      ref: ref,
      dangerouslySetInnerHTML: { __html: html },
    });
  }

  /* ------------------------------------------------------------------
   * Edit component
   * ------------------------------------------------------------------ */
  function Edit(props) {
    var attrs     = props.attributes;
    var setAttrs  = props.setAttributes;
    var blockProps = useBlockProps();

    return el('div', blockProps,
      el(InspectorControls, {},
        // Location & Template
        el(PanelBody, { title: __('Widget Settings', 'atmopress-weather'), initialOpen: true },
          el(TextControl, {
            label: __('Location', 'atmopress-weather'),
            value: attrs.location,
            onChange: function (v) { setAttrs({ location: v }); },
            help: __('City name (e.g. "London") or "lat,lon".', 'atmopress-weather'),
          }),
          el(SelectControl, {
            label: __('Template', 'atmopress-weather'),
            value: attrs.template,
            options: templateOptions,
            onChange: function (v) { setAttrs({ template: v }); },
          }),
          el(SelectControl, {
            label: __('Units', 'atmopress-weather'),
            value: attrs.units,
            options: [
              { label: 'Metric (°C)',   value: 'metric' },
              { label: 'Imperial (°F)', value: 'imperial' },
            ],
            onChange: function (v) { setAttrs({ units: v }); },
          }),
          el(RangeControl, {
            label: __('Forecast Days', 'atmopress-weather'),
            value: attrs.forecast_days,
            min: 1, max: 7,
            onChange: function (v) { setAttrs({ forecast_days: v }); },
          }),
          el(RangeControl, {
            label: __('Hourly Slots', 'atmopress-weather'),
            value: attrs.hourly_count,
            min: 1, max: 24,
            onChange: function (v) { setAttrs({ hourly_count: v }); },
          })
        ),

        // Show/Hide toggles
        el(PanelBody, { title: __('Show / Hide Elements', 'atmopress-weather'), initialOpen: false },
          el(ToggleControl, { label: __('Search Bar', 'atmopress-weather'),       checked: attrs.show_search,      onChange: function (v) { setAttrs({ show_search: v }); } }),
          el(ToggleControl, { label: __('Geolocation', 'atmopress-weather'),      checked: attrs.show_geolocation, onChange: function (v) { setAttrs({ show_geolocation: v }); } }),
          el(ToggleControl, { label: __('Humidity',    'atmopress-weather'),       checked: attrs.show_humidity,    onChange: function (v) { setAttrs({ show_humidity: v }); } }),
          el(ToggleControl, { label: __('Wind',        'atmopress-weather'),       checked: attrs.show_wind,        onChange: function (v) { setAttrs({ show_wind: v }); } }),
          el(ToggleControl, { label: __('Pressure',    'atmopress-weather'),       checked: attrs.show_pressure,    onChange: function (v) { setAttrs({ show_pressure: v }); } }),
          el(ToggleControl, { label: __('Visibility',  'atmopress-weather'),       checked: attrs.show_visibility,  onChange: function (v) { setAttrs({ show_visibility: v }); } }),
          el(ToggleControl, { label: __('Feels Like',  'atmopress-weather'),       checked: attrs.show_feels_like,  onChange: function (v) { setAttrs({ show_feels_like: v }); } }),
          el(ToggleControl, { label: __('Sunrise/Sunset', 'atmopress-weather'),   checked: attrs.show_sunrise,     onChange: function (v) { setAttrs({ show_sunrise: v }); } }),
          el(ToggleControl, { label: __('Hourly Forecast', 'atmopress-weather'),  checked: attrs.show_hourly,      onChange: function (v) { setAttrs({ show_hourly: v }); } }),
          el(ToggleControl, { label: __('Daily Forecast',  'atmopress-weather'),  checked: attrs.show_daily,       onChange: function (v) { setAttrs({ show_daily: v }); } })
        ),

        // Styling
        el(PanelBody, { title: __('Styling', 'atmopress-weather'), initialOpen: false },
          el('div', { style: { marginBottom: 12 } },
            el('label', { style: { display: 'block', fontWeight: 600, marginBottom: 8 } }, __('Primary Color', 'atmopress-weather')),
            el(ColorPicker, {
              color: attrs.color_primary,
              onChange: function (v) { setAttrs({ color_primary: v.hex || v }); },
              enableAlpha: false,
            })
          ),
          el('div', { style: { marginBottom: 12 } },
            el('label', { style: { display: 'block', fontWeight: 600, marginBottom: 8 } }, __('Background Color', 'atmopress-weather')),
            el(ColorPicker, {
              color: attrs.color_bg,
              onChange: function (v) { setAttrs({ color_bg: v.hex || v }); },
              enableAlpha: false,
            })
          ),
          el(RangeControl, {
            label: __('Border Radius (px)', 'atmopress-weather'),
            value: attrs.border_radius,
            min: 0, max: 32,
            onChange: function (v) { setAttrs({ border_radius: v }); },
          }),
          el(RangeControl, {
            label: __('Font Size (px)', 'atmopress-weather'),
            value: attrs.font_size,
            min: 10, max: 22,
            onChange: function (v) { setAttrs({ font_size: v }); },
          }),
          el(TextControl, {
            label: __('Custom CSS Class', 'atmopress-weather'),
            value: attrs.custom_class,
            onChange: function (v) { setAttrs({ custom_class: v }); },
          })
        )
      ),

      // Editor preview
      el('div', { className: 'ap-editor-preview' },
        el(LivePreview, { attributes: attrs })
      )
    );
  }

  /* ------------------------------------------------------------------
   * Register Block
   * ------------------------------------------------------------------ */
  registerBlockType('atmopress/weather-widget', {
    title:       __('AtmoPress Weather', 'atmopress-weather'),
    description: __('Display live weather data with multiple templates.', 'atmopress-weather'),
    category:    'widgets',
    icon:        'cloud',
    keywords:    [ __('weather'), __('forecast'), __('atmopress') ],
    supports:    { html: false, align: [ 'wide', 'full' ] },
    attributes:  defaults ? Object.keys(defaults).reduce(function (acc, key) {
      var val = defaults[key];
      var type = typeof val;
      if (type === 'boolean') acc[key] = { type: 'boolean', default: val };
      else if (type === 'number') acc[key] = { type: 'integer', default: val };
      else acc[key] = { type: 'string', default: val };
      return acc;
    }, {}) : {},

    edit: Edit,
    save: function () { return null; }, // Server-side rendered
  });

})();
