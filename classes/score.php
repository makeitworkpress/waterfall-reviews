<?php
/**
 * This algorithm automatically scores a review based upon the values of subattributes.
 * Scoring is hooked upon the saving of a post and examines existing values to top values in the record.
 */
namespace Waterfall_Reviews;
use Waterfall_Reviews\Base as Base;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Score extends Base {

    /**
     * Registers our hooks. Is automatically performed upon initialization, as we extend the WFR_Base class
     */
    protected function register() {

        $this->actions = [
            ['comment_post', 'saveComment', 20, 2],
            ['transition_comment_status', 'processCommentRating', 10, 3],
            ['save_post', 'score', 20, 1]
        ];

    }

    /**
     * Saves our new comment data
     *
     * @param int $id The comment id that is saved
     */
    public function saveComment( $id, $approved ) {

        // Ratings should be enabled
        if( ! isset($this->options['rating_visitors']) || ! $this->options['rating_visitors'] ) {
            return;
        }  
        
        // Visitors may disable ratings within the form
        if( isset($_POST['wfr_reply']) && $_POST['wfr_reply'] ) {
            add_comment_meta( $id, 'rating_not_counted', true );
            return;
        }        

        // If we have a rating, process it
        if( isset($_POST['rating']) ) {
            $rating = intval($_POST['rating']) > $this->options['rating_maximum'] ? intval($this->options['rating_maximum']) : intval($_POST['rating']);
            add_comment_meta( $id, 'rating', $rating );
        }

        // Add the rating for each of our criteria
        if( $this->options['rating_criteria'] ) {
            foreach( $this->options['rating_criteria'] as $criteria ) {

                if( ! isset($criteria['name']) || ! $criteria['name'] ) {
                    continue;
                }

                $key     = sanitize_key($criteria['name']);
                
                if( isset($_POST[$key]) ) {
                    $rating = intval($_POST[$key]) > $this->options['rating_maximum'] ? intval($this->options['rating_maximum']) : intval($_POST[$key]);
                    add_comment_meta( $id, 'rating_' . $key, $rating );
                }
            }
        }

        // Process the comment ratings to calculate other scores if it is immediately approved such as in logged in accounts
        if( $approved === 1 ) {
            $comment = get_comment($id);
            $this->processCommentRating( 'approved', 'unapproved', $comment );
        }

    }        
 
    /**
     * Process ratings when comments are approved
     * 
     * @param Int $id The comment id
     * @param Int $id The comment id
     * @param OBject $comment The comment object
     * @param String $status The status for the comment, either approve, trash, spam or hold
     * 
     * @return Void
     */
    public function processCommentRating( $new, $old, $comment ) {

        // Only process a rating if we are approving new comments or disapproving old ones
        $rate = false;

        if( $new == 'approved' || $old == 'approved' ) {
            $rate = true;
        }

        if( ! $rate ) {
            return;
        }        

        // Upon permanent deletion, the rating is already processed properly. Also, the comment will not exist anymore. 
        if( ! isset($comment->comment_post_ID) ) {
            return;
        }

        // Retrieve the post for the given comment
        $post  = get_post( $comment->comment_post_ID );
        
        // Only the reviews post type is benefiting from the calculation.
        if( $post->post_type != 'reviews' ) {
            return;
        }

        $count          = wp_count_comments( $comment->comment_post_ID )->approved - count( get_comments(['post_id' => $comment->comment_post_ID, 'meta_key' => 'rating_not_counted', 'meta_value' => true]) );
        $currentRating  = floatval( get_post_meta($comment->comment_post_ID, 'visitors_rating', true) ); 
        $visitorRating  = intval( get_comment_meta($comment->comment_ID, 'rating', true) );

        // A newly approved comment influencing the overall rating
        if( $new == 'approved' && $visitorRating ) {           
            $newRating = ( (($count - 1) * $currentRating ) + $visitorRating ) / $count;
        // A previously approved comment is spammed, disapproved or removed
        } elseif( $old == 'approved' && $visitorRating && $count ) {
            $newRating = ( (($count + 1) * $currentRating ) - $visitorRating ) / $count;
        } 

        // Check if we end up with a number. For example, if a first comment is the rating is 0
        if( ! isset($newRating) || is_nan($newRating) ) {
            $newRating = 0;
        }
    
        // Update our post meta, so that the rating for all visitors are tied to a post
        update_post_meta( $comment->comment_post_ID, 'visitors_rating', round($newRating, 1) );

        // If only visitors calculate the rating for a review, it is updated here
        if( $this->options['rating_calculation'] == 'visitors' ) {
            update_post_meta( $comment->comment_post_ID, 'rating', round($newRating, 1) );
        }

        // Update the criteria ratings for visitors
        if( $this->options['rating_criteria'] && is_array($this->options['rating_criteria']) ) {

            foreach( $this->options['rating_criteria'] as $criteria ) {

                if( ! isset($criteria['name']) || ! $criteria['name'] ) {
                    continue;
                }

                $key    = sanitize_key($criteria['name']);
                $cR     = get_post_meta( $comment->comment_post_ID, 'visitors_rating_' . $key, true );
                $vR     = intval( get_comment_meta($comment->comment_ID, 'rating_' . $key, true) );
                
                if( $new == 'approved' && $vR ) {  
                    $nR = ( (($count - 1) * $cR ) + $vR ) / $count;
                } elseif( $old == 'approved' && $vR ) {
                    $nR = ( (($count + 1) * $cR ) - $vR ) / $count;
                }

                if( ! isset($nR) || is_nan($nR) ) {
                    $nR = 0;
                }

                update_post_meta( $comment->comment_post_ID, 'visitors_rating_' . $key, round($nR, 1) );

                // Visitors influence all of the criteria
                if( $this->options['rating_calculation'] == 'visitors' ) {
                    update_post_meta( $comment->comment_post_ID,  $key . '_rating', round($nR, 1) );    
                }

            }

        }            

        // Update the general rating if visitors may influence this.
        $this->weightVisitors( $comment->comment_post_ID );
        
        // Update the rating based on criteria if users influence as a criteria.
        if( $this->options['rating_visitors_influence'] == 'criteria' ) {
            $this->weightCriteria( $comment->comment_post_ID, false );
        }

    }

    /**
     * Calculates our rating scores for each of the criteria
     * Also sets some default values that are needed for a proper functioning of the site
     * 
     * @param Int $id The post ID for the post being saved
     */
    public function score( $id ) {

        $type = get_post_type($id);

        if( $type != 'reviews' ) {
            return;
        }

        /**
         * Automatically calculates our scores when these are set to automatic. Criteria and ratings are required.
         */
        $this->weightCriteria( $id );        

        /** 
         * Influence the rating if visitors may have influence on the overall rating
         */
        $this->weightVisitors( $id );     

        /**
         * Calculate and set the highest values
         * The highest values are stored in the options table, and may be used for easy reference
         * @todo Add additional factors, such as properties and criteria
         */
        $factors    = ['prices', 'rating'];
        $options    = get_option( 'wfr_review_maxima' );
        $newOptions = $options;

        foreach( $factors as $factor ) {

            if( ! isset($_POST[$factor]) ) {
                continue;
            }

            // Conditional calculating for prices;
            if( $factor == 'prices' ) {

                // First, we should have prices at all
                if( isset($_POST['prices'][0]['price']) ) {

                    

                    // Check if we have a manual best price
                    foreach( $_POST['prices'] as $price ) {
                        if( isset($price['best']) && $price['best'] && $price['price'] ) {
                            $best = $price['price'];
                        }
                    }

                    // No manual best price has been set-up, so we take the lowest
                    if( ! isset($best) ) {
                        usort( $_POST['prices'], function($a, $b) { return strnatcmp($a['price'], $b['price']); } );
                        $best = $_POST['prices'][0]['price'];
                    }

                    // Add the highest price
                    if( $best ) {
                        update_post_meta( $id, 'price', floatval($best) );
                        $newOptions['price'] = floatval( $best );
                    } 

                    
                }

            // Conditional calculating other factors    
            } else {            

                // Save highest factor
                if( ! isset($options[$factor]) || (isset($options[$factor]) && $_POST[$factor] > $options[$factor]) ) {
                    $newOptions[$factor] = floatval($_POST[$factor]);    
                }

            }

        }

        // If our options have changed, update them.
        if( $options != $newOptions ) {
            update_option( 'wfr_review_maxima', $options );
        }

    }


    /**
     * Helper function to update the overal ratings, when this may be influenced by visitors
     * 
     * @param Int $id The post id
     */
    private function weightVisitors( $id ) {

        // The theme settings should allow for the influence of visitors as a part of the overall rating. If only visitors rate, we return.
        if( $this->options['rating_visitors_influence'] != 'overall' || $this->options['rating_calculation'] == 'visitors' ) {
            return;
        }

        // Automatic calculations should be allowed
        if( get_post_meta($id, 'disable_calculation', true) == true ) {
            return;
        }

        $rating         = get_post_meta($id, 'visitors_rating', true);

        // Visitors should have an average rating
        if( ! $rating ) {
            return;
        }

        $ratio          = isset( $this->options['rating_weight_ratio'] ) ? floatval($this->options['rating_weight_ratio']) : 1;
        $overallRating  = (get_post_meta($id, 'rating', true) + $rating * $ratio)/(1 + $ratio);

        update_post_meta( $id, 'rating', round($overallRating, 1) );

    }

    /**
     * Helper function to calculate the scores of criteria automatically, and their influence on the overall score
     * 
     * @param Int $id The post id
     * @param Boolean $post Whether we are weighting criteria at post level or at comment level
     * @return void
     */
    private function weightCriteria( $id, $post = true ) {

        // Rating criteria should be defined
        if( ! $this->options['rating_criteria'] || ! is_array($this->options['rating_criteria']) ) {
            return;
        }
        
        // Rating calculation should be automatic
        if( $this->options['rating_calculation'] != 'automatic' ) {
            return;
        }
        
        // Automatic calculation should not be disabled
        if( get_post_meta($id, 'disable_calculation', true) == true ) { 
            return;
        }
        
        // Automatically calculates the overall rating based on the ratings for the several criteria.
        $ratio      = 0;
        $rating     = 0;

        foreach( $this->options['rating_criteria'] as $criteria ) {

            if( ! isset($criteria['name']) || ! $criteria['name'] ) {
                continue;
            }

            $key    = sanitize_key($criteria['name']);
            $score  = isset($_POST[$key . '_rating']) && $post ? floatval($_POST[$key . '_rating']) : get_post_meta( $id, $key . '_rating', true);
            $weight = $criteria['weight'] ? floatval($criteria['weight']) : 1;

            if( $score ) {
                $rating += $score * $weight;
                $ratio  += $weight; 
            }

        }

        // Visitors rating act as one of the criteria and is added to the other criteria
        if( $this->options['rating_visitors_influence'] == 'criteria' ) {

            $score  = get_post_meta( $id, 'visitors_rating', true );
            $weight = isset($this->options['rating_weight_ratio']) ? floatval($this->options['rating_weight_ratio']) : 1;

            if( $score ) {
                $rating += $score * $weight;
                $ratio  += $weight;
            }

        } 

        if( $rating && $ratio ) {
            $rating = $rating/$ratio;
            update_post_meta( $id, 'rating', round($rating, 1) );
        }        
   
    }    

}