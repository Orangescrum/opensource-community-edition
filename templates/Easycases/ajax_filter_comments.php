<input type="hidden" id="types_all">
<input type="text" placeholder="<?php echo __('Search');?>" class="searchType" onkeyup="searchFilterItems(this);" />
<?php
$m=0;
if(isset($comArr))
{
    $m=0;
    $totComCase = 0;
    $h = 0;
    foreach($comArr as $memVal)
    {   $members= explode("-",$CookieMem);
        $m++;
        $mem = $memVal['User'];
        $memId = $mem['id'];
        $memName = $mem['name'];
        if(!empty($mem['last_name'])){
            $memName .= ' '.$mem['last_name'];
        }
        $memLogin = $mem['dt_last_login'];
        $shortname =  $mem['short_name'];
        ?>
        <li class="li_check_radio" <?php if($m > 5){$h++;?> id="hidCom_<?php echo $h; ?>"  <?php }?> > <!--style="display:none;"-->
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="coms_<?php echo $m; ?>" class="comment<?php echo $memId; ?>" onClick="checkboxComs('coms_<?php echo $m; ?>','check');filterRequest('coms');"  style="cursor:pointer;" <?php if (in_array($memId, $members) ) { echo "checked"; } ?>/> <?php echo $this->Format->formatText($memName); ?>
                </label>
                <input type="hidden" name="comids_<?php echo $m; ?>" id="comids_<?php echo $m; ?>" value="<?php echo $memId; ?>" readonly="true">
            </div>
        </li>
        <?php
    }
    if($h != 0){?>

        <?php
    } ?>

<?php } ?>
<input type="hidden" id="totComId" value="<?php echo $m; ?>" readonly="true"/>
