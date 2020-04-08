<?php
/**
 * Displays our price(s)
 */
if( ! $prices['prices'] ) {
    return;
} ?>
<div class="wfr-prices" >
    <?php foreach( $prices['prices'] as $price ) { ?> 
        <div class="wfr-price <?php echo $price['class']; ?>"<?php if($prices['schema']) { ?> itemprop="offers" itemscope="itemscope" itemtype="http://schema.org/Offer"<?php } ?>>
            <?php if( $price['name'] ) { ?>
                <span class="wfr-price-name"<?php if($prices['schema']) { ?>  itemprop="name"<?php } ?>>
                    <?php echo $price['name']; ?>
                </span>
            <?php } ?> 
            <span class="wfr-price-value"> 
                <?php if( $price['prefix'] ) { ?>
                    <span class="wfr-price-prefix">
                        <?php echo $price['prefix']; ?>
                    </span>
                <?php } ?>                  
                <?php if( $price['currency'] ) { ?>
                    <span class="wfr-price-currency"<?php if($prices['schema']) { ?>  itemprop="priceCurrency"<?php } ?>><?php echo trim($price['currency']); ?></span>
                <?php } ?>
                <span class="wfr-price-number"<?php if($prices['schema']) { ?>  itemprop="price"<?php } ?>>
                    <?php echo $price['value']; ?>
                </span>
                <?php if( $price['unit'] ) { ?>
                    <span class="wfr-price-unit">
                        <?php echo $price['unit']; ?>
                    </span>
                <?php } ?>
            </span>
            <?php if( $price['button'] ) { echo $price['button']; } ?>
        </div>
    <?php } ?>                                    
</div>