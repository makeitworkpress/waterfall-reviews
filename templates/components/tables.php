<?php
/**
 * Displays our price(s)
 */

if( ! $tables['fields'] && $tables['load'] ) {
    return;
} ?>

<div class="wfr-tables atom-<?php echo $tables['view']; ?>">

    <?php if( $tables['form'] && count($tables['reviews']) > 1  ) { ?>
        <form class="wfr-tables-form" <?php foreach($tables['data'] as $key => $value ) { if($value) { echo 'data-' . $key . '="' . $value . '"'; } } ?>>
            <label><?php echo $tables['label']; ?></label>
            <ul>
                <?php foreach( $tables['reviews'] as $id => $review ) { ?>
                    <li data-target="<?php echo $id; ?>">
                        <?php echo $review['image']; ?>
                        <p><?php echo $review['title']; ?></p>
                    </li>
                <?php } ?>
            </ul>
        </form>
    <?php } ?>

    <div class="wfr-tables-view">

        <div class="wfr-load-ellipsis">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>    
        
        <?php if( $tables['fields'] ) { ?>

            <?php if( $tables['view'] == 'tabs' ) { ?>
                
                <ul class="atom-tabs-navigation">
                    <?php foreach( $tables['fields'] as $key => $group ) { ?>
                        <li>
                            <a class="atom-tab<?php if( array_search($key, array_keys($tables['fields'])) == 0 ) { ?> active<?php } ?>" href="#" data-target="<?php echo $key; ?>">
                                <?php echo $group['label']; ?>    
                            </a>
                        </li>
                    <?php 
                        } 
                    ?>
                </ul>

                <div class="atom-tabs-content">
                
                    <?php foreach( $tables['fields'] as $key => $group ) { ?>

                        <section class="atom-tab<?php if( array_search($key, array_keys($tables['fields'])) == 0 ) { ?> active<?php } ?>" data-id="<?php echo $key; ?>">

                            <?php if( $group['fields'] ) { ?>

                                <div class="wfr-tables-wrapper">
                                    <table class="wfr-tables-table">

                                        <?php if( count($tables['reviews']) > 1 && $tables['title'] ) { ?>
                                            <tr>
                                                <th></th>
                                                <?php foreach( $tables['reviews'] as $review ) { ?>
                                                    <th>
                                                        <a href="<?php echo $review['link']; ?>" title="<?php echo $review['title']; ?>"><?php echo $review['title']; ?></a>
                                                        <?php if($review['price']) { echo $review['price']; } ?>
                                                    </th>
                                                <?php } ?>
                                            </tr>
                                        <?php } ?>

                                        <?php foreach( $group['fields'] as $field ) { ?>
                                            <tr>
                                                <th><?php echo $field['label']; ?></th>
                                                <?php foreach( $field['values'] as $id => $value ) { ?>
                                                    <td><?php echo $value; ?></td>
                                                <?php } ?>
                                            </tr>
                                        <?php } ?>

                                    </table>
                                </div>    

                            <?php } ?>

                        </section>

                    <?php } ?>

                </div>         

            <?php } elseif( $tables['view'] == 'table') { ?>
                <div class="wfr-tables-wrapper">
                    <table class="wfr-tables-table">
                        <?php if( $tables['title'] ) { ?>
                            <tr>
                                <th></th>
                                <?php foreach( $tables['reviews'] as $review ) { ?>
                                    <th class="wfr-tables-review">
                                        <h4>
                                            <a href="<?php echo $review['link']; ?>" title="<?php echo $review['title']; ?>"><?php echo $review['title']; ?></a>
                                        </h4>
                                        <?php if(isset($review['price']) && $review['price']) { echo $review['price']; } ?>
                                    </th>
                                <?php } ?>
                            </tr>
                        <?php } ?>     
                        <?php foreach( $tables['fields'] as $key => $group ) { ?>
                            <tr>
                                <th class="wfr-tables-group-label" colspan="<?php echo count($tables['reviews']) + 1; ?>"><?php echo $group['label']; ?></th>
                            </tr>
                            <?php foreach( $group['fields'] as $field ) { ?>
                                <tr>
                                    <th><?php echo $field['label']; ?></th>
                                    <?php foreach( $field['values'] as $id => $value ) { ?>
                                        <td><?php echo $value; ?></td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>                
                        <?php } ?>        
                    </table>
                </div>
            <?php } ?>

        <?php } ?>

    </div><!-- .wfr-tables-view -->

</div>