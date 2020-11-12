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