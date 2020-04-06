<?php
/**
 * Displays compare tables
 */
?>
<div class="wfr-tables">
    <?php if( $tables['form'] ) { ?>

    <?php } ?>
    <?php if( $tables['values'] ) { ?>
        <table class="wfr-tables-table">
            <tr>
                <th></th>
                <?php foreach( $tables['reviews'] as $review ) { ?>
                    <th><?php echo $review['title']; ?></th>
                <?php } ?>
            </tr>
            <?php foreach( $tables['groups'] as $g => $group ) { ?>
                <?php if( $group['label'] ) { ?>
                    <tr>
                        <th class="wfr-tables-group-label"><?php echo $group['label']; ?></th>
                        <?php foreach( $tables['reviews'] as $review ) { ?><th></th><?php } ?>
                    </tr>
                <?php } ?>
                <?php foreach( $group['fields'] as $key => $field ) { ?>
                    <tr>
                        <td class="wfr-tables-field-label">
                            <?php echo $field['label'] ?>
                            <?php if($tables['weight'] && isset($field['type']) && $field['type'] == 'number' ) { ?>
                                <span class="wfr-tables-weighted-label"><?php echo $tables['weighted']; ?></span>
                            <?php } ?>
                        </td>    
                        <?php foreach( $tables['reviews'] as $id => $review ) { ?>
                            <td>
                                <?php if( isset($tables['values'][$id][$key][0]['plan']) && isset($tables['values'][$id][$key][0]['value']) ) { ?>

                                    <?php foreach( $tables['values'][$id][$key] as $plan ) { ?>
                                        
                                        <?php if( ! $plan['value']['normal'] ) { continue; } ?>

                                        <?php echo $plan['value']['normal']; ?><span class="wfr-tables-plain-details"> - <?php echo $plan['plan']; ?></span>
                                        <?php if($tables['weight'] && $plan['value']['weighted'] ) { ?>
                                            <span class="wfr-tables-weighted-value"><?php echo $plan['value']['weighted']; ?></span>
                                        <?php } ?>

                                    <?php } ?> 

                                <?php } elseif( isset($tables['values'][$id][$key]['normal']) && $tables['values'][$id][$key]['normal'] ) { ?>

                                    <?php echo $tables['values'][$id][$key]['normal']; ?>
                                    <?php if($tables['weight'] && $tables['values'][$id][$key]['weighted'] ) { ?>
                                        <span class="wfr-tables-weighted-value"><?php echo $tables['values'][$id][$key]['weighted']; ?></span>
                                    <?php } ?>

                                <?php } ?>
                            </td>
                        <?php } ?>
                        
                    </tr>
                <?php } ?>
            <?php } ?>
        </table>
    <?php } ?>
    
</div>