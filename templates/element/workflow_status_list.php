<div class="m-cmn-flow cmn-overflow-list" style="overflow: auto;width: 100%;">
    <table  class="table table-striped table-hover m-list-tbl" style="display: table;overflow-x: auto;white-space: nowrap;border-collapse: separate;">
        <thead>
        <tr class="hd-new"> 
            <th><?php echo  __("Task Status Name");?></th>
            <th><?php echo  __("Color");?></th>
            <th><?php echo  __("Progress %");?></th>
            <th><?php echo  __("Status Type");?></th>
            <th><?php echo  __("Action");?></th>
        </tr>
        </thead>
        <?php if (count($result) > 0) { ?>
            <tbody  <?php if ($statusGroup['StatusGroup']['company_id']) { ?>id="sortableStatus"<?php } ?> >
            <?php foreach ($result as $k => $v) { ?>
                <tr id="custom_status_tr_<?php echo $v['CustomStatus']['id'];?>" data-master-id="<?php echo $v['CustomStatus']['status_master_id'];?>">
                    <td><div class="dot-bar-group"></div><?php echo $v['CustomStatus']['name'];?></td>
                    <td><div style="background-color:#<?php echo $v['CustomStatus']['color'];?>;width: 25px;height: 25px;border-radius: 50%;">
                    </td>
                    <td><?php echo $v['CustomStatus']['progress'];?></td>
                    <td>
                        <?php if ($v['CustomStatus']['company_id']) { ?>
                            <?php echo $statusMaster[$v['CustomStatus']['status_master_id']];?>
                        <?php } else { ?>
                            <?php echo $v['CustomStatus']['name'];?>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($v['CustomStatus']['company_id']) { ?>
                            <a href="javascript:void(0)" onclick="editWfStatus(<?php echo $v['CustomStatus']['id'];?>)" title="<?php echo __('Edit');?>"><i class="material-icons">edit</i></a>
                            <?php if (count($v['CustomStatus']['easycases']) == 0 && empty($v['CustomStatus']['final'])) { ?>
                                <a href="javascript:void(0)" onclick="deleteWfStatus(<?php echo $v['CustomStatus']['id'];?>)" title="<?php echo __('Delete');?>"><i class="material-icons">delete_outline</i></a>
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>

        <?php } else { ?>
            <tr>
                <td colspan="6" style="color: red; text-align: center;"><?php echo __("No Task Status found.");?></td>
            </tr>
        <?php } ?>
    </table>
</div>
</div>
