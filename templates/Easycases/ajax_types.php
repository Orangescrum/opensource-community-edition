<input type="hidden" id="types_all">
<input type="text" placeholder="Search" class="searchType" onkeyup="searchFilterItems(this);" />
<?php

$DEFAULT_TASK_TYPES = array("bug"=>"&#xE60E;","enh"=>"&#xE01D;","cr"=>"&#xE873;","dev"=>"&#xE1B0;","idea"=>"&#xE90F;","mnt"=>"&#xE869;","oth"=>"&#xE892;","qa"=>"Q","rel"=>"&#xE031;","rnd"=>"&#xE8FA;","unt"=>"&#xE3E8;","upd"=>"&#xE923;");
if(isset($typeArr))
{
    $t=0;
    $totCase = 0;
    $h=0;
    $CookieTypes = explode("-", $CookieTypes ?? '');
    foreach($typeArr as $typ)
    {
        $typecount = $typ['cnt'];
        $typeId = $typ['id'];
        $typeShortName = $typ['short_name'];
        $typeName = $typ['name'] ?? '';

        $img = "<img src='".HTTP_IMAGES."images/types/".$typeShortName.".png' />";
        if (isset($typ['company_id']) && trim($typ['company_id'])) {
            $img = "<span class='ttl_dd_icn'>".mb_substr(trim($typeName),0,1, "utf-8")."</span>";
        }
        $t++;
        $txs_typ = $typeName;
        foreach($DEFAULT_TASK_TYPES as $i=>$n) {
            if($i == $typeShortName){
                $txs_typ = $n;
            }
        }
        //if($t > 5)	$h++;
        ?>
        <li class="li_check_radio" <?php if($t > 5){ $h++;?>id="hidType_<?php echo $h; ?>" <?php } ?>>

            <div class="checkbox">
                <label>
                    <input type="checkbox" class="cst_type_cls" id="types_<?php echo $typeId; ?>" data-id="<?php echo $typeId; ?>" onClick="checkboxTypes('types_<?php echo $typeId; ?>','check');filterRequest('type');" style="cursor:pointer;" <?php if(in_array($typeId,$CookieTypes)) { echo "checked"; } ?>/>
                    <?php if($txs_typ == $typeName){ ?>
                        <span class="ttl_dd_icn"><?php echo mb_substr(trim($typeName),0,1, "utf-8"); ?></span>
                    <?php }else{ ?>
                        <i class="material-icons"><?php echo $txs_typ; ?></i>
                    <?php } ?>
                    <?php echo $typeName; ?>
                </label>
                <input type="hidden" name="typeids_<?php echo $typeId; ?>" id="typeids_<?php echo $typeId; ?>" value="<?php echo $typeId; ?>" readonly="true">
            </div>
        </li>
        <?php
    }
    if($h != 0)
    {
        ?>
        <?php
    }
    ?>
    <input type="hidden" id="totType" value="<?php echo $t; ?>" readonly="true"/>
    <?php
}
?>
