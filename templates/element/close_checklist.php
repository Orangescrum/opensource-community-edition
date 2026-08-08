<div class="modal-body popup-container flex_scroll checklist_scroll checklist_modal_body" style="padding-top:0px; ">

    <div class="row pop-user-checkbox checklist_row_info" id="all_chechboxes">

        <div class="col-md-12">

            <div class="d-flex checkbox add-all-user">
                <label class="checklist_label all_check_box">
                    <input type="checkbox" checked disabled>
                    <span><?php echo __('All'); ?></span>
                </label>
            </div>

            <%
			var count=0;
            console.log(openChecklists);
			for(var sKey in openChecklists){
			var getdata = openChecklists[sKey]['CheckList'] || openChecklists[sKey];
			count++;
			%>
            <div class="checklist_content_ht">
                <div class="checkbox custom-checkbox add-user-pro-chk" style="width:100%;">
                    <label for="checked_ckecklist_<%= getdata.id%>">
                        <input id="checked_ckecklist_checkbox_<%= getdata.id%>" type="checkbox" value="1" class="checked_chekclists_for_close" checked disabled />
                        <span class="oya-blk checklist_content">
                            <span class="close_checklist_chk" title="<%= getdata.title%>" rel="tooltip">
                                <%= getdata.title%>
                            </span>
                        </span>
                    </label>
                </div>
            </div>
            <% } %>
        </div>
    </div>
</div>
<div class="modal-footer">
    <div class="popup-btn" style="text-align: right;">

        <span class="cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size reset_btn nothanks_btn" onclick="closePopup();"><?php echo __('Cancel'); ?></button></span>
        <%
			let methodToCall = 'actiononTask';
			let methodParams;
			methodParams = params;
			methodToCall += "(";
			for (let g = 0; g < methodParams.length; g++) {
			  methodToCall += '"'+ methodParams[g]+'"'+',';
			}
			methodToCall += '"closeWithCheckLists"'+',';
			methodToCall = methodToCall.replace(/,\s*$/, "");
			methodToCall += ");";
		%>
        <span class="cancel-link" id="checklistCloseBtn"><button type="button" class="btn btn_cmn_efect cmn_bg btn-info cmn_size" onclick='<%= methodToCall %>'><?php echo __('Close Task'); ?></button></span>

        <div class="loader_dv checklist_close_loader" style="display: none;" id="checkListCloseLdr">
            <center>
                <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." title="Loading..." />
            </center>
        </div>
    </div>
</div>