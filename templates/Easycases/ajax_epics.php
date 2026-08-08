<input type="hidden" id="epics_all">
<input type="text" placeholder="<?php echo __('Search');?>" class="searchType" onkeyup="searchFilterItems(this);" />
<?php
$m=0;
if(isset($epicsArr) && !empty($epicsArr))
{
    $m=0;
    $h = 0;
    foreach($epicsArr as $epic)
    {
        $m++;
        $epicId = $epic['Easycase']['id'];
        $epicName = $epic['Easycase']['title'];
        ?>
        <li class="li_check_radio" <?php if($m > 5){$h++;?> id="hidEpic_<?php echo $h; ?>"  <?php }?>>
            <div class="checkbox">
                <label>
                    <input type="checkbox" class="epics_type_cls" data-id="<?php echo $m; ?>" id="Epic_<?php echo $m ?>" onClick="checkboxEpics('Epic_<?php echo $m; ?>','check');filterRequest('epics');" /> <?php echo $this->Format->formatText($epicName); ?>
                </label>
                <input type="hidden" name="Epicids_<?php echo $m; ?>" id="Epicids_<?php echo $m; ?>" value="<?php echo $epicId; ?>" readonly="true">
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
        <span class="no-data-found"><?php echo __('No Epics Created.'); ?></span>
    </li>
<?php } ?>
<input type="hidden" id="totEpics" value="<?php echo $m; ?>" readonly="true"/>
