<style type="text/css">
    .crt_cheklist_tbl table tr td {
        text-align: left;
        padding: 8px;
        vertical-align: middle !important;
        border: none
    }

    .crt_cheklist_tbl table .date_time_td {
        width: 40px
    }

    .crt_cheklist_tbl table .action_td {
        width: 50px;
        text-align: left
    }

    .crt_cheklist_tbl table .message_td .check_chklist_crt_ttl {
        width: 100%;
        border: 1px solid #ddd;
        padding: 5px;
        font-size: 14px;
        color: #333;
        border-radius: 4px;
    }

    .crt_cheklist_tbl table .action_td .material-icons {
        font-size: 18px;
        color: #333
    }

    .crt_cheklist_tbl table .action_td .material-icons:hover {
        color: #ff0000
    }
</style>

<div class="crtsk_add_checklist">
    <div class="field_wrapper nofloat_wrapper">
        <div id="tour_crt_checklist" class="auto_label_choice">
            <input class="" id="CS_checklist" type="text" data-id="0" />
            <div class="field_placeholder" for="CS_checklist"><span><?php echo __('Checklist'); ?></span></div>
        </div>
        <a href="javascript:void(0);" onclick="addChecklistCreate();">+ <?php echo __('Add'); ?> </a>
    </div>
</div>

<div class="crt_cheklist_tbl">
    <table class="table">
        <tbody id="checklist_body_create">
        </tbody>
    </table>
</div>