<?php
/**
 * This class adapts the hooks in our index.php template in the Waterfall theme, so the data from the review is loaded accordingly.
 */
namespace Waterfall_Reviews\Views;
use Waterfall_Reviews\Base as Base;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Archive_Reviews extends Base {

    /**
     * Register our hooks and actions
     */
    protected function register() {

        $this->defaults = [
            'reviews_archive_content_charts'    => false,    
            'reviews_archive_content_compare'   => false,    
        ];

        $this->actions = [
            ['waterfall_before_archive_posts', 'before'],
            ['waterfall_after_archive_posts', 'after'],
        ];

        $this->filters = [
            ['waterfall_blog_schema_post_types', 'blogSchema'],
            ['waterfall_archive_posts_args', 'reviews']
        ];
        
    }

    /**
     * Enables BlogPosting schema for archives
     */
    public function blogSchema($types) {
        
        if( wf_get_archive_post_type() != 'reviews' ) {
            return $types;
        }

        $noSchema = isset($this->options['scheme_post_types_disable']) && $this->options['scheme_post_types_disable'] ? $this->options['scheme_post_types_disable'] : [];

        // Schemas should not be disabled
        if( in_array('reviews', $noSchema) ) {
            return $types;     
        }  
        
        if( $this->options['review_scheme'] == 'BlogPosting' ) {
            $types[] = 'reviews';
        }

        return $types;        

    }

    /**
     * Add the content container before the archive posts and add possible tabs
     */
    public function before() {

        if( wf_get_archive_post_type() != 'reviews' ) {
            return;
        }

        echo '<div class="content">';

        if( $this->layout['reviews_archive_content_charts'] || $this->layout['reviews_archive_content_compare']  ) {

            echo '<div class="wfr-reviews-tabs atom-tabs">';

            echo '<ul class="atom-tabs-navigation">';
            echo '  <li><a class="atom-tab active" href="#" data-target="reviews"><i class="fa fa-list"></i> ' . __('List', 'wfr') . '</a></li>';

            if( $this->layout['reviews_archive_content_charts'] ) {
                echo '  <li><a class="atom-tab" href="#" data-target="charts"><i class="fa fa-bar-chart"></i> ' . __('Compare', 'wfr') . '</a></li>';
            }          

            echo '</ul>';

            // Open up the tabs content
            echo '<div class="atom-tabs-content">';
            echo '  <section class="wfr-reviews-tab atom-tab active" data-id="reviews">';

        }

    }

    /**
     * Hook into our public posts with reviews arguments
     * 
     * @param   array $args The original arguments passed by the filter
     * @return  array $args The modified arguments
     * 
     * @todo    Add support for charts and compare
     */
    public function reviews( $args ) {

        if( wf_get_archive_post_type() != 'reviews' ) {
            return $args;
        }

        global $wp_query;

        $reviews                        = new Components\Reviews();
        $args                           = $reviews->params;
        $args['attributes']['class']    = 'archive-posts wfr-reviews-component';
        $args['query']                  = $wp_query;
        $args['queryArgs']              = [];

        return $args;

    }

    /**
     * Close the content container and add the graphs and compare tab
     */ 
    public function after() {

        if( wf_get_archive_post_type() != 'reviews' ) {
            return;
        }

        // Make the closing of tabs for our posts
        if( $this->layout['reviews_archive_content_charts'] || $this->layout['reviews_archive_content_compare']  ) {
            echo '</section><!-- .wfr-reviews-tab -->';
        }     

        // Add our content charts
        if( $this->layout['reviews_archive_content_charts'] ) {

            echo '<section class="wfr-charts-tab atom-tab" data-id="charts">';

            $args = [];

            // Filter for certain categories
            if( isset(get_queried_object()->term_id) && isset(get_queried_object()->taxonomy) && get_queried_object()->taxonomy == 'reviews_category' ) {
                $args['categories'][] = get_queried_object()->term_id;
            }  

            // Filter for certain tags
            if( isset(get_queried_object()->term_id) && isset(get_queried_object()->taxonomy) && get_queried_object()->taxonomy == 'reviews_tag' ) {
                $args['tags'][] = get_queried_object()->term_id;
            }              

            // Retrieve the category id
            $charts = new Components\Charts( $args );
            $charts->render();

            echo '</section>';

        }

        // Adds our comparison - @todo complete
        // if( $this->layout['reviews_archive_content_compare'] ) {

        //     echo '<section class="wfr-compare-tab atom-tab" data-id="compare">';

        //     echo '</section>';

        // }        

        // Close our tabs
        if( $this->layout['reviews_archive_content_charts'] || $this->layout['reviews_archive_content_compare']  ) {
            echo '  </div><!-- .atom-tabs-content -->';
            echo '</div><!-- .atom-tabs -->';
        }

        echo '</div><!-- .content -->';
    }

}