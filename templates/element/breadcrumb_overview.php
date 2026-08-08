<div class="bread_crumb">
    <div class="overview_wrapper">
        <ul>
            <li class="a_task">
                <?php
                $taskOnclick = "return checkHashLoad('tasks');";
                if ($_SESSION['project_methodology'] == 'simple') {
                    $taskURL = HTTP_ROOT . 'dashboard#tasks';
                } else if ($_SESSION['project_methodology'] == 'scrum') {
                    if (defined('USE_SCRUM_PLUGIN_BACKLOG') && USE_SCRUM_PLUGIN_BACKLOG === '1') {
                        $taskURL = HTTP_ROOT . 'backlog';
                        $taskOnclick = '';
                    } else {
                        $taskURL = HTTP_ROOT . 'dashboard#backlog';
                    }
                } else {
                    $taskURL = HTTP_ROOT . 'dashboard#kanban';
                } ?>
                <a class="" href="<?php echo $taskURL; ?>"<?php if ($taskOnclick) { ?> onclick="<?php echo $taskOnclick; ?>"<?php } ?>><?php echo __('All Task'); ?> (<span id="ov_tsk_entry_cnt">0</span>)</a>
            </li>

            <li class="activity_icon">
                <a class="" href="<?php echo HTTP_ROOT . 'dashboard#activities'; ?>" onclick="return checkHashLoad('activities');"><?php echo __('All Activities'); ?> (<span id="ov_atvt_entry_cnt">0</span>)</a>
            </li>

            <li class="m_task">
                <a class="" href="javascript:void(0);" onclick="" style="cursor:default;border-bottom:none;"><?php echo __('Project Est.:'); ?> (<span><?php echo ($proj['estimated_hours']) ? $proj['estimated_hours'] : 0; ?> <?php echo ($proj['estimated_hours'] > 1) ? 'hrs' : 'hr'; ?></span>)</a>
            </li>

            <li class="t_est">
                <a class="" href="javascript:void(0);" onclick="" style="cursor:default;border-bottom:none;"><?php echo __('Task Est.:'); ?> (<span><?php echo $f_estd; ?></span>)</a>
            </li>

            <li class="t_entry">
                <?php
                $timelogurl = '';
                $timelogurl = DEFAULT_TIMELOGVIEW == 'calendar_timelog' ? 'calendar_timelog' : 'timelog';
                ?>
                <a class="" href="<?php echo HTTP_ROOT . 'dashboard#' . $timelogurl; ?>" onclick="return checkHashLoad('timelog');"><?php echo __('Time Entry'); ?> (<span id="ov_tim_entry_cnt">0</span>)</a>
            </li>

        </ul>
    </div>
</div>