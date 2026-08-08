<?php
// i18n extraction anchor: strings looked up by gettext.js _() at runtime that
// `bin/cake i18n extract` cannot scan (they live in .js files or inline <script>
// blocks). Listing them here as PHP __() calls makes them enter default.pot so
// the JS lookups resolve to a translation instead of rendering untranslated.
// The block never executes (if (false)); it exists purely for the extractor.
if (false):
    __('Export resource utilization to csv file'); // templates/Teams/team_task_reports.php
    __('Hide options');                             // webroot/js/script_v1.js, templates/Projects/ajax_edit_project.php
    __('Search Projects');                          // webroot/js/script_v1.js
    __('Type to select User');                      // webroot/js/script_v1.js
endif;
