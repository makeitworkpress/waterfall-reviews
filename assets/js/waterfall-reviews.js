(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
/**
 * All modules are bundled into one application
 */
'use strict';

var utils = require('./utils');

var App = {
    components: {
        charts: require('./components/charts'),
        comments: require('./components/comments'),
        filter: require('./components/filter'),
        tables: require('./components/tables')
    },     
    initialize: function() {

        // Initialize modules
        for( var key in this.components ) {
            this.components[key].initialize();
        }    
        
    }
}

// Store the application in our global scope
window.App = App;

// Boot our application after the document is ready
jQuery(document).ready( function() {
    App.initialize();
});

},{"./components/charts":2,"./components/comments":3,"./components/filter":4,"./components/tables":5,"./utils":6}],2:[function(require,module,exports){
/**
 * Handles our chart actions
 */
var randomColor = require('./../vendor/randomcolor'),
    utils       = require('./../utils');

var Charts = {

    chart: false,
    data: {
        normal: [],
        weighted: [], 
    },
    initialize: function() {

        var self = this;

        // Draw charts if we have an id, and thus we may load the chart
        jQuery('.wfr-charts').each( function() {

            var canvas  = jQuery(this).find('.wfr-charts-chart').get(0),
                ID      = jQuery(this).attr('id');

            if( ID && typeof(window['chart' + ID]) !== 'undefined' ) {
                self.data = window['chart' + ID];
                self.renderChart( self.data.normal, canvas );

                if( self.data.weighted.dataSet.data.length > 0 ) {
                    jQuery(this).find('.wfr-charts-weight').show();
                }

            }

        });         

        // Draw charts based upon changing the select form
        jQuery('.wfr-chart-selector select').change( function() {
            self.listener(this);
        });

        // Prevent submitting of the weight form
        jQuery('.wfr-charts-weight').submit( function(event) {
            event.preventDefault();
        });

        // Select charts based upon our weighted buttons
        jQuery('.wfr-charts-normal').click( function(event) {
            event.preventDefault();

            var canvas = jQuery(this).closest('.wfr-charts').find('.wfr-charts-chart').get(0);
            jQuery(this).addClass('active');
            jQuery(this).next('.wfr-charts-weighted').removeClass('active');
            self.renderChart(self.data.normal, canvas);

        });
        
        // Select charts based upon our weighted buttons
        jQuery('.wfr-charts-weighted').click( function(event) {
            event.preventDefault();

            var canvas = jQuery(this).closest('.wfr-charts').find('.wfr-charts-chart').get(0);
            jQuery(this).addClass('active');
            jQuery(this).prev('.wfr-charts-normal').removeClass('active');
            self.renderChart(self.data.weighted, canvas);

        });        

    },

    /**
     * Creates our form listener - upon changes it will load a new chart
     * 
     * @param object The select for the current objective
     */
    listener: function(object) {

        // A key should be defined
        if( ! jQuery(object).val() ) {
            return;
        }

        var canvas  = jQuery(object).closest('.wfr-charts').find('.wfr-charts-chart').get(0),
            self    = this,
            weight  = jQuery(object).closest('.wfr-charts').find('.wfr-charts-weight');

        // Reset weighted display
        if( weight.length > 0 ) {
            jQuery(weight).find('.wfr-charts-normal').addClass('active');    
            jQuery(weight).find('.wfr-charts-weighted').removeClass('active');    
        }  
        
        // Hide our weighted display
        jQuery(object).closest('.wfr-charts').find('.wfr-charts-weight').hide();

        utils.ajax({
            beforeSend: function() {
                jQuery(canvas).addClass('components-loading');
            },
            complete: function() {
                jQuery(canvas).removeClass('components-loading');
            },
            data: {
                action: 'getChartData', 
                categories: jQuery(object).closest('.wfr-chart-selector').data('categories'),
                key: jQuery(object).val(),
                include: jQuery(object).closest('.wfr-chart-selector').data('include'),
                nonce: wfr.nonce,
                tags: jQuery(object).closest('.wfr-chart-selector').data('tags')
            },
            success: function(response) {
                
                if( wfr.debug ) {
                    console.log(response);
                }

                if( ! response.success ) {
                    return;
                }

                self.data = response.data;

                jQuery(object).closest('.wfr-charts').addClass('wfr-charts-loaded');

                if( self.data.weighted.dataSet.data.length > 0 ) {
                    jQuery(object).closest('.wfr-charts').find('.wfr-charts-weight').show();
                }                

                self.renderChart(response.data.normal, canvas);

            }
        });

    },

    /**
     * Displays the chart
     * 
     * @param object data   The data object with unformatted data objects, either from an ajax response or direct response
     * @param object canvas The canvas to render the chart in
     */
    renderChart: function(data, canvas) {

        // Format our datasets with random colors and add our data to the right canvas
        var dataSet     = {
            backgroundColor: [],
            barThickness: 30,
            // maxBarThickness: 100,
            data: [],
            label: data.dataSet.label
        },
        dataSets    = [];

        if( typeof data.dataSet === 'undefined' ) {
            return;
        }

        // Adds the data
        for( var index in data.dataSet.data ) {
            var backgroundColor = randomColor(),
                dataSetdata     = parseFloat(data.dataSet.data[index]);

            dataSet.backgroundColor.push(backgroundColor);
            dataSet.data.push(dataSetdata);
        }

        dataSets.push(dataSet);

        // Redefine the chart data if our chart already exists
        if( this.chart ) {
            this.chart.data = {
                datasets: dataSets,
                labels: data.labels               
            };
            this.chart.update();
            this.setChartHeight(dataSets, dataSet.barThickness, canvas);
            return;
        }

        // Setup the cart
        this.chart = new Chart(canvas, {
            data: {
                datasets: dataSets,
                labels: data.labels
            },
            options: {
                legend: {
                    display: false
                }, 
                // title: {
                //     display: true,
                //     text: dataSet.label
                // },                          
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        // barPercentage: 0.8,
                        ticks: {
                            beginAtZero:true
                        }
                    }]                    
                }
            },                    
            type: 'horizontalBar'
        });

        // Set dynamic height
        this.setChartHeight(dataSets, dataSet.barThickness, canvas);

    },

    /**
     * Sets the minimum height of a chart based upon the amount of datasets
     * @param {array}   datasets    The datasets passed to the charts
     * @param {int}     thickness   The thickness of each bar
     * @param {node}    canvas      The canvas to which the height needs to be applied
     */
    setChartHeight: function(datasets, thickness, canvas) {
        
        var height = canvas.height;

        if( typeof datasets[0] !== 'undefined' && typeof datasets[0].data !== 'undefined' ) {
            height = datasets[0].data.length * (thickness + 10) + 64;
        }

        if( height < 500 ) {
            height = 500;
        }
        
        jQuery(canvas).closest('.wfr-charts-wrapper').height(height);
    }
    
};

module.exports = Charts;
},{"./../utils":6,"./../vendor/randomcolor":7}],3:[function(require,module,exports){
/**
 * Handles our comments actions
 */
var utils  = require('./../utils');

var Comments = {

    initialize: function() {

        // Sets the value
        jQuery('.comment-form-rating input').change( function() {
            utils.rangeValue(this);
        });

        jQuery('#wfr-reply').change( function() {
            if( this.checked ) {
                jQuery(this).closest('.comment-form-reply').prevAll('.comment-form-rating').slideUp();
            } else {
                jQuery(this).closest('.comment-form-reply').prevAll('.comment-form-rating').slideDown();   
            }  
        });

    },

};

module.exports = Comments;
},{"./../utils":6}],4:[function(require,module,exports){
/**
 * Handles our chart actions
 */

var utils  = require('./../utils');

var Filter = {

    /**
     * Contains our filter arguments
     */
    arguments: {},
    currentFilter: null,
    page: 0,

    initialize: function() {

        var self = this;

        jQuery('.wfr-filter').each( function() { 
            self.listener(this);
        });
        this.ratingSlider();
    },

    /**
     * Filters our result
     * 
     * @param object object The current form object that is initiating the filter action
     */
    filter: function(object) {

        this.arguments = new FormData( jQuery(object).get(0) );
        this.arguments.append('nonce', wfr.nonce); // The object wfr is available in global scope, being localized on waterfall-reviews.js
        this.arguments.append('action', 'filterReviews');

        var target = jQuery(object).data('target');

        // Add the page if we are filtering for pages
        if( this.page ) {
            this.arguments.append('page', this.page);
            this.page = 0;
        }

        // Execute our ajax action
        utils.ajax({
            beforeSend: function() {
                jQuery('div[data-id="' + target + '"]').addClass('components-loading');
            },
            complete: function() {
                jQuery('div[data-id="' + target + '"]').removeClass('components-loading');    
            },
            contentType: false,
            data: this.arguments,
            processData: false,
            success: function(response) {

                if( wfr.debug ) {
                    console.log(response);
                }

                if( ! response.success ) {
                    return;
                }

                if( typeof(response.data.html) !== 'undefined' ) {
                    jQuery('div[data-id="' + target + '"]').replaceWith(response.data.html);

                    if( typeof(lazyload) !== "undefined" ) {
                        lazyload.update();
                    } else if( typeof wpOptimizeLazyLoad !== 'undefined' ) {
                        wpOptimizeLazyLoad.update();   
                    } else if( typeof wpComponentsLazyLoad !== 'undefined' ) {
                        wpComponentsLazyLoad.update();  
                    }

                }

                // Emit a chart change - our chart object will listen to this change
                if( typeof(response.data.posts) !== 'undefined' ) {

                    var object = jQuery('div[data-id="' + target + '"]').closest('.atom-tabs-content').find('.wfr-chart-selector select').get(0);
                    
                    App.components.charts.posts = response.data.posts;
                    App.components.charts.listener(object);

                }

            }
        });

    },

    /**
     * Listens to form changes, in order to filter
     * 
     * @param object object The current form object that is initiating the filter action
     */
    listener: function(object) {

        var self = this;

        // If the input changes, we filter, but only if we have instant filtering
        if( jQuery(object).hasClass('wfr-instant-filter') ) {
            jQuery(object).find(':input').change(function(event) {
                self.filter(object);
            }); 
        }

        // If we submit, we filter
        jQuery(object).submit(function(event) {
            self.filter(object);

            event.preventDefault();

        });

    },

    /**
     * Sets the value of our rating slider
     */
    ratingSlider: function() {

        // Also set value on page load
        utils.rangeValue('.wfr-filter-rating input');

        // Sets the value
        jQuery('.wfr-filter-rating input').change( function() {
            utils.rangeValue(this);
        });

    }

};

module.exports = Filter;
},{"./../utils":6}],5:[function(require,module,exports){
/**
 * Handles our chart actions
 */
var utils       = require('./../utils');

var Tables = {

    reviews: [],
    initialize: function() {

        var self = this;        

        // Draw charts based upon changing the select form
        jQuery(document).on('click', '.wfr-tables-form li', function() {

            var review = this.dataset.target

            // Change class
            jQuery(this).toggleClass('active');

            // Remove the item if it's already there
            if( self.reviews.includes(review) ) {
                for(var i = self.reviews.length - 1; i >= 0; i--) {
                    if(self.reviews[i] === review) {
                        self.reviews.splice(i, 1);
                    }
                }
            } else {
                self.reviews.push(review);   
            }

            self.loadTables(this);

        });

        // Prevent submitting of the default form
        jQuery('.wfr-tables-form').submit( function(event) {
            event.preventDefault();
        });       

    },

    /**
     * Creates our table loader - upon changes it will load a new table
     * 
     * @param object The item for the current objective
     */
    loadTables: function(object) {


        if( this.reviews.length < 2 ) {
            return;
        }

        var form = jQuery(object).closest('.wfr-tables-form'),
            self = this,
            view = jQuery(form).next('.wfr-tables-view');

        utils.ajax({
            beforeSend: function() {
                jQuery(view).addClass('components-loading');
            },
            complete: function() {
                jQuery(view).removeClass('components-loading');
            },
            data: {
                action: 'loadTables', 
                attributes: jQuery(form).data('attributes'),
                categories: jQuery(form).data('categories'),
                groups: jQuery(form).data('groups'),
                reviews: self.reviews,
                nonce: wfr.nonce,
                properties: jQuery(form).data('properties'),
                tags: jQuery(form).data('tags'),
                view: jQuery(form).data('view'),
                weight: jQuery(form).data('weight')
            },
            success: function(response) {
                
                if( wfr.debug ) {
                    console.log(response);
                }

                if( ! response.success ) {
                    return;
                }

                jQuery(view).replaceWith( jQuery(response.data).find('.wfr-tables-view') );

            }
        });

    }
    
};

module.exports = Tables;
},{"./../utils":6}],6:[function(require,module,exports){
/**
 * A shorthand for executing jQuery ajax functions
 * @param {object} options The ajax options
 */
module.exports.ajax = function(options) {
    
    var options = options;
    
    options.data.nonce = wfr.nonce;
    options.type = 'POST';
    options.url = wfr.ajaxUrl;
    
    return jQuery.ajax(options);
    
};

/**
 * Automatically set the value of a range field to a nearby span field
 * 
 * @param {object} input The range input slider
 */
module.exports.rangeValue = function(input) {

    var style = jQuery(input).data('style'),
        value = jQuery(input).val();

    if( style === '' ) {
        return;
    }

    if( style === 'percentages' ) {
        value = parseInt( (value/jQuery(input).attr('max')) * 100 ) + '%';
    }

    jQuery(input).next('.wfr-range-value').html(value);

};
},{}],7:[function(require,module,exports){
// randomColor by David Merfield under the CC0 license
// https://github.com/davidmerfield/randomColor/

;(function(root, factory) {

    // Support CommonJS
    if (typeof exports === 'object') {
      var randomColor = factory();
  
      // Support NodeJS & Component, which allow module.exports to be a function
      if (typeof module === 'object' && module && module.exports) {
        exports = module.exports = randomColor;
      }
  
      // Support CommonJS 1.1.1 spec
      exports.randomColor = randomColor;
  
    // Support AMD
    } else if (typeof define === 'function' && define.amd) {
      define([], factory);
  
    // Support vanilla script loading
    } else {
      root.randomColor = factory();
    }
  
  }(this, function() {
  
    // Seed to get repeatable colors
    var seed = null;
  
    // Shared color dictionary
    var colorDictionary = {};
  
    // Populate the color dictionary
    loadColorBounds();
  
    var randomColor = function (options) {
  
      options = options || {};
  
      // Check if there is a seed and ensure it's an
      // integer. Otherwise, reset the seed value.
      if (options.seed !== undefined && options.seed !== null && options.seed === parseInt(options.seed, 10)) {
        seed = options.seed;
  
      // A string was passed as a seed
      } else if (typeof options.seed === 'string') {
        seed = stringToInteger(options.seed);
  
      // Something was passed as a seed but it wasn't an integer or string
      } else if (options.seed !== undefined && options.seed !== null) {
        throw new TypeError('The seed value must be an integer or string');
  
      // No seed, reset the value outside.
      } else {
        seed = null;
      }
  
      var H,S,B;
  
      // Check if we need to generate multiple colors
      if (options.count !== null && options.count !== undefined) {
  
        var totalColors = options.count,
            colors = [];
  
        options.count = null;
  
        while (totalColors > colors.length) {
  
          // Since we're generating multiple colors,
          // incremement the seed. Otherwise we'd just
          // generate the same color each time...
          if (seed && options.seed) options.seed += 1;
  
          colors.push(randomColor(options));
        }
  
        options.count = totalColors;
  
        return colors;
      }
  
      // First we pick a hue (H)
      H = pickHue(options);
  
      // Then use H to determine saturation (S)
      S = pickSaturation(H, options);
  
      // Then use S and H to determine brightness (B).
      B = pickBrightness(H, S, options);
  
      // Then we return the HSB color in the desired format
      return setFormat([H,S,B], options);
    };
  
    function pickHue (options) {
  
      var hueRange = getHueRange(options.hue),
          hue = randomWithin(hueRange);
  
      // Instead of storing red as two seperate ranges,
      // we group them, using negative numbers
      if (hue < 0) {hue = 360 + hue;}
  
      return hue;
  
    }
  
    function pickSaturation (hue, options) {
  
      if (options.hue === 'monochrome') {
        return 0;
      }
  
      if (options.luminosity === 'random') {
        return randomWithin([0,100]);
      }
  
      var saturationRange = getSaturationRange(hue);
  
      var sMin = saturationRange[0],
          sMax = saturationRange[1];
  
      switch (options.luminosity) {
  
        case 'bright':
          sMin = 55;
          break;
  
        case 'dark':
          sMin = sMax - 10;
          break;
  
        case 'light':
          sMax = 55;
          break;
     }
  
      return randomWithin([sMin, sMax]);
  
    }
  
    function pickBrightness (H, S, options) {
  
      var bMin = getMinimumBrightness(H, S),
          bMax = 100;
  
      switch (options.luminosity) {
  
        case 'dark':
          bMax = bMin + 20;
          break;
  
        case 'light':
          bMin = (bMax + bMin)/2;
          break;
  
        case 'random':
          bMin = 0;
          bMax = 100;
          break;
      }
  
      return randomWithin([bMin, bMax]);
    }
  
    function setFormat (hsv, options) {
  
      switch (options.format) {
  
        case 'hsvArray':
          return hsv;
  
        case 'hslArray':
          return HSVtoHSL(hsv);
  
        case 'hsl':
          var hsl = HSVtoHSL(hsv);
          return 'hsl('+hsl[0]+', '+hsl[1]+'%, '+hsl[2]+'%)';
  
        case 'hsla':
          var hslColor = HSVtoHSL(hsv);
          var alpha = options.alpha || Math.random();
          return 'hsla('+hslColor[0]+', '+hslColor[1]+'%, '+hslColor[2]+'%, ' + alpha + ')';
  
        case 'rgbArray':
          return HSVtoRGB(hsv);
  
        case 'rgb':
          var rgb = HSVtoRGB(hsv);
          return 'rgb(' + rgb.join(', ') + ')';
  
        case 'rgba':
          var rgbColor = HSVtoRGB(hsv);
          var alpha = options.alpha || Math.random();
          return 'rgba(' + rgbColor.join(', ') + ', ' + alpha + ')';
  
        default:
          return HSVtoHex(hsv);
      }
  
    }
  
    function getMinimumBrightness(H, S) {
  
      var lowerBounds = getColorInfo(H).lowerBounds;
  
      for (var i = 0; i < lowerBounds.length - 1; i++) {
  
        var s1 = lowerBounds[i][0],
            v1 = lowerBounds[i][1];
  
        var s2 = lowerBounds[i+1][0],
            v2 = lowerBounds[i+1][1];
  
        if (S >= s1 && S <= s2) {
  
           var m = (v2 - v1)/(s2 - s1),
               b = v1 - m*s1;
  
           return m*S + b;
        }
  
      }
  
      return 0;
    }
  
    function getHueRange (colorInput) {
  
      if (typeof parseInt(colorInput) === 'number') {
  
        var number = parseInt(colorInput);
  
        if (number < 360 && number > 0) {
          return [number, number];
        }
  
      }
  
      if (typeof colorInput === 'string') {
  
        if (colorDictionary[colorInput]) {
          var color = colorDictionary[colorInput];
          if (color.hueRange) {return color.hueRange;}
        } else if (colorInput.match(/^#?([0-9A-F]{3}|[0-9A-F]{6})$/i)) {
          var hue = HexToHSB(colorInput)[0];
          return [ hue, hue ];
        }
      }
  
      return [0,360];
  
    }
  
    function getSaturationRange (hue) {
      return getColorInfo(hue).saturationRange;
    }
  
    function getColorInfo (hue) {
  
      // Maps red colors to make picking hue easier
      if (hue >= 334 && hue <= 360) {
        hue-= 360;
      }
  
      for (var colorName in colorDictionary) {
         var color = colorDictionary[colorName];
         if (color.hueRange &&
             hue >= color.hueRange[0] &&
             hue <= color.hueRange[1]) {
            return colorDictionary[colorName];
         }
      } return 'Color not found';
    }
  
    function randomWithin (range) {
      if (seed === null) {
        return Math.floor(range[0] + Math.random()*(range[1] + 1 - range[0]));
      } else {
        //Seeded random algorithm from http://indiegamr.com/generate-repeatable-random-numbers-in-js/
        var max = range[1] || 1;
        var min = range[0] || 0;
        seed = (seed * 9301 + 49297) % 233280;
        var rnd = seed / 233280.0;
        return Math.floor(min + rnd * (max - min));
      }
    }
  
    function HSVtoHex (hsv){
  
      var rgb = HSVtoRGB(hsv);
  
      function componentToHex(c) {
          var hex = c.toString(16);
          return hex.length == 1 ? '0' + hex : hex;
      }
  
      var hex = '#' + componentToHex(rgb[0]) + componentToHex(rgb[1]) + componentToHex(rgb[2]);
  
      return hex;
  
    }
  
    function defineColor (name, hueRange, lowerBounds) {
  
      var sMin = lowerBounds[0][0],
          sMax = lowerBounds[lowerBounds.length - 1][0],
  
          bMin = lowerBounds[lowerBounds.length - 1][1],
          bMax = lowerBounds[0][1];
  
      colorDictionary[name] = {
        hueRange: hueRange,
        lowerBounds: lowerBounds,
        saturationRange: [sMin, sMax],
        brightnessRange: [bMin, bMax]
      };
  
    }
  
    function loadColorBounds () {
  
      defineColor(
        'monochrome',
        null,
        [[0,0],[100,0]]
      );
  
      defineColor(
        'red',
        [-26,18],
        [[20,100],[30,92],[40,89],[50,85],[60,78],[70,70],[80,60],[90,55],[100,50]]
      );
  
      defineColor(
        'orange',
        [19,46],
        [[20,100],[30,93],[40,88],[50,86],[60,85],[70,70],[100,70]]
      );
  
      defineColor(
        'yellow',
        [47,62],
        [[25,100],[40,94],[50,89],[60,86],[70,84],[80,82],[90,80],[100,75]]
      );
  
      defineColor(
        'green',
        [63,178],
        [[30,100],[40,90],[50,85],[60,81],[70,74],[80,64],[90,50],[100,40]]
      );
  
      defineColor(
        'blue',
        [179, 257],
        [[20,100],[30,86],[40,80],[50,74],[60,60],[70,52],[80,44],[90,39],[100,35]]
      );
  
      defineColor(
        'purple',
        [258, 282],
        [[20,100],[30,87],[40,79],[50,70],[60,65],[70,59],[80,52],[90,45],[100,42]]
      );
  
      defineColor(
        'pink',
        [283, 334],
        [[20,100],[30,90],[40,86],[60,84],[80,80],[90,75],[100,73]]
      );
  
    }
  
    function HSVtoRGB (hsv) {
  
      // this doesn't work for the values of 0 and 360
      // here's the hacky fix
      var h = hsv[0];
      if (h === 0) {h = 1;}
      if (h === 360) {h = 359;}
  
      // Rebase the h,s,v values
      h = h/360;
      var s = hsv[1]/100,
          v = hsv[2]/100;
  
      var h_i = Math.floor(h*6),
        f = h * 6 - h_i,
        p = v * (1 - s),
        q = v * (1 - f*s),
        t = v * (1 - (1 - f)*s),
        r = 256,
        g = 256,
        b = 256;
  
      switch(h_i) {
        case 0: r = v; g = t; b = p;  break;
        case 1: r = q; g = v; b = p;  break;
        case 2: r = p; g = v; b = t;  break;
        case 3: r = p; g = q; b = v;  break;
        case 4: r = t; g = p; b = v;  break;
        case 5: r = v; g = p; b = q;  break;
      }
  
      var result = [Math.floor(r*255), Math.floor(g*255), Math.floor(b*255)];
      return result;
    }
  
    function HexToHSB (hex) {
      hex = hex.replace(/^#/, '');
      hex = hex.length === 3 ? hex.replace(/(.)/g, '$1$1') : hex;
  
      var red = parseInt(hex.substr(0, 2), 16) / 255,
            green = parseInt(hex.substr(2, 2), 16) / 255,
            blue = parseInt(hex.substr(4, 2), 16) / 255;
  
      var cMax = Math.max(red, green, blue),
            delta = cMax - Math.min(red, green, blue),
            saturation = cMax ? (delta / cMax) : 0;
  
      switch (cMax) {
        case red: return [ 60 * (((green - blue) / delta) % 6) || 0, saturation, cMax ];
        case green: return [ 60 * (((blue - red) / delta) + 2) || 0, saturation, cMax ];
        case blue: return [ 60 * (((red - green) / delta) + 4) || 0, saturation, cMax ];
      }
    }
  
    function HSVtoHSL (hsv) {
      var h = hsv[0],
        s = hsv[1]/100,
        v = hsv[2]/100,
        k = (2-s)*v;
  
      return [
        h,
        Math.round(s*v / (k<1 ? k : 2-k) * 10000) / 100,
        k/2 * 100
      ];
    }
  
    function stringToInteger (string) {
      var total = 0
      for (var i = 0; i !== string.length; i++) {
        if (total >= Number.MAX_SAFE_INTEGER) break;
        total += string.charCodeAt(i)
      }
      return total
    }
  
    return randomColor;
}));  
},{}]},{},[1]);
