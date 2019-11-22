<?php
/**
 * Displays our price(s)
 */

if( ! isset($properties['tabs']) ) {
    return;
} ?>
<div class="wfr-properties atom-<?php echo $properties['class']; ?>">

    <?php if( $properties['class'] == 'tabs' ) { ?>
        <ul class="atom-tabs-navigation">
            <?php 
                $count = 0;
                foreach( $properties['tabs'] as $key => $tab ) { 
                    $active = $count === 0 ? ' active' : 'inactive';
                    $count++;
                    
            ?>
                <li>
                    <a class="atom-tab <?php echo $active; ?>" href="#" data-target="<?php echo $key; ?>">
                        <?php echo $tab['title']; ?>    
                    </a>
                </li>
            <?php 
                } 
            ?>
        </ul>
    <?php } ?>
    <div class="atom-<?php echo $properties['class']; ?>-content">
        
        <?php 
            $count = 0;
            foreach( $properties['tabs'] as $key => $tab ) { 
                $active = $count === 0 ? ' active' : '';
                $count++;
        ?>
            <section class="<?php if( $properties['class'] == 'tabs' ) { ?>atom-tab <?php echo $active; ?> <?php } ?>" data-id="<?php echo $key; ?>">
                <?php if( $properties['class'] == 'list' ) { ?>
                    <h3><?php echo $tab['title']; ?> </h3>
                <?php } ?>

                <?php if( $tab['content'] ) { ?>
                    <table>
                    <?php foreach( $tab['content'] as $content ) { ?>
                        <tr>
                            <th><?php echo $content['label']; ?></th>
                            <td><?php echo $content['value']; ?></td>
                        </tr>
                    <?php } ?>
                    </table>
                <?php } ?>

            </section>

        <?php 
            } 
        ?>

    </div>                                
</div>