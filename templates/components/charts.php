<?php
/**
 * Displays a chart or a selectable chart
 */
?>
<div class="wfr-charts<?php echo $charts['class']; ?>" <?php if($charts['id']) { echo 'id="' . $charts['id'] . '"'; } ?>>
    <?php if( $charts['form'] ) { ?> 
        <form class="wfr-chart-selector" data-categories="<?php esc_attr_e($charts['categories']); ?>" data-tags="<?php esc_attr_e($charts['tags']); ?>" data-include="<?php esc_attr_e($charts['include']); ?>">
            <label><?php echo $charts['label']; ?></label>
            <select name="wfr_chart_data_source">
                <option value=""><?php echo $charts['select']; ?></option>
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
    <div class="wfr-charts-wrapper">
        <canvas class="wfr-charts-chart"></canvas>
    </div>
    <?php if( $charts['weight'] ) { ?> 
        <form class="wfr-charts-weight">
            <button class="wfr-charts-normal atom atom-button atom-button-small active"><?php echo $charts['normal'] ?></button>
            <button class="wfr-charts-weighted atom atom-button atom-button-small"><?php echo $charts['weighted'] ?></button>
        </form>
    <?php } ?>
</div>