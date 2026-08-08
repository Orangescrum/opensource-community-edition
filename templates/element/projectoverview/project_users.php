<style>
    .pu_unm {
        display: inline-block;
        width: 60px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 13px;
        vertical-align: middle;
    }
</style>
<?php if (is_array($users)) { ?>
    <div class="team">
        <table>
            <thead>
            <tr>
                <th><?php echo __('User'); ?></th>
                <th><?php echo __('Total Task'); ?></th>
                <th><?php echo __('Overdue Task'); ?></th>
                <th><?php echo __('Billable Hours'); ?></th>
                <th><?php echo __('Non-Billable Hours'); ?></th>
            </tr>
            </thead>
        </table>
        <div class="scroll_body">
            <table>
                <tbody>
                <?php foreach ($users as $key => $val) {
                    $t_nm = '';
                    if (trim($val['Users']['name']) == '') {
                        $t_nm = explode('@', $val['Users']['email']);
                        $val['Users']['name'] = $t_nm[0];
                    }
                    if ($val['Users']['name'] != '') {
                        $random_bgclr = $this->Format->getProfileBgColr($val['Users']['id']);
                        $usr_name_fst = mb_substr(trim($val['Users']['name']), 0, 1, "utf-8");
                        ?>
                        <tr id="<?php echo $projid . '_'; ?><?php echo $val['Users']['uniq_id']; ?>">
                            <td>
                                <div class="user_name" title="<?php echo $val['Users']['name'] . ' ' . $val['Users']['last_name']; ?>">
                                    <?php if (isset($extra) && $extra == 'overview' && $this->Format->isAllowed('Remove Users from Project', $roleAccess)) {
                                        ?>
                                        <span class="drop_icon" onclick="removeUserOverview(this);" class="remove_user_hover" data-pid="<?php echo $projid; ?>" data-uid="<?php echo $val['Users']['uniq_id']; ?>" data-name="<?php echo $val['Users']['name']; ?>" title="<?php echo __('Remove'); ?> <?php echo $val['Users']['name']; ?> <?php echo __('from this project'); ?>"><span class="h_line"></span></span>
                                    <?php } ?>
                                    <span class="pfl_img" id="user_prof_<?php echo $val['Users']['uniq_id']; ?>">
                                            <?php if (trim($val['Users']['photo']) != '') { ?>
                                                <img title="<?php echo $val['Users']['name']; ?>" alt="" rel="tooltip" src="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<?php echo trim($val['Users']['photo']) != '' ? trim($val['Users']['photo']) : 'user.png'; ?>&sizex=35&sizey=35&quality=100" class="lazy round_profile_img" height="35" width="35" alt="No Image" />
                                            <?php } else { ?>
                                                <span title="<?php echo $val['Users']['name']; ?>" rel="tooltip" class="cmn_profile_holder <?php echo $random_bgclr; ?>"><?php echo $usr_name_fst; ?></span>
                                            <?php } ?>
                                        </span>
                                    <span class="pu_unm"><?php echo $val['Users']['name']; ?></span>
                                </div>
                            </td>
                            <td>
                                <?php
                                if (SES_TYPE < 3) {
                                    echo isset($val['tot_task']) ? $val['tot_task'] : 0;
                                } elseif (SES_TYPE == 3 || $isClient == 1) {
                                    if ($userData['id'] == $val['Users']['id']) {
                                        echo isset($val['tot_task']) ? $val['tot_task'] : 0;
                                    } else {
                                        echo 0;
                                    }
                                }
                                ?>
                            </td>
                            <td class="red_txt">
                                <?php
                                if (SES_TYPE < 3) {
                                    echo isset($val['od_task']) ? $val['od_task'] : 0;
                                } elseif (SES_TYPE == 3 || $isClient == 1) {
                                    if ($userData['id'] == $val['Users']['id']) {
                                        echo isset($val['od_task']) ? $val['od_task'] : 0;
                                    } else {
                                        echo 0;
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if (SES_TYPE < 3) {
                                    echo isset($val['billable']) ? $this->Format->format_time_hr_min($val['billable'], 1) : 0;
                                } elseif (SES_TYPE == 3 || $isClient == 1) {
                                    if ($userData['id'] == $val['Users']['id']) {
                                        echo isset($val['billable']) ? $this->Format->format_time_hr_min($val['billable'], 1) : 0;
                                    } else {
                                        echo 0;
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if (SES_TYPE < 3) {
                                    echo isset($val['non_billable']) ? $this->Format->format_time_hr_min($val['non_billable'], 1) : 0;
                                } elseif (SES_TYPE == 3 || $isClient == 1) {
                                    if ($userData['id'] == $val['Users']['id']) {
                                        echo isset($val['non_billable']) ? $this->Format->format_time_hr_min($val['non_billable'], 1) : 0;
                                    } else {
                                        echo 0;
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>
