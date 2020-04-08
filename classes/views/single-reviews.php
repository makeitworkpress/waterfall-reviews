<?php
/**
 * This class adapts the hooks in our single.php template in the Waterfall theme, so the data from the review is loaded accordingly.
 */
namespace Waterfall_Reviews\Views;
use MakeitWorkPress\WP_Components as WP_Components;
use Waterfall_Reviews\Base as Base;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Single_Reviews extends Base {

    /**
     * Contains the post types for which no schema is applied
     * @access private
     */
    private $noSchema;

    /**
     * Registers our associated custom fields, actions and filters
     */
    protected function register() {

        $this->defaults = [
            'reviews_header_media'              => false,
            'reviews_header_rating'             => false,
            'reviews_header_rating_criteria'    => false,
            'reviews_header_prices_disable'     => false,
            'reviews_content_readable'          => true,
            'reviews_content_summary_disable'   => false,
            'reviews_content_rating_disable'    => false,
            'reviews_content_prices'            => false,
            'reviews_content_properties_before' => false,
            'reviews_content_summary_after'     => false,
            'reviews_content_rating_after'      => false,
            'reviews_content_prices_after'      => false,
            'reviews_content_properties_after'  => false,
            'reviews_related_featured'          => true,
            'reviews_related_price'             => true,
            'reviews_related_price_button'      => '',
            'reviews_related_rating'            => true,
            'reviews_related_summary'           => true,
            'reviews_similar'                   => true,
            'reviews_similar_height'            => '',
            'reviews_similar_button'            => __('View', 'wfr'),
            'reviews_similar_grid'              => 'third',
            'reviews_similar_image_enlarge'     => true,
            'reviews_similar_image_float'       => false,
            'reviews_similar_image'             => 'ld-square',
            'reviews_similar_price'             => true,
            'reviews_similar_price_button'      => '',
            'reviews_similar_rating'            => true,
            'reviews_similar_summary'           => true,
            'reviews_similar_number'            => 3,
            'reviews_similar'                   => true,
            'reviews_similar_style'             => 'grid',
            'reviews_similar_title'             => __('Similar', 'wfr'),
            'reviews_visitors_rating_add'       => false,
            'reviews_visitors_rating_component' => false,
            'reviews_visitors_rating_title'     => __('Average Visitor Rating', 'wfr')
        ];

        $this->actions = [  
            ['comment_form_top', 'commentFields'],
            ['components_post_header_container_end', 'header'],   
            ['waterfall_before_reviews_content', 'before'],   
            ['waterfall_after_reviews_content', 'after'],   
            ['waterfall_before_reviews_related', 'similar']   
        ];

        $this->filters = [
            ['comment_text', 'commentText'],
            ['waterfall_blog_schema_post_types', 'blogSchema'],
            ['waterfall_singular_schema', 'articleSchema'],
            ['waterfall_related_args', 'related'], 
            ['waterfall_content_content_args', 'content'],
            ['waterfall_content_footer_args', 'commentRating'], 
        ];

        $this->noSchema = isset($this->options['scheme_post_types_disable']) && is_array($this->options['scheme_post_types_disable']) ? $this->options['scheme_post_types_disable'] : [];

    }

    /**
     * Sets our Blog Post Schema if we have it selected
     * @param   string      $types The post types that retrieve the blog schema
     * @return  string      $types The added types
     */
    public function blogSchema($types) {

        // We should be looking at reviews
        if( ! is_singular('reviews') ) {
            return $types;
        } 
        
        // Schemas should not be disabled
        if( in_array('reviews', $this->noSchema) ) {
            return $types;     
        }        

        if( $this->options['review_scheme'] == 'BlogPosting' ) {
            $types[] = 'reviews';
        }

        return $types;

    }

    /**
     * Sets our article microscheme if it is not a Blog Post
     * @param   string  $schema The scheme
     * @return  string  $schema The altered scheme
     */
    public function articleScheme($schema) {

        // Only applies to reviews
        if( ! is_singular('reviews') ) {
            return $schema;
        }

        // Schemes should not be disabled
        if( in_array('reviews', $this->noSchema) ) {
            return $schema;     
        }

        if( $this->options['review_scheme'] != 'BlogPosting' ) {
            $schema = 'itemscope="itemscope" itemtype="http://schema.org/' . $this->options['review_scheme'] . '"';   
        }

        return $schema;
    }    


    /**
     * Alters the atoms within our header
     * 
     * @return void
     */
    public function header() {

        if( ! is_singular('reviews') ) {
            return;
        }

        global $post;

        // Displays the media within the header
        if( $this->layout['reviews_header_media'] ) {

            $images = get_post_meta( $post->ID, 'media', true );
            $video  = get_post_meta( $post->ID, 'video', true );

            // Slider arguments
            if( $video || $images) {
                $args = ['attributes' => ['class' => 'wfr-media'], 'slides' => []];
            }

            if( $video ) {
                $args['slides'][] = ['video' => ['schema' => false, 'video' => $video, 'videoHeight' => 480, 'videoWidth' => 854]];
            } 
            
            if( $images ) {
                $images = array_filter(explode( ',', $images ));

                foreach( $images as $image ) {
                    $args['slides'][] = ['image' => ['image'  => $image, 'size' => 'sd']];
                }
            }

            if( isset($args) ) {
                WP_Components\Build::molecule( 'slider', $args );
            }

        }  
        
        $source = [];

        // Displays the overall rating within the header
        if( $this->layout['reviews_header_rating'] ) {
            $source[] = 'overall';
        }

        // If we want to display additional ratings
        if( $this->layout['reviews_visitors_rating_add'] && $this->layout['reviews_header_rating_criteria'] ) {
            $source[] = 'users';
        }
        
        if( $this->layout['reviews_header_rating_criteria'] ) {
            $source[] = 'criteria';
        }  
        
        if( $source ) {
            $rating = new Components\Rating( ['source' => $source, 'schema' => in_array('reviews', $this->noSchema) ? false : 'aggregate'] );
            $rating->render();
        }

        // Displays the prices
        if( ! $this->layout['reviews_header_prices_disable'] ) {
            $prices = new Components\Prices( ['best' => isset($this->layout['reviews_prices_best']) ? $this->layout['reviews_prices_best']: false ]);
            $prices->render();
        }

    }

    /**
     * Adds a couple of sections before our content
     */
    public function before() {

        if( ! is_singular('reviews') ) {
            return;
        }        

        global $post;

        // We have to add an extra opening bracket for our content
        $readable = $this->layout['reviews_content_readable'] ? ' readable-content' : '';
        echo '<div class="content' . $readable . '">';

        // No extra content is added in our content section
        if( get_post_meta($post->ID, 'manual_editing', true) == true ) {
            return;
        }

        // Displays our rating criteria
        if( ! $this->layout['reviews_content_rating_disable'] ) {

            $source = ['overall'];

            if( $this->layout['reviews_visitors_rating_add'] ) {
                $source[] = 'users';
            }
            if( $this->options['rating_criteria'] ) {
                $source[] = 'criteria';
            } 

            $rating = new Components\Rating( [
                'source' => $source, 
                'schema' => $this->layout['reviews_header_rating'] || in_array('reviews', $this->noSchema) ? false : 'aggregate'
            ] );
            $rating->render();           

        }           

        if( ! $this->layout['reviews_content_summary_disable'] ) {
            $summary = new Components\Summary( ['author' => get_the_author_meta('display_name', $post->post_author)] );
            $summary->render();
        }


        // Loads the prices within our review
        if( $this->layout['reviews_content_prices'] ) {
            $prices = new Components\Prices(['best' => $this->layout['reviews_prices_best']]);
            $prices->render();
        }             

        // Loads our custom properties
        if( $this->layout['reviews_content_properties_before'] ) {

            $properties = new Components\Tables( [
                'groups'        => $this->propertyGroups(),
                'reviews'       => [$post->ID]
            ] );
            $properties->render();

        }        

    } 

    /**
     * Alters our content class, so that we can wrap the content container around all our elements
     * 
     * @param array $args The arguments for the content element
     * @return array $args The altered arguments for the content element
     */
    public function content( $args ) {

        if( ! is_singular('reviews') ) {
            return $args;
        }        

        $args['attributes']['class'] = 'entry-content';
        return $args;

    }
    
    /**
     * Adds a couple of sections after our content
     */
    public function after() {

        if( ! is_singular('reviews') ) {
            return;
        }        

        global $post;

        // No extra content is added in our content section
        if( get_post_meta($post->ID, 'manual_editing', true) == true ) {
            echo '</div><!-- .content -->'; // Make sure we close it, even with manual editing
            return;
        }
        
        // Displays our rating criteria
        if( $this->layout['reviews_content_rating_after'] ) {

            // Define our sources
            $source = ['overall'];

            if( $this->layout['reviews_visitors_rating_add'] ) {
                $source[] = 'users';
            }

            if( $this->options['rating_criteria'] ) {
                $source[] = 'criteria';
            } 

            $rating = new Components\Rating( [
                'source' => $source, 
                'schema' => $this->layout['reviews_header_rating'] || ! $this->layout['reviews_content_rating_disable'] || $this->layout['reviews_header_rating'] || in_array('reviews', $this->noSchema) ? false : 'aggregate'
            ] );
            $rating->render();           

        }         

        if( $this->layout['reviews_content_summary_after'] ) {
            $summary = new Components\Summary( ['author' => get_the_author_meta('display_name', $post->post_author)] );
            $summary->render();
        }
        
        if( $this->layout['reviews_content_prices_after'] ) {
            $prices = new Components\Prices( ['best' => $this->layout['reviews_prices_best']] );
            $prices->render();
        }        

        // Loads our custom properties
        if( $this->layout['reviews_content_properties_after'] ) {
            $properties = new Components\Tables( [
                'groups'        => $this->propertyGroups(),
                'reviews'       => [$post->ID]
            ] );
            $properties->render();  
        }
        
        // We have to add an extra closing bracket for our content so our sidebar aligns out nicely
        echo '</div><!-- .content -->';        

    }
    
    /**
     * Alters our related posts to just display what we need
     * 
     * @param array $args The arguments filtered
     * @return array $args The altered arguments
     */
    public function related( $args ) {

        if( ! is_singular('reviews') ) {
            return $args;
        }        
        
        // Create a new instance of the reviews component, so we can use it arguments to pass to filter
        $reviews = new Components\Reviews( [
            'featured'      => $this->layout['reviews_related_featured'],
            'price'         => $this->layout['reviews_related_price'],
            'priceButton'   => $this->layout['reviews_related_price_button'],
            'rating'        => $this->layout['reviews_related_rating'],
            'summary'       => $this->layout['reviews_related_summary']
        ] );

        if( $reviews->params['featured'] == 'logo' ) {
            $args['postProperties']['image']['image']           = 'logo'; // Looks for the image under the media meta key
        }

        // Adds the price
        if( $reviews->params['price'] ) {
            $args['postProperties']['footerAtoms']['price']     = $reviews->params['postProperties']['footerAtoms']['price'];  
        } 
            
        // Adds the rating component
        if( $reviews->params['rating'] ) {
            $args['postProperties']['headerAtoms']['rating']    = $reviews->params['postProperties']['headerAtoms']['rating'];
        }

        // Adds our summary
        if( $reviews->params['summary'] ) {
            $args['postProperties']['contentAtoms']['summary']  = $reviews->params['postProperties']['contentAtoms']['summary'];
        }        

        return $args;

    }

    /**
     * Displays a host of similar posts
     */
    public function similar() {

        if( ! is_singular('reviews') ) {
            return;
        }        

        if( ! $this->layout['reviews_similar'] ) {
            return;
        }

        // Watch for similar posts
        global $post;
        $similar = get_post_meta( $post->ID, 'similar' , true);

        if( ! $similar ) {
            return;
        }

        // The element properties
        $reviews = new Components\Reviews( [
            'attributes'        => [
                'class'         => 'wfr-similar-reviews',
                'style'         => ['min-height' => $this->layout['reviews_similar_height'] ? $this->layout['reviews_similar_height'] . 'px' : '']
            ],
            'button'            => $this->layout['reviews_similar_button'] ? true : false,
            'buttonLabel'       => $this->layout['reviews_similar_button'],
            'featured'          => $this->layout['reviews_similar_featured'],
            'pagination'        => false,
            'postProperties'    => [
                'contentAtoms'  => [],
                'grid'          => $this->layout['reviews_similar_grid'],
                'headerAtoms'   => [
                    'title'     => ['atom' => 'title', 'properties' => ['attributes' => ['itemprop' => 'name headline', 'class' => 'entry-title'], 'tag' => 'h3', 'link' => 'post']]     
                ],                
                'image'         => [ 
                    'enlarge'   => $this->layout['reviews_similar_image_enlarge'],
                    'float'     => $this->layout['reviews_similar_image_float'], 
                    'lazyload'  => isset($this->options['optimize']['lazyLoad']) && $this->options['optimize']['lazyLoad'] ? true : false, 
                    'link'      => 'post', 
                    'size'      => $this->layout['reviews_similar_image']
                ]                 
            ],
            'price'             => $this->layout['reviews_similar_price'],
            'priceButton'       => $this->layout['reviews_similar_price_button'],
            'rating'            => $this->layout['reviews_similar_rating'],
            'summary'           => $this->layout['reviews_similar_summary'],            
            'queryArgs'         => [
                'post_type'         => 'reviews', 
                'posts_per_page'    => $this->layout['reviews_similar_number'],
                'post__in'          => $similar
            ],
            'view'              => $this->layout['reviews_similar_style']
        ] );          

        echo '<h2 class="wfr-similar-title">' . $this->layout['reviews_similar_title'] . '</h2>';
        
        $reviews->render();

    }

    /**
     * Adds additional fields to our comments field
     */
    public function commentFields() {

        if( ! is_singular('reviews') ) {
            return;
        }        

        // Ratings should be enabled
        if( ! isset($this->options['rating_visitors']) || ! $this->options['rating_visitors'] ) {
            return;
        }

        $output  = '<p class="comment-form-rating wfr-comment-form-rating">';
        $output .= '<label for="rating">' . __('Overall Rating', 'wfr') . '</label>';
        $output .= '<input type="range" id="rating" name="rating" min="1" max="' . $this->options['rating_maximum'] . '" value="' . $this->options['rating_maximum'] . '" />';
        $output .= '<span class="wfr-range-value">' . $this->options['rating_maximum'] . '</span>'; 
        $output .= '</p>';

        // Subrating - for each of our criteria
        if( $this->options['rating_criteria'] ) {
            foreach( $this->options['rating_criteria'] as $criteria ) {

                if( ! isset($criteria['name']) || ! $criteria['name'] ) {
                    continue;
                }

                $key     = sanitize_key($criteria['name']);

                $output .= '<p class="comment-form-rating wfr-comment-form-criteria-rating">';
                $output .= '<label for="' . $key . '">' . sprintf( __('%s Rating', 'wfr'), $criteria['name'] ) . '</label>';
                $output .= '<input type="range" id="' . $key . '" name="' . $key . '" min="1" max="' . $this->options['rating_maximum'] . '" value="' . $this->options['rating_maximum'] . '" data-style="' . $this->options['rating_style'] . '" />';
                $output .= '<span class="wfr-range-value">' . $this->options['rating_maximum'] . '</span>';                
                $output .= '</p>';                

            }
        }

        if( isset($this->layout['reviews_visitors_rating_reply']) && $this->layout['reviews_visitors_rating_reply'] ) {
            $output .= '<p class="comment-form-reply wfr-comment-form-reply">';
            $output .= '    <input type="checkbox" id="wfr-reply" name="wfr_reply" />';
            $output .= '    <label for="wfr-reply">' . $this->layout['reviews_visitors_rating_reply'] . '</label>';         
            $output .= '</p>';             
        }

        echo $output;

    }

    /**
     * Adds the rating text to a comment's text
     * 
     * @param string $text The text from the comment
     */
    public function commentText( $text ) {

        if( ! is_singular('reviews') ) {
            return $text;
        }

        // Ratings should be enabled
        if( ! isset($this->options['rating_visitors']) || ! $this->options['rating_visitors'] ) {
            return $text;
        }        

        $id     = get_comment_ID();
        $source = ['overall'];
        $values = [];

        // Regular rating
        $values['overall'] = get_comment_meta( $id, 'rating', true );

        // Criteria ratings
        if( isset($this->options['rating_criteria']) && is_array($this->options['rating_criteria']) ) {
            
            foreach( $this->options['rating_criteria'] as $criteria ) {

                if( ! isset($criteria['name']) || ! $criteria['name'] ) {
                    continue;
                }                
                $key            = sanitize_key($criteria['name']);
                $values[$key]   = get_comment_meta( $id, 'rating_' . $key, true );
            }

            if( count( array_filter($values) ) > 1 ) {
                $source[] = 'criteria';
            }

        }

        // If we have a rating value, we render the text
        if( $values['overall'] ) {

            // Additional structured data
            if( ! in_array('reviews', $this->noSchema) ) {
                $text  = '<div class="wfr-comment-text-body" itemprop="reviewBody">' . $text . '</div>';
            }

            $component = new Components\Rating( [
                'schema' => in_array('reviews', $this->noSchema) ? false : 'review', 
                'source' => $source, 
                'values' => $values
            ] );            
            
            $text      = $component->render(false) . $text;

            // Additional structured data
            if( ! in_array('reviews', $this->noSchema) ) {
                $author = '<span class="components-structured-data" itemprop="author" itemscope="itemscope" itemtype="https://schema.org/Person"><meta itemprop="name" content="' . get_comment_author($id) . '" /></span>';
                $text   = '<div class="wfr-comment-text" itemprop="review" itemscope="itemscope" itemtype="https://schema.org/Review">' . $author . $text . '</div>';  
            }            

        }

        return $text;

    }

    /**
     * Displays our visitor ratings scores, when enabled in the customizer
     * 
     * @param Array $args The arguments for the Footer Molecule
     */
    public function commentRating( $args ) {

        // The element should be enabled
        if( ! is_singular('reviews') || ! $this->layout['reviews_visitors_rating_component'] ) {
            return $args;
        }

        // Ratings should be enabled
        if( ! isset($this->options['rating_visitors']) || ! $this->options['rating_visitors'] ) {
            return $args;
        }          

        global $post;

        // Retrieves the various rating sources
        $source = ['overall'];
        $values = [];

        // Regular rating
        $values['overall'] = get_post_meta( $post->ID, 'visitors_rating', true );

        // Criteria ratings
        if( $this->options['rating_criteria'] ) {
            
            foreach( $this->options['rating_criteria'] as $criteria ) {

                if( ! isset($criteria['name']) || ! $criteria['name'] ) {
                    continue;
                }                
                $key            = sanitize_key($criteria['name']);
                $values[$key]   = get_post_meta( $post->ID, 'visitors_rating_' . $key, true );
            }

            if( count( array_filter($values) ) > 1 ) {
                $source[] = 'criteria';
            }

        }    

        $component  = new Components\Rating( ['class' => 'wfr-rating-average-visitors', 'schema' => false, 'source' => $source, 'values' => $values ] );
        $string     = $component->render(false); 
        $title      = $this->layout['reviews_visitors_rating_title'] ? '<h3 class="wfr-rating-average-visitors-title">' . $this->layout['reviews_visitors_rating_title'] . '</h3>' : '';
        
        // Remove the comments
        $comments = $args['atoms']['comments'];
        unset($args['atoms']['comments']);

        $args['atoms']['title']     = ['atom' => 'string', 'properties' => ['string' => $title]];
        $args['atoms']['rating']    = ['atom' => 'string', 'properties' => ['string' => $string]];
        $args['atoms']['comments']  = $comments; // But the comments back on the end
        
        return $args;

    }

    /**
     * Returns our property groups
     * 
     * @return Array $groups The array with property groups
     */
    private function propertyGroups() {

        $groups = ['properties'];

        if( $this->layout['reviews_content_criteria'] && isset($this->options['rating_criteria']) && $this->options['rating_criteria'] ) {

            foreach( $this->options['rating_criteria'] as $criteria ) {
                $groups[] = isset($criteria['key']) && $criteria['key'] ? sanitize_key($criteria['key']) : sanitize_key($criteria['name']);            
            }
            
        }
        
        return $groups;
        
    }

}