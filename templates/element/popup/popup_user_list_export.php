<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon" data-dismiss="modal" onclick="closePopup();"><i class="material-icons">&#xE14C;</i></button>
            <h4><?php echo __('Export User Details');?></h4>
        </div>
        <div class="cb"></div>
        <div id="inner_user_list_export" class="mtop10">
           <div class="modal-body popup-container">
						<div class="row">
                        <span class="exprt-txt"><?php echo __('Below columns will be exported');?>.</span>
                <div class="col-lg-12">
					<input type="hidden" name="hid_user_export_type" id="hid_user_export_type_id" value="" />
                    <div class="checkbox custom-checkbox pop-task-type-check">
							<label for="exp_userName">
									<input id="exp_userName" type="checkbox" value="user_name" class="user_exp_chkbx" checked="checked"/>
									<span class="oya-blk">
										 <?php echo __('Name'); ?>
									</span>
							</label>
					</div>
					<div class="checkbox custom-checkbox pop-task-type-check">
							<label for="exp_userLastName">
									<input id="exp_userLastName" type="checkbox" value="user_last_name" class="user_exp_chkbx" checked="checked"/>
									<span class="oya-blk">
										 <?php echo __('Last Name'); ?>
									</span>
							</label>
					</div>
					<div class="checkbox custom-checkbox pop-task-type-check">
							<label for="exp_userEmail">
									<input id="exp_userEmail" type="checkbox" value="user_email" class="user_exp_chkbx" checked="checked"/>
									<span class="oya-blk">
										 <?php echo __('Email'); ?>
									</span>
							</label>
					</div>
					<div class="checkbox custom-checkbox pop-task-type-check">
							<label for="exp_userRole">
									<input id="exp_userRole" type="checkbox" value="user_role" class="user_exp_chkbx" checked="checked"/>
									<span class="oya-blk">
										 <?php echo __('Role'); ?>
									</span>
							</label>
					</div>
					<div class="checkbox custom-checkbox pop-task-type-check">
							<label for="exp_userStatus">
									<input id="exp_userStatus" type="checkbox" value="user_status" class="user_exp_chkbx" checked="checked"/>
									<span class="oya-blk">
										 <?php echo __('Status');?>
									</span>
							</label>
					</div>
					<div class="checkbox custom-checkbox pop-task-type-check">
						<label for="exp_userProjects">
							<input id="exp_userProjects" type="checkbox" value="user_projects" class="user_exp_chkbx" checked="checked"/>
							<span class="oya-blk">
								 <?php echo __('Projects');?>
							</span>
						</label>
					</div>
					<div class="checkbox custom-checkbox pop-task-type-check">
							<label for="exp_userLastActivity">
									<input id="exp_userLastActivity" type="checkbox" value="user_last_activity" class="user_exp_chkbx" checked="checked"/>
									<span class="oya-blk">
										 <?php echo __('Last Activity');?>
									</span>
							</label>
					</div>
					<div class="checkbox custom-checkbox pop-task-type-check">
							<label for="exp_userCreatedDate">
									<input id="exp_userCreatedDate" type="checkbox" value="user_created_date" class="user_exp_chkbx" checked="checked"/>
									<span class="oya-blk">
										 <?php echo __('Created Date');?>
									</span>
							</label>
					</div>
					
				<div class="cb"></div>
                </div>
			</div>
            </div>
            <div class="modal-footer">
                <div class="fr popup-btn act_btttn">
                    <span class="fl cancel-link"><button type="button" class="btn btn-default btn_hover_link cmn_size" data-dismiss="modal" onclick="closePopup();"><?php echo __('Cancel');?></button></span>
                    <span class="fl hover-pop-btn"><a href="javascript:void(0)" class="btn btn_cmn_efect cmn_bg btn-info cmn_size" onclick="userlistexport();"><?php echo __('Submit');?></a></span>
                    <div class="cb"></div>
                </div>
            </div>
        </div>
    </div>
</div>
