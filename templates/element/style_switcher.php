<style>
    .material .material-icons {
        display: none
    }

    .material.material-off .material-icons:last-of-type {
        display: inline
    }

    .material:not(.material-off) .material-icons:first-of-type {
        display: inline
    }
     /* Map theme slugs to colors for indicators */
    .indicator-default { background-color: #444; }
    .indicator-deep-purple { background-color: #4100f3ff; }
    .indicator-pink { background-color: #d81b60; }
    .indicator-cyan { background-color: #06b6d4; }
    .indicator-amber { background-color: #f59e0b; }
    .indicator-teal { background-color: #00796b; }
    .indicator-light-blue { background-color: #0ea5e9; }
    .indicator-purple { background-color: #7c4dff; }
</style>
<?php 
    $themes = [
    'default' => 'Default',
    'deep-purple'  => 'Royal Amethyst',
    'pink'         => 'Blush Petals',
    'cyan'         => 'Crystal Lagoon',
    'amber'        => 'Golden Ember',
    'teal'         => 'Emerald Tide',
    'light-blue'   => 'Sky Serenity',
    'purple'       => 'Velvet Orchid'
];?>
<div id="style_switcher">
    <?php echo $this->Form->create(null, array('method' => 'post', 'name' => 'theme_settings', 'id' => 'theme_settings', 'autocomplete' => 'off')); ?>
    <?php echo $this->Form->input('sidebar_color', array('type' => "hidden", "id" => "sidebar_color")); ?>
    <?php echo $this->Form->input('navbar_color', array('type' => "hidden", "id" => "navbar_color")); ?>
    <!-- <div id="style_switcher_toggle" class="material"> <i class="material-icons">chevron_left</i>
        <i class="material-icons">chevron_right</i>
    </div> -->
    <div>
        <h4 class="heading_b"><?php echo __('Menu Options'); ?></h4>
        <h4 class="heading_c"><?php echo __('Menu Color'); ?></h4>
        
        <ul class="switcher_app_themes" id="leftmenu_theme_switcher">
            <?php foreach ($themes as $theme) { ?>
                <li class="nav-theme <?=$theme?>" data-app-theme="<?=$theme?>">
                    <span class="app_color_main"></span>
                </li>   
            <?php } ?> 
        </ul>
    </div>
    <div class="uk-visible-large os-margin-medium-bottom">
        <div class="checkbox">
            <label>
                <input type="checkbox" id="style_sidebar_mini" name="mini_leftmenu">
                <span><?php echo __('Menu Collapsed'); ?></span>
            </label>
        </div>
    </div>
    <div class="">
        <h4 class="heading_b"><?php echo __('Navbar Options'); ?></h4>
        <h4 class="heading_c"><?php echo __('Navbar Color'); ?></h4>
        <ul class="switcher_app_themes" id="navmenu_theme_switcher">
            <?php foreach ($themes as $theme) { ?>
                <li class="<?=$theme?>" data-app-theme="<?=$theme?>">
                    <span class="app_color_main"></span>
                </li>   
            <?php } ?>
        </ul>
    </div>
    <?php echo $this->Form->end(); ?>
</div>
<script>
    var thme_setting_url     = '<?= $this->Url->build(['controller' => 'Users', 'action' => 'ajaxSaveThemeSetting', 'plugin' => false]) ?>';
    var gettheme_setting_url  = '<?= $this->Url->build(['controller' => 'Users', 'action' => 'ajaxGetThemeSetting', 'plugin' => false]) ?>';
    var reset_setting_url = '<?= $this->Url->build(['controller' => 'Users', 'action' => 'ajaxResetThemeSetting', 'plugin' => false]) ?>';
    var existing_th_setting = '<?php echo json_encode($theme_settings); ?>'
    var existing_th_setting_obj = JSON.parse(existing_th_setting);;
    var $switcher = $('#style_switcher'),
        $switcher_toggle = $('#style_switcher_toggle'),
        $leftmenu_theme_switcher = $('#leftmenu_theme_switcher'),
        $navmenu_theme_switcher = $('#navmenu_theme_switcher'),
        $html = $('html'),
        $body = $('body');
    var theme_setting = {
        init: function() {
            this.getSetting();
            localStorage.removeItem(theme_setting);
            localStorage.setItem("theme_setting", JSON.stringify(existing_th_setting_obj));
            $switcher_toggle.click(function(e) {
                e.preventDefault();
                $switcher.toggleClass('switcher_active');
                $(this).toggleClass("material-off")
            });

            $leftmenu_theme_switcher.children('li').click(function(e) {
                e.preventDefault();
                var $this = $(this),
                    this_theme = $this.attr('data-app-theme');
                $('#sidebar_color').val(this_theme);
                $leftmenu_theme_switcher.children('li').removeClass('active_theme');
                $(this).addClass('active_theme');
                theme_setting.menuColor(this_theme);
                theme_setting.saveSetting();
            });

            $navmenu_theme_switcher.children('li').click(function(e) {
                e.preventDefault();
                var $this = $(this),
                    this_theme = $this.attr('data-app-theme');
                $('#navbar_color').val(this_theme);
                $navmenu_theme_switcher.children('li').removeClass('active_theme');
                $(this).addClass('active_theme');
                theme_setting.navColor(this_theme);
                theme_setting.saveSetting();
            });

            $('#style_sidebar_mini').change(function() {
                var el_id = $(this).attr('id');
                switch (el_id) {
                    case 'style_sidebar_mini':
                        toggleMenuBar();
                        break;
                    default:
                        $.noop();
                }
                theme_setting.saveSetting();
            });
            // hide style switcher

            $(document).on('click', function(e) {
                if ($switcher.hasClass('switcher_active')) {
                    if (
                        (!$(e.target).closest($switcher).length) ||
                        (e.keyCode == 27)
                    ) {
                        $switcher.removeClass('switcher_active');
                    }
                }
            });
            $(document).on('keyup', function(e) {
                e.preventDefault();
                if ($switcher.hasClass('switcher_active')) {
                    if (
                        (!$(e.target).closest($switcher).length) ||
                        (e.keyCode == 27)
                    ) {
                        $switcher.removeClass('switcher_active');
                    }
                }
            });

        },
        navColor: function(menu_color) {
            theme_setting.removenavColorClass(".upgrd_btn");
            $('.right_pfl_menu').removeClass(th_class_str).addClass('cmn_white_bg');
            menu_color == "gradient-45deg-white" && $('.right_pfl_menu').addClass('cmn_white_bg');

            $(".upgrd_btn").addClass(menu_color + " gradient-shadow");
            $(".profile-bar .top_header_icon_sec").css('color', 'gray');
            $('.profile-bar .top_header_icon_sec').addClass('dropdown_icon_black');
            $('.pfl_dtl_li .top_header_icon_sec').addClass('dropdown_icon_black_second');
        },
        removenavColorClass: function(el) {
            $(el).removeClass(th_class_str);
        },
        menuColor: function(menu_color) {
            var text_act_color = this.selSubmenu(menu_color);
            theme_setting.removeColorClass(".left-menu-panel .option_menu_panel .side-nav li.active,.left-menu-panel .option_menu_panel .side-nav li.active_bkp,.all_create_btn > a,.filter-dropdown .top_project_btn,.subscription_planbtn");
            theme_setting.removetextColorClass(".left-palen-submenu-items li > a,.left-palen-submenu-items li > a,.smenu_miscl_whit li.active_bk > a");
            menu_color == "gradient-45deg-white" && theme_setting.removeColorClass(".subscription_planbtn");
            $('.left-menu-panel,.logo_cmpnay_name_toggle').removeClass('cmn_white_bg').addClass('cmn_white_bg');
            $(".left-menu-panel .option_menu_panel .side-nav > li.active").css({
                background: "none",
                "box-shadow": "none"
            });
            $(".left-menu-panel .option_menu_panel .side-nav > li.active,.left-menu-panel .option_menu_panel .side-nav > li.active_bkp,.all_create_btn > a,.filter-dropdown .top_project_btn").addClass(menu_color + " gradient-shadow");
            $(".left-palen-submenu-items li.active > a,.left-palen-submenu-items li.active-list > a,.smenu_miscl_whit li.active_bk > a").addClass(text_act_color);
        },
        removetextColorClass: function(el) {
            $(el).removeClass(th_text_class_str);
        },
        removeColorClass: function(el) {
            $(el).removeClass(th_class_str);
        },
        selSubmenu: function(color) {
            var act_text_color;
            if (color.indexOf('gradient-45deg-') !== -1) {
                act_text_color = (color == "gradient-45deg-white") ? str_replace('gradient-45deg-', '', color) + "-text" : str_replace('gradient-45deg-', '', color);
            } else {
                act_text_color = color + "-text"
            }
            return act_text_color;
        },
        saveSetting: function(reload) {
            reload = (reload !== false);
            var frm = $('#theme_settings');
            var data = frm.serializeArray();
            $.ajax({
                    url: thme_setting_url,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                })
                .done(function(res) {
                    theme_setting.savetoStorage();
                    if (reload) {
                        window.location.reload();
                    }
                });
        },
        savetoStorage: function() {
            var th_setting_obj = new Object();
            th_setting_obj.sidebar_color = $('#sidebar_color').val();
            th_setting_obj.navbar_color = $('#navbar_color').val();
            th_setting_obj.style_sidebar_mini = $('#style_sidebar_mini').is(':checked') ? true : false;
            var th_setting_objJSON = JSON.stringify(th_setting_obj);
            localStorage.removeItem(theme_setting);
            localStorage.setItem("theme_setting", th_setting_objJSON);
        },
        getSetting: function() {
            $.ajax({
                    url: gettheme_setting_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {},
                })
                .done(function(res) {
                    if (!res.data.length) {
                        theme_setting.reset();
                        $('.reset_theme').hide();
                    } else {
                        theme_setting.setThemesetting(res.data);
                    }
                });
        },
        setThemesetting: function(data) {
            if (data.sidebar_color) {
                $leftmenu_theme_switcher.children('li').removeClass('active_theme');
                $leftmenu_theme_switcher.children('li[data-app-theme=' + data.sidebar_color + ']').addClass('active_theme');
                $('#sidebar_color').val(data.sidebar_color);
                // sync labeled dropdown
                var label = $('.theme-option[data-value="' + data.sidebar_color + '"]').find('span:not(.theme-indicator)').text();
                updateThemeSelection(data.sidebar_color, label);
            } else {
                $('#sidebar_color').val('');
                updateThemeSelection('default', 'Default');
            }
            if (data.navbar_color) {
                $navmenu_theme_switcher.children('li').removeClass('active_theme');
                $navmenu_theme_switcher.children('li[data-app-theme=' + data.navbar_color + ']').addClass('active_theme');
                $('#navbar_color').val(data.navbar_color);
            } else {
                $('#navbar_color').val('');
            }
            $('#style_sidebar_dark').prop('checked', parseInt(data.dark_leftmenu) ? !0 : !1);
            $('#navbar_dark').prop('checked', parseInt(data.dark_navbar) ? !0 : !1);
            $('#navbar_fixed').prop('checked', parseInt(data.fixed_navbar) ? !0 : !1);
            $('#footer_dark').prop('checked', parseInt(data.footer_dark) ? !0 : !1);
            $('#footer_fixed').prop('checked', parseInt(data.footer_fixed) ? !0 : !1);
            $('#style_sidebar_mini').prop('checked', parseInt(data.mini_leftmenu) ? !0 : !1);
            if (localStorage.getItem("theme_setting") === null) {
                theme_setting.savetoStorage();
            }
        },
        resetThemesetting: function() {
            $('.right_pfl_menu,.os_plus a,.upgrd_btn').removeClass(th_class_str);
            $(".left-menu-panel .option_menu_panel .side-nav > li.active,.all_create_btn > a,.filter-dropdown .top_project_btn").removeClass(th_class_str);
            $('.right_pfl_menu').addClass('cmn_white_bg');
            $('.left-menu-panel,.logo_cmpnay_name_toggle').removeClass('cmn_white_bg').removeClass('cmn_white_bg');
            this.reset();
            $.ajax({
                    url: reset_setting_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {},
                })
                .done(function(res) {
                    $.noop();
                });
        },
        reset: function() {
            $leftmenu_theme_switcher.children('li').removeClass('active_theme');
            $navmenu_theme_switcher.children('li').removeClass('active_theme');
            $('#sidebar_color,#navbar_color').val('');
            $('#theme_select').val('');
            $('#style_sidebar_mini').prop('checked', !1);
        }
    };
    $(function() {
        theme_setting.init();
    });
</script>