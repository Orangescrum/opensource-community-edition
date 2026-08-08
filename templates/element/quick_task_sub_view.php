<% if(d_mid==0 || d_mid !=0) { %>
    <?php if ($this->Format->isAllowed('Create Task', $roleAccess)) { ?>
        <% if(projUniq !="all" ) { %>
            <tr class="qtask quicktsk_tr_lnk qtsksbtsk" id="quicktsk_tr_lnk_<%= mid %>">
                <td colspan="10">
                    <div class="new_qktask_mc">
                        <div class="new_grp_tsk " id="new_task_label_<%= mid %>" style="width: 130px;"><a
                                href="javascript:void(0)" class="cmn-bxs-btn" onclick="showhideqt(<%= mid %>);"><i
                                    class="material-icons quick_task_icon">&#xE145;</i><?php echo __('Quick Task'); ?></a>
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="quicktsk_tr task_list_page qtsksbtsk" id="quicktsk_tr_<%= mid %>">
                <td colspan="12" class="quicktd_task">
                    <div class="col-md-3 form-group label-floating fl">
                        <div class="input-group">
                            <label class="control-label" for="addon3a"><?php echo __('Task Title'); ?></label>
                            <input class="form-control inline_qktask<%= mid %>" type="text" id="inline_qktask_<%= mid %>">
                        </div>
                    </div>
                    <div class="col-md-2 form-group label-floating fl stop-floating-top qt_form-group <?php if (!$this->Format->isAllowed('Update Task Duedate', $roleAccess)) { ?> no-pointer <?php } ?>"
                        style="width: 13%;">
                        <label class="control-label multilang_ellipsis" for="qt_due_dat_<%= mid %>"
                            title="<?php echo __('Due Date'); ?>"><?php echo __('Due Date'); ?></label>
                        <?php $dues_date_qt_top = $this->Tmzone->GetDateTime(SES_TIMEZONE, TZ_GMT, TZ_DST, TZ_CODE, date('Y-m-d H:i:s'), "date"); ?>

                        <input class="form-control" type="text" name="qt_due_dat" id="qt_due_dat_<%= mid %>"
                            readonly="readonly"
                            placeholder="Ex. <?php echo date('M d, Y', strtotime($dues_date_qt_top)); ?>">
                        <div class="cmn_help_select"></div>
                    </div>
                    <div
                        class="col-md-2 padrht-non cstm-drop-pad qt_dropdown task_type qt_tsk_type_dropdown <?php if (!$this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?> no-pointer <?php } ?>">
                        <select class="tsktyp-select form-control task_type floating-label"
                            placeholder="<?php echo __('Task Type'); ?>" data-dynamic-opts=true
                            id="qt_task_type_<%= mid %>">
                            <% var select_task_type_qt='' ; for(var k in GLOBALS_TYPE) {
                                if(isDisplayEpicType(GLOBALS_TYPE[k].Type.name)){ if(GLOBALS_TYPE[k].Type.project_id==0 ||
                                GLOBALS_TYPE[k].Type.project_id==PROJECTS_ID_MAP[$('#projFil').val()]){ var
                                v=GLOBALS_TYPE[k]; var t=v.Type.id; var t1=v.Type.short_name; var t2=v.Type.name; var
                                txs_typ=t2; var check_sel='' ; if(select_task_type_qt=='' ){
                                select_task_type_qt=v.Type.name; } if(defaultTaskType !="" && defaultTaskType==v.Type.id){
                                check_sel="selected" ; select_task_type_qt=v.Type.name; } %>
                                <option value="<%= v.Type.id %>" <%=check_sel %>><%= v.Type.name %></option>
                                <% } } } %>
                        </select>
                        <div class="cmn_help_select"></div>
                    </div>

                    <div class="col-md-1 padrht-non custom-task-fld task-type-fld labl-rt cstm-drop-pad qt_dropdown <?php if (!$this->Format->isAllowed('Change Assigned to', $roleAccess)) { ?> no-pointer <?php } ?> qt_dropdown_assn"
                        style="width:15%;">
                        <select class="form-control floating-label" placeholder="<?php echo __('Assign To'); ?>"
                            data-dynamic-opts=true onchange="changeTypeId(this)" id="quick-assign_<%= mid %>">
                            <% for(var qtk in QTAssigns){ var check_sel='' ; var user_nm_me='<?php echo __('Me'); ?>' ;
                                if(SES_TYPE>=3 && SES_ID ==
                                QTAssigns[qtk].id){
                                check_sel = "selected";
                                }else if(defaultAssign && QTAssigns[qtk].id == defaultAssign){
                                check_sel = "selected";
                                }
                                %>
                                <option value="<%= QTAssigns[qtk].id %>" <%=check_sel %>><% if(SES_ID==QTAssigns[qtk].id){
                                        %>
                                        <%= user_nm_me %>
                                            <% }else{ %>
                                                <%= QTAssigns[qtk].name %>
                                                    <% } %>
                                </option>
                                <% } %>
                                    <option value="0"><?php echo __('Unassigned'); ?></option>
                        </select>
                    </div>
                    <div
                        class="col-md-1 form-group label-floating fl stop-floating-top qt_form-group <?php if (!$this->Format->isAllowed('Est Hours', $roleAccess)) { ?> no-pointer <?php } ?>">
                        <label class="control-label" for="qt_estimated_hours"><?php echo __('Est. Hour'); ?></label>
                        <input class="form-control qt_estimated_hours<%= mid %>" placeholder="hh:mm" type="text"
                            id="qt_estimated_hours_<%= mid %>" onkeypress="return numeric_decimal_colon(event)">
                    </div>
                    <div class="quicktask_save_exit_btn">
                        <div class="btn-group save_exit_btn">
                            <input type="hidden" value="list" id="task_view_types_span" />
                            <a id="quickcase_qt" href="javascript:void(0)" class="btn btn-primary btn-raised"
                                onclick="AddnewQuickTask(<%= mid %>,<%= '\'qtg\'' %>);"><?php echo __('Save'); ?></a>
                        </div>
                        <span class="input-group-btn ds_ib_btn">
                            <a href="javascript:void(0);" onclick="blurqktask_qt_tg(<%= mid %>);">
                                <?php echo __('Cancel'); ?>
                            </a>
                        </span>
                    </div>
                    <div class="cb"></div>
                </td>
            </tr>
            <% } %>
            <?php } ?>
            <% } %>