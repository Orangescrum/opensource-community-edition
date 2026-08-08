<% if(label_tasks.length) { %>
<% for(var lbkey in label_tasks){ %>
<span class="dtl_label_tag tagsr" id="label_tsk_spn_<%= label_tasks[lbkey].id %>"><%= label_tasks[lbkey].Labels.lbl_title %> <% if(user_can_change == 1){ %><?php if($this->Format->isAllowed('Remove Label',$roleAccess)){ ?><a href="javascript:void(0)"; onclick="removeLabelTask(<%= label_tasks[lbkey].id %>, <%= csAtId %>)" title="Remove Label">&times;</a><?php } ?><% } %></span>

<% } %>
<div class="nodetail_found" style="display:none;">
    <figure>
        <img src="<?php echo HTTP_ROOT;?>img/tools/No-details-found.svg" width="120"
             height="120">
    </figure>
    <div class="colr_red mtop15">No Label Found</div>
</div>
<% }else{ %>
<div class="nodetail_found">
    <figure>
        <img src="<?php echo HTTP_ROOT;?>img/tools/No-details-found.svg" width="120"
             height="120">
    </figure>
    <div class="colr_red mtop15">No Label Found</div>
</div>
<div class="cb"></div>
<% } %>
