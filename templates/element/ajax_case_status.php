<%
var donot_show_deflt = 0;
if(typeof CustomStatus !="undefined" && CustomStatus != null){
if(Object.keys(CustomStatus).length > 0){ 
var donot_show_deflt = 1;
} }

var case_widgets = getCookie('CLOSE_WIDGET');
var case_news = getCookie('NEW_WIDGET');
var case_opens = getCookie('OPEN_WIDGET');
var case_starts = getCookie('START_WIDGET');
var case_resolves = getCookie('RESOLVE_WIDGET');
var chart_widgets = getCookie('CHART_WIDGET');

if(typeof al != 'undefined' && al!=0  && al && typeof cls != "undefined"){
	var fill = "(" + Math.round(((cls/al)*100))+"%)";
}
else {
	var fill = "(0%)";
}

if(case_widgets) {
	var case_wid = "display:none;";
	var case_wid1 = "display:block;";
} else {
	var case_wid = "display:block;";
	var case_wid1 = "display:none;";
}
if(case_news) {
	var case_new = "display:none;";
	var case_new1 = "display:block;";
} else {
	var case_new = "display:block;";
	var case_new1 = "display:none;";
}
if(case_opens) {
	var case_open = "display:none;";
	var case_open1 = "display:block;";
} else {
	var case_open = "display:block;";
	var case_open1 = "display:none;";
}
if(case_starts) {
	var case_start = "display:none;";
	var case_start1 = "display:block;";
} else {
	var case_start = "display:block;";
	var case_start1 = "display:none;";
}

if(case_resolves) {
	var case_resolve = "display:none;";
	var case_resolve1 = "display:block;";
} else {
	var case_resolve = "display:block;";
	var case_resolve1 = "display:none;";
}
if(chart_widgets) {
	var chart_widget = "display:none;";
	var chart_widget1 = "display:block;";
} else {
	var chart_widget = "display:block;";
	var chart_widget1 = "display:none;";
}

if(case_widgets=="1" || case_news == "1" || case_resolves =="1" || case_starts =="1" || case_opens =="1" || chart_widgets =="1"){
	var widget="display:block;";
} else {
	var widget="display:none;";
}

var disabled = "";
if(getCookie('CURRENT_FILTER') == 'closecase') {
	disabled = 1;
}
var nwper = 0;
var opnper = 0;
var rslvper = 0;
var clsper = 0;
var nw_wt = 0;
var opn_wt = 0;
var rslv_wt = 0;
var cls_wt = 0;

var chk_nt = '';
var chk_nt_amt = '';
var input_count = 0;
if(typeof nw != "undefined" && typeof opn != "undefined" && typeof rslv != "undefined" && typeof cls != "undefined" && !donot_show_deflt){
	var total = parseInt(nw)+parseInt(opn)+parseInt(rslv)+parseInt(cls);

if(nw != 0){
	nwper = (nw/total)*100;
	nw_wt = nwper-1.7;
	if(nw_wt > 88){
		chk_nt = 'nw_wt';
		chk_nt_amt = 15;
	}else if(nw_wt > 78){
		chk_nt = 'nw_wt';
		chk_nt_amt = 12;
	}else if(nw_wt > 68){
		chk_nt = 'nw_wt';
		chk_nt_amt = 10;
	}else if(nw_wt > 50){
		chk_nt = 'nw_wt';
		chk_nt_amt = 5;
	}
	nwper = parseFloat(nwper);
	nwper = nwper.toFixed(1);
	input_count++;
}

if(opn != 0){
opnper = (opn/total)*100;
opn_wt = opnper-1.7;
if(opn_wt > 88){
	chk_nt = 'opn_wt';
	chk_nt_amt = 15;
}else if(opn_wt > 78){
	chk_nt = 'opn_wt';
	chk_nt_amt = 12;
}else if(opn_wt > 68){
	chk_nt = 'opn_wt';
	chk_nt_amt = 10;
}else if(opn_wt > 50){
	chk_nt = 'opn_wt';
	chk_nt_amt = 5;
}
opnper = parseFloat(opnper);
opnper = opnper.toFixed(1);
input_count++;
}

if(rslv != 0){
rslvper = (rslv/total)*100;
rslv_wt = rslvper-1.7;

if(rslv_wt > 88){
	chk_nt = 'rslv_wt';
	chk_nt_amt = 15;
}else if(rslv_wt > 78){
	chk_nt = 'rslv_wt';
	chk_nt_amt = 12;
}else if(rslv_wt > 68){
	chk_nt = 'rslv_wt';
	chk_nt_amt = 10;
}else if(rslv_wt > 50){
	chk_nt = 'rslv_wt';
	chk_nt_amt = 5;
}
rslvper = parseFloat(rslvper);
rslvper = rslvper.toFixed(1);
input_count++;
}

if(cls != 0){
clsper = (cls/total)*100;
cls_wt = clsper-1.7;

if(cls_wt > 88){
	chk_nt = 'cls_wt';
	chk_nt_amt = 15;
}else if(cls_wt > 78){
	chk_nt = 'cls_wt';
	chk_nt_amt = 12;
}else if(cls_wt > 68){
	chk_nt = 'cls_wt';
	chk_nt_amt = 10;
}else if(cls_wt > 50){
	chk_nt = 'cls_wt';
	chk_nt_amt = 5;
}
clsper = parseFloat(clsper);
clsper = clsper.toFixed(1);
input_count++;
}
var gt = 400*(nw_wt/100) + 400*(opn_wt/100) + 400*(rslv_wt/100) + 400*(cls_wt/100);
if(chk_nt != ''){
	if(chk_nt == 'nw_wt'){
		nw_wt = nw_wt - chk_nt_amt;
	}else if(chk_nt == 'opn_wt'){
		opn_wt = opn_wt - chk_nt_amt;
	}else if(chk_nt == 'rslv_wt'){
		rslv_wt = rslv_wt - chk_nt_amt;
	}else{
		cls_wt = cls_wt-chk_nt_amt;
	}
}
var new_gt = parseInt(nw_wt)+parseInt(opn_wt)+parseInt(rslv_wt)+parseInt(cls_wt);
var first_ht = nw_wt;
if(input_count == 4 && new_gt >= 90){
    if(first_ht < opn_wt){
        first_ht = opn_wt;
    }else if(first_ht < rslv_wt){
        first_ht = rslv_wt;
    }else if(first_ht < cls_wt){
        first_ht = cls_wt;
    }
    if(first_ht == nw_wt){
        nw_wt = nw_wt - 4;
    }else if(first_ht == opn_wt){
        opn_wt = opn_wt - 4;
    }else if(first_ht == rslv_wt){
        rslv_wt = rslv_wt - 4;
    }else if(first_ht == cls_wt){
        cls_wt = cls_wt - 4;
    }
}
%>
<div class="dropdown cust_tskstatus_dropdown">
    <div class="more_opt_t" id="cust_drop_status">

        <ul class="dropdown-menu cust_drop_status">
            <li class="searchLi">
                <input type="text" placeholder="<?php echo __('Search'); ?>" class="searchType" onkeyup="seachitemsSts(this);" />
            </li>
            <% 
			$.each(CustomStatus, function (key, data) { %>
            <li onclick="customStatusTop(<%= data.legend %>);">
                <span rel="tooltip" title="<%= data.count %> <%= data.name %>" onclick="customStatusTop(<%= data.legend %>);" class="label customStatus" style="background-color:#<%= data.color %>"></span>
                <span class="cstm_tskstatus_name" onclick="customStatusTop(<%= data.legend %>);"><span><%= data.name %></span> (<%= data.count %>)</span>
            </li>
            <% }); %>

            <%
			if(1 || typeof nw != "undefined" && typeof opn != "undefined" && typeof rslv != "undefined" && typeof cls != "undefined" && donot_show_deflt){
		%>
            <li onclick="statusTop(1);">
                <span rel="tooltip" title="<%= nw %> <?php echo __('New'); ?> (<%= nwper %>%)" <% if(!disabled) { %> onclick="statusTop(1);" <% } %> class="label label-danger fade-red"></span><span class="oth_cmn_status" <% if(!disabled) { %> onclick="statusTop(1);" <% } %>><?php echo __('New'); ?> (<%= nw %>) </span>
            </li>
            <li onclick="statusTop(2);">
                <span rel="tooltip" title="<%= opn %> <?php echo __('In Progress'); ?> (<%= opnper %>%)" class="label label-info fade-blue" <% if(!disabled) { %> onclick="statusTop(2);" <% } %>></span><span class="oth_cmn_status" <% if(!disabled) { %> onclick="statusTop(2);" <% } %>><?php echo __('In Progress'); ?> (<%= opn %>) </span>
            </li>
            <li onclick="statusTop(5);">
                <span rel="tooltip" title="<%= rslv %> <?php echo __('Resolved'); ?> (<%= rslvper %>%)" <% if(!disabled) { %> onclick="statusTop(5);" <% } %> class="label label-warning fade-orange"></span><span class="oth_cmn_status" <% if(!disabled) { %> onclick="statusTop(5);" <% } %>><?php echo __('Resolved'); ?> (<%= rslv %>)</span>
            </li>
            <li onclick="statusTop(3);">
                <span rel="tooltip" title="<%= cls %> <?php echo __('Closed'); ?> (<%= clsper %>%)" <% if(!disabled) { %> onclick="statusTop(3);" <% } %> class="label label-success fade-green"></span><span class="oth_cmn_status" <% if(!disabled) { %> onclick="statusTop(3);" <% } %>><?php echo __('Closed'); ?> (<%= cls %>) </span>
            </li>
            <% } %>
        </ul>  
		 <a id="cust_drop_status_a" class="dropdown-toggle_t" data-toggle="dropdown" href="javascript:void(0);" data-target="#" onclick="showHideSts();">
            <i class="material-icons">&#xE5D4;</i><?php //&#xE5CF; 
                                                    ?>
        </a>
    </div>
</div>
<%
if(localStorage.getItem('STATUS')){
	var myString_def_status = localStorage.getItem('STATUS');
	var updatedNumber = myString_def_status.replace(/-/g, ',');
	var my_def_Array = updatedNumber.split(",");
	var def_multiplevalue = null;

	$.each(my_def_Array, function(index, value) {
		def_multiplevalue = value;
		return false;
	});
}
var mydefault_status = '"' + localStorage.getItem('STATUS') + '"';
	if (mydefault_status.indexOf('-') !== -1) {
	    var flag = "True";
	} else {
		var flag = "False";
	}
    var variables = [nw, opn, rslv,cls];
	var sums = 0;
	$.each(variables, function(index, value) {
		sums += +value;
	});
%>
<% if(nw != 0){ %>
<span rel="tooltip" title="<% if(flag == 'True'){ %><% if(def_multiplevalue == 1){ %><%= nw %> <?php echo __('New'); ?> (<%= nwper %>%)<% }else if(def_multiplevalue == 2){ %><%= opn %> <?php echo __('In Progress'); ?> (<%= opnper %>%)<% }else if(def_multiplevalue == 5){ %><%= rslv %> <?php echo __('Resolved'); ?> (<%= rslvper %>%)<% }else{ %><%= cls %> <?php echo __('Closed'); ?> (<%= clsper %>%)<% } %><% }else if(localStorage.getItem('STATUS') == 'all' || empty(localStorage.getItem('STATUS'))){ %><%= sums %> <?php echo __('All'); ?><% }else if(localStorage.getItem('STATUS') == 1){ %><%= nw %> <?php echo __('New'); ?> (<%= nwper %>%)<% }else if(localStorage.getItem('STATUS') == 2){ %><%= opn %> <?php echo __('In Progress'); ?> (<%= opnper %>%)<% }else if(localStorage.getItem('STATUS') == 5){ %><%= rslv %> <?php echo __('Resolved'); ?> (<%= rslvper %>%)<% }else{ %><%= cls %> <?php echo __('Closed'); ?> (<%= clsper %>%)<% } %>" style="cursor:context-menu;float: left;background-color:#<% if(flag == 'True'){ %><% if(def_multiplevalue == 1){ %><?php echo __('FFD1DE'); ?><% }else if(def_multiplevalue == 2){ %><?php echo __('CFE2FE'); ?><% }else if(def_multiplevalue == 5){ %><?php echo __('FBE1C9'); ?><% }else{ %><?php echo __('C0EECE'); ?><% } %><% }else if(localStorage.getItem('STATUS') == 'all' || empty(localStorage.getItem('STATUS'))){ %><?php echo __('FFD1DE'); ?> (<%= sums %>)<% }else if(localStorage.getItem('STATUS') == 1){ %><?php echo __('FFD1DE'); ?><% }else if(localStorage.getItem('STATUS') == 2){ %><?php echo __('CFE2FE'); ?><% }else if(localStorage.getItem('STATUS') == 5){ %><?php echo __('FBE1C9'); ?><% }else{ %><?php echo __('C0EECE'); ?><% } %>" <% if(!disabled) { %> <% } %> class="label label-danger fade-red"><% if(localStorage.getItem('STATUS') == 'all' || empty(localStorage.getItem('STATUS'))){ %><?php echo __('All'); ?> (<%= sums %>)<% }else if(localStorage.getItem('STATUS') == 1 || flag == 'True'){ %><% if(def_multiplevalue == '1'){ %><?php echo __('New'); ?> (<%= nw %>) <% } else if(def_multiplevalue == '2'){ %><?php echo __('In Progress'); ?> (<%= opn %>)<% }else if(def_multiplevalue == '5'){ %><?php echo __('Resolved'); ?> (<%= rslv %>)<% } else {  %><?php echo __('Closed'); ?> (<%= cls %>)<%  } %><% }else if(localStorage.getItem('STATUS') == 2){ %><?php echo __('In Progress'); ?> (<%= opn %>)<% }else if(localStorage.getItem('STATUS') == 5){ %><?php echo __('Resolved'); ?> (<%= rslv %>)<% }else{ %><?php echo __('Closed'); ?> (<%= cls %>)<% } %></span> <!-- This span for Default status -->
<% }else{ %>
<span rel="tooltip" title="0 <?php echo __('New'); ?> (0%)" style="cursor:context-menu;" onclick="statusTop(1);" class="label label-danger fade-red"><?php echo __('New'); ?> (0)</span>
<% } %>
<input type="hidden" id="closedcaseid" value="<%= cls %>">
<% } %>
<%  
if(typeof CustomStatus !="undefined" && CustomStatus != null && Object.keys(CustomStatus).length > 0){ %>
<%  
     var desiredItem = null;
	 var desiredItem2 = null;
     $.each(CustomStatus, function(element, index) {
         if (index.legend == localStorage.getItem('CUSTOM_STATUS')) {
             desiredItem = index;
             return false; 
         }
     });
	 var sum = 0;
	 $.each(CustomStatus, function(element, index) {
		var num = index.count;
		sum  += +num;
     });
	 if(localStorage.getItem('CUSTOM_STATUS')){
		var myString_status = localStorage.getItem('CUSTOM_STATUS');
		var updatedNumber = myString_status.replace(/-/g, ',');
		var myArray = updatedNumber.split(",");
		var multiplevalue = null;
		$.each(myArray, function(index, value) {
			multiplevalue = value;
			return false;
		});
		if (myString_status.indexOf('-') !== -1) {
		var flag = "True";
		} else {
			var flag = "False";
		}
	 }
	$.each(CustomStatus, function(element, index) {
         if (index.legend == multiplevalue) {
             desiredItem2 = index;
             return false; 
         }
     });

if(total_length > 1){ %>
<div class="dropdown cust_tskstatus_dropdown">
    <div class="more_opt_t" id="cust_drop_status">
        <a id="cust_drop_status_a" class="dropdown-toggle_t" data-toggle="dropdown" href="javascript:void(0);" data-target="#" onclick="showHideSts();">
            <i class="material-icons">&#xE5D4;</i><?php //&#xE5CF; 
                                                    ?>
        </a>
        <ul class="dropdown-menu cust_drop_status">
            <li class="searchLi">
                <input type="text" placeholder="<?php echo __('Search'); ?>" class="searchType" onkeyup="seachitemsSts(this);" />
            </li>
            <% $.each(CustomStatus, function (key, data) { %>
            <li>
                <span rel="tooltip" title="<%= data.count %> <%= data.name %>" onclick="customStatusTop(<%= data.legend %>);" class="label customStatus" style="background-color:#<%= data.color %>"></span>
                <span class="cstm_tskstatus_name" onclick="customStatusTop(<%= data.legend %>);"><span><%= data.name %></span> (<%= data.count %>)</span>
            </li>
            <% }); %>

            <%
			if(typeof nw != "undefined" && typeof opn != "undefined" && typeof rslv != "undefined" && typeof cls != "undefined" && donot_show_deflt){
			%>
            <li>
                <span rel="tooltip" title="<%= nw %> <?php echo __('New'); ?> (<%= nwper %>%)" <% if(!disabled) { %> onclick="statusTop(1);" <% } %> class="label label-danger fade-red"></span><span class="oth_cmn_status" <% if(!disabled) { %> onclick="statusTop(1);" <% } %>><?php echo __('New'); ?> (<%= nw %>) </span>
            </li>
            <li>
                <span rel="tooltip" title="<%= opn %> <?php echo __('In Progress'); ?> (<%= opnper %>%)" class="label label-info fade-blue" <% if(!disabled) { %> onclick="statusTop(2);" <% } %>></span><span class="oth_cmn_status" <% if(!disabled) { %> onclick="statusTop(2);" <% } %>><?php echo __('In Progress'); ?> (<%= opn %>) </span>
            </li>
            <li>
                <span rel="tooltip" title="<%= rslv %> <?php echo __('Resolved'); ?> (<%= rslvper %>%)" <% if(!disabled) { %> onclick="statusTop(5);" <% } %> class="label label-warning fade-orange"></span><span class="oth_cmn_status" <% if(!disabled) { %> onclick="statusTop(5);" <% } %>><?php echo __('Resolved'); ?> (<%= rslv %>)</span>
            </li>
            <li>
                <span rel="tooltip" title="<%= cls %> <?php echo __('Closed'); ?> (<%= clsper %>%)" <% if(!disabled) { %> onclick="statusTop(3);" <% } %> class="label label-success fade-green"></span><span class="oth_cmn_status" <% if(!disabled) { %> onclick="statusTop(3);" <% } %>><?php echo __('Closed'); ?> (<%= cls %>) </span>
            </li>
            <% } %>


        </ul>
    </div>
</div>
<% } %>
<% var cntrSts = 0;
$.each(CustomStatus, function (key, data) { cntrSts++; %>
<% if(cntrSts <= 1){ %>
<span rel="tooltip" title="<% if(flag == 'True'){ %><%= desiredItem2.count %> <%= desiredItem2.name %><% }else if(localStorage.getItem('CUSTOM_STATUS') == 'all' || empty(localStorage.getItem('CUSTOM_STATUS'))){ %><%= sum %> <?php echo "All"; ?> <% }else{ %><%= desiredItem.count %> <%= desiredItem.name %><% } %>" class="label customStatus customStatusTp" style="cursor:context-menu;background-color:#<% if(flag == 'True'){ %><%= desiredItem2.color %> <% }else if(localStorage.getItem('CUSTOM_STATUS') == 'all' || empty(localStorage.getItem('CUSTOM_STATUS'))){ %><%= data.color %><% }else{ %><%= desiredItem.color %><% } %>"><% if(localStorage.getItem('CUSTOM_STATUS')){ %><% if( localStorage.getItem('CUSTOM_STATUS') == 'all'){ %><?php echo "All"; ?> (<%= sum %>) <% }else if(flag == 'True'){ %><%= desiredItem2.name %> (<%= desiredItem2.count %>) <% }else if(localStorage.getItem('CUSTOM_STATUS') == 'all'){ %> <?php echo "All"; ?> (<%= sum %>) <% }else{ %><%= desiredItem.name %> (<%= desiredItem.count %>)<% } %><% }else{ %> <?php echo "All"; ?> (<%= sum %>) <% } %></span> <!-- This span for Custom status -->
<% } %>
<% }); %>
<input type="hidden" id="closedcaseid" value="">
<% } %>
<div class="cb"></div>