<?php
use Cake\Core\Configure;
$t_clr = Configure::read('PROFILE_BG_CLR');
$random_bgclr = $t_clr[array_rand($t_clr,1)];
?>
<%
var users_colrs = {"clr1":"#AB47BC;","clr2":"#455A64;","clr3":"#5C6BC0;","clr4":"#512DA8;","clr5":"#004D40;","clr6":"#EB4A3C;","clr7":"#ace1ad;","clr8":"#ffe999;","clr9":"#ffa080;","clr10":"#b5b8ea;",};
var total_records = sqlcasedata.length;
var totdata = 0;
var last_tot_rep = parseInt($('#lastTotReplies-lst').val());
sqlcasedata = sqlcasedata.reverse();
var edit_cnt;
if(0){ edit_cnt = 1; }else{ edit_cnt = total_records;}
totdata = total_records+1;
var i=0;
startSlno = (totalReplies < total_records) ? (last_tot_rep - total_records) + 1 : (totalReplies -total_records) + 1;
var sortOrder = getCookie('REPLY_SORT_ORDER');
if(sortOrder == 'ASC') { startSlno = (totalReplies < total_records) ? last_tot_rep : totalReplies; }
if(typeof thrdStOrd == 'undefined' || !thrdStOrd) {
if($('#thread_sortorder'+csAtId).length) {
var thrdStOrd = $('#thread_sortorder'+csAtId).val();
} else {
var thrdStOrd = 'ASC';
}
}
for(var repKey in sqlcasedata){
var getdata = sqlcasedata[repKey];
totdata--; i++;
var caseDtId = getdata.id;
var caseDtUniqId = getdata.uniq_id;
var caseDtPid = getdata.project_id;
var caseDtUid = getdata.user_id;
var caseDtMsg = getdata.message;
var wrap_msg = getdata.wrap_msg;
var caseDtFormat = getdata.format;
var taskLegend = getdata.legend;
var userArr = getdata.userArr;
var frmtCrtdDt = getdata.rply_dt;
var by_name = userArr.name;
var by_photo = userArr.photo;
var short_name = userArr.short_name;
var photo_exist = userArr.photo_exist;
var photo_existBg = userArr.photo_existBg;
var CSrep_count = getdata.CSrep_count;
if(!photo_exist){
by_photo = 'user.png';
var usr_name_fst = by_name.charAt(0);
}
var shRplyEdt = 0;
if(typeof user_can_change == 'undefined'){
if(is_active == 1 && ((taskLegend == 1 || taskLegend == 2 || taskLegend == 4) || SES_TYPE == 1 || SES_TYPE == 2 || (caseDtUid == SES_ID))){
user_can_change = 1;
}
}
if((taskLegend == 1 || taskLegend == 2) && SES_ID == caseDtUid && caseDtMsg) {
if((thrdStOrd=='ASC' && i==1) || (thrdStOrd=='DESC' && edit_cnt== i)) {
shRplyEdt = 1;
}
} %>
<% if((wrap_msg.length > 0) || (getdata.replyCap.length > 0) || (getdata.rply_files.length > 0) || (getdata.dms_docs && getdata.dms_docs.length > 0)){ %>
<div class="comments_item user-task-info details_task_block <% if((startSlno)%2 == 0) {%>content2 <% }else{ %> content4 <% } %> <% if(getdata.user_id != SES_ID) {%>replay_task_detail_blk <% } %>" id="rep<%= totdata %>">
    <div class="user-task-pf commenter-img">
        <% if(!photo_exist){ %>
        <span class="cmn_profile_holder <%= photo_existBg %>"><%= usr_name_fst %></span>
        <% }else{ %>
        <img class="lazy round_profile_img rep_bdr" data-original="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<%= by_photo %>&sizex=55&sizey=55&quality=100" src="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<%= by_photo %>&sizex=55&sizey=55&quality=100" title="<%= by_name %>" width="55" height="55" />
        <% } %>
    </div>
    <div id="tour_detl_comts" class="commentor_name create_reply_comment">
        <div class="right_name">
            <div class="width-100-per d-flex pr-10">
                <p class="width-60-per">
                    <strong style="color:<%= users_colrs[photo_existBg]%>"><%= formatText(getdata.usrName) %></strong>
                </p>
                <p class="replay_time ml-auto">
                    <span class="font-size-12"><%= getdata.rply_dt %></span>
                </p>
            </div>
        </div>
        <div class="details_task_block comment_data">
            <div id="cnt_<%= startSlno%>"class="details_task_desc wrapword" style="overflow:hidden;">
                <div class="pr pr-30" id="casereplytxt_id_<%= caseDtId %>">
                            <span class="d-block pr-30" id="replytext_content<%= caseDtId %>">
                                <%= wrap_msg %>
							</span>
                    <% if(shRplyEdt==1){ %>
                    <div rel="tooltip" title="<?php echo __('Edit');?>" onclick="editmessage(this,<%= caseDtId %>,<%= caseDtPid %>);" id="editpopup<%= caseDtId %>" class="rep_edit link-icon"><i class="material-icons icon-edit">&#xE3C9;</i></div>
                    <% } %>
                    <%= getdata.replyCap %>
                </div>
                <div id="casereplyid_<%= caseDtId %>" class="edit_reply_editor_tbl"></div>
                <% if(caseDtFormat != 2){
                var filesArr = getdata.rply_files;
                if(filesArr.length){
                var len = filesArr.length;
                %>
                <div class="fl attachment_wrap">
                    <div class="glyph attachment"></div>
                    <span>
                                <span class="attach_cnt"><%= len%> <% if(len > 1){ %> Files <% }else{ %> File<% } %></span>
                            </span>
                </div>
                <div class="cb"></div>
                <% var fc = 0;
                var imgaes = ""; var caseFileName = "";
                for(var fkey in filesArr){
                var getFiles = filesArr[fkey]; %>
                <% caseFileName = getFiles.file;
                caseFileUName = getFiles.upload_name;
                caseFileId = getFiles.id;
                downloadurl = getFiles.downloadurl;
                oneDriveMetaAvailable = getFiles.hasOwnProperty('OneDriveMeta') ? true : false;
                var d_name = getFiles.display_name;
                if(!d_name){
                d_name = caseFileName;
                }
                if(caseFileUName == null){
                caseFileUName = caseFileName;
                }
                if(getFiles.is_exist) {
                fc++;
                file_icon_name = easycase.imageTypeIcon(getFiles.format_file);%>
                <?php if($this->Format->isAllowed('View File',$roleAccess)){ ?>
                <div class="fr atachment_det atachment_<%=caseFileId%>" <% if(!downloadurl && (easycase.imageTypeIcon(getFiles.format_file) == 'jpg' || easycase.imageTypeIcon(getFiles.format_file) == 'png')){ %> <% }else{%> style="display:none;" <%} %>>
                <div class="aat_file">
                    <?php if($this->Format->isAllowed('Download File',$roleAccess)){ ?>
                        <div class="file_show_dload">
                            <a href="<%= getFiles.fileurl %>" target="_blank" alt="<%= caseFileName %>" title="<?php echo __('Preview Image');?>" <% if(easycase.imageTypeIcon(getFiles.format_file) == 'jpg' || easycase.imageTypeIcon(getFiles.format_file) == 'png'){ %>rel="prettyPhoto[<%=csAtId%>]"<% } %>><i class="material-icons">&#xE8FF;</i></a>
                            <a href="<?php echo HTTP_ROOT; ?>easycases/download/<%= caseFileUName %>" alt="<%= caseFileName %>" title="<?php echo __('Download');?>"><i class="material-icons">&#xE2C4;</i></a>
                        </div>
                    <?php } ?>
                    <?php if($this->Format->isAllowed('Delete File',$roleAccess)){ ?>
                        <div class="attach-close">
                            <% if(user_can_change == 1){ %>
                            <a href="javascript:void(0);" class="hover-close close-icon" onclick="removefiledirect(<%= '\''+caseFileId+'\'' %>,<%='\''+caseDtId+'\'' %>,<%='\''+caseDtUniqId+'\'' %>,<%= '\''+CSrep_count+'\'' %>)"><i class="material-icons">&#xE872;</i></a>
                            <% } %>
                        </div>
                    <?php } ?>
                    <% if(file_icon_name == 'jpg' || file_icon_name == 'png' || file_icon_name == 'bmp'){ %>
                    <% if (typeof getFiles.fileurl_thumb != 'undefined' && getFiles.fileurl_thumb != ''){%>
                    <img data-original="<%= getFiles.fileurl_thumb %>" class="lazy asignto" src="<%= getFiles.fileurl_thumb %>" style="max-width:180px;max-height: auto;" title="<%= d_name %>" alt="Loading image.." />
                    <% }else{ %>
                    <img data-original="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=&file=<%= caseFileUName %>&sizex=180&sizey=120&quality=100" src="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=&file=<%= caseFileUName %>&sizex=180&sizey=120&quality=100" class="lazy asignto" width="180" height="120" title="<%= d_name %>" alt="Loading image.." />
                    <% } %>
                    <% } else { %>
                    <div style="display:none;" class="tsk_fl <%= easycase.imageTypeIcon(getFiles.format_file) %>_file"></div>
                    <% } %>
                    <div class="file_cnt ellipsis-view" title="<%= d_name %>" rel="tooltip"><%= d_name %></div>
                    <div class="file_cnt_info">
                        <span class="file-date fl"><%= frmtCrtdDt %></span>
                        <span class="file-size fr"><%= getFiles.file_size %></span>
                        <div class="cb"></div>
                    </div>
                </div>
            </div>
            <div class="fr atachment_det parent_other_holder atachment_<%=caseFileId%>" <% if(!downloadurl && (easycase.imageTypeIcon(getFiles.format_file) == 'jpg' || easycase.imageTypeIcon(getFiles.format_file) == 'png')){ %> style="display:none;" <% } %>>
            <div class="aat_file">
                <?php if($this->Format->isAllowed('Download File',$roleAccess)){ ?>
                    <div class="file_show_dload">
                        <% if(easycase.imageTypeIcon(getFiles.format_file) == 'pdf'){ %>
                        <a href="javascript:void(0);" onclick="viewPdfFile(<%= getFiles.id %>);" target="_blank" alt="<%= caseFileName %>" title="<?php echo __('Preview');?>"><i class="material-icons">&#xE8FF;</i></a>
                        <% } %>
                        <% if(downloadurl) { %>
                        <% if(oneDriveMetaAvailable) { %>
                        <a href="<%= getFiles.OneDriveMeta.embedLink %>" alt="<%= caseFileName %>" title="<?php echo __('Preview');?>" target="_blank"><i class="material-icons">&#xE8FF;</i></a>
                        <% } else {%>
                        <a href="<%= downloadurl %>" alt="<%= caseFileName %>" title="<?php echo __('Preview');?>" target="_blank"><i class="material-icons">&#xE8FF;</i></a>
                        <% } %>
                        <% } else {%>
                        <a href="<?php echo HTTP_ROOT; ?>easycases/download/<%= caseFileUName %>" alt="<%= caseFileName %>" title="<?php echo __('Download');?>"><i class="material-icons">&#xE2C4;</i></a>
                        <% } %>
                    </div>
                <?php } ?>
                <?php if($this->Format->isAllowed('Delete File',$roleAccess)){ ?>
                    <div class="attach-close">
                        <% if(user_can_change == 1){ %>
                        <a href="javascript:void(0);" class="hover-close close-icon" onclick="removefiledirect(<%= '\''+caseFileId+'\'' %>,<%='\''+caseDtId+'\'' %>,<%='\''+caseDtUniqId+'\'' %>,<%= '\''+CSrep_count+'\'' %>)"><i class="material-icons">&#xE872;</i></a>
                        <% } %>
                    </div>
                <?php } ?>
                <% if(downloadurl) { %>
                <img src="<?php echo HTTP_IMAGES; ?>images/task_dtl_imgs/<%= easycase.imageTypeIcon(getFiles.format_file) %>_64.png" alt="Loading image.." />
                <% }else{ %>
                <% if(getFiles.is_ImgFileExt){ %>
                <img data-original="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=&file=<%= caseFileUName %>&sizex=180&sizey=120&quality=100" src="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=&file=<%= caseFileUName %>&sizex=180&sizey=120&quality=100" class="lazy asignto" width="180" height="120" title="<%= d_name %>" alt="Loading image.." />
                <%  } else{ %>
                <img src="<?php echo HTTP_IMAGES; ?>images/task_dtl_imgs/<%= easycase.imageTypeIcon(getFiles.format_file) %>_64.png" alt="Loading image.." />
                <% } %>
                <% } %>
                <div class="file_cnt ellipsis-view" title="<%= d_name %>" rel="tooltip" ><%= d_name %></div>
                <div class="file_cnt_info">
                    <span class="file-date fl"><%= frmtCrtdDt %></span>
                    <span class="file-size fr"><%= getFiles.file_size %></span>
                    <div class="cb"></div>
                </div>
            </div>
        </div>
        <?php } ?>
        <% if(fc%3==0) { %><div class="cb"></div><% } %>
        <% } %>
        <% } %>
        <div class="cb"></div>
        <% } %>
        <% } %>
        <% if(getdata.dms_docs && getdata.dms_docs.length){ %>
        <div class="dms-comment-chips" style="margin-top:6px;">
        <% for(var di = 0; di < getdata.dms_docs.length; di++){ var dmsDoc = getdata.dms_docs[di];
           var dmsTitle = dmsDoc.title || dmsDoc.file_name || ('Document #' + dmsDoc.id);
           var dmsUrl = '<?php echo HTTP_ROOT; ?>documents/repository/' + (dmsDoc.project_id || '') + '/' + (dmsDoc.folder_id || '') + '?highlight=' + dmsDoc.id;
        %>
        <div class="dms-comment-chip" style="display:inline-flex;align-items:center;gap:6px;margin:4px 6px 0 0;padding:4px 10px;border:1px solid #e5e7eb;border-radius:14px;background:#f9fafb;font-size:12px;">
            <span>📄</span>
            <a href="<%= dmsUrl %>" target="_blank" style="color:#2d6dc4;text-decoration:none;"><%= dmsTitle %></a>
            <span style="padding:1px 6px;border-radius:8px;background:#dcfce7;color:#15803D;font-size:10px;">DMS</span>
        </div>
        <% } %>
        </div>
        <% } %>
    </div>
</div>
</div>
<div class="cb"></div>
</div>
<% } %>
<div class="cb"></div>
<% if(sortOrder == 'ASC') { startSlno--; } else { startSlno++; } %>
<% } %>
<input type="hidden" value="<%= total_records %>" id="totdata<%= csAtId %>"/>
