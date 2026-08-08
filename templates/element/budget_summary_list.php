<style>
</style>
<table>
    <tbody>
        <% if(parseInt(budgetSummaryList.length) > 0){ %>
        <% 
			var count=0;
			for(var sKey in budgetSummaryList){
				var data_summary = budgetSummaryList[sKey];
				%>
        <tr>
            <td><%= formatText(data_summary.project_name) %></td>
            <td><%= data_summary.project_manager %></td>
            <td><%= formatWithCurrency(data_summary.budget, data_summary.cur_symbol) %></td>
            <td><%= formatWithCurrency(data_summary.cost_to_client, data_summary.cur_symbol) %></td>
            <td><%= formatWithCurrency(data_summary.cost_to_company, data_summary.cur_symbol) %></td>
            <td>
                <div class="status <%= data_summary.profit_class%>"><%= data_summary.profit %>%</div>
            </td>
        </tr>
        <% } %>
        <% } else { %>
        <tr class="checklist_tr_not">
            <td>
                <div class="no_checklist">
                    <div class="nodetail_found text-center">
                        <figure>
                            <img src="<?php echo HTTP_ROOT; ?>img/tools/No-details-found.svg" width="120" height="120">
                        </figure>
                        <div class="colr_red mtop15">
                            <?php echo __('No summary available.'); ?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <% } %>
    </tbody>
</table>