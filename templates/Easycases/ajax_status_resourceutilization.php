<input type="text" placeholder="<?php echo __('Search'); ?>" class="searchType" onkeyup="searchFilterItems(this);" />
<?php
//for default status
$dft_sts = array(1 => 'new', 2 => 'inprogress', 3 => 'closed', 5 => 'resolved');
foreach ($dft_sts as $dk => $dv) { ?>
    <li class="li_check_radio">
        <div class="checkbox">
            <label> <?php //archive_status_cls ?>
                <input class="utilization-status" type="checkbox" data-id="<?php echo $dk; ?>"
                    id="utilization_<?php echo $dv; ?>" onclick="utilization_status('<?php echo $dv; ?>', 'check');" />
                <?php
                if ($dk == 1) {
                    echo __('New');
                } else if ($dk == 2) {
                    echo __('In Progress');
                } else if ($dk == 3) {
                    echo __('Closed');
                } else {
                    echo __('Resolved');
                } ?>
            </label>
        </div>
    </li>
<?php } ?>

<?php
//for custom status
if (isset($allCustomStatus) && !empty($allCustomStatus)) {
    foreach ($allCustomStatus as $ck => $cv) {
        ?>
        <li class="li_check_radio">
            <div class="checkbox">
                <label>
                    <input class="utilization-status" type="checkbox" data-id="c<?php echo $cv['id']; ?>"
                        id="utilization_<?php echo $cv['id']; ?>"
                        onclick="utilization_status('<?php echo $cv['id']; ?>', 'check');" />
                    <?php echo $cv['name']; ?>
                </label>
            </div>
        </li>
    <?php }
} ?>