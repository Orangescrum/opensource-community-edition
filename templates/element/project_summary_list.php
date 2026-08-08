<style>
</style>	
<table>
	<tbody>
		<% if(parseInt(projSummaryList.length) > 0){ %>
			<% 
			var count=0;
			for(var sKey in projSummaryList){
				var data_summary = projSummaryList[sKey];
				count++;
				%>
				<tr>
					<td><%= formatText(data_summary.project_name) %></td>
					<td><%= data_summary.user_name %></td>
					<td><%= data_summary.due_date %></td>
					<td><div class="status <%= data_summary.status_class%>"><%= data_summary.status %></div></td>
					<td> 
						<div class="radial_count_chat">
							<div class="hover_data"><?php echo __('Progress');?>: <%= data_summary.percentage.toFixed() %>%</div>
							<div class="project_circle circle_percent" data-percent="<%= data_summary.percentage %>">
								<div class="circle_inner">
									<div class="round_per"></div>
								</div>
							</div>
						</div>

					</td>
					<td><%= data_summary.rem_days %></td>
					<td><%= data_summary.rem_hours %></td>
				</tr>
			<% } %>
		<% } else { %>
			<tr class="checklist_tr_not">
				<td>
					<div class="no_checklist">
						<div class="nodetail_found text-center">
							<figure>
								<img src="<?php echo HTTP_ROOT;?>img/tools/No-details-found.svg" width="120" height="120">
							</figure>
							<div class="colr_red mtop15">
								<?php echo __('No summary available.');?>
							</div>
						</div>
					</div>
				</td>
			</tr>
		<% } %>
	</tbody>
</table>
