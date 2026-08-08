<?php
$page_array = ['glideChart', 'hoursReport', 'chart', 'weeklyusageReport', 'pendingTask', 'completedSprintReport', 'velocityReports'];
$left_menu_exist = null;
$sub_text_class = $this->Format->getsubmenucolor($theme_settings['sidebar_color'] ?? 'white');
$exp_plan = null;

?>
<div class="fixed_left_nav">

    <ul class="side-nav sidebar-menu ajax-draw-left-menu" id="side-menu-dynamic-cnt">
        <?php $cstm_order = \Cake\Cache\Cache::read('menuOrderlists'); ?>
        <?php echo $this->element('custom_left_menu_new', [
            'checked_left_menu_submenu' => $checked_left_menu_submenu,
            'roleAccess' => $roleAccess,
            'page_array' => $page_array,
            'left_menu_exist' => $left_menu_exist,
            'sub_text_class' => $sub_text_class,
            'exp_plan' => $exp_plan,
            'cstm_order' => $cstm_order[$_SESSION['project_template_view_id'] ?? null] ?? []
        ]); ?>
    </ul>
</div>


<script>

    function searchMenuSettings(searchText) {
        searchText = searchText.toLowerCase();
        const menuItems = document.querySelectorAll('.menu-item-container');
        let anyFound = false;
        
        // Save currently active menu
        const activeMenu = document.querySelector('.active-lists')?.closest('[id$="-submenu"]');
        const activeMenuId = activeMenu?.id;
        
        // If search is empty, reset to show only active menu
        if (!searchText.trim()) {
            // Show all menu containers
            menuItems.forEach(item => {
                item.style.display = '';
                // Hide all submenus
                const submenu = item.querySelector('[id$="-submenu"]');
                if (submenu) {
                    submenu.classList.remove('show');
                    // Show all submenu items
                    submenu.querySelectorAll('.all-list').forEach(submenuItem => {
                        submenuItem.style.display = '';
                    });
                }
            });
            
            // Show only the active menu
            if (activeMenuId) {
                const activeMenuContainer = document.getElementById(activeMenuId)?.closest('.menu-item-container');
                if (activeMenuContainer) {
                    activeMenuContainer.style.display = '';
                    document.getElementById(activeMenuId)?.classList.add('show');
                }
            }
            return;
        }
        
        // First collapse all menus
        document.querySelectorAll('[id$="-submenu"]').forEach(menu => {
            menu.classList.remove('show');
        });
        
        menuItems.forEach(item => {
            const menuTitle = item.querySelector('.menu-item')?.textContent.toLowerCase() || '';
            const submenuItems = item.querySelectorAll('.all-list');
            let found = menuTitle.includes(searchText);
            let submenuFound = false;
            
            submenuItems.forEach(submenu => {
                if (submenu.textContent.toLowerCase().includes(searchText)) {
                    found = true;
                    submenuFound = true;
                    submenu.style.display = '';
                } else {
                    submenu.style.display = 'none';
                }
            });
            
            if (found) {
                anyFound = true;
                item.style.display = '';
                const submenu = item.querySelector('[id$="-submenu"]');
                if (submenu) {
                    submenu.classList.add('show');
                    if (!submenuFound) {
                        // If match was in menu title, show all submenu items
                        submenuItems.forEach(submenu => {
                            submenu.style.display = '';
                        });
                    }
                }
            } else {
                item.style.display = 'none';
            }
        });

        if (!anyFound) {
            // Show "No results found" message
            menuItems.forEach(item => {
                item.style.display = 'none';
            });
            
            // If no search results, show only the previously active menu
            if (activeMenuId) {
                const activeMenuContainer = document.getElementById(activeMenuId)?.closest('.menu-item-container');
                if (activeMenuContainer) {
                    activeMenuContainer.style.display = '';
                    document.getElementById(activeMenuId)?.classList.add('show');
                }
            }
        }
    }    

    
    $(document).ready(function () {
        const currentUrl = window.location.href;
        const profileUrl = "<?php echo HTTP_ROOT . 'users/profile'; ?>";
        const $menuSettingsSidebar = $('#menu-settings-sidebar');
        const $mainTableContent = $('.wrapper-body');
        const $menuSettLi = $(".menu_sett_li");

        $menuSettLi.find("a").on("click", function () {
            localStorage.setItem('menuSettingsSidebarState', 'visible');
            window.location.href = profileUrl;
        });

        if (currentUrl.includes(profileUrl)) {
            $menuSettLi.addClass("active");
        }

        // Load sidebar state on page load
        if (localStorage.getItem('menuSettingsSidebarState') === 'visible' && $menuSettingsSidebar.length) {
            $menuSettingsSidebar.addClass('visible').show();
            $mainTableContent.addClass('shifted');
        }
        
        $mainTableContent.show();
        $('#beforeRenderPage').remove();
    });

    function closeSidebar(event) {
        event.stopPropagation();
        var $menuSettingsSidebar = $('#menu-settings-sidebar');
        var $mainTableContent = $('.wrapper-body');
        if (!$menuSettingsSidebar.length) {
            window.location.href = HTTP_ROOT + 'users/profile';
            return;
        }

        if ($menuSettingsSidebar.length) {
            $menuSettingsSidebar.css('transition', 'transform 0.5s ease-in-out, opacity 0.2s ease-in-out');
            var isVisible = $menuSettingsSidebar.toggleClass('visible').hasClass('visible');
            if ($mainTableContent.length) {
                $mainTableContent.css({
                    'width': '100%',
                    'transition': 'padding-left 0.5s ease-in-out'
                });
                $mainTableContent.toggleClass('shifted', isVisible);
                $mainTableContent.show();
                $menuSettingsSidebar.show();
            }
            $(event.target).css('transform', isVisible ? 'rotate(0deg)' : 'rotate(180deg)');
            localStorage.setItem('menuSettingsSidebarState', isVisible ? 'visible' : 'hidden');
        }
    }

    function toggleSubmenu(submenuId, arrowId) {
        const $submenus = $('.multi-menu div[id$="-submenu"]');
        const $arrows = $('.multi-menu i[id$="-arrow"]');
        const $submenu = $('#' + submenuId);
        const $arrow = $('#' + arrowId);

        $submenus.not('#' + submenuId).each(function () {
            $(this).css({
                'transition': 'max-height 0.3s ease-out, opacity 0.3s ease-out',
                'opacity': '0'
            });
            setTimeout(() => $(this).hide(), 300);
        });

        $arrows.not('#' + arrowId).text('keyboard_arrow_right');

        const isHidden = $submenu.is(':hidden');

        if (isHidden) {
            $submenu.show().css({
                'transition': 'max-height 0.3s ease-in, opacity 0.3s ease-in',
                'overflow-y': $submenu.prop('scrollHeight') > 440 ? 'auto' : 'hidden',
                'scrollbar-width': $submenu.prop('scrollHeight') > 440 ? 'thin' : '',
                'scrollbar-color': $submenu.prop('scrollHeight') > 440 ? '#8888886e #f1f1f1' : '',
                'opacity': '1'
            });

            $arrow.text('keyboard_arrow_down');

            const $activeItem = $submenu.find('.active-lists');
            if ($activeItem.length) {
                $activeItem[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            $submenu.css({
                'transition': 'max-height 0.3s ease-out, opacity 0.3s ease-out',
                'opacity': '0'
            });
            setTimeout(() => $submenu.hide(), 300);

            $arrow.text('keyboard_arrow_right');
        }
    }

    window.onload = function () {
        var activeItems = document.querySelectorAll('.active-lists');
        activeItems.forEach(function (activeItem) {
            var submenu = activeItem.closest('div[id$="-submenu"]');
            if (submenu) {
                submenu.style.display = "block";
                setTimeout(function () {
                    activeItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
                var submenuId = submenu.id;
                var arrowId = submenuId.replace('-submenu', '-arrow');
                var arrow = document.getElementById(arrowId);
                if (arrow) {
                    arrow.textContent = "keyboard_arrow_down";
                }
            }
        });
    };</script>