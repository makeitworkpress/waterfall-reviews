<?php
/**
 * Displays our price(s) with possible buttons
 */
namespace Waterfall_Reviews\Views\Components;
use MakeitWorkPress\WP_Components as WP_Components;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Prices extends Component {

    /**
     * Set up our component for displaying prices
     */
    protected function initialize() {
        
        // Our default parameters
        $this->params   = wp_parse_args( $this->params, [
            'best'          => false,   // Examines the best price and set this on top
            'button'        => true,    // If we want to display a button to our suppliers
            'button_label'  => isset($this->layout['reviews_price_button']) ? $this->layout['reviews_price_button'] : '', // The default label for our button
            'currency'      => isset($this->options['review_currency']) ? $this->options['review_currency'] : '',
            'id'            => 0,       // The id of our post were we are retrieving the prices for
            'names'         => true,    // If we display supplier names
            'single'        => false,    // If we display only one price
            'schema'        => true     // Whether microschemes should be used or not
        ] );

        // Define our post id
        if( ! $this->params['id'] ) {
            global $post;
            $this->params['id'] = is_numeric($post) ? $post : $post->ID;
        }        

        $this->props        = ['prices' => []];
        $this->template     = 'prices';      

    }

    /**
     * Queries and formats our data
     */
    protected function query() {

        $best           = false; // If we have a manual best price, this is set to true.
        $currency       = get_post_meta( $this->params['id'], 'price_currency', true );
        $prefix         = get_post_meta( $this->params['id'], 'price_prefix', true );
        $prices         = get_post_meta( $this->params['id'], 'prices', true );
        $unit           = get_post_meta( $this->params['id'], 'price_unit', true );

        // If we don't have prices, there is nothing to show
        if( ! $prices ) {
            return;
        }

        $this->props['schema'] = $this->params['schema'];

        // Sort our prices
        if( $this->params['single'] ) {

            foreach( $prices as $price ) {

                // Puts the best price at the beginning of the array
                if( isset($price['best']) && $price['best'] ) {
                    $best = $price;
                }

            }

            // Sort our prices if we don't have a manual best price, else add it to the first place of the array
            if( ! $best ) {
                usort( $prices, function($a, $b) { return strnatcmp($a['price'], $b['price']); } );
            } else { 
                array_unshift( $prices, $best );
            }

        }

        // If we show multiple prices     
        foreach( $prices as $key => $price ) {

            if( ! $price['price'] ) {
                continue;
            }

            $this->props['prices'][$key]['button']          = ''; 
            $this->props['prices'][$key]['class']           = 'wfr-regular-price';
            $this->props['prices'][$key]['name']            = '';

            // Our prices
            $this->props['prices'][$key]['currency']    = $currency ? $currency : $this->params['currency'];
            $this->props['prices'][$key]['prefix']      = $prefix;
            $this->props['prices'][$key]['unit']        = $unit;
            $this->props['prices'][$key]['value']       = $price['price'];

            // Names of supplier
            if( $this->params['names'] && $price['name'] ) {
                $this->props['prices'][$key]['name'] = $price['name'];
            }

            if( $this->params['button'] && $price['url'] ) {
                $this->props['prices'][$key]['button'] = WP_Components\Build::atom(
                    'button', 
                    [
                        'attributes'    => ['class' => 'wfr-price-button', 'href' => $price['url'], 'itemprop' => $this->params['schema'] ? 'url' : '', 'rel' => 'nofollow', 'target' => '_blank'], 
                        'background'    => 'default', 
                        'label'         => isset($price['button']) && $price['button'] ? $price['button'] : $this->params['button_label'],
                        'size'          => 'small'
                    ],
                    false
                );
            }

            // Break the loop if we have single prices, after the first array
            if( $this->params['single'] && $key == 0 ) {
                break;
            }

        }

        // Shows our best price as a seperate price on top or just show the best price
        if( $this->params['best'] ) {

            array_unshift( $this->props['prices'], [
                'button'   => false,
                'class'    => 'wfr-best-price',
                'currency' => $currency ? $currency : $this->params['currency'],
                'name'     => '',
                'prefix'   => $prefix,
                'unit'     => $unit,
                'value'    => get_post_meta( $this->params['id'], 'price', true )             
            ] );

        } 
    
    }

}