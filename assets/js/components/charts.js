/**
 * Handles our chart actions
 */
var randomColor = require('./../vendor/randomcolor'),
    utils       = require('./../utils');

var Charts = {

    charts: {}, // Contains active charts, per id
    data: {}, // Contains the data of chart
    initialize: function() {

        var self = this;

        // Initial set-up
        jQuery('.wfr-charts').each( function() {

            var canvas  = jQuery(this).find('.wfr-charts-chart').get(0),
                ID      = jQuery(this).attr('id');

            // Default data values
            self.data[ID] = {
                normal: [],
                weighted: []
            };

            // Our chart is not yet initialized
            self.charts[ID] = false;

            // Draw charts if we have an id with data attached, and thus we may load the chart
            if( ID && typeof(window['chart' + ID]) !== 'undefined' ) {
                self.data[ID] = window['chart' + ID]
                self.renderChart( self.data[ID].normal, canvas, ID );

                if( self.data[ID].weighted.dataSet.data.length > 0 ) {
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

            var canvas  = jQuery(this).closest('.wfr-charts').find('.wfr-charts-chart').get(0),
                ID      = jQuery(this).closest('.wfr-charts').attr('id');
            
            jQuery(this).addClass('active');
            jQuery(this).next('.wfr-charts-weighted').removeClass('active');
            self.renderChart(self.data[ID].normal, canvas, ID);

        });
        
        // Select charts based upon our weighted buttons
        jQuery('.wfr-charts-weighted').click( function(event) {
            event.preventDefault();

            var canvas  = jQuery(this).closest('.wfr-charts').find('.wfr-charts-chart').get(0),
                ID      = jQuery(this).closest('.wfr-charts').attr('id');

            jQuery(this).addClass('active');
            jQuery(this).prev('.wfr-charts-normal').removeClass('active');
            self.renderChart(self.data[ID].weighted, canvas, ID);

        });        

    },

    /**
     * Creates our form listener - upon changes it will load a new chart
     * 
     * @param selector The select element for the current objective, defines the context of our chart
     */
    listener: function(selector) {

        // A key should be defined
        if( ! jQuery(selector).val() ) {
            return;
        }

        var canvas  = jQuery(selector).closest('.wfr-charts').find('.wfr-charts-chart').get(0),
            ID      = jQuery(selector).closest('.wfr-charts').attr('id'),
            self    = this,
            weight  = jQuery(selector).closest('.wfr-charts').find('.wfr-charts-weight');

        // Reset weighted display
        if( weight.length > 0 ) {
            jQuery(weight).find('.wfr-charts-normal').addClass('active');    
            jQuery(weight).find('.wfr-charts-weighted').removeClass('active');    
        }  
        
        // Hide our weighted display
        jQuery(selector).closest('.wfr-charts').find('.wfr-charts-weight').hide();

        utils.ajax({
            beforeSend: function() {
                jQuery(canvas).closest('.wfr-charts-wrapper').addClass('components-loading');
            },
            complete: function() {
                jQuery(canvas).closest('.wfr-charts-wrapper').removeClass('components-loading');
            },
            data: {
                action: 'get_chart_data', 
                categories: jQuery(selector).closest('.wfr-chart-selector').data('categories'),
                key: jQuery(selector).val(),
                include: jQuery(selector).closest('.wfr-chart-selector').data('include'),
                nonce: wfr.nonce,
                tags: jQuery(selector).closest('.wfr-chart-selector').data('tags')
            },
            success: function(response) {
                
                if( wfr.debug ) {
                    console.log(response);
                }

                if( ! response.success ) {
                    return;
                }

                self.data[ID] = response.data;

                // Chart has been loaded
                jQuery(selector).closest('.wfr-charts').addClass('wfr-charts-loaded');

                if( self.data[ID].weighted.dataSet.data.length > 0 ) {
                    jQuery(selector).closest('.wfr-charts').find('.wfr-charts-weight').show();
                }                

                self.renderChart(response.data.normal, canvas, ID);

            }
        });

    },

    /**
     * Displays the chart
     * 
     * @param {object} data   The data object with unformatted data objects, either from an ajax response or direct response
     * @param {object} canvas The canvas to render the chart in
     * @param {string} ID     The ID of the rendered chart
     */
    renderChart: function(data, canvas, ID) {

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
        if( this.charts[ID] ) {
            this.charts[ID].data = {
                datasets: dataSets,
                labels: data.labels               
            };
            this.charts[ID].options.title.text = dataSet.label; 
            this.charts[ID].update();
            this.setChartHeight(dataSets, dataSet.barThickness, canvas);
            return;
        }

        // Setup the cart
        this.charts[ID] = new Chart(canvas, {
            data: {
                datasets: dataSets,
                labels: data.labels
            },
            options: {
                legend: {
                    display: false
                }, 
                title: {
                    display: true,
                    text: dataSet.label
                },                          
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