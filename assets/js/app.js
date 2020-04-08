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
