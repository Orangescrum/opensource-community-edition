<% localStorage.setItem("split_estd_date",date);%>
<div class="split_total_hrs"><span class="tthr_spn"><strong><?php echo __("Total Hour : "); ?></strong></span> <span class="tthr_vl_spn" id="splitted_total_hour"></span></div>
<div class="split_hrs_list">
    <table class="width-100-per">
        <thead>
            <tr>
                <th class="text-left"><?php echo __("Allocated Date"); ?></th>
                <th class="text-left"><?php echo __("Already Booked"); ?></th>
                <th class="text-left"><?php echo __("Allocate"); ?></th>
            <tr>
        </thead>
        <tbody>
            <% for(var i = 0;i < date.length;i++){ %>
            <tr>
                <td class="text-left"><%= date[i] %></td>
                <td class="text-left"><% if(case_id != '' ){ %><%= alredy_book_hr[i]['booked_hours'] %> <% }else{ %><%= format_time_hr_min(book_hr[i]['booked_hours']) %><% } %></td>
                <td class="text-left"><input type="text" class="check_minute_range split_est est ttfont" onchange="mins_validation(this);calculateTotalHr();" onkeypress="return numeric_decimal_colon(event),mins_validation(this)" id="splt_estd<%= date[i] %>" name="data[Easycase][split_estimated_hours]" maxlength="5" <% if(case_id != '' && book_hr[i]['booked_hours'] != 0){%> value="<%= book_hr[i]['booked_hours'] %>" <% }else{ %> value="" <% } %> placeholder="hh:mm" /></td>
            <tr>
                <% } %>
        </tbody>
    </table>
</div>

<input type="hidden" value="" id="split_t_hr" name="" />
<input type="hidden" value="<% if (case_id != null){ %><%= case_id %> <% } %>" id="task_id_for_split" name="" />
<div class="modal-footer">
    <div class="fr popup-btn">
        <span id="split_task_loader2" class="addlnkder fr" style="display: none;"><img src="<?php echo HTTP_ROOT; ?>img/images/case_loader2.gif" alt="loading..." title="loading..."> </span>
        <div id="split_tsk_sve_cncl">
            <%  if(!case_id && case_id == ''){ %>
            <span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();resetSplitTaskValues();"><?php echo __('Cancel'); ?></button></span>
            <% }else{ %>
            <span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel'); ?></button></span>
            <% } %>
            <span class="fl hover-pop-btn"><a href="javascript:void(0)" id="savesplttask" onclick="saveSplitTaskEstimation();" class="btn btn_cmn_efect cmn_bg btn-info cmn_size"><?php echo __('Save'); ?></a></span>
            <div class="cb"></div>
        </div>
    </div>
</div>
</div>