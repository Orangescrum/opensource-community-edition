<input type="hidden" id="types_all">
<input type="text" placeholder="Search" class="searchType" onkeyup="searchFilterItems(this);" />
<?php
$m=0;
if(isset($memArr))
{
    $m=0;
    $totMemCase = 0;
    $h = 0;
    foreach($memArr as $memVal)
    {   $members=explode("-",$CookieMem);
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
        <li class="li_check_radio" <?php if($m > 5){$h++;?> id="hidMem_<?php echo $h; ?>"  <?php }?> > <!--style="display:none;"-->
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="mems_<?php echo $m; ?>" class="member<?php echo $memId; ?>" onClick="checkboxMems('mems_<?php echo $m; ?>','check');filterRequest('mems');"  style="cursor:pointer;" <?php if (in_array($memId, $members) ) { echo "checked"; } ?>/> <?php echo $this->Format->formatText($memName); ?>
                </label>
                <input type="hidden" name="memids_<?php echo $m; ?>" id="memids_<?php echo $m; ?>" value="<?php echo $memId; ?>" readonly="true">
            </div>
        </li>
        <?php
    } ?>

<?php } ?>
<input type="hidden" id="totMemId" value="<?php echo $m; ?>" readonly="true"/>
