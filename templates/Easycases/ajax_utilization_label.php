<!--li class="nohover"-->
<input type="text" name="search_label" id="label_utilization_search" placeholder="Search" class="searchType"
    onkeyup="searchFilterItems(this);"
    onfocus="$('#Label_more').find('a').trigger('click');$('#Label_more,#Label_hide').hide();" />
<!--/li-->
<?php
if (($_COOKIE['utilization_label_filter'] ?? '') != '' && ($_COOKIE['utilization_label_filter'] ?? '') != 'all') {
    $archive_label_fil = explode("-", ($_COOKIE['utilization_label_filter'] ?? ''));
}
$m = 0;
if (isset($list)) {
    $m = 0;
    $h = 0;
    foreach ($list as $li) {
        $m++;
        $labelId = $li['id'];
        $labalName = $li['lbl_title'];
        ?>
        <li class="li_check_radio" <?php if ($m > 5) {
            $h++; ?> id="hidlabelid_<?php echo $h; ?>" style="display:blcok;" <?php } ?>>
            <div class="checkbox">
                <label class="ellipsis">
                    <input class="utilization-label" type="checkbox" id="labelid_<?php echo $labelId; ?>"
                        onClick="utilization_label('<?php echo $labelId; ?>','check');" data-id="<?php echo $labelId; ?>" <?php if (in_array($labelId, $archive_label_fil ?? [])) {
                                  echo "checked";
                              } ?> />
                    &nbsp;<span title="<?php echo $labalName; ?>"><?php echo $this->Format->shortLength($labalName, 15); ?></span>
                    <input type="hidden" name="labelids_<?php echo $labelId; ?>" id="labelids_<?php echo $labelId; ?>"
                        value="<?php echo $labelId; ?>" readonly="true">
                </label>
            </div>
        </li>
    <?php }
    $h = 0;
    if ($h != 0) {
        ?>
        <div class="slide_menu_div1 more-hide-div">
            <div class="more" align="right" id="Label_more">
                <a href="javascript:jsVoid();"
                    onClick="moreLeftNav('Label_more','Label_hide','<?php echo $h; ?>','hidlabelid_',event)"><?php echo __('more'); ?>...</a>
            </div>
            <div class="more" align="right" id="Label_hide" style="display:none;">
                <a href="javascript:jsVoid();"
                    onClick="hideLeftNav('Label_more','label_hide','<?php echo $h; ?>','hidlabelid_',event)"><?php echo __('hide'); ?>...</a>
            </div>
        </div>
        <?php
    } ?>
<?php } ?>