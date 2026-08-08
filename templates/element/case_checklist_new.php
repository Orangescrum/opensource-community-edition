<style>
    #chklist-icon{font-size:20px !important;color: #183247;display: inline-block;vertical-align: middle;}
    #chklist-icon:hover{color: #0d9ef8;opacity: 1;}
</style>
<div class="sec_title d-flex tog" data-cmnt_id ="chklst_sec">
    <div class="heading_title">
        <span class="sec_icon checklist_icon"></span>
        <h3>Checklist</h3>
    </div>
    <div class="icon_collapse " ></div>

</div>


<div class="toggle_details">
    <div class="detail_checklist_section">
        <% if(is_inactive_case == 0 && is_active == 1) {%>
        <div class="check_list_element p-0">
            <% if(SES_TYPE == 1 || SES_TYPE == 2 || SES_TYPE == 3) { %>
            <?php if($this->Format->isAllowed('Create Task',$roleAccess)){ ?>
                <input type="text" placeholder="<?php echo __('Add checklist'); ?>" data-chid="" data-isinactive="<%= is_inactive_case %>"
                       data-projid="<%= projUniqId %>" data-seslogin="" data-caseid="<%= csUniqId %>" class="add_checklist_inpt" id="checklist_inpt<%= csUniqId %>"

                />
                <span class="ml-15">
				<a href="javascript:void(0);" rel="tooltip" title="<?php echo __('Add Checklist'); ?>" onclick="addChecklistNew();"><i class="material-icons"id="chklist-icon">&#xE145;</i></a>
			</span>
            <?php } ?>
            <% }else{ %>
            <span>
										<?php echo __('Checklist'); ?>
									</span>
            <% } %>
        </div>
        <% } %>
        <div class="checklist_tbl detail_list_table">
            <table class="table width-100 layout_fixed">
                <tbody id="checklist_body<%= csUniqId %>" class="sortableChecklist">
                <% if(parseInt(checklists.length) > 0){ %>
                <%
                var count=0;
                for(var sKey in checklists){
                var data_chklst = checklists[sKey].CheckList;
                var caseAutoId = data_chklst.id;
                count++;
                %>
                <tr class="timelog-hover-block row_tr checklist_tr <% if(data_chklst.is_checked){ %>completed <% } %>" id="tr_checklist_<%= data_chklst.id %>">
                    <td class="date_time_td">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" class="check_chklist" <% if(data_chklst.is_checked){ %> checked="checked"<% } %> id="checklist_c_<%= data_chklst.id %>" data-id="<%= data_chklst.id %>" <%if(is_active == 0){  %> disabled <% } %>/>
                            </label>
                        </div>
                    </td>
                    <td class="message_td">
                        <div class="" title="<%= formatText(data_chklst.title) %>">
                            <a href="javascript:void(0);" onclick="openEdtCheecklist(<%= data_chklst.id %>, this);" id="checklist_lkn<%= data_chklst.id %>"
                               class="chklist_ttl_lnk link-text">
                                <%= formatText(data_chklst.title) %>
                            </a>
                            <input type="text" value="<%= formatText(data_chklst.title) %>" class="edit_checklist_data checklist_ttl" data-chid="<%= data_chklst.uniq_id %>"
                                   data-id="<%= data_chklst.id %>" id="checklist_t_<%= data_chklst.id %>" data-projid="<%= projUniqId %>" data-caseid="<%= csUniqId %> "
                            />
                            <input type="hidden" value="<%= formatText(data_chklst.title) %>" id="checklist_b_<%= data_chklst.id %>" />
                        </div>
                    </td>
                    <% if(is_inactive_case == 0 && is_active == 1) {%>
                    <td class="action_td">
                        <a href="javascript:void(0);" onclick="deleteChecklist(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + csUniqId + '\'' %>,<%= '\'' + projUniqId + '\'' %>);"
                           id="delete<%= caseAutoId %>">
                            <i title="<?php echo __('Delete');?>" class="material-icons delete_icon">delete_outline</i>
                        </a>
                    </td>
                    <% } %>
                </tr>
                <tr class="checklist_tr_not" style="display:none;">
                    <td>
                        <div class="no_checklist">
                            <div class="nodetail_found">
                                <figure>
                                    <img src="<?php echo HTTP_ROOT;?>img/tools/No-details-found.svg" width="120" height="120">
                                </figure>
                                <div class="colr_red mtop15">
                                    <?php echo __('No checklist available.');?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <% } %>
                <% } else { %>
                <tr class="checklist_tr_not">
                    <td>
                        <div class="no_checklist">
                            <div class="nodetail_found">
                                <figure>
                                    <img src="<?php echo HTTP_ROOT;?>img/tools/No-details-found.svg" width="120" height="120">
                                </figure>
                                <div class="colr_red mtop15">
                                    <?php echo __('No checklist available.');?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <% } %>
                </tbody>
            </table>
        </div>
    </div>
    <div>
