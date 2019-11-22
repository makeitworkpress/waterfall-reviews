<?php
/**
 * Displays a rating component
 */
if( ! isset($rating['ratings']) ) {
    return;
} ?>

<div class="wfr-rating <?php echo $rating['class']; ?>">
    <?php if( ! $rating['ratings'] ) { ?>
        <p><?php echo $rating['nothing'] ?></p>
    <?php } else { ?>
        <?php foreach($rating['ratings'] as $element ) { ?>   
            <div class="wfr-rating-source wfr-source-<?php echo $element['source']; ?>">
                <div class="wfr-rating-element wfr-style-<?php echo $rating['style']; ?>" <?php if( $element['source'] == 'overall') { echo $rating['schema']; } ?>>
                    
                    <?php if( $element['name'] && $rating['names'] ) { ?>
                        <div class="wfr-rating-name">
                            <?php echo $element['name']; ?>
                        </div>
                    <?php } ?>

                    <div class="wfr-rating-display">
                        <?php echo $element['display']; ?>
                    </div>

                    <?php if( $element['source'] == 'overall' && $rating['schema'] ) { ?>

                        <?php if( $element['item'] && strpos($rating['schema'], 'aggregateRating') ) { ?>
                            <meta itemprop="itemReviewed" content="<?php echo $element['item']; ?>" />
                        <?php } ?> 

                        <?php if(strpos( $rating['schema'], 'aggregateRating') ) { ?>  
                            <meta itemprop="ratingCount" content="<?php echo $element['count']; ?>" />        
                            <meta itemprop="reviewCount" content="<?php echo $element['count']; ?>" />        
                        <?php } ?>

                        <meta itemprop="ratingValue" content="<?php echo $element['value']; ?>" />
                        <meta itemprop="bestRating" content="<?php echo $rating['max']; ?>" />
                        <meta itemprop="worstRating" content="<?php echo $rating['min']; ?>" />
                        
                    <?php } ?>

                </div>
                   
            </div>
        <?php } ?>
    <?php } ?>
</div>