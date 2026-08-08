<input type="hidden" id="assignTo_all">
<input type="text" placeholder="<?php echo __('Search');?>" class="searchType" onkeyup="searchFilterItems(this);" />
<li class="li_check_radio" id="unassigned">
    <div class="checkbox">
        <label>
            <input type="checkbox" id="unassgn" class="assignto0" <?php $last = [];
            if(in_array('unassigned', $last?:[])){ echo "checked"; } ?>onclick="checkboxAsns('unassgn','check');filterRequest('assignto');" <?php if($_COOKIE['ASSIGNTO'] ?? null == "unassigned" || in_array("unassigned", $last?:[])){ echo "checked"; } ?> /> Unassigned
        </label>
        <input type="hidden" name="Asnids_0" id="Asnids_0" value="0" readonly="true">
    </div>
</li>
<?php
$m=0;
if(isset($asnArr))
{
    $m=0;
    $totAsnCase = 0;
    $h = 0;
    foreach($asnArr as $AsnVal)
    {   $Asnbers=explode("-",$CookieAsn ?? '');
        $m++;
        $Asn = $AsnVal['User'];
        $AsnId = $Asn['id'];
        $AsnName = $Asn['name'];
        if(!empty($Asn['last_name'])){
            $AsnName .= ' '.$Asn['last_name'];
        }
        $AsnLogin = $Asn['dt_last_login'];
        $shortname =  $Asn['short_name'];
        //if($m > 5){$h++;
        ?>
        <li class="li_check_radio" <?php if($m > 5){$h++;?> id="hidAsn_<?php echo $h; ?>"  <?php }?>> <!--style="display:none;"-->
            <div class="checkbox">
                <label>
                    <input type="checkbox" class="assignto<?php echo $AsnId; ?>" id="Asns_<?php echo $m; ?>" onClick="checkboxAsns('Asns_<?php echo $m; ?>','check');filterRequest('assignto');"  <?php if (in_array($AsnId, $Asnbers)) { echo "checked"; } ?>/> <?php echo $this->Format->formatText($AsnName); ?>
                </label>
                <input type="hidden" name="Asnids_<?php echo $m; ?>" id="Asnids_<?php echo $m; ?>" value="<?php echo $AsnId; ?>" readonly="true">
            </div>
        </li>
        <?php
    }
    if($h != 0)
    {
        ?>
        <?php
    } ?>
<?php } ?>
<input type="hidden" id="totAsnId" value="<?php echo $m; ?>" readonly="true"/>
