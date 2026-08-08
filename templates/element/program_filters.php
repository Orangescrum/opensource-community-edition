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
        font-size: 13px;
        color: #333;
        gap: 10px;
    }
        .serch-box{padding: 10px 0px;}
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
                <h3 class="modal-title" id="filter_title_sec">
                    <?php echo __('Apply Filters'); ?>
                    <span class="arrow"></span>
                </h3>
                <button type="button" class="close" id="tour_closeTaskFilter" data-dismiss="modal" aria-label="Close"
                    onclick="closeTaskFilter();"><span aria-hidden="true">&times;</span></button>
            </div>
            <!-- Filter Modal Body Start -->
            <div class="modal-body">
                <div class="program-filter-section filter_accordion">
                    <form method="get" action="" id="filterForm">
                        <div class="accordion" id="filterAccordion">

                            <!-- Priority Accordion -->
                            <div class="card">
                                <div class="card-header p-0" id="headingPriority">
                                    <button class="btn btn-link w-100 text-left collapsed" type="button" data-toggle="collapse" data-target="#collapsePriority" aria-expanded="false" aria-controls="collapsePriority">
                                        Priority
                                        <span class="arrow"></span>
                                    </button>
                                </div>
                                <div id="collapsePriority" class="collapse" aria-labelledby="headingPriority" data-parent="#filterAccordion">
                                    <div class="card-body checkbox-list">
                                        <ul>
                                            <li>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="priority[]" value="2" <?= in_array("2", $_GET['priority'] ?? []) ? 'checked' : '' ?> />
                                                        <?php echo __('Low'); ?>
                                                    </label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="priority[]" value="1" <?= in_array("1", $_GET['priority'] ?? []) ? 'checked' : '' ?> />
                                                        <?php echo __('Medium'); ?>
                                                    </label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="priority[]" value="0" <?= in_array("0", $_GET['priority'] ?? []) ? 'checked' : '' ?> />
                                                        <?php echo __('High'); ?>
                                                    </label>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Project Manager Accordion -->
                            <div class="card">
                                <div class="card-header p-0" id="headingPM">
                                    <button class="btn btn-link w-100 text-left collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapsePM" aria-expanded="false" aria-controls="collapsePM">
                                        Project Manager
                                        <span class="arrow"></span>
                                    </button>
                                </div>
                                <div id="collapsePM" class="collapse" aria-labelledby="headingPM" data-parent="#filterAccordion">
                                    <div class="card-body checkbox-list">
                                        <div class="serch-box mb-2">
                                            <input type="text" placeholder="<?= __('Search') ?>" class="searchType cmn_search" data-target="#pm-checkbox-list" />
                                        </div>
                                        <ul id="pm-checkbox-list"></ul>
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
            url: `${HTTP_ROOT}projects/ajaxGetAllMeta`,
            type: 'POST',
            success: function(res) {
                const selectedPMs = <?= json_encode($_GET['project_manager'] ?? []) ?>;
                let pmList = '';

                Object.entries(res.All_managers)
                    .filter(([key, _]) => key !== "0")
                    .forEach(([key, value]) => {
                        const isChecked = selectedPMs.includes(key) ? 'checked' : '';
                        pmList += `
                    <li>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="project_manager[]" value="${key}" ${isChecked}>
                                  <span class="checkbox-material"><span class="check"></span></span>
                                ${value}
                            </label>
                        </div>
                    </li>
                `;
                    });

                $('#pm-checkbox-list').html(pmList);
            }
        });
        $(document).on('click', '.card-header', function(e) {
            if (!$(e.target).is('button')) {
                $(this).find('button[data-toggle="collapse"]').trigger('click');
            }
        });
        $(document).on('change', 'input[name="priority[]"], input[name="organization[]"] ,input[name="project_manager[]"]', fetchFilteredPrograms);

        function setCookie(name, value, days = 1) {
            const expires = new Date(Date.now() + days * 864e5).toUTCString();
            document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
        }

        function getCheckedValues(name) {
            return $(`input[name="${name}[]"]:checked`).map(function() {
                return $(this).closest('label').text().trim(); // Get the label text
            }).get();
        }
        // Ajax fetch function
        function fetchFilteredPrograms() {
            const priorities = getCheckedValues('priority');
            const organizations = getCheckedValues('organization');
            const projectManagers = getCheckedValues('project_manager');

            // Save to cookies
            setCookie('filter_priority', JSON.stringify(priorities));
            setCookie('filter_organization', JSON.stringify(organizations));
            setCookie('filter_project_manager', JSON.stringify(projectManagers));
            const data = $('#filterForm').serialize(); // Serialize entire form

            $.ajax({
                url: `${HTTP_ROOT}programs/listProgram`,
                type: 'GET',
                data: data,
                success: function(response) {
                    $('#DefectViewSpan').html(response);
                    updateFilterTags(priorities, organizations, projectManagers);
                },
                error: function() {
                    $('#DefectViewSpan').html('<div class="text-danger">Failed to load data. Please try again.</div>');
                }
            });
        }
    });

    function updateFilterTags(priorities, organizations, projectManagers) {
        let tagsHtml = '';
        let showReset = false;
        if (priorities.length > 0) {
            tagsHtml += `<span class="filter-tag">Priority: ${priorities.join(', ')}</span>`;
            showReset = true;
        }
        if (organizations.length > 0) {
            tagsHtml += `<span class="filter-tag">Org: ${organizations.join(', ')}</span>`;
            showReset = true;
        }
        if (projectManagers.length > 0) {
            tagsHtml += `<span class="filter-tag">PM: ${projectManagers.join(', ')}</span>`;
            showReset = true;
        }

        $('#filtered_items_program').html(tagsHtml);
        if (showReset) {
            $('#savereset_filter_program').removeClass('d-none');
        } else {
            $('#savereset_filter_program').addClass('d-none');
        }
    }

    function resetAllFiltersProgram() {
        $('#filterForm input[type="checkbox"]').prop('checked', false);
        $('#filtered_items_program').html('');
        $('#savereset_filter_program').addClass('d-none');

        $.ajax({
            url: `${HTTP_ROOT}programs/listProgram`,
            type: 'GET',
            data: {},
            success: function(response) {
                $('#DefectViewSpan').html(response);
            },
            error: function() {
                $('#DefectViewSpan').html('<div class="text-danger">Failed to load data. Please try again.</div>');
            }
        });
    }

    $(document).ready(function() {
        $('.searchType').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            var targetList = $($(this).data('target'));
            targetList.find('li').each(function() {
                var labelText = $(this).text().toLowerCase();
                if (labelText.indexOf(searchTerm) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
        $(document).on('show.bs.collapse', '#filterAccordion .collapse', function () {
        $('#filterAccordion .collapse').not(this).collapse('hide');
    });
</script>