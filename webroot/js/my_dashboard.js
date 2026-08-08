const cardParentEls = ['proj-count-complete', 'tasks-count-complete', 'rsrc-count-complete', 'time-count-complete'];
var projectSummary = milestoneSummary = budgetSummary = programSummery = [];
$(document).ready(function() {
    fetchSummaryLists();
    if($('#program_summary_container').length){
        fetchProgramSummaryLists();
    }
    fetchMilestoneSummaryLists();
    if($('#workload_summary_container').length){
        fetchWorkloadSummary();
    }   
    fetchBudgetSummary(0); 

    $("#sel_rsrc_project").select2({
        placeholder: _("Project"),
        allowClear: true
    });
    $("#sel_rsrc_project_program").select2({
        placeholder: _("Project"),
        allowClear: true
    });
    $("#sel_rsrc_program").select2({
        placeholder: _("Project"),
        allowClear: true
    });
    $("#sel_project_manager").select2({
        placeholder: _("Project Manager"),
        allowClear: true
    });
    $("#sel_program_manager").select2({
        placeholder: _("Project Manager"),
        allowClear: true
    });
    $("#sel_project_status").select2({
        placeholder: "Status",
        allowClear: true
    });
    $("#sel_program_status").select2({
        placeholder: "Status",
        allowClear: true
    });

    $("#sel_rsrc_project, #sel_project_manager, #sel_project_status, #sel_rsrc_project_program").on('change', function() {
        fetchSummaryLists();
    });

    $("#sel_rsrc_program, #sel_program_manager, #sel_program_status").on('change', function() {
        if($('#program_summary_container').length){
            fetchProgramSummaryLists();
        }
    });

    $("#sel_proj_milestone").select2({
        placeholder: _("Project"),
        allowClear: true
    });
    $("#sel_milestone").select2({
        placeholder: _("Project Manager"),
        allowClear: true
    });
    $("#sel_milestone_status").select2({
        placeholder: "Status",
        allowClear: true
    });

    $("#sel_proj_milestone, #sel_milestone, #sel_milestone_status").on('change', function() {
        fetchMilestoneSummaryLists();
    });


    $("#workload_dates").select2({
        placeholder: _("Project"),
        allowClear: true
    });
    $("#workload_rolegroup").select2({
        placeholder: _("Project Manager"),
        allowClear: true
    });
    $("#workload_role").select2({
        placeholder: "Status",
        allowClear: true
    });
    $("#workload_project").select2({
        placeholder: "Status",
        allowClear: true
    });
    $("#workload_users").select2({
        placeholder: "Status",
        allowClear: true
    });
    $("#workload_dates, #workload_rolegroup, #workload_role, #workload_project, #workload_users").on('change', function() {
        fetchWorkloadSummary();
    });

    //Budget and cost
    $("#sel_proj_budget").select2({
        placeholder: "Status",
        allowClear: true
    }).on('select2:select', function (e) {
        fetchBudgetSummary(e.params.data.id);
    });  
    $("#toggleShowbudget").on('change',function(){
        if($(this).is(':checked')){
          $('#is_budgeted').val(0);
        }else{
         $('#is_budgeted').val(1);
        }
        fetchBudgetSummary($("#sel_proj_budget").val());
     });
     //Budget and cost end       

    $("#sel_new_proj_todo").select2({
        placeholder: _("Project"),
        allowClear: true
    });
    $("#sel_proj_activities").select2({
        placeholder: _("Project"),
        allowClear: true
    });
    $("#sel_actvt_user").select2({
        placeholder: _("User"),
        allowClear: true
    });
    $("#sel_new_proj_tlog").select2({
        placeholder: _("Project"),
        allowClear: true
    });
    $("#sel_user_ttype").select2({
        placeholder: _("User"),
        allowClear: true
    });
    $("#sel_proj_ttype").select2({
        placeholder: _("Project"),
        allowClear: true
    });
    $("#sel_rsrc_time_typ").select2();

    $("#sel_new_proj_todo, #sel_proj_activities, #sel_actvt_user, #sel_new_proj_tlog").on('change', function() {
        callAsyncFunc($(this).attr('id'), $(this).val());
    });
    $(document).on('click', '.to_dos_summarry', function(){
        if($(this).attr('href') == "#upcoming_tab"){
            $('#overdue_tab').hide();
            $('#upcoming_tab').show();
        } else {
            $('#upcoming_tab').hide();
            $('#overdue_tab').show();
        }
    });
    //callAsyncFunc('sel_new_proj_todo', 0);
    //callAsyncFunc('sel_new_proj_tlog');
});
//Thoussand separator
function numberWithCommas(input_number) 
{
    return input_number.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
}
function formatWithCurrency(input_number, currency_code='')
{
    if(parseFloat(input_number) > 0){
        return currency_code+numberFormat(input_number,2);
    }else{
        return '--';
    }
}
function numberFormat(labelValue, decimal=0, is_thousand=0) 
{
    // Nine Zeroes for Billions
    let num_placeholder = '';
    let is_in_thousand = 0;
    labelValue = labelValue.toString().replace(/[,]+/g,'');
    let newNumber = labelValue;
    if(Math.abs(Number(labelValue)) >= 1.0e+9){
        newNumber = Math.abs(Number(labelValue)) / 1.0e+9;
        num_placeholder = 'B';
    }else if(Math.abs(Number(labelValue)) >= 1.0e+6){
        newNumber = Math.abs(Number(labelValue)) / 1.0e+6;
        num_placeholder = 'M';
    }else if(Math.abs(Number(labelValue)) >= 1.0e+3){
        is_in_thousand = 1;
        if(is_thousand){
            newNumber = Math.abs(Number(labelValue)) / 1.0e+3;
            num_placeholder = 'K';
        }else{
            labelValue = Number(labelValue).toFixed(decimal);
            let num_split = labelValue.split('.');
            if(num_split[1] === '00' || num_split[1] === '0'){
                labelValue = num_split[0];
            }
            newNumber = numberWithCommas(labelValue);
        }        
    }else{
        newNumber = Math.abs(Number(labelValue));
    }
    if(decimal && !is_in_thousand){
        newNumber = newNumber.toFixed(decimal);
        let num_split = newNumber.split('.');
        if(num_split[1] === '00' || num_split[1] === '0'){
            newNumber = num_split[0];
        }
    }

    //return (!decimal) ? newNumber+num_placeholder : (is_thousand) ? newNumber.toFixed(decimal)+num_placeholder : newNumber;
    return newNumber+num_placeholder;
}

let blockui = (block) => {
    let blockEl = $(block);
    $(blockEl).block({
        message: `<img src="${HTTP_ROOT}img/images/case_loader2.gif" alt="Loading..." title="Loading...">`,
        overlayCSS: {
            backgroundColor: '#fff',
            opacity: 0.8,
            cursor: 'wait',
            borderRadius: '10px',
            zIndex:1
        },
        css: {
            border: 0,
            padding: 0,
            backgroundColor: 'transparent',
            zIndex:1
        }
    });
};

let unblockui = (block) => {
    let blockEl = $(block);
    $(blockEl).unblock();
};

function sortTableData(column, obj, type){
    if(type == 'project'){
        var data = projectSummary.projSummaryList;
    }else if(type == 'milestone'){
        var data = milestoneSummary.mileSummaryList;
    }else if(type == 'budget'){
        var data = budgetSummary.budgetSummaryList;
    }else if(type == 'program'){
        var data = programSummary.projSummaryList;
    }         
    $('.frontend_sorting').find('span.sort-icon').html('<i class="material-icons default-sort">sort</i>');
    if($(obj).attr('data-sort') == '-1'){
        var sort_direction = 1;
        $(obj).attr('data-sort', '1');
        $(obj).find('span.sort-icon').html('<i class="material-icons">expand_less</i>');
    }else{
        var sort_direction = -1;
        $(obj).attr('data-sort', '-1');
        $(obj).find('span.sort-icon').html('<i class="material-icons">expand_more</i>');
    }
    data.sort((a, b) => {
        a_eval = a[column];
        b_eval = b[column];
        //To remove the $ and thousand separator ftom the numbers to sort exactly using the value
        if ((a_eval != null && b_eval != null) && a_eval.toString().indexOf("$") !== -1) {
            a_eval = a_eval.toString().replace(/\$/g, "").replace(/,/g, "");
            b_eval = b_eval.toString().replace(/\$/g, "").replace(/,/g, "");
        }
        try {
            //convert null value to string
            a_eval = (a_eval != null) ? a_eval : '';
            b_eval = (b_eval != null) ? b_eval : '';
            //for number comparision
            a_float = parseFloat(a_eval);
            b_float = parseFloat(b_eval);
            //if not a number then compare as string
            if (isNaN(a_float) || isNaN(b_float) || isNaN(a_eval) || isNaN(b_eval)) {
                return a_eval.localeCompare(b_eval) * sort_direction;
            }
            return (a_float - b_float) * sort_direction;
        } catch (error) {
            console.log(error);
            //Otherwise assume string
            return a_eval.localeCompare(b_eval) * sort_direction;
        }
    });
    if(type == 'project'){
        projectSummary.projSummaryList = data;
        renderSummary(projectSummary, 'proj');
    }else if(type == 'milestone'){
        milestoneSummary.mileSummaryList = data;
        renderSummary(milestoneSummary, 'milestone');
    }else if(type == 'budget'){
        budgetSummary.budgetSummaryList = data;
        renderSummary(budgetSummary, 'budget');
    }else if(type == 'program'){
        programSummary.projSummaryList = data;
        renderSummary(programSummary, 'program');
    }        
}

function renderSummary(data, id){
    blockui($('#'+id+'-summary-block'));
    $("#"+id+"_summary_container").html('');
    if(id == 'proj'){
        $("#"+id+"_summary_container").html(tmpl("projectSummaryTmpl", data));
    }else if(id == 'budget'){
        $("#"+id+"_summary_container").html(tmpl("budgetSummaryTmpl", data));
    }else if(id == 'program'){
        $("#"+id+"_summary_container").html(tmpl("programSummaryTmpl", data));
    }else{
        $("#"+id+"_summary_container").html(tmpl("milestoneSummaryTmpl", data));
    }
    unblockui($('#'+id+'-summary-block'));
    $("#"+id+"_summary_container").show();
    if(id == 'proj' || id == 'program'){
        renderProgressGraph('project_circle');
    }else if(id == 'budget'){        
    }else{
        renderProgressGraph('milestone_circle');
    }    
}

function renderProgressGraph(id){
    $("."+id+".circle_percent").each(function() {
        var $this = $(this),
            $dataV = $this.data("percent"),
            $dataDeg = $dataV * 3.6,
            $round = $this.find(".round_per");                
        $per_more_cls = 'percent_more_complete';
        if ($dataV == 100) {
            $this.find('.round_per').css('background', '#2AD36C');
        } else if ($dataV <= 30) {
            $this.find('.round_per').css('background', '#E84C85');
            $per_more_cls = 'percent_more_risk';
        } else if ($dataV >= 60) {
            $this.find('.round_per').css('background', '#6570FD');
            $per_more_cls = 'percent_more_ontrack';
        } else {
            $this.find('.round_per').css('background', '#F99003');
            $per_more_cls = 'percent_more_delay';
        }
        $round.css("transform", "rotate(" + parseInt($dataDeg + 180) + "deg)");
        $this.append('<div class="circle_inbox"><span class="percent_text"></span></div>');
        $this.prop('Counter', 0).animate({
            Counter: $dataV
        }, {
            duration: 2000,
            easing: 'swing',
            step: function(now) {
                $this.find(".percent_text").text(Math.ceil(now) + "%");
            }
        });
        if (parseFloat($dataV) >= 51) {
            $round.css("transform", "rotate(" + 360 + "deg)");
            //setTimeout(function() {
                $this.addClass("percent_more"+" "+$per_more_cls); 
            //}, 1000);
            //setTimeout(function() {
                $round.css("transform", "rotate(" + parseInt($dataDeg + 180) + "deg)");
            //}, 1000);
        }
    });
}

function fetchWorkloadSummary() {
    var proj_id = $("#workload_project").val();
    var input_date = $("#workload_dates").val();
    var group_id = $("#workload_rolegroup").val();
    var role = $("#workload_role").val();
    var user = $("#workload_users").val();
    var strUrl = HTTP_ROOT + "project_reports/ajaXFetchWorkloadSummary";
    blockui($('#workload-summary-block'));
    $("#workload_summary_container").hide();
    $.post(strUrl, {
        "proj_id": proj_id,
        "input_date": input_date,
        "group_id": group_id,
        "role": role,
        "user": user,
    }, function(response) {
        if (response) {            
            $("#workload_summary_container").html('');
            //$("#workload_summary_container").html(tmpl("projectSummaryTmpl", data));
            if(!response.categories || !response.categories.length){
                $('#workload_summary_container').html("<img src='/img/sample/dashboard/workload.png' alt='' style='max-width:100%;max-height:100%;'/>");
            }else{                
                var chart_height = response.categories.length * 20;
                if(response.categories.length <= 4){
                    var chart_height = 200;
                }else if(response.categories.length <= 10){
                    var chart_height = 400;
                }else if(response.categories.length <= 18){
                    var chart_height = 400;
                }else if(response.categories.length <= 25){
                    var chart_height = 600;
                }
                $('#tot_workload_info').text(response.total_hours+' Total Hours ('+response.date.strddt+' - '+response.date.enddt+')');
                Highcharts.chart('workload_summary_container', {
                    credits: {
                        enabled: false
                    },
                    chart: {
                        type: 'bar',
                        height: chart_height+'px'
                    },
                    title: {
                        //text: response.total_hours+' Total Hours ('+response.date.strddt+' - '+response.date.enddt+')'
                        text: '',
                        style: { display: 'none'}
                    },
                    xAxis: {
                        categories: response.categories
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Hours'
                        }
                    },
                    legend: {
                        reversed: true
                    },
                    colors: [ 
                        '#FFF59A',                     
                        '#716C71',
                        '#7C6AFF',
                        '#FF3A46', 
                        '#6BBD2E' 
                    ],
                    plotOptions: {
                        series: {
                            stacking: 'normal'
                        }
                    },
                    tooltip: {
                        shared: true
                    },
                    series: response.data
                });
            }            
            unblockui($('#workload-summary-block'));
            $("#workload_summary_container").show();
        }
    }, "json");
}

function fetchSummaryLists() {
    var proj_id = $("#sel_rsrc_project").val();
    var prog_id = $("#sel_rsrc_project_program").val();
    var manager_id = $("#sel_project_manager").val();
    var status = $("#sel_project_status").val();
    var strUrl = HTTP_ROOT + "my-dashboards/ajaXFetchProjectSummary";
    blockui($('#proj-summary-block'));
    blockui($('#overall-progress-block'));
    $("#proj_summary_container").hide();
    $.post(strUrl, {
        "proj_id": proj_id,
        "prog_id": prog_id,
        "manager_id": manager_id,
        "status": status,
    }, function(data) {
        if (data) {
            projectSummary = data;
            $("#proj_summary_container").html('');
            $("#proj_summary_container").html(tmpl("projectSummaryTmpl", data));
            unblockui($('#proj-summary-block'));
            $("#proj_summary_container").show(); 
            renderProgressGraph('project_circle'); 
            renderOverallPie({
                id: 'project_status',
                cmn_count_cls: '_graph_cnt',
                total_cls: 'total_prj',
                individual_cls: '_graph'
            }, data.projSummaryGraph, 1);
        }
    }, "json");
}
function fetchProgramSummaryLists() {
    var proj_id = $("#sel_rsrc_program").val();
    var manager_id = $("#sel_program_manager").val();
    var status = $("#sel_program_status").val();
    var purpose_type = 'program';
    var strUrl = HTTP_ROOT + "my-dashboards/ajaXFetchProjectSummary";
    blockui($('#program-summary-block'));
    blockui($('#program-progress-block'));
    $("#program_summary_container").hide();
    $.post(strUrl, {
        "proj_id": proj_id,
        "manager_id": manager_id,
        "status": status,
        "purpose_type" : purpose_type,
    }, function(data) {
        if (data) {
            programSummary = data;
            $("#program_summary_container").html('');
            $("#program_summary_container").html(tmpl("programSummaryTmpl", data));
            unblockui($('#program-summary-block'));
            $("#program_summary_container").show(); 
            renderProgressGraph('project_circle'); 
            renderOverallPie({
                id: 'program_status',
                cmn_count_cls: '_prog_cnt',
                total_cls: 'total_prg',
                individual_cls: '_prog'
            }, data.projSummaryGraph, 1);
        }
    }, "json");
}

function renderOverallPie(obj, res) {
    if (obj.id == 'program_status') {
        unblockui($('#program-progress-block'))
    } else if (obj.id == 'project_status') {
        unblockui($('#overall-progress-block'));
    } else if (obj.id == 'milestone_status'){
        unblockui($('#milestone-progress-block'))
    }

    var show_legend = (typeof arguments[2] != 'undefined') ? false : true;
    if (res.length) {
        var tot_item = tot_complete = 0;
        $('.' + obj.cmn_count_cls).text(0);
        $.each(res, function(index, value) {
            tot_item += value.y;
            if(value.class == "status_complete"){
                tot_complete += value.y;
            }
            $('.' + value.class + obj.individual_cls).text(value.y);
        });
        $('.' + obj.total_cls).text(tot_item);

        var height = 200;
        var x = 0;
        var y = -40;
        var align = 'right';
        var verticalAlign = 'top';
        var innerSize = '85%';
        var width = 170;
        var layout = 'vertical';
        var text = '';
        var data = res;
        $('#' + obj.id + '_pie').highcharts({
            credits: {
                enabled: false
            },
            exporting: {
                enabled: false,
                buttons: {
                    contextButton: {
                        symbolStrokeWidth: 2,
                        symbolStroke: '#969696',
                        menuItems: [{
                            text: 'PNG',
                            onclick: function() {
                                this.exportChart();
                            },
                            separator: false
                        }, {
                            text: 'JPEG',
                            onclick: function() {
                                this.exportChart({
                                    type: 'image/jpeg'
                                });
                            },
                            separator: false
                        }, {
                            text: 'PDF',
                            onclick: function() {
                                this.exportChart({
                                    type: 'application/pdf'
                                });
                            },
                            separator: false
                        }, {
                            text: 'Print',
                            onclick: function() {
                                this.print();
                            },
                            separator: false
                        }]
                    }
                },
            },
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                height: height
            },
            title: {
                align: "center",
                floating: true,
                margin: 0,
                style: {
                    "color": "#333333",
                    "fontSize": "18px"
                },
                text: text,
                useHTML: false,
                verticalAlign: "middle",
                x: x,
                y: y
            },
            tooltip: {
                formatter: function() {
                    var precsson = 3;
                    if (this.point.percentage < 1)
                        precsson = 2;
                    if (this.point.percentage >= 10)
                        precsson = 4;
                    return '<b>' + this.point.name + '</b>: ' + parseFloat((this.point.percentage).toPrecision(precsson)) + ' %';
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    borderWidth: 0,
                    showInLegend: show_legend,
                    dataLabels: {
                        enabled: false,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                    }
                }
            },
            legend: {
                enabled: true,
                layout: layout,
                align: align,
                verticalAlign: verticalAlign,
                width: width,
                borderWidth: null,
                labelFormatter: function() {
                    return this.name + ' - ' + this.y + '';
                }
            },
            series: [{
                size: '100%',
                innerSize: innerSize,
                type: 'pie',
                name: ' ',
                data: data,
            }]
        });

        //calculate the total percentage here        
        let tot_per = (tot_item > 0) ? (tot_complete/tot_item)*100 : 0;
        tot_per = tot_per.toFixed()+'%';
        $('.total_percentage'+obj.individual_cls).text(tot_per).show();
    } else {
        $('.' + obj.cmn_count_cls).text(0);
        $('.' + obj.total_cls).text(0);
        $('#' + obj.id + '_pie').html("<img src='"+HTTP_ROOT+"/img/sample/dashboard/staus.png' alt='' style='max-width:100%;max-height:100%;'/>");
    }
}

function fetchMilestoneSummaryLists() {
    var proj_id = $("#sel_proj_milestone").val();
    var milestone_id = $("#sel_milestone").val();
    var status = $("#sel_milestone_status").val();
    var strUrl = HTTP_ROOT + "milestones/ajaXFetchMilestoneSummary";
    blockui($('#milestone-summary-block'));
    blockui($('#milestone-progress-block'));
    $("#milestone_summary_container").hide();
    $.post(strUrl, {
        "proj_id": proj_id,
        "milestone_id": milestone_id,
        "status": status,
    }, function(data) {
        if (data) {
            milestoneSummary = data;
            $("#milestone_summary_container").html(tmpl("milestoneSummaryTmpl", data));
            unblockui($('#milestone-summary-block'));
            $("#milestone_summary_container").show();
            renderProgressGraph('milestone_circle');
            renderOverallPie({
                id: 'milestone_status',
                cmn_count_cls: '_graph_cnt_mile',
                total_cls: 'total_mile',
                individual_cls: '_mile'
            }, data.mileSummaryGraph, 1);
        }
    }, "json");
}

function fetchBudgetSummary(proj_id) {
    var strUrl = HTTP_ROOT + "projects/ajaXFetchBudgetSummary";
    blockui($('#budget-summary-block'));
    blockui($('#budget_status_pie'));
    $("#budget_summary_container").hide();
    $.post(strUrl, {
        "proj_id": proj_id,
        "is_budget": $('#is_budgeted').val()
    }, function(response) {
        if (response) { 
            budgetSummary = response;
            $("#budget_summary_container").html('');
            //$("#workload_summary_container").html(tmpl("projectSummaryTmpl", data));
            $('.budget-total-cnt .profit_green').text('');
            $('.budget-total-cnt .profit_purple').text('');
            if(!response.budgetSummaryList.length){
                $('#budget_summary_container').html("<img src='img/sample/dashboard/workload.png' alt='' style='max-width:100%;max-height:100%;'/>");
                $('#budget_status_pie').html("<img src='img/sample/dashboard/workload.png' alt='' style='max-width:100%;max-height:100%;'/>");
            }else{    
                $("#budget_summary_container").html(tmpl("budgetSummaryTmpl", response));            
                if(response.budgetSummaryGraph.length){
                    $('.budget-total-cnt .profit_green').text(formatWithCurrency(response.budgetSummaryGraph[0]['data'][0], response.currency_symbol));
                    $('.budget-total-cnt .profit_purple').text(formatWithCurrency(response.budgetSummaryGraph[1]['data'][0], response.currency_symbol));
                    Highcharts.chart('budget_status_pie', {
                        credits: {
                            enabled: false
                        },
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: '',
                            style: {display: 'none'}
                        },
                        subtitle: {
                            text: '',
                            style: {display: 'none'}
                        },
                        xAxis: {
                            categories: [
                                ''
                            ],
                            crosshair: true
                        },
                        yAxis: {
                            title: {
                                useHTML: true,
                                text: ''
                            }
                        },
                        colors: [ 
                            '#2AD36C',                     
                            '#6570FD'
                        ],
                        tooltip: {
                            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                '<td style="padding:0"><b>{point.y:.2f}</b></td></tr>',
                            footerFormat: '</table>',
                            //shared: true,
                            useHTML: true
                        },
                        plotOptions: {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 0
                            }
                        },
                        series: response.budgetSummaryGraph
                    });
                    unblockui($('#budget_status_pie'));
                }else{
                    $('#budget_status_pie').html("<img src='/img/sample/dashboard/workload.png' alt='' style='max-width:100%;max-height:100%;'/>");
                }
            }            
            unblockui($('#budget-summary-block'));
            $("#budget_summary_container").show();
        }
    }, "json");
}

async function fetchDashboardData() {
    let dashboardCountUri = `${HTTP_ROOT}my-dashboards/ajaxGetdashboardcounts`;
    cardParentEls.forEach(el => {
        blockui($(`#${el}`).closest('.item_card'));
    });
    const [countResponse] = await Promise.allSettled([
        fetch(dashboardCountUri, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        }),
    ]);
    const counts = await countResponse.value.json();

    return [counts];
}
fetchDashboardData().then(([counts]) => {
    displayCardCounts(counts);
}).catch(error => {
    console.log(error);
});

let callAsyncFunc = (type, ...rest) => {
    if(type === "sel_new_proj_todo"){
        fetchToDos(rest[0]).then(todos => {
            displayTodos(todos, 'overdue');
        }).catch(error => {
            console.log(error.message);
        });
    } else if(type === "sel_new_proj_tlog"){
        fetchTimelogChart().then(logData => {
            displayTimelog(logData);
        }).catch(error => {
            console.log(error.message);
        });
    }
};

async function fetchToDos(projid) {
    blockui($('#to-do-list-block'));
    let toDosUri = `${HTTP_ROOT}Easycases/to_dos`;
    let postData = {
        extra: '',
        task_type_id: 0,
        projid: projid == 0 ? "all" : projid,
        angular: 1,
        field: 'id',
        page: 'newDashboard'
    };
    const response = await fetch(toDosUri, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(postData)
    });
    if (!response.ok) {
        const message = `An error has occured: ${response.status}`;
        throw new Error(message);
    }
    return await response.json();
}

let displayTodos = (todos, ...rest) => {
    let response = {todos};
    unblockui($('#to-do-list-block'));
    $("#new_to_do_content").html('').html(tmpl("todoSummaryTmpl", response));
    if(rest[0] == "overdue") {
        $('#upcoming_tab').hide();
        $('#overdue_tab').show();
    } else {
        $('#overdue_tab').hide();
        $('#upcoming_tab').show();
    }
};

async function fetchTimelogChart() {
    blockui($('#tlog-block'));
    let toDosUri = `${HTTP_ROOT}Easycases/ajax_dashboard_timelog_new`;
    let postData = {
        extra: '',
        task_type_id: 0,
        projid: "all",
        angular: 1,
        page: 'newDashboard'
    };
    const response = await fetch(toDosUri, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(postData)
    });
    if (!response.ok) {
        const message = `An error has occured: ${response.status}`;
        throw new Error(message);
    }
    return await response.json();
}

let displayTimelog = (logData) => {
    let totalNonBillable = logData.series[0]['data'].reduce((partialSum, a) => partialSum + a, 0);
    let totalBillable = logData.series[1]['data'].reduce((partialSum, a) => partialSum + a, 0);
    let interval= logData.tinterval;
    $('#billable-stats').html('').html(`${totalBillable} hours`);
    $('#non-billable-stats').html('').html(`${totalNonBillable} hours`);
    Highcharts.chart('dashboard_timelog_new', {
        credits: {
            enabled: false
        },
        title: {
            text: null
        },
        subtitle: {
            text: null
        },
        yAxis: {
            title: {
                text: 'Hour(s) Spent'
            }
        },
        xAxis: {
            type: 'datetime',
            categories: eval(logData.dates),
            showFirstLabel: true,
            showLastLabel: true,
            tickInterval: interval
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },
        plotOptions: {
            series: {
                label: {
                    connectorAllowed: false
                }
            }
        },
        tooltip: {
            formatter:function(){
                return'<b>'+this.x+'</b><br/>'+this.series.name+': '+this.y+'<br/>'+'Total: '+this.point.stackTotal;
            }
        },
        series: logData.series,
        legend: {
            enabled: false
        },
        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                }
            }]
        }
    });
    unblockui($('#tlog-block'));
};

async function fetchActivities() {
    let activitiesUri = `${HTTP_ROOT}Easycases/recent_activities`;
    let postData = {
        extra: '',
        task_type_id: 0,
        projid: 'all',
        angular: 1
    };
    const response = await fetch(activitiesUri, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(postData)
    });
    if (!response.ok) {
        const message = `An error has occured: ${response.status}`;
        throw new Error(message);
    }
    return await response.json();;
}

/*fetchActivities().then(activities => {
    displayActivities(activities);
}).catch(error => {
    console.log(error.message);
});*/

let displayActivities = (activities) => {

};
let displayCardCounts = (counts) => {
    for (const countsKey in counts) {
        if (countsKey !== "proj-stats" && countsKey !== "task-stats" && countsKey !== "rsrc-stats" && countsKey !== "time-spent-stats") {
            $(`#${countsKey}`).html(``).html(numberFormat(counts[countsKey]));
        } else {
            let targetEl = '';
            switch (countsKey) {
                case 'proj-stats':
                    targetEl = 'proj';
                    break;
                case 'task-stats':
                    targetEl = 'tasks';
                    break;
                case 'rsrc-stats':
                    targetEl = 'rsrc';
                    break;
                default:
                    targetEl = 'timlog';
            }
            targetEl += `-inc-dec`;
            let statTypeClass = counts[countsKey]['type'] === "up" ? "increase" : "decrease";
            $(`#${targetEl}`).html(``).html(`<span class="${statTypeClass}">${counts[countsKey]['value']}%</span> ${statTypeClass.charAt(0).toUpperCase() + statTypeClass.slice(1)} from Last Month`);
        }
    }
    cardParentEls.forEach(el => {
        unblockui($(`#${el}`).closest('.item_card'));
    });
};