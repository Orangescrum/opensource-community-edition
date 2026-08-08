<div class="task_group_catagory">
    <?php if (empty($res_out)) { ?>
        <div class="data_not_avail"><?php echo __('No Data Available'); ?></div>
    <?php } ?>
    <?php #pr($all_mil_names_epty); 
    ?>
    <?php if (!empty($res_out)) { ?>
        <?php foreach ($res_out as $k => $v) { ?>
            <?php if (!empty($k)) { ?>
                <div class="col-md-2">
                    <table>
                        <thead>
                            <tr>
                                <th colspan="2"><a style="color:#fff;" href="<?php echo HTTP_ROOT; ?>dashboard#kanban/<?php echo $v['uniq_id']; ?>"><?php echo $v['title']; ?></a></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2"><small><?php echo (isset($v['od_cnt'])) ? $v['od_cnt'] : __('No'); ?> <?php echo __('Overdue task'); ?></small></td>
                            </tr>
                            <tr class="close_progress">
                                <td>
                                    <?php echo __('Closed'); ?><strong><?php echo (isset($v['cls_cnt'])) ? $v['cls_cnt'] : '0'; ?></strong>
                                </td>
                                <td>
                                    <?php echo __('Work in Progress'); ?>
                                    <strong><?php echo (isset($v['inp_cnt'])) ? $v['inp_cnt'] : '0'; ?></strong>
                                </td>
                            </tr>
                            <tr class="total">
                                <td colspan="2"><?php echo __('Total task'); ?>: <strong><?php echo ((isset($v['inp_cnt'])) ? $v['inp_cnt'] : 0) + ((isset($v['cls_cnt'])) ? $v['cls_cnt'] : 0); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="col-md-2">
                    <table>
                        <thead>
                            <tr>
                                <th colspan="2">
                                    <a style="color:#fff;cursor:default;text-decoration:none;" href="javascript:void(0);"><?php echo __('Default Task Group'); ?></a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2"><small><?php echo (isset($v['od_cnt'])) ? $v['od_cnt'] : __('No'); ?> <?php echo __('Overdue task'); ?></small></td>
                            </tr>
                            <tr class="close_progress">
                                <td>
                                    <?php echo __('Closed'); ?><strong><?php echo (isset($v['cls_cnt'])) ? $v['cls_cnt'] : '0'; ?></strong>
                                </td>
                                <td>
                                    <?php echo __('Work in Progress'); ?>
                                    <strong><?php echo (isset($v['inp_cnt'])) ? $v['inp_cnt'] : '0'; ?></strong>
                                </td>
                            </tr>
                            <tr class="total">
                                <td colspan="2"><?php echo __('Total task'); ?>: <strong><?php echo ((isset($v['inp_cnt'])) ? $v['inp_cnt'] : 0) + ((isset($v['cls_cnt'])) ? $v['cls_cnt'] : 0); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        <?php } ?>
        <div class="clearfix"></div>
    <?php } ?>
    <?php if (empty($res_out)) { ?>
        <div class="col-md-2 empty_line_cont">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">
                            <div class="line_bar medium white"></div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <div class="line_bar small gray width_30"></div>
                            <div class="line_bar small gray width_70"></div>
                        </td>
                    </tr>
                    <tr class="close_progress">
                        <td>
                            <div class="line_bar small gray"></div>
                            <div class="circle"></div>
                        </td>
                        <td>
                            <div class="line_bar small gray"></div>
                            <div class="circle"></div>
                        </td>
                    </tr>
                    <tr class="total">
                        <td colspan="2">
                            <div class="line_bar small gray width_30"></div>
                            <div class="line_bar small gray width_70"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-2 empty_line_cont">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">
                            <div class="line_bar medium white"></div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <div class="line_bar small gray width_30"></div>
                            <div class="line_bar small gray width_70"></div>
                        </td>
                    </tr>
                    <tr class="close_progress">
                        <td>
                            <div class="line_bar small gray"></div>
                            <div class="circle"></div>
                        </td>
                        <td>
                            <div class="line_bar small gray"></div>
                            <div class="circle"></div>
                        </td>
                    </tr>
                    <tr class="total">
                        <td colspan="2">
                            <div class="line_bar small gray width_30"></div>
                            <div class="line_bar small gray width_70"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-2 empty_line_cont">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">
                            <div class="line_bar medium white"></div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <div class="line_bar small gray width_30"></div>
                            <div class="line_bar small gray width_70"></div>
                        </td>
                    </tr>
                    <tr class="close_progress">
                        <td>
                            <div class="line_bar small gray"></div>
                            <div class="circle"></div>
                        </td>
                        <td>
                            <div class="line_bar small gray"></div>
                            <div class="circle"></div>
                        </td>
                    </tr>
                    <tr class="total">
                        <td colspan="2">
                            <div class="line_bar small gray width_30"></div>
                            <div class="line_bar small gray width_70"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-2 empty_line_cont">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">
                            <div class="line_bar medium white"></div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <div class="line_bar small gray width_30"></div>
                            <div class="line_bar small gray width_70"></div>
                        </td>
                    </tr>
                    <tr class="close_progress">
                        <td>
                            <div class="line_bar small gray"></div>
                            <div class="circle"></div>
                        </td>
                        <td>
                            <div class="line_bar small gray"></div>
                            <div class="circle"></div>
                        </td>
                    </tr>
                    <tr class="total">
                        <td colspan="2">
                            <div class="line_bar small gray width_30"></div>
                            <div class="line_bar small gray width_70"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-2 empty_line_cont">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">
                            <div class="line_bar medium white"></div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <div class="line_bar small gray width_30"></div>
                            <div class="line_bar small gray width_70"></div>
                        </td>
                    </tr>
                    <tr class="close_progress">
                        <td>
                            <div class="line_bar small gray"></div>
                            <div class="circle"></div>
                        </td>
                        <td>
                            <div class="line_bar small gray"></div>
                            <div class="circle"></div>
                        </td>
                    </tr>
                    <tr class="total">
                        <td colspan="2">
                            <div class="line_bar small gray width_30"></div>
                            <div class="line_bar small gray width_70"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="clearfix"></div>
    <?php } ?>
</div>