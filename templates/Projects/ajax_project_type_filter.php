<input type="text" name="search_user" id="" placeholder="<?php echo __('Search'); ?>" class="searchType" onkeyup="searchFilterItems(this);" autocomplete="off" />
<input type="hidden" id="project_type_all">
<?php
$m = 0;
$str = '';
if(!isset($page)){
    if (isset($diy_list) && !empty($diy_list)) {
        $m = 0;
        $h = 0;
        $newArr = array();
        foreach ($diy_list as $diyKey => $diyVal) {
            $m++;
            $diyId = $diyKey;
            $diyTitle = $diyVal;
            array_push($newArr,$diyId);
            ?>

            <li>
                <a href="javascript:void(0);">
                    <div class="slide_menu_div1 ellipsis-view">
                        <input type="checkbox" id="dprojectType_<?php echo $diyId; ?>" onClick="checkboxProjectType('dprojectType_<?php echo $diyId; ?>', 'check','<?php echo $diyId; ?>','<?php echo $this->Format->formatText($diyTitle); ?>');"/>
                        <font onClick="checkboxProjectType('dprojectType_<?php echo $diyId; ?>', 'text','<?php echo $diyId; ?>','<?php echo $this->Format->formatText($diyTitle); ?>');"   title='<?php echo $this->Format->formatText($diyTitle); ?>'>
                            &nbsp;<?php echo $this->Format->formatText($diyTitle); ?></font>
                        <div style="margin:0;color:#999999;line-height:16px;padding-left:20px;font-size:11px;">
                        </div>
                        <input type="hidden" name="Ttypids_<?php echo $diyId; ?>" id="Ttypids_<?php echo $diyId; ?>" value="<?php echo $diyId; ?>" readonly="true">
                    </div>
                </a>
            </li>
            <?php
        }
        $str = json_encode($newArr);
        if ($h != 0) {
            ?>
        <?php }
        ?>
    <?php } else { ?>
        <li><a href="javascript:void(0);">
                <div class="slide_menu_div1 ellipsis-view">
                    <font title='<?php echo __("Sorry, No Project Type");?>'>
                        &nbsp;<?php echo __('No Project Type'); ?> </font>
                    <div style="margin:0;color:#999999;line-height:16px;padding-left:20px;font-size:11px;">
                    </div>
                </div>
            </a></li>
    <?php } ?>
    <input type="hidden" id="totPtypId" value="<?php echo $str; ?>" readonly="true"/>
<?php } else if(CONTROLLER == 'projects' && isset($page)){
    if (isset($diy_new_list) && !empty($diy_new_list)) {
        $m = 0;
        $h = 0;
        $newArr = array();
        foreach ($diy_new_list as $diyKey=>$diyVal) {
            $m++;
            $diyId = $diyKey;
            $diyTitle = $diyVal;
            array_push($newArr,$diyId);
            ?>

        <li style="display: block;">
                <a href="javascript:void(0);">
                    <div class="slide_menu_div1 ellipsis-view">
                        <input type="checkbox" id="dprojectmanageType_<?php echo $diyId; ?>" onClick="checkboxProjectType('dprojectmanageType_<?php echo $diyId; ?>', 'check','<?php echo $diyId; ?>','<?php echo $this->Format->formatText($diyTitle); ?>');"/>
                        <font onClick="checkboxProjectType('dprojectmanageType_<?php echo $diyId; ?>', 'text','<?php echo $diyId; ?>','<?php echo $this->Format->formatText($diyTitle); ?>');"   title='<?php echo $this->Format->formatText($diyTitle); ?>'>
                            &nbsp;<?php echo $this->Format->formatText($diyTitle); ?></font>
                        <div style="margin:0;color:#999999;line-height:16px;padding-left:20px;font-size:11px;">
                        </div>
                        <input type="hidden" name="Ttypids_<?php echo $diyId; ?>" id="Ttypids_<?php echo $diyId; ?>" value="<?php echo $diyId; ?>" readonly="true">
                    </div>
                </a>
            </li>
            <?php
        }
        $str = json_encode($newArr);
        if ($h != 0) {
            ?>
        <?php }
        ?>
    <?php } else { ?>
        <li><a href="javascript:void(0);">
                <div class="slide_menu_div1 ellipsis-view">
                    <font title='<?php echo __("Sorry, No Project Type");?>'>
                        &nbsp;<?php echo __('No Project Type'); ?> </font>
                    <div style="margin:0;color:#999999;line-height:16px;padding-left:20px;font-size:11px;">
                    </div>
                </div>
            </a></li>
    <?php } ?>
    <input type="hidden" id="totPmanagetypId" value="<?php echo $str; ?>" readonly="true"/>
<?php }  ?>

