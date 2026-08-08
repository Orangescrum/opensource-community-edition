<?php

use Cake\Cache\Cache;
use Cake\Core\Configure;


$free_user_chat_hide = 0;
$chat_active = 1;
$settings_arr = ['mycompany', 'groupupdatealerts', 'importexport', 'task_type', 'labels', 'settings', 'task_settings', 'resource_utilization', 'pricing', 'creditcard', 'transaction', 'subscription', 'profile', 'changepassword', 'email_notifications', 'email_reports', 'default_view', 'getting_started', 'get_mobile_device']; ?>
<?php if (isset($_COOKIE['FIRST_INVITE_1']) && $_COOKIE['FIRST_INVITE_1'] == 1 && PAGE_NAME == 'dashboard') { ?>
    <div class="onboard_indicate_popup onboardPop">
        <h6><?php echo __('Task Management'); ?></h6>
        <p><?php echo __('Create and assign tasks to collaborate & initiate project execution'); ?>!</p>
        <div style="margin-top: 30px;">
            <a href="javascript:void(0)" class="skip"
                onclick="removeOnboard();"><?php echo __('Done? Click here to skip'); ?></a>
            <a href="javascript:void(0)" class="next"
                onclick="nextOnboard('onboard_indicate_popup_timelog');"><?php echo __('Next'); ?></a>
            <div class="cb"></div>
        </div>
    </div>
    <div class="onboard_indicate_popup_timelog onboardPop" style="display:none;">
        <h6><?php echo __('Time Tracking'); ?></h6>
        <p><?php echo __('Log spent hours against your tasks & let all project members know of the progress'); ?>!</p>
        <div style="margin-top: 30px;">
            <a href="javascript:void(0)" class="skip"
                onclick="removeOnboard();"><?php echo __('Done? Click here to skip'); ?></a>
            <a href="javascript:void(0)" class="next"
                onclick="nextOnboard('onboard_indicate_popup_invoice');"><?php echo __('Next'); ?></a>
            <div class="cb"></div>
        </div>
    </div>
    <div class="onboard_indicate_popup_invoice onboardPop" style="display:none;">
        <h6><?php echo __('Invoice'); ?></h6>
        <p><?php echo __('Generate professional invoices from your unbilled times for your Clients'); ?>!</p>
        <div style="margin-top: 30px;">
            <a href="javascript:void(0)" class="skip"
                onclick="removeOnboard();"><?php echo __('Done? Click here to skip'); ?></a>
            <a href="javascript:void(0)" class="next" onclick="removeOnboard();"><?php echo __('Got It'); ?></a>
            <div class="cb"></div>
        </div>
    </div>
<?php } ?>

<div class="sticky_footer">
    <footer class="common-footer" <?php if (PAGE_NAME == "updates" || PAGE_NAME == "help" || PAGE_NAME == "tour" || PAGE_NAME == "customer_support") { ?>style="padding-left:0px;" <?php } ?>>
        <div class="col-lg-12">
            <div class="row footer_wrapper">
                <div class="col-lg-4 text-left cmn_foot_cont" id="csTotalHours" style="padding:0px;"></div>
                <div class="col-lg-4 text-left cmn_foot_cont" id="projectaccess"></div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
    </footer>
</div>
<script type="text/javascript">
    function openCalendly() {
        $('#calendly_modal').modal('show');
    }

    function openForm() {
        document.getElementById("myForm").style.display = "block";
    }

    function closeForm() {
        document.getElementById("myForm").style.display = "none";
    }
    $(document).ready(function () {
        $(".slide-toggle").click(function () {
            $('.slide-toggle').not(this).removeClass('active');
            $(".slide-toggle").toggleClass('active');
            $(".box").animate({
                width: "toggle"
            });
        });

    });
</script>
<script type="text/template" id="ajax_list_view_tmpl">
    <?php echo $this->element('manage_grid_tmpl'); ?>
</script>
<script type="text/javascript">
    var is_change_reason_set = "<?php if ($this->Format->isAllowedModuleAction('Task', 'Change Due Date Reason', $roleAccess)) { ?>1<?php } else { ?>0<?php } ?>";
    var selectedColumns = "<?php echo $defaultfields['form_fields']; ?>";
    var selectedColumnsProject = "<?php echo $defaultfields['project_form_fields']; ?>";
    var bugSelectedColumns = "<?php echo $defaultdefectfields['form_fields']; ?>";
    $(document).ready(function () {
        //set the change reason
        localStorage.setItem("is_change_reason_set", is_change_reason_set);

        /* show hide fields */
        var selectedColumnsarr = selectedColumns.split(",");
        $('.task-field-all').hide();
        for (var i = 0; i < selectedColumnsarr.length; i++) {
            $(".task-field-" + selectedColumnsarr[i]).show();
        }
        // for create bug popup
        var bugSelectedColumnsarr = bugSelectedColumns.split(",");
        $('.bug-field-all').hide();
        $('.edit-bug-field-all').hide();
        for (var i = 0; i < bugSelectedColumnsarr.length; i++) {
            $(".bug-field-" + bugSelectedColumnsarr[i]).show();
            $(".bug-edit-field-" + bugSelectedColumnsarr[i]).show();
        }


        // Every project field is always shown — the per-field show/hide
        // configuration was removed from the create-project form.
        $('.project-field-all').show();
        /* End */

        // Sync the "Select All" state on load. The per-field change handler keeps
        // it in step afterwards, but nothing set it initially, so it read
        // unchecked even when every field was ticked (DF-011).
        if ($('.configfields').length && $('.configfields').length === $('.configfields:checked').length) {
            $('#column_all_fields').prop('checked', true);
        }

        $('.select-timer-proj').selectize();
        $('.select-timer-task').selectize();
    });
</script>
<!-- Footer ends -->

<script type="text/javascript">
    <?php /*?>JS VARs from footer_inner<?php */ ?>
    var HTTP_ROOT = '<?php echo HTTP_ROOT; ?>'; //pageurl
    var HTTP_HOME = '<?php echo HTTPS_HOME; ?>'; //pageurl
    var HTTP_IMAGES = '<?php echo HTTP_IMAGES; ?>'; //hid_http_images
    var MAX_FILE_SIZE = '<?php echo MAX_FILE_SIZE; ?>'; //fmaxilesize
    var SES_ID = '<?php echo SES_ID; ?>'; //pub_show
    var SES_COMP = '<?php echo SES_COMP; ?>';
    var SES_TYPE = '<?php echo SES_TYPE; ?>';
    <?php $GLOBALS['TYPE'] = array_filter($GLOBALS['TYPE']); ?>;
    var GLOBALS_TYPE = <?php echo json_encode($GLOBALS['TYPE']); ?>;
    var DESK_NOTIFY = <?php echo defined('DESK_NOTIFY') ? (int) DESK_NOTIFY : 0; ?>;
    var CONTROLLER = '<?php echo CONTROLLER; ?>';
    var PAGE_NAME = '<?php echo PAGE_NAME; ?>';
    var ARC_CASE_PAGE_LIMIT = 10;
    var ARC_FILE_PAGE_LIMIT = 10;
    var PUSERS = <?php echo json_encode($GLOBALS['projUser'] ?? []); ?>;
    var ACUSERS = <?php echo json_encode($GLOBALS['AllCompUser']); ?>;
    var PROJECTS = <?php echo json_encode($GLOBALS['getallproj'] ?? []); ?>;
    var PROJECTS_ID_MAP = <?php echo json_encode($GLOBALS['project_id_map'] ?? []); ?>;
    var defaultAssign = '<?php echo $defaultAssign ?? 1; ?>';
    var dassign;
    var TASKTMPL = <?php echo json_encode($GLOBALS['getTmpl'] ?? []); ?>;
    var SITENAME = '<?php echo SITE_NAME; ?>';
    var DEFAULT_TASKVIEW = '<?php echo DEFAULT_TASKVIEW; ?>';
    var PLUGIN_NAME = '<?php echo PLUGIN_NAME; ?>';
    var DEFAULT_KANBANVIEW = '<?php echo DEFAULT_KANBANVIEW; ?>';
    var DEFAULT_TIMELOGVIEW = '<?php echo DEFAULT_TIMELOGVIEW; ?>';
    var DEFAULT_PROJECTVIEW = '<?php echo DEFAULT_PROJECTVIEW; ?>';
    var DEFAULT_VIEW_TASK = '<?php echo DEFAULT_VIEW_TASK; ?>';
    var DEFAULT_VIEW_VALUE = '<?php echo DEFAULT_VIEW_VALUE; ?>';
    var USE_SCRUM_PLUGIN_BACKLOG = '<?php echo defined('USE_SCRUM_PLUGIN_BACKLOG') ? USE_SCRUM_PLUGIN_BACKLOG : '0'; ?>';
    var USE_SCRUM_PLUGIN_BOARD = '<?php echo defined('USE_SCRUM_PLUGIN_BOARD') ? USE_SCRUM_PLUGIN_BOARD : '0'; ?>';

    function switchToScrumPluginView(surface) {
        if (surface !== 'backlog' && surface !== 'board') return false;
        $.ajax({
            url: HTTP_ROOT + 'users/scrumViewPreference',
            type: 'POST',
            data: { surface: surface, value: 1 },
            headers: { 'X-CSRF-Token': _csrfToken },
            dataType: 'json',
            success: function (resp) {
                if (resp && resp.success) {
                    var path = surface === 'board' ? 'sprint' : 'backlog';
                    var pid = ($('#projFil').val() || '');
                    if (pid === 'all') pid = '';
                    window.location.assign(HTTP_ROOT + path + (pid ? '#/' + pid : ''));
                } else {
                    showTopErrSucc('error', (resp && resp.error) || 'Could not switch view.');
                }
            },
            error: function () {
                showTopErrSucc('error', 'Could not switch view.');
            }
        });
        return false;
    }
    var DEFAULT_PAID = '<?php echo SES_COMP; ?>';
    var CMP_ARABK = '<?php echo SES_COMP; ?>';
    var SHOW_ARABK = '<?php echo SHOW_ARABIC; ?>';
    var EDIT_TASK = 1; //'<?php //echo $edit_task;
    ?>';
    var NODEJS_SECURE = '<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "true" : "true"; ?>';
    var COMPANY_WORK_HOUR = '<?php echo $GLOBALS['company_work_hour'] ?>';
    var COMPANY_WEEK_ENDS = '<?php echo $GLOBALS['company_week_ends'] ?>';
    var COMPANY_HOLIDAY = '<?php echo $GLOBALS['company_holiday'] ?? '' ?>';
    var LANG_PREFIX = '<?php echo LANG_PREFIX; ?>';
    var SES_TIME_FORMAT = '<?php echo SES_TIME_FORMAT; ?>';
    var roleAccess = <?php echo json_encode($roleAccess); ?>;
    var METHODOLOGY = '<?php echo $_SESSION['project_methodology']; ?>';
    var ALLMETHODOLOGY = <?php echo json_encode(Cache::read('allTemplate')); ?>;

    var CMP_CREATED = "<?php echo CMP_CREATED; ?>";

    var COMP_LAYOUT = "<?php echo (defined('COMP_LAYOUT') && COMP_LAYOUT) ? COMP_LAYOUT : 0; ?>";
    var KEEP_HOVER_EFFECT = "<?php echo (isset($_SESSION['KEEP_HOVER_EFFECT']) && $_SESSION['KEEP_HOVER_EFFECT']) ? $_SESSION['KEEP_HOVER_EFFECT'] : 0; ?>";

    var GCAPTCH_KEY = "<?php echo (defined('GCAPTCH_KEY') && GCAPTCH_KEY) ? GCAPTCH_KEY : 0; ?>";

    var DEFAULT_TASK_TYPES = {
        "bug": "&#xE60E;",
        "enh": "&#xE01D;",
        "cr": "&#xE873;",
        "dev": "&#xE1B0;",
        "idea": "&#xE90F;",
        "mnt": "&#xE869;",
        "oth": "&#xE892;",
        "qa": "Q",
        "rel": "&#xE031;",
        "rnd": "&#xE8FA;",
        "unt": "&#xE3E8;",
        "upd": "&#xE923;"
    };
    var DEFAULT_THEME_COLOR = 'amber';
    var bxslid = null;
    var bxslid1 = null;
    var TLUSER = null;
    var EPIC_DATE = '<?php echo EPIC_DATE; ?>';
    var TITLE_DLYUPD = '<?php echo "Daily Update - " . date("m/d"); ?>';
    var RELEASE = '<?php echo RELEASE; ?>';
    var CompWorkHR = <?php echo $GLOBALS['company_work_hour'] == '' ? 8 : $GLOBALS['company_work_hour']; ?>;
    var CHAT_ONLINE_USERS = [];
    var IS_WIKI_ENABLED = +'<?php echo intval($this->Format->isWikiEnabled()); ?>';
    var CUSTOM_FIELD_LIMIT = +'<?php echo intval($this->Format->customFieldLimit()); ?>';
</script>

<script type="text/javascript" src="<?php echo JS_PATH; ?>bootstrap.min.js?v=<?php echo ASSET_RELEASE; ?>"
    defer></script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>material.min.js?v=<?php echo ASSET_RELEASE; ?>"
    defer></script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>jquery.dropdown.js?v=<?php echo ASSET_RELEASE; ?>"
    defer></script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>ripples.min.js?v=<?php echo ASSET_RELEASE; ?>" defer></script>
<?php if (PAGE_NAME == "dashboard") { ?>
    <script type="text/javascript" src="<?php echo JS_PATH; ?>jquery.contextMenu.min.js?v=<?php echo ASSET_RELEASE; ?>"
        defer></script>
<?php } ?>
<script type="text/javascript" src="<?php echo JS_PATH; ?>jquery.mask.min.js?v=<?php echo ASSET_RELEASE; ?>"
    defer></script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>selectize.min.js?v=<?php echo ASSET_RELEASE; ?>"
    defer></script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>angular_select.js?v=<?php echo ASSET_RELEASE; ?>"
    defer></script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>moment.js?v=<?php echo ASSET_RELEASE; ?>" defer></script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>xeditable.min.js?v=<?php echo ASSET_RELEASE; ?>"
    defer></script>
<script type="text/javascript"
    src="<?php echo JS_PATH; ?>bootstrap-datetimepicker.min.js?v=<?php echo ASSET_RELEASE; ?>" defer></script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>select2.min.js?v=<?php echo ASSET_RELEASE; ?>" defer></script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>os_core.js?v=<?php echo ASSET_RELEASE; ?>" defer></script>

<?php if ((CONTROLLER == 'templates') || (CONTROLLER == 'easycases' && (PAGE_NAME == "mydashboard" || PAGE_NAME == "dashboard")) || (CONTROLLER == 'projectreports' && PAGE_NAME == "dashboard")) { ?>
    <script type="text/javascript" src="<?php echo JS_PATH; ?>jquery-ui-1.10.3.js?v=<?php echo ASSET_RELEASE; ?>"
        defer></script>
<?php } else if ((CONTROLLER != 'easycases' && PAGE_NAME != "dashboard" && PAGE_NAME != "manageStatus" && PAGE_NAME != "bookmarksList" && trim(PAGE_NAME) != "customField" && trim(PAGE_NAME) != "project_custom_field")) { ?>
        <!-- <script type="text/javascript" src="<?php echo JS_PATH; ?>jquery-ui-1.9.2.custom.min.js" defer></script> -->
<?php } ?>
<script type="text/javascript" src="<?php echo JS_PATH; ?>script_v1.js?v=<?php echo ASSET_RELEASE; ?>" defer></script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>easycase_new.js?v=<?php echo ASSET_RELEASE; ?>"
    defer></script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>jquery.tipsy.js?v=<?php echo ASSET_RELEASE; ?>"
    defer></script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>jquery.lazyload.min.js?v=<?php echo ASSET_RELEASE; ?>"
    defer></script>
<script type="text/javascript"
    src="<?php echo JS_PATH; ?>adv-tinymce/tinymce/tinymce.min.js?v=<?php echo ASSET_RELEASE; ?>" defer></script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>jquery.fcbkcomplete.js?v=<?php echo ASSET_RELEASE; ?>"
    defer></script>
<script type="text/javascript">
    var DOMAIN_COOKIE = "<?php echo DOMAIN_COOKIE; ?>";
</script>
<script type="text/javascript" src="<?php echo JS_PATH; ?>fileupload.js?v=<?php echo ASSET_RELEASE; ?>" defer></script>

<?php if (CONTROLLER == "templates" && (PAGE_NAME == "tasks" || PAGE_NAME == "projects")) { ?>
    <script type="text/javascript">
        $(document).ready(function () {
            tinymce.init({
                selector: "#desc, #desc_edit",
                plugins: 'image paste importcss autolink directionality fullscreen link template table charmap hr pagebreak nonbreaking anchor advlist lists wordcount autoresize imagetools help',
                menubar: false,
                branding: false,
                statusbar: false,
                toolbar: 'bold italic underline strikethrough | outdent indent | numlist bullist | forecolor backcolor fullscreen',
                toolbar_sticky: true,
                importcss_append: true,
                image_caption: true,
                browser_spellcheck: true,
                quickbars_selection_toolbar: 'bold italic | quicklink h2 h3',
                toolbar_drawer: 'sliding',
                contextmenu: "link",
                resize: false,
                min_height: 130,
                max_height: 400,
                paste_data_images: false,
                paste_as_text: true,
                autoresize_on_init: true,
                autoresize_bottom_margin: 20,
                content_css: HTTP_ROOT + 'css/tinymce.css?v=' + ASSET_RELEASE,
                setup: function (ed) {
                    ed.on('Click', function (ed, e) { });
                    ed.on('KeyUp', function (ed, e) { });
                    ed.on('Change', function (ed, e) { });
                    ed.on('init', function (e) { });
                }
            });
        });
    </script>
<?php } ?>

<?php
$allowedPages = [
    'defectDetails',
    'defect',
    'mydashboard',
    'mydashboardv2',
    'dashboard',
    'milestone',
    'milestonelist',
    'resourceUtilization',
    'pendingTask'
];
$allowedControllerPageCombinations = [
    ['controller' => 'archives', 'page' => 'listall'],
    ['controller' => 'yourworks', 'page' => 'index'],
    ['controller' => 'epics', 'page' => 'index'],
    ['controller' => 'epics', 'page' => 'features'],
];

/*
 * The Vue task pages open the same task-detail slider, which lives in
 * dashboard_v1.js. Without it a task could only be opened by navigating to
 * /dashboard first — a full page load that also stranded you on the legacy
 * list when you closed the panel.
 *
 * Matched on the controller, not a page: TaskViews renders one action but sets
 * PAGE_NAME per tab (views / subtasks / myworks / kanban / calendar / overview),
 * so a page-specific entry would only ever cover one of them.
 */
$allowedControllers = ['taskviews'];

if (
    in_array(PAGE_NAME, $allowedPages) ||
    in_array(CONTROLLER, $allowedControllers) ||
    in_array(['controller' => CONTROLLER, 'page' => PAGE_NAME], $allowedControllerPageCombinations)
):
    ?>
    <?= $this->Html->script('dashboard_v1.js?v=' . ASSET_RELEASE, ['defer' => true]); ?>
    <?= $this->Html->script('outer_js/jquery.magnific-popup.min.js?v=' . ASSET_RELEASE, ['defer' => true]); ?>
    <?= $this->Html->script('lightbox.js?v=' . ASSET_RELEASE, ['defer' => true]); ?>
<?php endif; ?>

<?php if (in_array(PAGE_NAME, ["mydashboard", "milestone", "dashboard", "milestonelist", "user_detail"]) || (CONTROLLER == "projects" && PAGE_NAME == "manage")): ?>
    <?= $this->Html->script('jquery/jquery.mousewheel.js?v=' . ASSET_RELEASE, ['defer' => true]); ?>
    <?= $this->Html->script('jquery/jquery.jscrollpane.min.js?v=' . ASSET_RELEASE, ['defer' => true]); ?>
    <?= $this->Html->css('jquery.jscrollpane.css?v=' . ASSET_RELEASE); ?>
<?php endif; ?>

<?= $this->Html->script('gettext.js?v=' . ASSET_RELEASE, ['defer' => true]); ?>
<?= $this->Html->script('introjs/intro.min.js?v=' . ASSET_RELEASE, ['defer' => true]); ?>

<?php
$gtLang = h(\Cake\I18n\I18n::getLocale());
?>
<?php /* English is the source language — no catalog exists for it. AppController
sets 'en' (not 'eng'), so match both to avoid requesting a 404ing en.json. */ ?>
<?php if (!in_array($os_locale, ['eng', 'en'], true)): ?>
    <link
        href="<?= $this->Url->build(['controller' => 'Languages', 'action' => 'index', $os_locale . '.json', '?' => ['v' => ASSET_RELEASE]], ['fullBase' => true]); ?>"
        lang="<?= $gtLang; ?>" rel="gettext" type="application/json" />
<?php endif; ?>


<script type="text/javascript">
    <?php if (PAGE_NAME != "dashboard" && PAGE_NAME != 'pricing' && PAGE_NAME != 'onbording') { ?>
        <?php if (CONTROLLER == "milestones" && PAGE_NAME == "manage") { ?>
            var project = $("#projFil").val();
        <?php } else { ?>
            var project = 'all';
        <?php } ?>
        var project = typeof $("#projFil").val() != 'undefined' ? $("#projFil").val() : 'all';
        <?php if (
            ((CONTROLLER == "projects" || CONTROLLER == "users") && PAGE_NAME == "manage")
            || (CONTROLLER == "projects" && PAGE_NAME == "groupupdatealerts")
            || (CONTROLLER == "archives" && PAGE_NAME == "listall")
            || (CONTROLLER == "templates" && PAGE_NAME == "tasks")
        ) { ?>
            project = 'all';
        <?php } ?>
        /** Call to new RequestsController function as per optimization process */
        $.ajax({
            url: HTTP_ROOT + "requests/ajaxProjectSize",
            type: 'POST',
            data: {
                "projUniq": project,
                "pageload": 0
            },
            dataType: "json",
            headers: {
                'X-CSRF-Token': _csrfToken
            },
            success: function (data) {
                if (data) {
                    $('#csTotalHours').html(data.used_text);
                    if (data.last_activity) {
                        $('#projectaccess').html(data.last_activity);
                        $('#last_project_id').val(data.lastactivity_proj_id);
                        $('#last_project_uniqid').val(data.lastactivity_proj_uid);
                        var url = document.URL.trim();
                        if (isNaN(url.substr(url.lastIndexOf('/') + 1)) && (url.substr(url.lastIndexOf('/') + 1)).length != 32) {
                            $('#selproject').val($('#last_project_id').val());
                            $('#project_id').val($('#last_project_id').val());
                        }
                        <?php if (CONTROLLER == "milestones" && PAGE_NAME == "add" && !$milearr['Milestone']['project_id']) { ?>
                            $('#selproject').val(data.lastactivity_proj_id);
                            $('#project_id').val(data.lastactivity_proj_id);
                        <?php } ?>
                    }
                }
            }
        });
    <?php }
    if (!$this->Format->isiPad()) { ?>
        $(function () {
            checkuserlogin();
        });
    <?php } ?>
    var window_height = $(window).height();
    var top_menubar_height = $(".navbar.custom-navbar").height();
    var left_menu_height = (window_height) - (top_menubar_height);
    $(".left-menu-panel .fixed_left_nav").css({
        "height": left_menu_height
    });
    $(function () {
        $('[rel="tooltip_down_btm"]').tipsy({
            gravity: 'n',
            fade: true
        });
        $('[rel="tooltip_down"]').tipsy({
            gravity: 'w',
            fade: true
        });
        $('[rel="tooltip_bot"]').tipsy({
            gravity: 'n',
            fade: true
        });
        $(".circle_refer_friend").tipsy({
            gravity: 'e',
            fade: true
        });
        $.material.init();
        $(".hover-menu").on('click', function (e) {
            e.stopPropagation();
            if ($('#style_switcher').hasClass('switcher_active')) {
                $('#style_switcher_toggle').trigger('click');
            }
            if ($(".hover-menu").find('.top_maindropdown-menu').hasClass("fadein_bkp") && $(this).hasClass('profl_nav_active_section')) {
                $(".hover-menu").removeClass('profl_nav_active_section').removeClass('open');
                $(".hover-menu").find('.top_maindropdown-menu').removeClass("fadein_bkp").addClass("fadeout_bkp").hide();
            } else {
                $(".hover-menu").removeClass('profl_nav_active_section');
                $(".hover-menu").find('.top_maindropdown-menu').removeClass("fadein_bkp").addClass("fadeout_bkp").hide();
                $(this).find('.top_maindropdown-menu').removeClass("fadeout_bkp").addClass("fadein_bkp").show();
                $(this).addClass('profl_nav_active_section').addClass('open');
            }
        });
        $('.hide_on_click li').not('.not-hide-li').on('click', function (e) {
            e.stopPropagation();
            $('.top_maindropdown-menu').hide();
        });
        $('.top_maindropdown-menu').on('click', function (e) {
            if (!$(e.target).closest('div.top_maindropdown-menu').length && !$(e.target).closest('ul.top_maindropdown-menu').length) {
                $(this).removeClass("fadein_bkp").addClass("fadeout_bkp").hide();
            }
        });
        $(document).on('click', function () {
            $(".hover-menu").removeClass('profl_nav_active_section');
            $(this).find('.top_maindropdown-menu').removeClass("fadein_bkp").addClass("fadeout_bkp").hide();
        });

        $(".prevent_togl_li").click(function (event) {
            event.stopPropagation();
        });
        $(".template-menu-parent").click(function () {
            if (!$(this).hasClass('menu-logs') && $('body').hasClass('big-sidebar')) {
                if ($(".template-menu").css("display") == "none") {
                    $(".template-menu").css({
                        display: "block"
                    });
                    $(".template-menu-parent").find(".gly_mis.glyphicon").removeClass("glyphicon-menu-right");
                    $(".template-menu-parent").find(".gly_mis.glyphicon").addClass("glyphicon-menu-down");
                } else {
                    $(".template-menu").css({
                        display: "none"
                    });
                    $(".template-menu-parent").find(".gly_mis.glyphicon").removeClass("glyphicon-menu-down");
                    $(".template-menu-parent").find(".gly_mis.glyphicon").addClass("glyphicon-menu-right");
                }
            }
        });

        if ($(window).width() < 1000) {
            $(".respo_menu .material-icons").click(function () {
                $(".main-container").addClass("body_overlay");
                $(".left-menu-panel").show();
                $(".left-menu-panel").animate({
                    left: 0
                }, 'slow');
            });
            $(".main-container").click(function (e) {
                if (!$(e.target).parent('li').hasClass('list_miscl')) {
                    $(".main-container").removeClass('body_overlay');
                    $(".left-menu-panel").hide();
                    $(".left-menu-panel").animate({
                        left: -200
                    }, 'slow');
                }
            });

        }
        $(".more_in_menu").parent("li").click(function () {
            if ($(".more_menu_li").css("display") == "none") {
                $(".more_menu_li").css({
                    display: "block"
                });
                $(this).children("a.more_in_menu").html("<div class='more_n smenu'></div>Less");
                $(this).addClass("open");
                $(".cust_rec").css({
                    display: "none"
                });
            } else {
                $(".more_menu_li").css({
                    display: "none"
                });
                $(this).children("a.more_in_menu").html("<div class='more_n smenu'></div>More");
                $(this).removeClass("open");
                $(".cust_rec").css({
                    display: "block"
                });
            }
        });


        $('[rel=tooltip]').tipsy({
            gravity: 's',
            fade: true
        });
        $('.top_project_name').tipsy({
            gravity: 'n',
            fade: true
        });

        $(".scrollTop").click(function () {
            $('html, body').animate({
                scrollTop: 0
            }, 1200);
        });
        $('body').click(function () {
            $(".tipsy").remove();
            $(".hover-menu").removeClass('profl_nav_active_section');
            $(this).find('.top_maindropdown-menu').removeClass("fadein_bkp").addClass("fadeout_bkp").hide();
        });
    });

    function showhelp() {
        openPopup();
        $('.loader_dv').hide();
        $('.help_popup').show();
    }

    function trackEventGoogle(page, section, message) {
        return true;
    }

    function filter_ga(type, value) {
        return true;
    }

    function dashboadrview_ga(type) {
        return true;
    }

    function action_ga(type) {
        return true;
    }
</script>

<?php if (in_array(PAGE_NAME, ["profile", "manage"])): ?>
    <?= $this->Html->script('scripts/jquery.imgareaselect.pack.js?v=' . ASSET_RELEASE, ['defer' => true]); ?>
<?php endif; ?>

<?= $this->Html->script('jquery.fileupload.js?v=' . ASSET_RELEASE, ['defer' => true]); ?>
<?= $this->Html->script('jquery.fileupload-ui.js?v=' . ASSET_RELEASE, ['defer' => true]); ?>

<?= $this->Html->script('smart_input.js?v=' . ASSET_RELEASE, ['defer' => true]); ?>
<?= $this->Html->script('jquery.timepicker.min.js?v=' . ASSET_RELEASE, ['defer' => true]); ?>
<?= $this->Html->script('chart.umd.min.js?v=' . ASSET_RELEASE); ?>
<?= $this->Html->script('charts.js?v=' . ASSET_RELEASE); ?>

<script type="text/javascript">
    $(document).click(function (ev) {
        if (!$(ev.target).closest('ul.cust_drop_status').length) {
            $(".cust_drop_status").hide();
        }
    });

    function saveDefaultLanguage() {
        v = $("#notify_lang_pop").val();
        $(".loaderLanguage").show();
        $.post('<?php echo HTTP_ROOT; ?>users/saveUserData', {
            language: v
        }, function (res) {
            $(".loaderLanguage").hide();
            closePopup();
            location.reload();
        }, 'json');
    }

    function showMobileApppop() {
        openPopup();
        $("#dialog-form-iosandroid").css('width', '60%');
        $("#dialog-form-iosandroid").show();
        $(".mobile-app-ppop").show();
    }

    function closeMobilepop() {
        localStorage.setItem('isMobilepopup', 1);
        $("#dialog-form-iosandroid").hide();
        $(".mobile-app-ppop").hide();
    }

    function trackEventWithIntercom(event_name, meta) { return true; }
    function setSessionStorage(StorageRefer, StorageEvent) { }
</script>

<!-- Flash Success and error msg starts -->
<div id="topmostdiv">
    <?php
    if ($success ?? '') { ?>
        <div class="comn_message_div">
            <div class="comn_message_div_ctnir comn_message_div" style="overflow-x: hidden;"></div>
            <script>
                $(function () {
                    showTopErrSucc('success', "<?php echo $success; ?>");
                    setTimeout('removeMsg()', 9000);
                });
            </script>
        </div>
    <?php } elseif ($error ?? '') { ?>
        <?php if (!stristr($error, 'Object(CakeResponse)')) { ?>
            <div class="comn_message_div">
                <div class="comn_message_div_ctnir comn_message_div" style="overflow-x: hidden;"></div>
                <?php if (stristr("Your uploaded CSV file is blank", $error)) { ?>
                    <script>
                        $(function () {
                            showTopErrSucc('error', "<?php echo $error; ?>");
                            setTimeout('removeMsg()', 40000);
                        });
                    </script>
                <?php } else { ?>
                    <script>
                        $(function () {
                            <?php if (PAGE_NAME == 'importexport') { ?>
                                showTopErrSucc('error', "<?php echo $error; ?>", 1);
                            <?php } else { ?>
                                showTopErrSucc('error', "<?php echo $error; ?>");
                                setTimeout('removeMsg()', 9000);
                            <?php } ?>
                        });
                    </script>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="comn_message_div">
            <div class="comn_message_div_ctnir comn_message_div" style="overflow-x: hidden;"></div>
        </div>
    <?php } ?>
</div>
<!-- Flash Success and error msg ends -->

<!-- track login -->
<script type="text/javascript">
    <?php if (!Configure::read('debug', false)) { ?>
        function trackLogin() {
            $.get(HTTP_ROOT + "users/session_maintain").done(function (data) {
                if (data === 1) {
                    window.top.location = HTTP_ROOT + "users/login";
                }
            });
        }
        const TRACK_INTERVAL = 60000;
        setInterval(trackLogin, TRACK_INTERVAL);
    <?php } ?>
</script>


<script type="text/template" id="case_subtask_load_tmpl">
    <?php echo $this->element('case_subtasks_new'); ?>
</script>
<script type="text/template" id="case_subtasks_tmpl">
    <?php echo $this->element('case_subtasks_new'); ?>
</script>
<script type="text/template" id="fetchAllActivityTskTmpl">
    <?php echo $this->element('case_detail_right_activity_new'); ?>
</script>
<script type="text/template" id="case_timelog_load_tmpl">
    <?php echo $this->element('case_timelog_new'); ?>
</script>
<script type="text/template" id="fetchFilesTskDtlTmpl">
    <?php echo $this->element('case_files_new'); ?>
</script>
<?php if (\Cake\Core\Plugin::isLoaded('Dms')) { ?>
<?= $this->element('Dms.dms_task_files_loader') ?>
<?php } ?>
<script type="text/template" id="fetchChkLstTskTmpl">
    <?php echo $this->element('case_checklist_new'); ?>
</script>
<script type="text/template" id="fetchAllReminderTskTmpl">
    <?php echo $this->element('case_reminder_new'); ?>
</script>

<script type="text/template" id="fetchAllLinkedTskTmpl">
    <?php echo $this->element('case_link_task_new'); ?>
</script>

<script type="text/template" id="case_label_task_cmn_tmpl">
    <?php echo $this->element('case_label_task'); ?>
</script>
<script type="text/template" id="case_detail_right_activity_tmpl">
    <?php echo $this->element('case_detail_right_activity_new'); ?>
</script>
<script type="text/template" id="case_thread_tmpl">
    <?php echo $this->element('case_thread'); ?>
</script>