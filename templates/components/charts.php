<?php
/**
 * Displays a selector for charts
 */
?>
<div class="wfr-charts" <?php if($charts['id']) { echo 'id="' . $charts['id'] . '"'; } ?>>
    <?php if( $charts['selector'] ) { ?> 
        <form class="wfr-chart-selector" data-category="<?php echo $charts['category']; ?>" data-tag="<?php echo $charts['tag']; ?>">
            <label><?php echo $charts['selector']; ?></label>
            <select name="wfr_chart_data_source">
                <option value=""><?php echo $charts['default']; ?></option>
                <?php foreach( $charts['selectorGroups'] as $group ) { ?> 
                    <optgroup label="<?php echo $group['label']; ?>">
                        <?php foreach( $group['options'] as $value => $label ) { ?>
                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                        <?php } ?>
                    </optgroup>
                <?php } ?>
            </select>
        </form>
    <?php } ?>
    <canvas class="wfr-charts-chart"></canvas>
    <?php if( $charts['weight'] ) { ?> 
        <form class="wfr-charts-weight">
            <button class="wfr-charts-normal"><?php echo $charts['normal'] ?></button>
            <button class="wfr-charts-weighted"><?php echo $charts['weighted'] ?></button>
        </form>
    <?php } ?>
</div>