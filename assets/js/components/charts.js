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
    posts: [],
    
    initialize: function() {

        var self = this;

        // Draw charts if we have an id, and thus we may load the chart
        jQuery('.wfr-charts').each( function() {

            var canvas  = jQuery(this).find('.wfr-charts-chart').get(0),
                ID      = jQuery(this).attr('id');

            if( ID && typeof(window['chart' + ID]) !== 'undefined' ) {
                this.data = window['chart' + ID];
                self.renderChart( this.data.normal, canvas );
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
            jQuery(this).toggleClass('active');
            self.renderChart(self.data.normal, canvas);

        });
        
        // Select charts based upon our weighted buttons
        jQuery('.wfr-charts-weighted').click( function(event) {
            event.preventDefault();

            var canvas = jQuery(this).closest('.wfr-charts').find('.wfr-charts-chart').get(0);
            jQuery(this).toggleClass('active');
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
            self    = this;

        utils.ajax({
            beforeSend: function() {
                jQuery(canvas).addClass('components-loading');
            },
            complete: function() {
                jQuery(canvas).removeClass('components-loading');
            },
            data: {
                action: 'getChartData', 
                category: jQuery(object).closest('.wfr-chart-selector').data('category'),
                key: jQuery(object).val(),
                include: this.posts, // @todo provide support for including certain posts
                nonce: wfr.nonce,
                tag: jQuery(object).closest('.wfr-chart-selector').data('tag')
            },
            success: function(response) {
                
                if( wfr.debug ) {
                    console.log(response);
                }

                if( ! response.success ) {
                    return;
                }

                self.data = response.data;

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
            barThickness: 25,
            data: [],
            label: data.dataSet.label
        },
        dataSets    = [];

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
            this.chart.destroy();
        }

        this.chart = new Chart(canvas, {
            data: {
                datasets: dataSets,
                labels: data.labels
            },
            options: {
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero:true
                        }
                    }]                    
                }
            },                    
            type: 'horizontalBar'
        });

    }
    
};

module.exports = Charts;