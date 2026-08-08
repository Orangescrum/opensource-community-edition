<div class="task-list-bar templates-bar">
    <div class="row">
        <div class="col-lg-12">
            <div class="col-lg-12">
                <?php if (CONTROLLER == 'programs' && strtolower(PAGE_NAME) == 'listprogram') { ?>
                    <div class="task-list-bar dbrd-bar">
                        <div class="wrap_top_tlbar">
                            <div class="row d-flex align-items-center" style="display:flex;align-items:center;">
                                <div class="col-md-6 col-sm-6 col-xs-6" style="padding-left:0;display:flex;align-items:center;">
                                    <ul id="tour_crt_proj_swtch" class="fl pl-10" style="margin-bottom:0;">
                                        <?php if ($this->Format->isAllowed('Create Program', $roleAccess)) { ?>
                                        <li>
                                            <a class="cmn-btn" title="<?php echo __("Add Program"); ?>" href="javascript:void(0)" onClick="newProgram('add',0,'')">
                                                <i class="material-icons">&#xE145;</i> 
                                                <?php echo __("Add Program"); ?>
                                            </a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right"
                                    style="display:flex;align-items:center;justify-content:flex-end;margin-top: 15px;">
                                    <div class="tag-btn fl filter_det" style="margin-left: 15px;height: auto;">
                                        <div class="ver_midl">
                                            <div class="tag-block" id="filtered_items_program"></div>
                                        </div>
                                        <div class="filter_btn_section ver_midl_prm d-none" id="savereset_filter_program">
                                            <span onClick="resetAllFiltersProgram('all');" id="reset_btn_program"
                                                title="<?php echo __('Reset Filters'); ?>" rel="tooltip">
                                                <i class="material-icons">&#xE8BA;</i>
                                            </span>
                                        </div>

                                    </div>
                                    <span id="task_filter" class="dropdown task_section case-filter-menu">
                                        <a class="dropdown-toggle dropdown_menu_all_filters_togl"
                                            href="javascript:void(0);showHidetaskFilter();" rel="tooltip"
                                            title="<?php echo __('Filter'); ?>"
                                            style="display:inline-flex;align-items:center;color:#727272;text-decoration:none;">
                                            <i class="glyphicon glyphicon-filter" style="font-size:18px;vertical-align:middle;"></i>
                                        </a>
                                        <div class="dropdown_menu_t dropdown_menu_all_filters_ul_bkp"
                                            id="dropdown_menu_all_filters_t">
                                            <?php echo $this->element('program_filters'); ?>
                                        </div>
                                    </span>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="col-lg-4 text-right"></div>
        </div>
    </div>
</div>