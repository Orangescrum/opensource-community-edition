<div class="impexp_div">
    <div class="add-on-tab fl">
        <ul id="tabnav">
            <?php
            // Import Customers, Import Epics and Advanced Import Task belonged to
            // features removed from this edition — their routes 404 — so only the
            // two working importers are offered.
            $timelogLink = HTTP_ROOT . 'import-timelog';
            $taskLink = HTTP_ROOT . 'import-export';
            $taskLabel = __('Import Task');
            $timelogLabel = __('Import Time Log');

            $tabs = [
                'importexport' => [
                    0 => ['label' => $taskLabel, 'url' => null],
                    1 => ['label' => $timelogLabel, 'url' => $timelogLink],
                ],
                'importtimelog' => [
                    0 => ['label' => $taskLabel, 'url' => $taskLink],
                    1 => ['label' => $timelogLabel, 'url' => null],
                ],
            ];

            if (isset($tabs[$mode])) {
                ksort($tabs[$mode]); // Order tabs by key
                foreach ($tabs[$mode] as $tab) {
                    $active = is_null($tab['url']) ? 'active-list' : '';
                    $href = is_null($tab['url']) ? 'javascript:void(0)' : $tab['url'];
                    echo '<li class="' . $active . '"><a href="' . $href . '"><span>' . $tab['label'] . '</span></a></li>';
                }
            }
            ?>
        </ul>
    </div>
    <?php if ($mode == 'importexport') { ?>
        <div class="fr btn_tlog_top">
            <a>
                <button class="customfile-button btn btn-sm btn_cmn_efect hide" onclick="ajax_exportCsv(0);" rel="tooltip"
                    title="<?php echo __('Export Task To CSV'); ?>">
                    <i class="material-icons">&#xE8D5;</i>
                </button>
            </a>
        </div>
    <?php } ?>
    <div class="cb"></div>
</div>