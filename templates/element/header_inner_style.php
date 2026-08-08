<style type="text/css">
    /* Sidebar toggle (panel icon). Dim slightly when collapsed instead of
       rotating, so the affordance reads as on/off rather than a direction. */
    .toggle-icon {
        transition: opacity 0.2s ease-in-out;
        cursor: pointer;
        vertical-align: middle;
    }
    body.mini-sidebar .toggle-icon {
        opacity: 0.6;
    }
    /* Hide the double_arrow icon when sidebar is collapsed */
    body.mini-sidebar .dbl_arrow_icon {
        display: none !important;
    }
    /* Smooth sidebar panel width transition */
    .left-menu-panel .option_menu_panel .fixed_left_nav {
        transition: width 0.3s ease-in-out;
    }
    /* Smooth main content shift */
    .rht_content_cmn {
        transition: padding-left 0.3s ease-in-out;
    }
    .pad-left-non {
        padding-left: 0;
    }

    .project-name-hd {
        min-width: 138px;
        background: none;
        box-shadow: none;
        border-right: 1px solid #6A7A89;
        font-family: "RobotoDraft-Medium";
    }

    ul.dropdown-menu.drop_menu_mc.dropdown_menu_all_filters_ul.header-items-ui {
        left: -80px;
    }

    /* Right-align to the palette icon. A fixed negative `left` (inherited from
       .dropdown_menu_all_filters_ul) pushed the panel past the viewport edge. */
    ul.dropdown-menu.drop_menu_mc.dropdown_menu_all_filters_ul.theme_drop_menu_mc {
        left: auto;
        right: 0;
        min-width: 190px;
        max-height: calc(100vh - 60px);
        overflow-y: auto;
    }

    .navbar-brand.nav-logo {
        background: none;
        border-right: none;
        box-shadow: none;
        padding: 8px 0px 8px 19px;
    }

    .navbar .navbar-nav>li.nav-logo>a {
        padding: 16px 10px 16px 10px;
        color: #2e2e2e;
        font-size: 16px;
        width: 136px;
        text-align: left;
    }

    .coupon-hello-bar {
        height: 50px;
        width: 100%;
        background: #ccb485;
        position: fixed;
        top: 0;
        text-align: center;
        padding: 0 5%;
        z-index: 999;
    }

    .coupon-hello-bar p {
        display: inline-block;
        vertical-align: middle;
        line-height: 40px;
        font-weight: normal;
        color: #2e2e2e;
        font-size: 13px;
        margin: 0;
        padding: 0;
    }

    .coupon-hello-bar a {
        text-decoration: none;
    }

    .coupon-hello-bar a:hover {
        text-decoration: none;
    }

    .coupon-hello-bar a:hover span {
        color: #436089;
        border-color: #436089;
    }

    .coupon-hello-bar span {
        border: 1px dashed #2e2e2e;
        color: #2e2e2e;
        padding: 3px 5px;
        font-weight: 600;
    }

    .coupon-hello-bar .v-seperator {
        width: 2px;
        height: 18px;
        background: #2e2e2e;
        margin: 0 5px;
        display: inline-block;
        vertical-align: middle;
    }

    .tandc {
        text-align: center;
        margin-top: -8px;
        color: #333;
        font-weight: 600;
        font-size: 11px;
        font-style: italic;
        text-align: center;
    }

    .custom-navbar.nav_inr_menu .right_pfl_menu .left_trial {
        margin-right: 15px;
    }

    .custom-navbar.nav_inr_menu .container-fluid {
        position: relative;
    }

    .custom-navbar.nav_inr_menu .upgrade-btn-center {
        position: absolute;
        left: 50%;
        top: 10px;
        transform: translateX(-50%);
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .custom-navbar.nav_inr_menu .upgrade-trial-days {
        display: inline-block;
        margin-top: 0;
        font-size: 11px;
        color: #2e2e2e;
        white-space: nowrap;
    }


    .nav.navbar-nav .btn.btn_cmn_efect.cmn_bg.btn-info,
    .right_pfl_menu .btn.btn_cmn_efect.cmn_bg.btn-info.upgrd_btn {
        padding: 6px 15px;
        width: auto;
        border-radius: 20px;
    }

    .custom-navbar.nav_inr_menu .right_pfl_menu .profile-bar>li {
        padding: 5px 9px;
    }

    .custom-navbar.nav_inr_menu .right_pfl_menu .quick_tour a {
        position: static
    }

    .custom-navbar.nav_inr_menu .right_pfl_menu .quick_tour a .refer_frnd {
        display: block;
        margin: 0 auto
    }

    .custom-navbar.nav_inr_menu .right_pfl_menu .quick_tour a small {
        font-size: 12px;
        display: block;
        margin: 0;
        position: relative;
        bottom: 0;
    }

    .custom-navbar.nav_inr_menu .right_pfl_menu {
        position: relative
    }

    .custom-navbar.nav_inr_menu .right_pfl_menu .left_trial.upgrd {
        padding: 0;
        position: absolute;
        left: 34px;
        right: 0;
        margin: auto;
        display: inline-block;
        width: 283px;
        top: 25px;
        float: none;
    }


    .profile-bar ul.dropdown-menu li.top_hader_dropdown_listing {
        position: relative;
        overflow: visible;
        font-size: 13px;
        color: #181717;
        font-weight: 500;
        max-width: 100%;
        padding: 5px 5px 5px 15px;
    }

    .profile-bar ul.dropdown-menu li.top_hader_dropdown_listing a,
    .profile-bar ul.dropdown-menu li.top_hader_dropdown_listing a:hover {
        text-decoration: none;
        font-size: 13px;
        color: #5f5e60;
        font-family: 'Inter', sans-serif;
        display: block;
        text-align: left;
        border-radius: 0px;
        padding: 5px;
        background-color: unset;
    }

    .profile-bar ul.dropdown-menu li.top_hader_dropdown_listing:hover {
        color: #2e2e2e;
        background: #f6f6f6;
        border-bottom: 0px solid #ccc;
    }

    .profile-bar .top_header_icon_sec.hlp-icon {
        margin-right: 3px;
    }

    .profile-bar .top_header_icon_sec::after {
        content: '';
        background: url(<?php echo $this->Url->build('/img/header/arrow-dropdown-header.png'); ?>) no-repeat 0px 0px;
        position: absolute;
        display: block;
        right: -16px;
        top: 9px;
        height: 14px;
        width: 14px;
    }

    .pfl_dtl_li .top_header_icon_sec.dropdown_icon_black_second::after {
        content: '';
        background: url(<?php echo $this->Url->build('/img/header/drop-arrow.png'); ?>) no-repeat 0px 0px;
        position: absolute;
        display: block;
        right: -16px;
        top: 9px;
        height: 14px;
        width: 14px;
    }

    .pfl_dtl_li .top_header_icon_sec::after {
        right: -16px;
        top: 12px;
    }

    span#heder_timer_tab {
        display: block;
        position: absolute;
        width: 70px;
        padding: 3px;
        left: -18px;
        top: -12px;
        z-index: 999999;
        font-size: 11px;
        line-height: 14px;
        height: 20px;
        vertical-align: middle;
        border-radius: 4px;
    }

    .help_dropdown_text {
        font-size: 14px;
        line-height: 13px;
    }

    i.material-icons.cmn-icon-prop.start_timer_icon {
        color: #727272;
        font-size: 18px;
        width: 22px;
        position: absolute;
        top: 9px;
        left: 19px;
        font-weight: normal;
        padding: 0px;
        background: none;
        display: inline-block;
    }

    .drop_menu_mc.top_hader_dropdown_listing a#header_timer_i {
        padding-left: 25px
    }

    .profile-bar ul.dropdown-menu li.top_hader_dropdown_listing:hover i {
        color: #5f5e60;
    }
</style>