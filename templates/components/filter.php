<?php
/**
 * Displays our filter form
 * Only applies if a reviews element is near
 */
?>
<form class="wfr-filter <?php if($filter['instant']) { echo 'wfr-instant-filter'; } ?>" data-category="<?php echo $filter['category']; ?>" data-target="<?php echo $filter['target']; ?>">
    
    <?php if( $filter['sort'] ) { ?>
        <p class="wfr-filter-sort wfr-filter-field">
            <?php if( $filter['sort_label'] ) { ?> 
                <label for="sort"><?php echo $filter['sort_label']; ?></label>
            <?php } ?>
            <select name="sort" id="sort">
                <option value=""><?php _e('Select', 'wfr'); ?></option>
                <?php foreach( $filter['sortOptions'] as $option => $name ) { ?> 
                    <option value="<?php echo $option; ?>"><?php echo $name; ?></option>
                <?php } ?>
            </select>        
        </p>    
    <?php } ?>

    <?php if( $filter['search'] ) { ?>
        <p class="wfr-filter-search wfr-filter-field">
            <?php if( $filter['search_label'] ) { ?> 
                <label for="search"><?php echo $filter['search_label']; ?></label>
            <?php } ?>            
            <input name="search" id="search" type="search" placeholder="<?php echo $filter['searchPlaceholder']; ?>">      
        </p>    
    <?php } ?>    

    <?php if( $filter['price'] ) { ?>
        <p class="wfr-filter-price wfr-filter-field">
            <?php if( $filter['price_label'] ) { ?> 
                <label for="price"><?php echo $filter['price_label']; ?></label>
            <?php } ?>
            <input name="price_min" id="price" type="number" placeholder="<?php _e('min', 'wfr'); ?>" />
            <input name="price_max" type="number" placeholder="<?php _e('max', 'wfr'); ?>" />
        </p>   
    <?php } ?>

    <?php if( $filter['rating'] ) { ?>
        <p class="wfr-filter-rating wfr-filter-field">
            <label for="rating"><?php echo $filter['rating_label']; ?></label>
            <?php if( $filter['ratingStyle'] == 'smileys' ) { ?>
                <input name="rating" id="rating"  type="radio" value="frown" /><i class="fa fa-smile-o"></i>
                <input name="rating" type="radio" value="meh" /><i class="fa fa-meh-o"></i>
                <input name="rating" type="radio" value="smile" /><i class="fa fa-smile-o"></i>
            <?php } else { ?>
                <input name="rating" type="range" min="1" max="<?php echo $filter['ratingMax']; ?>" value="<?php echo $filter['rating_min']; ?>" data-style="<?php echo $filter['ratingStyle']; ?>" />
                <span class="wfr-range-value"><?php echo $filter['rating_min']; ?></span>
            <?php } ?>
        </p>
    <?php } ?> 

    <?php if( $filter['taxonomy'] && $filter['terms'] ) { ?>
        <p class="wfr-filter-taxonomy wfr-filter-field">
            <?php if( $filter['taxonomy_label'] ) { ?> 
                <label for="term"><?php echo $filter['taxonomy_label']; ?></label>
            <?php } ?>
            <select name="term" id="term">
                <option value=""><?php _e('Select', 'wfr'); ?></option>
                <?php foreach( $filter['terms'] as $id => $name ) { ?> 
                    <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                <?php } ?>
            </select>
        </p>
    <?php } ?>            

    <?php if( ! $filter['instant'] ) { ?>
        <input type="submit" value="<?php echo $filter['label']; ?>">
    <?php } ?> 

</form>