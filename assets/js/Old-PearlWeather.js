/**
 * Pearl Weather - API Client
 * 
 * Handles weather data fetching from OpenWeatherMap API
 *
 * @package    PearlWeather
 * @subpackage Frontend
 * @since      1.0.0
 */

class PearlWeatherAPIClient {
    
    /**
     * Constructor
     * 
     * @param {Object} config - Configuration options
     */
    constructor(config) {
        this.config = {
            apiKey: config.apiKey || null,
            units: config.units || 'metric', // metric, imperial
            lang: config.lang || 'en',
            city: config.city || null,
            lat: config.lat || null,
            lng: config.lng || null,
            customIcons: config.customIcons || null,
            onSuccess: config.onSuccess || function() {},
            onError: config.onError || function() {}
        };
        
        this.baseURL = 'https://api.openweathermap.org/data/2.5/weather';
        this.weatherData = null;
    }
    
    /**
     * Build API URL with parameters
     * 
     * @returns {string} Complete API URL
     */
    buildAPIUrl() {
        let url = `${this.baseURL}?lang=${this.config.lang}`;
        
        // Add location parameter (city or coordinates)
        if (this.config.city) {
            url += `&q=${encodeURIComponent(this.config.city)}`;
        } else if (this.config.lat && this.config.lng) {
            url += `&lat=${this.config.lat}&lon=${this.config.lng}`;
        } else {
            throw new Error('Either city or lat/lng must be provided');
        }
        
        // Add API key
        if (this.config.apiKey) {
            url += `&appid=${this.config.apiKey}`;
        } else {
            throw new Error('API key is required');
        }
        
        // Add units
        if (this.config.units === 'imperial') {
            url += '&units=imperial';
        } else {
            url += '&units=metric';
        }
        
        return url;
    }
    
    /**
     * Format date from Unix timestamp
     * 
     * @param {number} timestamp - Unix timestamp
     * @returns {string} Formatted date
     */
    formatDate(timestamp) {
        const date = new Date(timestamp * 1000);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${date.getDate()} ${months[date.getMonth()]}, ${date.getFullYear()}`;
    }
    
    /**
     * Format time from Unix timestamp
     * 
     * @param {number} timestamp - Unix timestamp
     * @returns {string} Formatted time (e.g., "9:30 AM")
     */
    formatTime(timestamp) {
        const date = new Date(timestamp * 1000);
        let hours = date.getHours();
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        hours = hours % 12;
        hours = hours ? hours : 12; // Convert 0 to 12
        
        return `${hours}:${minutes} ${ampm}`;
    }
    
    /**
     * Get temperature with proper unit symbol
     * 
     * @param {number} kelvinTemp - Temperature in Kelvin
     * @returns {string} Formatted temperature with unit
     */
    formatTemperature(kelvinTemp) {
        let temp;
        let unit;
        
        if (this.config.units === 'imperial') {
            // API returns Fahrenheit directly when units=imperial
            temp = Math.round(kelvinTemp);
            unit = '°F';
        } else {
            // API returns Celsius directly when units=metric
            temp = Math.round(kelvinTemp);
            unit = '°C';
        }
        
        return `${temp}${unit}`;
    }
    
    /**
     * Map OpenWeatherMap icon code to custom icon filename
     * 
     * @param {string} iconCode - OpenWeatherMap icon code (e.g., "01d")
     * @returns {Object} Icon mapping result
     */
    mapCustomIcon(iconCode) {
        const timeOfDay = iconCode.includes('d') ? 'day' : 'night';
        let iconName;
        
        const iconMap = {
            '01d': 'clear', '01n': 'clear',
            '02d': 'clouds', '02n': 'clouds',
            '03d': 'clouds', '03n': 'clouds',
            '04d': 'clouds', '04n': 'clouds',
            '09d': 'shower-rain', '09n': 'shower-rain',
            '10d': 'rain', '10n': 'rain',
            '11d': 'storm', '11n': 'storm',
            '13d': 'snow', '13n': 'snow',
            '50d': 'mist', '50n': 'mist'
        };
        
        iconName = iconMap[iconCode] || 'clear';
        
        return {
            timeOfDay: timeOfDay,
            iconName: iconName,
            iconUrl: `${this.config.customIcons}${timeOfDay}/${iconName}.png`
        };
    }
    
    /**
     * Parse API response into structured weather object
     * 
     * @param {Object} data - API response data
     * @returns {Object} Structured weather data
     */
    parseWeatherData(data) {
        return {
            city: `${data.name}, ${data.sys.country}`,
            coordinates: {
                lat: data.coord.lat,
                lon: data.coord.lon
            },
            temperature: {
                current: this.formatTemperature(data.main.temp),
                min: this.formatTemperature(data.main.temp_min),
                max: this.formatTemperature(data.main.temp_max),
                feels_like: this.formatTemperature(data.main.feels_like),
                units: this.config.units === 'imperial' ? 'F' : 'C'
            },
            description: data.weather[0].description,
            condition: data.weather[0].main,
            icon: {
                code: data.weather[0].icon,
                url: this.config.customIcons 
                    ? this.mapCustomIcon(data.weather[0].icon).iconUrl
                    : `https://openweathermap.org/img/w/${data.weather[0].icon}.png`
            },
            wind: {
                speed: `${Math.round(data.wind.speed)} ${this.config.units === 'imperial' ? 'mph' : 'm/s'}`,
                gust: data.wind.gust ? `${Math.round(data.wind.gust)} ${this.config.units === 'imperial' ? 'mph' : 'm/s'}` : null,
                direction: data.wind.deg ? this.getWindDirection(data.wind.deg) : null
            },
            humidity: `${data.main.humidity}%`,
            pressure: `${data.main.pressure} hPa`,
            visibility: data.visibility ? `${(data.visibility / 1000).toFixed(1)} km` : null,
            clouds: `${data.clouds.all}%`,
            sunrise: this.formatTime(data.sys.sunrise),
            sunset: this.formatTime(data.sys.sunset),
            date: this.formatDate(data.dt),
            timestamp: data.dt
        };
    }
    
    /**
     * Get wind direction from degrees
     * 
     * @param {number} degrees - Wind direction in degrees
     * @returns {string} Cardinal direction
     */
    getWindDirection(degrees) {
        const directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        const index = Math.round(degrees / 22.5) % 16;
        return directions[index];
    }
    
    /**
     * Fetch weather data from API
     * 
     * @returns {Promise} Promise with weather data
     */
    async fetch() {
        try {
            const url = this.buildAPIUrl();
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`API returned ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            this.weatherData = this.parseWeatherData(data);
            
            // Execute success callback
            if (typeof this.config.onSuccess === 'function') {
                this.config.onSuccess(this.weatherData);
            }
            
            return this.weatherData;
            
        } catch (error) {
            console.error('Pearl Weather API Error:', error);
            
            // Execute error callback
            if (typeof this.config.onError === 'function') {
                this.config.onError({
                    message: error.message,
                    code: error.code || 'UNKNOWN_ERROR'
                });
            }
            
            throw error;
        }
    }
    
    /**
     * Get cached weather data
     * 
     * @returns {Object|null} Cached weather data or null
     */
    getCachedData() {
        return this.weatherData;
    }
}

// Export for use in plugin
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PearlWeatherAPIClient;
}