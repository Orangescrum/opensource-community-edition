<div class="sec_title d-flex tog" data-cmnt_id ="rmnd_sec">
    <div class="heading_title">
        <span class="sec_icon reminder_icon"></span>
        <h3><?php echo __('Reminders'); ?></h3>
    </div>
    <div class="icon_collapse " ></div>
</div>
<div class="toggle_details">
    <div class="d-flex width-100-per">
        <div class="ml-auto sec_action_item">
            <% if(is_inactive_case == 0 && is_active == 1) {%>
            <?php if($this->Format->isAllowed('Create Task',$roleAccess)){ ?>
                <div class="cursor link-icon" onclick="addReminderPopup(<%= '\'' + projUniqId + '\'' %>,<%= '\'' + csUniqId + '\'' %>);"
                     id="tour_task_detail_reminder_v2">
                    <i class="material-icons">add</i> <?php echo __('Add New Reminder'); ?>
                </div>
            <?php  } ?>
            <% } %>
        </div>
    </div>

    <% if(parseInt(reminders.length) > 0){ var caseParenTUID = csUniqId; %>

    <div class="detail_list_table mtop20">
        <table class="width-100-per layout_fixed">
            <thead>
            <tr>
                <th class="width-20-per"><?php echo __('Date'); ?></th>
                <th class="width-15-per"><?php echo __('Time'); ?></th>
                <th class="width-35-per"><?php echo __('Message'); ?></th>
                <th class="width-20-per"><?php echo __('Notify Users'); ?></th>
                <th class="width-10-per"><?php echo __('Action'); ?></th>
            </tr>
            </thead>
            <tbody>
            <%
            var count=0;
            for(var sKey in reminders){
            var getdata = reminders[sKey].CaseReminder;
            var caseAutoId = getdata.id;
            count++;
            %>
            <tr data-id="<%= formatText(getdata.id) %>" id="tr_reminder_<%= formatText(getdata.id) %>">
                <td>
                    <div><%= getdata.date%></div>
                </td>
                <td><%= getdata.time%></td>
                <td>
                    <div><%= formatText(getdata.comment) %></div>
                </td>
                <td>
                    <%= getdata.user_id%>
                </td>
                <td>
                    <div class="more_action dropdown mtop5 text-center">
                        <div class="cursor" data-toggle="dropdown" aria-haspopup="true">
                            <i class="material-icons">
                                more_vert</i>
                        </div>
                        <% if(is_inactive_case == 0 && is_active == 1) { %>
                        <ul class="dropdown-menu">
                            <%
                            if(SES_TYPE == 1 || SES_TYPE == 2) { %>
                            <li onclick="editReminder(<%= '\'' + caseAutoId + '\'' %>, <%= '\'' + csUniqId + '\'' %>,<%= '\'' + projUniqId + '\'' %>);" id="edit<%= caseAutoId %>">
                                <a href="javascript:void(0)"><i class="material-icons">&#xE254;</i><?php echo __('Edit'); ?></a>
                            </li>
                            <li onclick="deleteReminder(<%= '\'' + caseAutoId + '\'' %>,<%= '\'' + csUniqId + '\'' %>,<%= '\'' + projUniqId + '\'' %>);" id="delete<%= caseAutoId %>">
                                <a href="javascript:void(0)"><i class="material-icons">&#xE872;</i><?php echo __('Delete'); ?></a>
                            </li>

                            <% } %>
                        </ul>
                        <% } %>
                    </div>
                </td>
            </tr>
            <% } %>
            </tbody>
        </table>
    </div>
    <% } else { %>
    <div class="nodetail_found">
        <figure>
            <img src="<?php echo HTTP_ROOT;?>img/tools/No-details-found.svg" width="120"
                 height="120">
        </figure>
        <div class="colr_red mtop15"><?php echo __('No Reminders found'); ?></div>
    </div>
    <% } %>
</div>
