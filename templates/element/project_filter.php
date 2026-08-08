<style>
    /* Reduce extra height in filter checkboxes */
    .program-filter-section ul {
        padding-left: 0;
        margin-bottom: 0;
        margin: 0px;
    }

    .program-filter-section ul li {
        list-style: none;
        margin-bottom: 4px;
        /* Reduce space between items */
        padding: 0;
    }

    .program-filter-section .checkbox {
        margin: 0;
        padding: 0;
        line-height: 1.2;
    }

    .program-filter-section .checkbox label {
        margin-bottom: 8px;
        padding: 0;
        font-weight: normal;
        display: flex;
        align-items: center;
        font-size: 14px;
        color: #2e2e2e;
        gap: 10px;
    }
    .serch-box{padding: 10px;}
    .searchType.cmn_search{
        border: 1px solid #ddd;
        margin: 0 0 10px;
        padding: 5px 10px;
        border-radius: 3px;
        height: 30px;
        font-size: 14px;
        width: 100%;
}
.card-header .arrow {
    margin-left: auto;
    display: inline-block;
    transition: transform 0.3s;
    width: 4px;
    height: 4px;
    background: none;
    vertical-align: middle;
    content: '';
    border: 1px solid #cbcaca;
    border-width: 0 2px 2px 0;
    padding: 3px;
    transform: rotate(45deg);
    float: right;
    margin: 6px 12px 0 0;
}
.card-header .btn.collapsed .arrow {
    transform: rotate(45deg);
}
.card-header .btn:not(.collapsed) .arrow {
    transform: rotate(-135deg);
}
</style>

<div class="modal right fade filterModal program_filter" id="filterModal" role="dialog" data-backdrop="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="filter_title_sec"><?php echo __('Apply Filters'); ?></h3>
                <button type="button" class="close" id="tour_closeTaskFilter" data-dismiss="modal" aria-label="Close"
                    onclick="closeTaskFilter();"><span aria-hidden="true">&times;</span></button>
            </div>
            <!-- Filter Modal Body Start -->
            <div class="modal-body">
                <div class="program-filter-section filter_accordion">
                    <form method="get" action="" id="filterForm">
                        <div class="accordion" id="filterAccordion">

                            <!-- ProjectType Accordion -->
                            <div class="card">
                                <div class="card-header p-0" id="headingProjectType">
                                    <button class="btn btn-link w-100 text-left collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapseProjectType" aria-expanded="false" aria-controls="collapseProjectType">
                                        <?php echo __('Project Type'); ?>
                                        <span class="arrow"></span>
                                    </button>
                                </div>
                                <div id="collapseProjectType" class="collapse" aria-labelledby="headingProjectType" data-parent="#filterAccordion">
                                    <div class="serch-box">
                                        <input type="text" placeholder="<?php echo __('Search');?>" class="searchType cmn_search" onkeyup="projectSearchFilterItems(this, '#project-type-checkbox-list');" />
                                    </div>
                                    <div class="card-body checkbox-list">
                                        <ul id="project-type-checkbox-list"></ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Project Status Accordion -->
                            <div class="card">
                                <div class="card-header p-0" id="headingProjectStatus">
                                    <button class="btn btn-link w-100 text-left collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapseProjectStatus" aria-expanded="false" aria-controls="collapseProjectStatus">
                                        <?php echo __('Project Status'); ?>
                                        <span class="arrow"></span>
                                    </button>
                                </div>
                                <div id="collapseProjectStatus" class="collapse" aria-labelledby="headingProjectStatus" data-parent="#filterAccordion">
                                    <div class="serch-box">
                                        <input type="text" placeholder="<?php echo __('Search');?>" class="searchType cmn_search" onkeyup="projectSearchFilterItems(this, '#project-status-checkbox-list');" />
                                    </div>
                                    <div class="card-body checkbox-list">
                                        <ul id="project-status-checkbox-list"></ul>
                                    </div>
                                </div>
                            </div>
                            <!-- Project Client Accordion -->
                            <div class="card">
                                <div class="card-header p-0" id="headingCL">
                                    <button class="btn btn-link w-100 text-left collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapseCL" aria-expanded="false" aria-controls="collapseCL">
                                        <?php echo __('Client'); ?>
                                        <span class="arrow"></span>
                                    </button>
                                </div>
                                <div id="collapseCL" class="collapse" aria-labelledby="headingCL" data-parent="#filterAccordion">
                                    <div class="serch-box">
                                        <input type="text" placeholder="<?php echo __('Search');?>" class="searchType cmn_search" onkeyup="projectSearchFilterItems(this, '#project-client-checkbox-list');" />
                                    </div>
                                    <div class="card-body checkbox-list">
                                        <ul id="project-client-checkbox-list"></ul>
                                    </div>
                                </div>
                            </div>
                            <!-- Project Manager Accordion -->
                            <div class="card">
                                <div class="card-header p-0" id="headingPM">
                                    <button class="btn btn-link w-100 text-left collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapsePM" aria-expanded="false" aria-controls="collapsePM">
                                        <?php echo __('Project Manager'); ?>
                                        <span class="arrow"></span>
                                    </button>
                                </div>
                                <div id="collapsePM" class="collapse" aria-labelledby="headingPM" data-parent="#filterAccordion">
                                    <div class="serch-box">
                                        <input type="text" placeholder="<?php echo __('Search');?>" class="searchType cmn_search" onkeyup="projectSearchFilterItems(this, '#project-manager-checkbox-list');" />
                                    </div>
                                    <div class="card-body checkbox-list">
                                        <ul id="project-manager-checkbox-list"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Filter Modal Body End -->
        </div>
    </div>
</div>

<?php
$selectedProjectManager = isset($_GET['project_manager']) ? $_GET['project_manager'] : '';
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (localStorage.getItem('keepFilterModalOpen') === 'true') {
            $('#filterModal').modal('show');
            localStorage.removeItem('keepFilterModalOpen');
        }
    });

    const selectedProjectManager = "<?= $selectedProjectManager ?>";
    $(document).ready(function() {
        $('#filterModal').on('shown.bs.modal', function() {
            $(this).find('select').each(function() {
                $(this).select2({
                    width: '100%',
                    placeholder: '-- Select --',
                    allowClear: true
                });
            });
        });
        $('#filterModal').on('hidden.bs.modal', function() {
            $(this).find('select').select2('destroy');
        });
        $.ajax({
            url: HTTP_ROOT + "projects/ajaxProjectTypeFilter",
            type: 'POST',
            data: {
                'page': PAGE_NAME,
                'value': 'filter_type'
            },
            dataType: "html",
            headers: {
                'X-CSRF-Token': _csrfToken
            },
            success: function(data) {
                if (data) {
                    const cleanedData = $('<div>').html(data);
                    cleanedData.find('input.searchType').remove();
                    $('#project-type-checkbox-list').html(cleanedData.html());
                    var project_type = (PAGE_NAME == 'manage') ? localStorage.getItem('PROJECTMANAGETYPE') : localStorage.getItem('PROJECTTYPE');
                    if (project_type != '' && project_type != null) {
                        var diyArr = JSON.parse(project_type);
                        for (var i = 0; i < diyArr.length; i++) {
                            var diyId = diyArr[i];
                            if (PAGE_NAME == 'manage') {
                                document.getElementById('dprojectmanageType_' + diyId).checked = true;
                            } else {
                                document.getElementById('dprojectType_' + diyId).checked = true;
                            }
                        }
                    }
                }
            }
        });
        $.ajax({
            url: HTTP_ROOT + "projects/ajaxProjectStatusFilter",
            type: 'POST',
            data: {
                'page': PAGE_NAME,
                'value': 'filter_type'
            },
            dataType: "html",
            headers: {
                'X-CSRF-Token': _csrfToken
            },
            success: function(data) {
                if (data) {
                    const cleanedData = $('<div>').html(data);
                    cleanedData.find('input.searchType').remove();
                    $('#project-status-checkbox-list').html(cleanedData.html());
                    var created_by = (PAGE_NAME == 'manage') ? localStorage.getItem('PROJECTMANAGESTATUS') : localStorage.getItem('PROJECTSTATUS');
                    if (created_by != '' && created_by != null) {
                        var dpArr = JSON.parse(created_by);
                        for (var i = 0; i < dpArr.length; i++) {
                            var dpId = dpArr[i];
                            if (PAGE_NAME == 'manage') {
                                document.getElementById('dprojstatusmange_' + dpId).checked = true;
                            } else {
                                document.getElementById('dprojstatus_' + dpId).checked = true;
                            }
                        }
                    }
                }
            }
        });
        $.ajax({
            url: HTTP_ROOT + "projects/ajaxProjectClientsFilter",
            type: 'POST',
            data: {
                'page': PAGE_NAME,
                'value': 'filter_type'
            },
            dataType: "html",
            headers: {
                'X-CSRF-Token': _csrfToken
            },
            success: function(data) {
                if (data) {
                    const cleanedData = $('<div>').html(data);
                    cleanedData.find('input.searchType').remove();
                    $('#project-client-checkbox-list').html(cleanedData.html());
                    var clients = (PAGE_NAME == 'manage') ? localStorage.getItem('PROJECTMANAGECLIENTS') : localStorage.getItem('PROJECTCLIENTS');
                    if (clients != '' && clients != null) {
                        var dcArr = JSON.parse(clients);
                        for (var i = 0; i < dcArr.length; i++) {
                            var dcId = dcArr[i];
                            if (PAGE_NAME == 'manage') {
                                document.getElementById('dpmanageclients_' + dcId).checked = true;
                            } else {
                                document.getElementById('dclients_' + dcId).checked = true;
                            }
                        }
                    }
                }
            }
        });
        $.ajax({
            url: HTTP_ROOT + "projects/ajaxProjectManagerFilter",
            type: 'POST',
            data: {
                'page': PAGE_NAME,
                'value': 'filter_type'
            },
            dataType: "html",
            headers: {
                'X-CSRF-Token': _csrfToken
            },
            success: function(data) {
                if (data) {
                    const cleanedData = $('<div>').html(data);
                    cleanedData.find('input.searchType').remove();
                    $('#project-manager-checkbox-list').html(cleanedData.html());
                    var project_manager = (PAGE_NAME == 'manage') ? localStorage.getItem('PROJECTMANAGEMANAGER') : localStorage.getItem('PROJECTMANAGER');
                    if (project_manager != '' && project_manager != null) {
                        var dcArr = JSON.parse(project_manager);
                        for (var i = 0; i < dcArr.length; i++) {
                            var dcId = dcArr[i];
                            if (PAGE_NAME == 'manage') {
                                document.getElementById('dprojectmanage_manager_' + dcId).checked = true;
                            } else {
                                document.getElementById('dproject_manager_' + dcId).checked = true;
                            }
                        }
                    }
                }
            }
        });
        $(document).on('click', '.card-header', function(e) {
            if (!$(e.target).is('button')) {
                $(this).find('button[data-toggle="collapse"]').trigger('click');
            }
        });
        $(document).on('change', 'input[name="project_type[]"], input[name="project_status[]"], input[name="project_client[]"], input[name="project_manager[]"]', function() {
            updateProjectFilterTags();
        });


        function setCookie(name, value, days = 1) {
            const expires = new Date(Date.now() + days * 864e5).toUTCString();
            document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
        }

        function getCheckedValues(name) {
            return $(`input[name="${name}[]"]:checked`).map(function() {
                return $(this).closest('label').text().trim(); // Get the label text
            }).get();
        }

    });

    function updateProjectFilterTags() {
        const projectTypes = getCheckedValues('project_type');
        const projectStatuses = getCheckedValues('project_status');
        const projectClients = getCheckedValues('project_client');
        const projectManagers = getCheckedValues('project_manager');

        let tagsHtml = '';
        let showReset = false;

        if (projectTypes.length > 0) {
            tagsHtml += `<span class="filter-tag">${_('Type')}: ${projectTypes.join(', ')}</span>`;
            showReset = true;
        }
        if (projectStatuses.length > 0) {
            tagsHtml += `<span class="filter-tag">${_('Status')}: ${projectStatuses.join(', ')}</span>`;
            showReset = true;
        }
        if (projectClients.length > 0) {
            tagsHtml += `<span class="filter-tag">${_('Client')}: ${projectClients.join(', ')}</span>`;
            showReset = true;
        }
        if (projectManagers.length > 0) {
            tagsHtml += `<span class="filter-tag">${_('PM')}: ${projectManagers.join(', ')}</span>`;
            showReset = true;
        }

        $('#filtered_items_project').html(tagsHtml);
        if (showReset) {
            $('#savereset_filter_project').removeClass('d-none');
        } else {
            $('#savereset_filter_project').addClass('d-none');
        }
    }

    function resetAllFiltersproject() {
        $('#filterForm input[type="checkbox"]').prop('checked', false);
        $('#filtered_items_project').html('');
        $('#savereset_filter_project').addClass('d-none');

        $.ajax({
            url: `${HTTP_ROOT}programs/listProgram`,
            type: 'GET',
            data: {},
            success: function(response) {
                $('#DefectViewSpan').html(response);
            },
            error: function() {
                $('#DefectViewSpan').html('<div class="text-danger">' + _('Failed to load data. Please try again.') + '</div>');
            }
        });
    }
  
    function projectSearchFilterItems(input, listSelector) {
        var filter = input.value.toLowerCase();
        var $list = $(listSelector || $(input).closest('.serch-box').next('.card-body').find('ul'));
        $list.children('li').each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(filter) > -1);
        });
    }                               

    $(document).on('show.bs.collapse', '#filterAccordion .collapse', function () {
        $('#filterAccordion .collapse').not(this).collapse('hide');
    });
</script>