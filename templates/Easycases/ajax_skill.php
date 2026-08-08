<input type="hidden" id="skill_all">
<input type="text" placeholder="<?php echo __('Search');?>" class="searchType" onkeyup="searchFilterItems(this);" />
<?php
$m=0;
if(isset($skillsArr) && !empty($skillsArr))
{
    $m=0;
    $h = 0;
    foreach($skillsArr as $skill)
    {
        $m++;
        $skillId = $skill['id'];
        $skillName = $skill['name'];
        ?>
        <li class="li_check_radio" <?php if($m > 5){$h++;?> id="hidSkill_<?php echo $h; ?>"  <?php }?>>
            <div class="checkbox">
                <label>
                    <input type="checkbox" class="skill_type_cls" data-id="<?php echo $m; ?>" id="Skill_<?php echo $m ?>" onClick="checkboxSkill('Skill_<?php echo $m; ?>','check');filterRequest('skill');" /> <?php echo $this->Format->formatText($skillName); ?>
                </label>
                <input type="hidden" name="Skillids_<?php echo $m; ?>" id="Skillids_<?php echo $m; ?>" value="<?php echo $skillId; ?>" readonly="true">
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
        <span class="no-data-found"><?php echo __('No Skills Created.'); ?></span>
    </li>
<?php } ?>
<input type="hidden" id="totSkill" value="<?php echo $m; ?>" readonly="true"/>
