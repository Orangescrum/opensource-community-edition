<input type="hidden" id="features_all">
<input type="text" placeholder="<?php echo __('Search');?>" class="searchType" onkeyup="searchFilterItems(this);" />
<?php
$m=0;
if(isset($featuresArr) && !empty($featuresArr))
{
    $m=0;
    $h = 0;
    foreach($featuresArr as $feature)
    {
        $m++;
        $featureId = $feature['Easycase']['id'];
        $featureName = $feature['Easycase']['title'];
        ?>
        <li class="li_check_radio" <?php if($m > 5){$h++;?> id="hidFeature_<?php echo $h; ?>"  <?php }?>>
            <div class="checkbox">
                <label>
                    <input type="checkbox" class="features_type_cls" data-id="<?php echo $m; ?>" id="Feature_<?php echo $m ?>" onClick="checkboxFeatures('Feature_<?php echo $m; ?>','check');filterRequest('features');" /> <?php echo $this->Format->formatText($featureName); ?>
                </label>
                <input type="hidden" name="Featureids_<?php echo $m; ?>" id="Featureids_<?php echo $m; ?>" value="<?php echo $featureId; ?>" readonly="true">
            </div>
        </li>
        <?php
    }
    if($h != 0)
    {
        ?>
        <?php
    } ?>
<?php }else{ ?>
    <li style="color: #e47a7a;font-size: 13px;text-align: center;padding: 5px;">
        <span class="no-data-found"><?php echo __('No Features Created.'); ?></span>
    </li>
<?php } ?>
<input type="hidden" id="totFeatures" value="<?php echo $m; ?>" readonly="true"/>
