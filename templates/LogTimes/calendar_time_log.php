<style>
.tg_calendar-bar .col-lg-7{width: 75% !important;}
.tg_calendar-bar .col-lg-5{width:25% !important;}
</style>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ROOT; ?>css/fullcalendar/fullcalendar.css"/>
<div class="timelog-table">
    <div class="timelog-table-head">
        <div>
            <div class="fl tl-msg-header">
                <span class="time-log-head"><?php echo __('Time Log');?><span class="tl-colon">:</span></span>
                <div class="spent-time tl-msg-box">
                    <div class="fl">
                        <span class="total"><?php echo __('Total');?>:</span>
                        <span class="use-time"><?php echo __('Logged');?>:</span>
                        <span><?php echo $this->Format->format_time_hr_min($data['totalHrs']); ?></span>
                    </div>
                    <div class="fl" style="margin:0px 20px 0px 20px;">
                        <span class="use-time"><?php echo __('Billable');?>:</span>
                        <span><?php echo $this->Format->format_time_hr_min($data['billableHrs']); ?></span>
                    </div>
                    <div class="fl">
                        <span class="use-time"><?php echo __('Estimated');?>:</span>
                        <span><?php echo $this->Format->format_time_hr_min($data['estimatedHrs']); ?></span>
                    </div>
                    <div class="cb"></div>
                </div>
            </div>
            <div class="fr tl-msg-btn">
				<div class="logmore-btn fr">
					<a class="anchor" style="padding-left: 0px;margin-left: 5px; width: 120px; padding-right: 0px;" onclick="ajax_timelog_export_csv();" rel="tooltip" title="<?php echo __('Export To CSV');?>"><i class="material-icons">&#xE8D5;</i></a>
				</div>
                <div class="logmore-btn fr">
                    <a class="anchor" onclick="createlog(0,'')"><?php echo __('Time Entry');?><span class="sprite btn-clock"></span></a>
                </div>
            </div>
            <div class="cb"></div>
        </div>
    </div>
</div>
<div class="cb"></div>
<script>
	var usr = [];
        var view_typ_cal = "<?php echo !empty($data['calander_view'])?$data['calander_view']:'month' ?>";
	$(document).ready(function() {
		$.getScript("<?php echo HTTP_ROOT; ?>js/fullcalendar/fullcalendar.min.js", function( data, textStatus, jqxhr ) {
	    if(textStatus == 'success'){

	    var strURL = HTTP_ROOT + "easycases/";
		var url = strURL+"getTimeLogs";
		var current_url = '';
		var new_url     = '';
		var date = new Date();
		var d = date.getDate();
		var m = date.getMonth();
		var y = date.getFullYear();
		var calendar = $('#calendar_timelog').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			selectable: true,
			selectHelper: true,
			defaultView: "month",
			slotEventOverlap:false,
			events: function( start, end, callback ) {
			    $('.fc-button-today').text('Today');
			    $('.fc-button-month').text('Month');
			    $('.fc-button-agendaWeek').text('Week');
			    $('.fc-button-agendaDay').text('Day');
			    var year = end.getFullYear();
			    var month = end.getMonth();
			    var s_year = start.getFullYear();
			    var s_month = start.getMonth();
			    var type ='calendar';
			    new_url  = url;
				var params = parseUrlHash(urlHash);
				var milestone_uid = $('#milestoneUid').val();
				if(params[1]){
					milestone_uid = params[1];
					$('#milestoneUid').val(params[1]);
					if(($('#caseMenuFilters').val() =='milestone') || ($('#caseMenuFilters').val()=='milestonelist'))
						$('#refMilestone').val($('#caseMenuFilters').val());
				}
				//$('#select_view_timelog a').tipsy({gravity:'n', fade:true});
				var globalkanbantimeout =null;
				var morecontent ='';
				if(type =='calendar'){
					$('#select_view_timelog a').removeClass('disable');
					$('#calendar_btn_timelog').addClass('disable');
					calenderForTimeLog('calendar');
					$("#caseMenuFilters").val('calendar_timelog');
					$(".menu-files").removeClass('active');
					$(".menu-milestone").removeClass('active');
				}
				var casePage = $('#casePage').val();
				$('#caseLoader').show();
				var projFil = $('#projFil').val();
				var projIsChange = $('#projIsChange').val();
				var customfilter = $('#customFIlterId').value;//Change case type
				var caseStatus = $('#caseStatus').val(); // Filter by Status(legend)
				var priFil = $('#priFil').val(); // Filter by Priority
				var caseTypes = $('#caseTypes').val(); // Filter by case Types
				var caseLabel = $('#caseLabel').val(); // Filter by case Label
				var caseMember = $('#caseMember').val();  // Filter by Member
				var caseAssignTo = $('#caseAssignTo').val();  // Filter by AssignTo
				var caseSearch = $('#case_search').val(); // Search by keyword
				var case_date = $('#caseDateFil').val(); // Search by Date
				var case_due_date = $('#casedueDateFil').val(); // Search by Date
				var case_srch = $('#case_srch').val();
				var tskURL = strURL+"getTimeLogs";
				$.post(tskURL,{"from_view_year":s_year,"from_view_month":s_month,"to_view_year":year,"to_view_month":month,"projFil":projFil,"projIsChange":projIsChange,"casePage":casePage,'caseStatus':caseStatus,'customfilter':customfilter,'caseTypes':caseTypes,'caseLabel':caseLabel,'priFil':priFil,'caseMember':caseMember,'caseAssignTo':caseAssignTo,'caseSearch':caseSearch,'case_srch':case_srch,'case_date':case_date,'case_due_date':case_due_date,'morecontent':'','milestoneUid':milestone_uid,'view_type' :view_typ_cal},function(res){
				    $('#caseLoader').hide();
				    callback(res);
					getvioews();
					$('.fc-button-month, .fc-button-agendaWeek, .fc-button-agendaDay, .fc-button-today, .fc-button-next, .fc-button-prev').on('click',function(){
						getvioews();
					});
					$('.fc-button-month').attr({'title':_('Month View'),'rel':'tooltip'}).tipsy({gravity:'s',html: true });
					$('.fc-button-agendaWeek').attr({'title':_('Week View'),'rel':'tooltip'}).tipsy({gravity:'e',html: true });
					$('.fc-button-agendaDay').attr({'title':_('Day View'),'rel':'tooltip'}).tipsy({gravity:'e',html: true });
          $('.fc-button-today').attr({'title':'Today','rel':'tooltip'}).tipsy({gravity:'s',html: true });
				},'json');
			},
			select: function(start, end, allDay,jsEvent, view) {
				// var check = $.fullCalendar.formatDate(start,'yyyy-MM-dd');
			// var today = $.fullCalendar.formatDate(new Date(start),'yyyy-MM-dd');
				// if(check < today){
				//     return false;
				// }else if(check > today){
				//     return false;
				// }else{
        <?php if($this->Format->isAllowed('Manual Time Entry',$roleAccess)){ ?>
				createlog('', '','',moment(start).format('YYYY-MM-DD'));
				// s}
				//console.log(start+'--'+end);
				var st_time = $.fullCalendar.formatDate(new Date(start),'hh:mm:tt');
				var et_time = $.fullCalendar.formatDate(new Date(end),'hh:mm:tt');
                                if(view.name != 'month'){
				setTimeout(function(){setTimeOnCreate(st_time,et_time)},2500);
                                }
				<?php } ?>
			},
			eventClick:function(calEvent, jsEvent, view){
				<?php if($this->Format->isAllowed('Edit Timelog Entry',$roleAccess)){ ?>
					createlog('','',calEvent.log_id,0,calEvent.user_id);
				<?php } ?>
			},
			eventRender: function(event, element) {
			    var prj_typ = $('#projFil').val();
			 	var message = 'Assigned to : '+event.name;
			 	element.find('.fc-event-time').text(event.duration);
			 	//element.find(".fc-event-time").after("<br/>");
                element.find('.fc-event-inner').attr({title:message,rel:'tooltip'}).tipsy({html: true });
				if(typeof event.prj_uniq_id != 'undefined'){				element.find('.fc-event-inner').attr({'data-id':'log_prjuid_'+event.log_id});				element.find('.fc-event-inner').attr({'data-puid':event.prj_uniq_id});
				}
			    var clrCod = '';
				clrCod = getRandomColor(event.uniq_id);
			    if(clrCod != ''){
				    element.find('.fc-event-inner').parent().css('border','1px solid '+clrCod);
				    element.find('.fc-event-inner').css('background-color',clrCod);
			    }
			    $('[rel=tooltip]').tipsy({gravity:'s', fade:true});
			},
			editable: false
		    });
		}
		});
		$('.tsk-typ-txt-tg').show();
                setTimeout(function(){$('#calendar_timelog').fullCalendar('changeView', view_typ_cal)},1500);
	});
    /*
    *This function generates random color codes every time
    *it gets triggered.Each Color is unique for
    *a resource but they are random.
    */
	function getRandomColor(user_uniq_id) {
		if(!usr[user_uniq_id]){
	    var letters = '0123456789ABCDEF'.split('');
	    var color = '#';
	    for (var i = 0; i < 6; i++ ) {
	        color += letters[Math.floor(Math.random() * 16)];
	    }
	    	return usr[user_uniq_id] = color;
		}else{
			return usr[user_uniq_id];
		}
	}
	var chk_view = '';
	var chk_start = '';
	var chk_end = '';
	function getvioews(){
		var view = $('#calendar_timelog').fullCalendar('getView');
		var t_st = $.fullCalendar.formatDate(new Date(view.start),'yyyy-MM-dd');
		var e_st = $.fullCalendar.formatDate(new Date(view.end),'yyyy-MM-dd');
		var projFil = $('#projFil').val();
		if(chk_view != view.name || (t_st != chk_start || e_st != chk_end)){
			chk_view = view.name;
			chk_start = t_st;
			chk_end = e_st;
			$.post(HTTP_ROOT + "log-times/calendarTimeLog",{'view_typ':chk_view,'chk_start':chk_start,'chk_end':chk_end,'projFil':projFil,'is_cnt':1},function(data) {
				var lgged_hr = format_time_hr_min(data.totalHrs);
				var bilble_hr = format_time_hr_min(data.billableHrs);
				var nonbilble_hr = format_time_hr_min(data.nonbillableHrs);
				if($('#tlog_total_hour').length){
					$('#tlog_total_hour').html('<?php echo __('Total hours logged');?>: <b>'+lgged_hr+'</b>, <?php echo __('Billable');?>: <b>'+bilble_hr+'</b>, <?php echo __('Non-Billable');?>: <b>'+nonbilble_hr+'</b>');
				}else{
					$('.fc-header-title').append('<div id="tlog_total_hour" style="margin-top:-20px;margin-bottom:10px;font-style:italic;"><?php echo __('Total hours logged');?>: <b>'+lgged_hr+'</b>, <?php echo __('Billable');?>: <b>'+bilble_hr+'</b>, <?php echo __('Non-Billable');?>: <b>'+nonbilble_hr+'</b></div>');
				}
			},'json');
		}
	}
</script>
<div class="pr width100">
<?php
		$calender_export=SES_TYPE == 3 ? 'calender_export_user' :'calender_export' ; ?>
	<div class="btn_tlog_top <?php echo $calender_export ;?>">
		<a href="javascript:void(0)" class="btn btn-sm btn_cmn_efect" onclick="ajax_timelog_export_csv();" rel="tooltip" title="<?php echo __('Export To CSV');?>">
		<i class="material-icons">&#xE8D5;</i>
		</a>
	</div>
<div id='calendar_timelog'></div>
</div>
<?php // Resource Utilization is not part of the Community Edition. ?>
<div class="crt_task_btn_btm">
	<?php // OSS edition: task timer removed ?>
<?php if($this->Format->isAllowed('Manual Time Entry',$roleAccess)){ ?>
    <div class="os_plus">
        <div class="ctask_ttip">
            <span class="label label-default">
                <?php echo __('Time Entry');?>
            </span>
        </div>
        <a href="javascript:void(0)" onclick="createlog(0,'')">
            <i class="material-icons cmn-icon-prop ctask_icn">&#xE192;</i>
            <img src="<?php echo HTTP_ROOT; ?>img/images/plusct.png" class="add_icn" />
        </a>
    </div>
<?php } ?>
</div>
<div class="cb"></div>
