<style>
    .split_total_hrs {
        font-size: 16px;
        margin-bottom: 10px
    }

    .split_hrs_list {
        min-height: 200px;
        max-height: 400px;
        overflow-y: auto;
        padding-right: 15px;
    }

    .split_hrs_list table {
        table-layout: fixed;
    }

    .split_hrs_list table tr th {
        font-size: 13px;
        font-family:'Inter', sans-serif;
        padding: 8px;
        border-bottom: 2px solid #ddd;
    }

    .split_hrs_list table tr td {
        font-size: 13px;
        font-family: 'Inter', sans-serif;
        padding: 8px;
    }

    .split_hrs_list input.split_est {
        width: 100%;
        border: 1px solid #CFD7DF;
        height: 30px;
        line-height: 30px;
        padding: 0 10px;
        border-radius: 4px;
    }

    .split_hrs_list input.split_est:focus {
        border-color: #2e2e2e
    }
</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i class="material-icons">&#xE14C;</i></button>
            <h4 class="width-85-per ellipsis-view"><?php echo __('Split the estimation hour of task'); ?></h4>
        </div>
        <div class="modal-body popup-container">
            <div class="loader_bg" id="split_task_loader">
                <div class="loadingdata">
                    <img src="<?php echo HTTP_ROOT; ?>images/rolling.gif?v=5019" alt="loading..." title="loading...">
                </div>
            </div>
            <div id="split_est_task_tmpl"></div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.split_est').mask('00:00');
    });
</script>
<script type="text/template" id="split_est_task_tmpl_page">
    <?php echo $this->element('split_est_task_tmpl'); ?>
</script>