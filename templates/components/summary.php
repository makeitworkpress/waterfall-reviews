<?php
/**
 * Displays our review summary
 */

if( ! $summary['description'] && ! $summary['advantages'] && ! $summary['disadvantages'] ) {
    return;
} ?>
<div class="wfr-summary" <?php if($summary['schema']) { ?>itemprop="review" itemscope="itemscope" itemtype="http://schema.org/Review"<?php } ?>>

    <?php if( $summary['author'] ) { ?>
        <meta itemprop="author" content="<?php echo $summary['author']; ?>" />
    <?php } ?>
    
    <?php if( $summary['description'] ) { ?>
    <p class="wfr-summary-description" <?php if($summary['schema']) { ?>itemprop="description"<?php } ?>>
            <?php echo $summary['description']; ?>
        </p>
    <?php } ?>

    <?php if( $summary['advantages'] || $summary['disadvantages'] ) { ?>
        <div class="wfr-summary-details">
            <?php foreach( ['advantages', 'disadvantages'] as $aspect ) { ?>
                
                <?php if( $summary[$aspect] ) { ?>
                    <div class="summary-column">
                        <?php if( $summary[$aspect . '_title'] ) { ?>
                            <h4><?php echo $summary[$aspect . '_title']; ?></h4>
                        <?php } ?>

                        <ul class="<?php echo $aspect; ?>">
                            <?php foreach( $summary[$aspect] as $item ) { ?>
                                <li>
                                    <i class="fa fa-<?php echo $aspect == 'advantages' ? 'check' : 'times'; ?>"></i>
                                    <?php echo $item; ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>            
                <?php } ?>

            <?php } ?>
        </div>
    <?php } ?>

</div>