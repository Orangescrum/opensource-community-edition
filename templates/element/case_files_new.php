<div class="sec_title tog" data-cmnt_id ="files_sec">
    <div class="heading_title">
        <span class="sec_icon file_icon"></span>
        <h3><?php echo __('Files'); ?></h3>
    </div>
    <div class="icon_collapse " ></div>
</div>
<div class="toggle_details mt-20">
    <?php if (\Cake\Core\Plugin::isLoaded('Dms')) { ?>
    <% if (is_inactive_case == 0 && is_active == 1) { %>
    <div class="dms-files-toolbar" style="text-align:right;margin-bottom:8px;">
        <button type="button" class="dms-link-files-btn" data-task-id="<%= csAtId %>" data-project-id="<%= (typeof csProjId !== 'undefined' && csProjId) ? csProjId : '' %>"
            style="padding:6px 12px;background:#2e2e2e;color:#fff;border:0;border-radius:6px;font-size:12px;cursor:pointer;">
            + <?php echo __('Link from DMS'); ?>
        </button>
    </div>
    <% } %>
    <?php } ?>
    <div class="detail_list_table">
        <% if (all_new_files.length > 0) { %>

        <table class="width-100-per layout_fixed">
            <thead>
            <tr>
                <th><?php echo __('Task#'); ?></th>
                <th class="width-50-per text-center"><?php echo __('File Name'); ?></th>
                <th class="text-center"><?php echo __('User'); ?></th>
                <th class="text-center"><?php echo __('Download'); ?></th>
            </tr>
            </thead>
            <tbody>
            <% for(var file in all_new_files){
            var getFiles = all_new_files[file];
            caseFileName = getFiles.CaseFile.file;
            caseFileUName = getFiles.CaseFile.upload_name;
            var caseFileFormat = getFiles.CaseFile.format_file;
            downloadurl = getFiles.CaseFile.downloadurl;
            var csby_file = getFiles.CaseFile.csby;
            var d__fil_name = getFiles.CaseFile.display_name; %>
            <% if(!d__fil_name){d__fil_name = caseFileName;} %>
            <% if(caseFileUName == null){caseFileUName = caseFileName;} %>
            <?php if($this->Format->isAllowed('View File',$roleAccess)){ ?>
                <tr>
                    <td>
                        <div><%= csNoRep %></div>
                    </td>
                    <td>
                        <div class="file_type_icon" style="display:flex;align-items:center;gap:4px;min-width:0;">
                            <% if(caseFileFormat == 'xls' ) {%>
                            <img src="<?php echo HTTP_ROOT;?>img/excel.png" width="20" height="20" style="flex:none;">
                            <% } else if((caseFileFormat =='png' || (caseFileFormat =='jpg'))){ %>
                            <img src="<?php echo HTTP_ROOT;?>img/png.png" width="20" height="20" style="flex:none;">
                            <% }else if(caseFileFormat == 'pdf'){ %>
                            <img src="<?php echo HTTP_ROOT;?>img/pdf.png" width="20" height="20" style="flex:none;">
                            <% }else if(caseFileFormat == 'pptx'){ %>
                            <img src="<?php echo HTTP_ROOT;?>img/ppt.png" width="20" height="20" style="flex:none;">
                            <% }else{ %>
                            <img src="<?php echo HTTP_ROOT;?>img/<%= caseFileFormat%>.png" width="20" height="20" style="flex:none;">
                            <% } %>
                            <span title="<%= caseFileUName %>" style="flex:1 1 auto;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><%= caseFileUName %></span>
                        </div>
                    </td>
                    <td class="text-left">
                        <div class="username">
                            <%= csby_file %>
                        </div>
                    </td>
                    <td class="action_td">
                        <% if(is_inactive_case == 0 && is_active == 1) {%>
                        <% if(downloadurl){ %>
                        <a <?php if($this->Format->isAllowed('Download File',$roleAccess)){ ?>href="<%= downloadurl %>" <?php } ?>target="_blank" alt="<%= caseFileName %>" title="<%= caseFileName %>"><div class="download_icon"></div></a>
                        <% } else { %>
                        <a <?php if($this->Format->isAllowed('Download File',$roleAccess)){ ?>href="<?php echo HTTP_ROOT; ?>easycases/download/<%= caseFileUName %>" <?php } ?> alt="<%= d__fil_name %>" title="<%= d__fil_name %>" rel="prettyImg[<%= csAtId %>]"><div class="download_icon"></div></a>
                        <% } %>
                        <% } %>
                    </td>

                </tr>
            <?php } ?>
            <% } %>
            </tbody>
        </table>
        <% } else { %>
        <div class="nodetail_found" id="local_no_files_<%= csAtId %>">
            <figure>
                <img src="<?php echo HTTP_ROOT;?>img/tools/No-details-found.svg" width="120"
                     height="120">
            </figure>
            <div class="colr_red mtop15"><?php echo __('No Files found'); ?></div>
        </div>
        <% } %>

        <?php if (\Cake\Core\Plugin::isLoaded('Dms')) { ?>
        <div class="dms-task-files" data-task-id="<%= csAtId %>" data-booted="0" style="margin-top:16px;"></div>
        <?php } ?>
    </div>
</div>
