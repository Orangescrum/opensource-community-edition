<?php if (isset($recent_activities) && !empty($recent_activities)) {
    $cnt = 0;
    $lastdate = '';
    foreach ($recent_activities as $key => $value) {
        $cnt++;
        if($cnt < 11) {
        ?>
        <?php if (($value['pclient_status'] == 1 && $value['puserid'] == SES_ID) || $value['pclient_status'] != 1 || SES_TYPE < 3) { ?>
            <?php if (isset($extra) && !empty($extra)) { ?>
                <?php if ($cnt == 1) { ?>
                    <ul>
                <?php } ?>
                <li>
                        <span class="pfl_img" rel="tooltip" title="<?php echo ucfirst($value['Users']['funll_name']); ?>">
                            <?php if ($value['Users']['photo']) { ?>
                                <img src="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<?php echo $value['Users']['photo']; ?>&sizex=30&sizey=30&quality=100" title="<?php echo $value['Users']['name']; ?>" rel="tooltip" alt="Loading" />
                            <?php } else {
                                $random_bgclr = $this->Format->getProfileBgColr($value['Users']['id']);
                                $usr_name_fst = mb_substr(trim($value['Users']['name']), 0, 1, "utf-8");
                                ?>
                                <span class="cmn_profile_holder <?php echo $random_bgclr; ?>"><?php echo $usr_name_fst; ?></span>
                            <?php } ?>
                        </span>
                    <div class="date" <?php if ($value['newActuldt'] == $lastdate) { ?> style="display:none;" <?php } ?>>
                        <?php echo $value['newActuldt']; ?>
                    </div>
                    <?php $lastdate = $value['newActuldt']; ?>
                    <div class="type"><?php echo $value['nmsg']; ?></div>
                    <div class="link_time">
                        <a href="" title="<?php echo strip_tags($value['ntxt']); ?>"><?php echo $value['ntxt']; ?></a>
                        <span class="time"><?php echo $value['updated']; ?></span>
                        <div class="clearfix"></div>
                    </div>
                    <div class="name"><?php echo ucfirst($value['Users']['name']); ?></div>
                </li>
                <?php if ($cnt == count($recent_activities)) { ?>
                    </ul>
                <?php } ?>
            <?php } else { ?>
                <div class="gray-dot" <?php if ($value['newActuldt'] == $lastdate) { ?> style="display:none;" <?php } ?>>
                    <div class="activity-date"><?php echo $value['newActuldt']; ?></div>
                </div>
                <?php $lastdate = $value['newActuldt']; ?>
                <div class="activity-row">
                    <span class="activity-img" rel="tooltip" title="<?php echo ucfirst($value['Users']['funll_name']); ?>">
                        <?php if ($value['Users']['photo']) { ?>
                            <img src="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<?php echo $value['Users']['photo']; ?>&sizex=30&sizey=30&quality=100" title="<?php echo $value['Users']['name']; ?>" rel="tooltip" alt="Loading" />
                        <?php } else {
                            $random_bgclr = $this->Format->getProfileBgColr($value['Users']['id']);
                            $usr_name_fst = mb_substr(trim($value['Users']['name']), 0, 1, "utf-8");
                            ?>
                            <span class="cmn_profile_holder <?php echo $random_bgclr; ?>"><?php echo $usr_name_fst; ?></span>
                        <?php } ?>
                    </span>
                    <small class="fr activity-time"><?php echo $value['updated']; ?></small>
                    <span class="red-txt"><?php echo $value['nmsg']; ?></span>
                    <p class="linkable-txt" title="<?php echo strip_tags($value['ntxt']); ?>"><?php echo $value['ntxt']; ?></p>
                    <?php if ($project == 'all') { ?>
                        <p style="color:#6699ff;"><i class="material-icons" style="font-size: 18px;vertical-align: middle;">work</i><a href="<?php echo HTTP_ROOT; ?>dashboard/?project=<?php echo $value['Projects']['uniq_id']; ?>" title="<?php echo ucfirst($value['Projects']['name']); ?>" style="color:#6699ff;vertical-align: middle;"><?php echo ucfirst($value['Projects']['short_name']); ?></a></p>
                    <?php } ?>
                    <span><?php echo ucfirst($value['Users']['name']); ?></span>
                </div>
            <?php } ?>
        <?php } ?>
        <?php } ?>
    <?php } ?>
    <div id="recent_activities_more" data-value="<?php echo $total; ?>" style="display: none;"></div>
    <div class="fr moredb" id="more_recent_activities"><a href="javascript:void(0);" onclick="showTasks('activities');"><?php echo __('View All'); ?> <span id="todos_cnt" style="display:none;">(0)</span></a></div>

<?php } else { ?>
    <div id="recent_activities_more" data-value="<?php echo isset($total) ? $total : 0; ?>" style="display: none;"></div>
    <?php if (isset($extra) && !empty($extra)) { ?>
        <div class="scroll_body empty_line_cont">
            <ul>
                <li>
                    <span class="pfl_img"></span>
                    <div class="line_bar medium blue"></div>
                    <div class="line_bar small dark_gray"></div>
                    <div class="line_bar small gray"></div>
                </li>
                <li>
                    <span class="pfl_img"></span>
                    <div class="line_bar medium blue"></div>
                    <div class="line_bar small dark_gray"></div>
                    <div class="line_bar small gray"></div>
                </li>
                <li>
                    <span class="pfl_img"></span>
                    <div class="line_bar medium blue"></div>
                    <div class="line_bar small dark_gray"></div>
                    <div class="line_bar small gray"></div>
                </li>
                <li>
                    <span class="pfl_img"></span>
                    <div class="line_bar medium blue"></div>
                    <div class="line_bar small dark_gray"></div>
                    <div class="line_bar small gray"></div>
                </li>
            </ul>
            <div class="data_not_avail">No Data Available</div>
        </div>
    <?php } else { ?>
        <?php if ($this->Format->isAllowed('Create Task', $roleAccess)) { ?>
            <div class="mytask" onclick="creatask();"></div>
        <?php } ?>
        <div class="mytask_txt"><?php echo __('No Recent Activities'); ?></div>
        <div id="recent_activities_more" class="dash-activity-cont" data-value="0" style="display: none;"></div>
    <?php } ?>
<?php } ?>
