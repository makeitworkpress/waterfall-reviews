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
            'reviews_archive_content_charts'            => false,    
            'reviews_archive_content_compare'           => false,    
            'reviews_archive_content_compare_category'  => false,    
        ];

        $this->actions = [
            ['waterfall_before_archive_posts', 'before'],
            ['waterfall_after_archive_posts', 'after'],
        ];

        $this->filters = [
            ['waterfall_blog_schema_post_types', 'blog_schema'],
            ['waterfall_archive_posts_args', 'reviews']
        ];
        
    }

    /**
     * Enables BlogPosting schema for archives
     */
    public function blog_schema($types) {
        
        if( wf_get_archive_post_type() != 'reviews' ) {
            return $types;
        }

        $no_schema = isset($this->options['scheme_post_types_disable']) && $this->options['scheme_post_types_disable'] ? $this->options['scheme_post_types_disable'] : [];

        // Schemas should not be disabled
        if( in_array('reviews', $no_schema) ) {
            return $types;     
        }  
        
        if( isset($this->options['review_scheme']) && $this->options['review_scheme'] == 'BlogPosting' ) {
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

        /**
         * We put everything here in a content wrapper,
         * This is normally applied to the posts molecule for the given archive,
         * but as review archives should support tabs, an extra wrapper is needed
         */
        echo '<div class="content">';

        // Only allow comparison tables within categorie pages, if set-up.
        if( $this->layout['reviews_archive_content_compare_category'] && ! $this->check_term_query('reviews_category') ) {
            return;
        }

        if( $this->layout['reviews_archive_content_compare']  ) {

            echo '<div class="wfr-reviews-tabs atom-tabs">';

            echo '<ul class="atom-tabs-navigation">';
            echo '  <li><a class="atom-tab active" href="#" data-target="reviews"><i class="fa fa-list"></i> ' . __('List', 'wfr') . '</a></li>';
            echo '  <li><a class="atom-tab" href="#" data-target="compare"><i class="fa fa-bar-chart"></i> ' . __('Compare', 'wfr') . '</a></li>';         
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
        $args['query_args']             = [];

        return $args;

    }

    /**
     * Close the content container and add the graphs and compare tab
     */ 
    public function after() {

        if( wf_get_archive_post_type() != 'reviews' ) {
            return;
        }

        // Only allow comparison tables within categorie pages, if set-up.
        if( $this->layout['reviews_archive_content_compare_category'] && ! $this->check_term_query('reviews_category') ) {

            // Close our content wrapper
            echo '</div><!-- .content -->';
            return;

        }        

        // Add our comparison tabs
        if( $this->layout['reviews_archive_content_compare'] ) {

            // Close the post reviews tab
            echo '      </section><!-- .wfr-reviews-tab -->';

            // Start the new tab
            echo '      <section class="wfr-compare-tab atom-tab" data-id="compare">';

            $args = [
                'weight' => $this->layout['reviews_archive_content_compare_weighted']
            ];

            // Filter for certain categories
            if( $this->check_term_query('reviews_category') ) {
                $args['categories'] = [$this->check_term_query('reviews_category')];
            }  

            // Filter for certain tags
            if( $this->check_term_query('reviews_tag') ) {
                $args['tags']       = [$this->check_term_query('reviews_tag')];
            }              

            // Show our charts
            $charts = new Components\Charts( $args );
            $charts->render();

            // Add comparison tables
            $args['form'] = true;
            $args['load'] = false;
            $args['price'] = false;
            $args['view'] = 'table';

            $tables = new Components\Tables( $args );
            $tables->render();

            echo '      </section>';


            // Close our tabs
            echo '  </div><!-- .atom-tabs-content -->';
            echo '</div><!-- .atom-tabs -->';            

        }     

        // Close our content wrapper
        echo '</div><!-- .content -->';

    }

    /**
     * Check if a reviews tag or category is queried, and returns the id if so
     * 
     * @param   String  $taxonomy   The taxonomy that needs to be checked
     * @return  Int     @term       The queried term id
     */
    private function check_term_query( $taxonomy = '') {

        $term = '';

        if( isset(get_queried_object()->term_id) && isset(get_queried_object()->taxonomy) && get_queried_object()->taxonomy == $taxonomy ) {
            $term = get_queried_object()->term_id;
        }

        return $term;

    }

}