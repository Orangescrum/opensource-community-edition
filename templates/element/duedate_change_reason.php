<div class="row mtop20">
    <div class="col-md-12">
        <div class="cmn_custom_dtable due-date-table">
            <table class="table custom_fld_list_table m-btm0" id="duedt_chnge_rsn_tbl">
                <thead>
                    <tr>
                        <th class="th_1"></th>
                        <th>Reason</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allReasons as $key => $value) { ?>
                        <?php $checked = 'checked = true';
                        if (($value['is_active']) == 1) {
                            $checked = 'checked = true';
                        } else {
                            $checked = '';
                        }
                        ?>
                        <tr id="due_dt_change_tr_<?php echo $value['id']; ?>">
                            <td>
                                <div class="checkActiveDuedt<?php echo $value['id']; ?> text-center">
                                    <input type="checkbox" class="all_tt" value="" onclick="return ajaXCheckActiveDuedtReason(this);" data-id="<?php echo $value['id']; ?>" <?php echo $checked; ?> />
                                </div>
                            </td>
                            <td>
                                <?php echo $value['reason']; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($value['company_id'] != 0) { ?>
                                    <span>
                                        <a href="javascript:void(0);" class=" custom-t-type" onclick="ajaXEditDuedtChangeReason(this);" data-label="<?php echo $value['reason']; ?>" data-id="<?php echo $value['id']; ?>">
                                            <i class="edit-link material-icons" title="Edit" id="edit_tsk_id<?php echo $value['id']; ?>">&#xE254;</i>
                                        </a>
                                    </span>
                                    <?php if (empty($value['TaskDueChangeReason'])) { ?>
                                        <span>
                                            <a href="javascript:void(0);" class="custom-t-type" onclick="ajaXDeleteDuedtChangeReason(this);" data-label="<?php echo $value['reason']; ?>" data-id="<?php echo $value['id']; ?>">
                                                <i class="delete-link material-icons" title="Delete">&#xE872;</i>
                                            </a>
                                        </span>
                                <?php }
                                } ?>
                            </td>
                        </tr>
                    <?php  } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.search_control').on('keyup', function() {
            var searchValue = $('.search_control').val();
            $("#duedt_chnge_rsn_tbl tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(searchValue.toLowerCase()) > -1)
            });
        })
    })

    function ajaXCheckActiveDuedtReason(obj) {
        var isChecked = 0;
        var id = $(obj).attr('data-id');
        $(".checkActiveDuedt" + id).each(function() {
            if ($(obj).is(":checked")) {
                isChecked = 1;
            }
        });
        $.post(HTTP_ROOT + "task_actions/ajaXCheckActiveDuedateReason", {
            'Id': id,
            'chkValue': isChecked
        }, function(res) {
            if (res == 1) {
                showTopErrSucc('success', _('Status changed successfully.'));
            } else {
                showTopErrSucc('error', _('Something went wrong.'));
            }
            ajaXLoadDuedateChangeReason();
        });
    }
</script>