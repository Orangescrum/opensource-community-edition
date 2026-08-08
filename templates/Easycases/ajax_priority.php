<input type="hidden" id="priority_all">
<?php foreach (['Low', 'Medium', 'High'] as $p) { ?>
    <li class="li_check_radio">
        <div class="checkbox">
            <?php
            $labelStyle = match ($p) {
                'High' => 'color:#AE432E',
                'Medium' => 'color:#28AF51',
                default => 'color:#AD9227',
            };
            $isChecked = strstr($CookiePriority ?? '', (string) $p) ? "checked" : "";
            ?>
            <label style="<?php echo $labelStyle; ?>">
                <input type="checkbox" id="priority_<?php echo $p; ?>"
                    onClick="checkboxPriority('priority_<?php echo $p; ?>','check');filterRequest('priority');"
                    style="cursor:pointer;" <?php echo $isChecked; ?> />
                <?php echo $p; ?>
            </label>
        </div>
    </li>
<?php } ?>