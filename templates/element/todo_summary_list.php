<div id="overdue_tab" class="tab_panel">
    <% if(todos.get_od_todos.length) { %>
    <% for (let i = 0; i < todos.get_od_todos.length; i++) {
    var caseDetails = todos.get_od_todos[i]['Easycase'];
    var projectDetails = todos.get_od_todos[i]['Project'];
    var userDetails = todos.get_od_todos[i]['User']; %>
    <div class="task_todo_item">
        <div class="d-flex width-100-per task_name_no">
            <p><%= projectDetails.name %></p>
            <p><span class="task_no">#<%= caseDetails.case_no %>:</span> <%= caseDetails.title %></p>
        </div>
        <p><small>Created on <%= caseDetails.case_created_date %></small><small>Overdue by:</small><span class="due_tag"><%= caseDetails.due_date %></span></p>
    </div>
    <% } %>
    <% }else{ %>
        <div class="nodetail_found text-center">
            <figure>
                <img src="<%= HTTP_ROOT %>img/tools/No-details-found.svg" width="120" height="120">
            </figure>
            <div class="colr_red mtop15">No tasks available.</div>
        </div>
    <% } %>
</div>
<div id="upcoming_tab" class="tab_panel">
    <% if(todos.gettodos.length) { %>
        <% for (let i = 0; i < todos.gettodos.length; i++) {
        var caseDetails = todos.gettodos[i]['Easycase'];
        var projectDetails = todos.gettodos[i]['Project'];
        var userDetails = todos.gettodos[i]['User']; %>
        <div class="task_todo_item">
            <div class="d-flex width-100-per task_name_no">
                <p><%= projectDetails.name %></p>
                <p><span class="task_no">#<%= caseDetails.case_no %>:</span>  <%= caseDetails.title %></p>
            </div>
            <p><small>Created on <%= caseDetails.case_created_date %></small><% if (caseDetails.due_date) { %><small>Due by:</small><span class="due_tag"><%= caseDetails.due_date %></span><% } %></p>
        </div>
        <% } %>
    <% }else{ %>
        <div class="nodetail_found text-center">
            <figure>
                <img src="<%= HTTP_ROOT %>img/tools/No-details-found.svg" width="120" height="120">
            </figure>
            <div class="colr_red mtop15">No tasks available.</div>
        </div>
    <% } %>
</div>
