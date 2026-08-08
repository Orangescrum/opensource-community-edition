<style type="text/css">
    .grecaptcha-badge {
        position: relative !important;
        bottom: 398px !important;
        right: 0 !important;
        left: 0px !important;
        margin: 0px auto !important;
        display: none !important;
    }
    .edit-user-pops.cmn_popup {
        position: fixed;
        inset: 0;
        z-index: 100001;
          overflow: auto;
        padding: 24px 12px;
        box-sizing: border-box;
          background: rgba(0, 0, 0, 0.28);
    }
    .edit-user-pops .modal-dialog.edit-user-popup {
        position: relative;
        margin: 0 auto;
        max-width: 880px;
        width: 100%;
        max-height: calc(100vh - 48px);
        pointer-events: auto;
    }
    .edit-user-popup .modal-content {
        position: relative;
        z-index: 2;
        max-height: calc(100vh - 48px);
        display: flex;
        flex-direction: column;
        border-radius: 8px;
        box-shadow: 0 16px 48px rgba(15, 23, 42, 0.24);
    }
    @media (max-width: 920px) {
        .edit-user-pops .modal-dialog.edit-user-popup { max-width: 100%; }
    }
    .edit-user-popup .popup_overlay_2 {
        display: none !important;
    }

    .edit-user-popup .form-group .dropdownjs { display: none !important; }
    .edit-user-popup .modal-header,
    .edit-user-popup .modal-footer,
    .edit-user-popup .btn_row {
        flex-shrink: 0;
    }
    .edit-user-popup .modal-body.popup-container {
        overflow-y: auto;
        overflow-x: hidden;
        flex: 1 1 auto;
        padding: 20px 28px 12px;
    }
    .edit-user-popup .modal-body.popup-container > .row {
        margin-left: 0;
        margin-right: 0;
    }
    .edit-user-popup .form-group.mtop20 {
        margin-top: 14px;
    }
    .edit-user-popup .btn_row {
        border-top: 1px solid #eef0f3;
        padding: 12px 28px;
        background: #fff;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        float: none !important;
    }
    .edit-user-popup .btn_row .btn-margin {
        margin: 0 !important;
    }
    .edit-user-popup .eu-section-title {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #94a3b8;
        margin: 18px 0 4px;
        padding-bottom: 4px;
        border-bottom: 1px solid #f1f5f9;
    }
    .edit-user-popup .eu-section-title:first-of-type {
        margin-top: 4px;
    }
</style>
<div class="modal-dialog edit-user-popup">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-icon"
                onclick="$('.edit_usr_pop').hide(); $('.popup_overlay_2').hide(); $('body').removeClass('modal-open'); if (typeof closePopup === 'function') closePopup(); return false;">
                <i class="material-icons">&#xE14C;</i>
            </button>
            <h4><?php echo __('Edit User'); ?>
                <span><img src="<?php echo HTTP_IMAGES; ?>html5/icons/icon_breadcrumbs.png"></span>
                <span id="header_usr_name_edit" class="fnt-nrml ellipsis-view max-width-75"></span>
            </h4>
        </div>
        <div class="modal-body popup-container">
            <div class="row">
                <div class="col-lg-12">
                    <div id="edit_user_recaptcha" style="display:none;"></div>
                    <?php echo $this->form->create(null, array('url' => '/users/profile', 'onsubmit' => '', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal', 'id' => 'profile-edit-popup', 'autocomplete' => 'off')); ?>
                    <input type="hidden" name="data[User][id]" id="edit-user-id-popup" value="" />

                    <div class="eu-section-title"><?php echo __('Identity'); ?></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group custom-drop-lebel label-floating mtop20">
                                <label class="control-label" for="profile_name"><?php echo __('First Name'); ?></label>
                                <input type="text" name="data[User][name]" id="profile_name-popup" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group custom-drop-lebel label-floating mtop20">
                                <label class="control-label" for="profile_last_name"><?php echo __('Last Name'); ?></label>
                                <input type="text" name="data[User][last_name]" id="profile_last_name-popup" class="form-control" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group custom-drop-lebel label-floating mtop20">
                        <label class="control-label" for="short_name"><?php echo __('Short Name'); ?></label>
                        <input type="text" name="data[User][short_name]" id="short_name-popup" class="form-control" value="" />
                    </div>
                    <div class="form-group custom-drop-lebel label-floating relative mtop20">
                        <label class="control-label" for="email"><?php echo __('Email'); ?></label>
                        <input type="text" name="data[User][email]" id="email-popup" class="form-control" value="" />
                        <div id="emailVarify-popup"></div>
                    </div>

                    <div class="eu-section-title"><?php echo __('Profile'); ?></div>
                    <div class="form-group custom-drop-lebel label-floating relative mtop20">
                        <div class="multiselect_formgroup">
                            <div class="select_field_wrapper up_select_control">
                                <select class="select form-control floating-label" name="data[User][skill][]" id="user-skills" placeholder="<?php echo __('Skills'); ?>" multiple="multiple" data-dynamic-opts=true>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group custom-drop-lebel mtop20">
                        <select name="data[User][timezone_id]" id="timezone_id-popup" class="form-control floating-label" placeholder="<?php echo __('Time Zone'); ?>" data-dynamic-opts=true>
                            <?php if (isset($timezones) && !empty($timezones)) : ?>
                                <?php foreach ($timezones as $get_timezone) { ?>
                                    <option <?php if ($get_timezone['id'] == $userdata['timezone_id']) { ?> selected <?php } ?> value="<?php echo $get_timezone['id']; ?>"><?php echo $get_timezone['gmt']; ?> <?php echo $get_timezone['zone']; ?></option>
                                <?php } ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="eu-section-title"><?php echo __('Membership'); ?></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group custom-drop-lebel mtop20">
                                <select id="edit-popup-role" class="select form-control floating-label" placeholder="<?php echo __('Role'); ?>" data-dynamic-opts=true>
                                    <option value=""><?php echo __('Select Role'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group custom-drop-lebel mtop20">
                        <label class="control-label"><?php echo __('Projects'); ?></label>
                        <div id="edit-popup-projects-list" class="mtop10" style="max-height:150px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;padding:8px;">
                            <span class="loader_dv"><?php echo __('Loading...'); ?></span>
                        </div>
                    </div>
                    <?php if (defined('ATTENDANCE_LEAVE_ENABLED') && ATTENDANCE_LEAVE_ENABLED === true && \Cake\Core\Plugin::isLoaded('AttendanceLeave')) : ?>
                    <div class="form-group custom-drop-lebel mtop20" id="edit-popup-reporting-manager-group">
                        <select id="edit-popup-reporting-manager" class="select form-control floating-label" placeholder="<?php echo __('Reporting Manager'); ?>" data-dynamic-opts=true>
                            <option value=""><?php echo __('Select Reporting Manager'); ?></option>
                        </select>
                        <input type="hidden" id="edit-popup-employee-profile-id" value="" />
                    </div>
                    <?php endif; ?>

                    <div class="eu-section-title"><?php echo __('Avatar'); ?></div>
                    <div class="form-group custom-drop-lebel pro-img " style="margin: 0;">
                        <label class="control-label" for="short_name"><?php echo __('Profile Image'); ?></label>
                        <div id="profDiv-popup"></div>
                        <div id="IMG-DIV"></div>
                    </div>
                    <div class="cb"></div>
                </div>
            </div>
        </div>
        <div class="btn_row">
            <div id="subprof1-popup">
                <a class="btn btn-default btn_hover_link cmn_size" onclick="closePopup();"><?php echo __('Cancel'); ?></a>
                <button type="button" value="Update" name="submit_Profile" id="submit_Profile-popup" class="btn btn_cmn_efect cmn_bg btn-info cmn_size btn-margin"><?php echo __('Update'); ?></button>
            </div>
            <span id="subprof2-popup" style="display:none">
                <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." />
            </span>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
    <div class="popup_overlay_2"></div>
</div>
<script>
    $(function() {
        if ($.fn.select2) {
            var $tz = $('#timezone_id-popup');
            $tz.siblings('.dropdownjs').remove();
            $tz.next('.dropdownjs').remove();
            try {
                if ($tz.data('select2')) {
                    $tz.select2('destroy');
                }
            } catch (e) { }

            $tz.select2({
                placeholder: _('Search timezone…'),
                minimumResultsForSearch: 0,
                allowClear: false,
                width: '100%',
                dropdownParent: $('.edit-user-popup .modal-content')
            });

            var pruneTimezoneSiblings = function () {
                var $tzGroup = $tz.closest('.form-group');
                if (!$tzGroup.length) return;
                $tzGroup.children().each(function () {
                    var $el = $(this);
                    if ($el.is($tz)) return;
                    if ($el.hasClass('select2-container')) return;
                    if ($el.is('label, comment, script, style')) return;
                    $el.remove();
                });
            };
            pruneTimezoneSiblings();
            setTimeout(pruneTimezoneSiblings, 250);
            setTimeout(pruneTimezoneSiblings, 1000);
        }

        $('.select2-selection__choice__remove').on('click', function() {
            $(this).parent('.select2-selection__choice').remove();
        });
        $('#submit_Profile-popup').off().on('click', function() {
            $("input[name='g-recaptcha-response']").remove();
            $("#profile-edit-popup").submit();
        });
        $("#profile-edit-popup").submit(function(e) {
            e.preventDefault();
            if (submitProfile('popup') != false) {
                $("#subprof1-popup").hide();
                $("#subprof2-popup").show();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    dataType: "json",
                    data: $(this).serialize(),
                    success: function(result) {
                        $("#subprof2-popup").hide();
                        $("#subprof1-popup").show();
                        saveUserSkills();
                        if (typeof result.close != 'undefined') {
                            if (result.error) {
                                showTopErrSucc('success', result.error);
                            }
                            var profile_name = $('#profile_name-popup').val().toString();
                            profile_name = profile_name.replace(/<[^>]*>/g, '');
                            $('#pn_' + $('#edit-user-id-popup').val()).text(profile_name).attr('data-usr-name', profile_name).attr('title', profile_name);
                            $('#edit-exist-usr' + $('#edit-user-id-popup').val()).attr('data-usr-name', profile_name);

                            $('#psn_' + $('#edit-user-id-popup').val()).text($('#short_name-popup').val());
                            if ($('#email-popup').val().length > 25) {
                                var formated_email = $('#email-popup').val().substr(0, 24) + '...';
                            } else {
                                var formated_email = $('#email-popup').val();
                            }
                            $('#pemail_' + $('#edit-user-id-popup').val()).html(formated_email).attr('title', $('#email-popup').val());
                            imgN = $("#exst_photo-popup").val();
                            if ($("#imgName1-popup").val() != '') {
                                imgN = $("#imgName1-popup").val();
                            }
                            img = "<img class='lazy' src='" + HTTP_ROOT + "files/photos/" + imgN + "' width='94' height='94' />";
                            if (($("#imgName1-popup").val() != '' || $("#exst_photo-popup").val() != '') && typeof imgN != 'undefined') {
                                $('#pimg_' + $('#edit-user-id-popup').val()).html(img);
                            } else {
                                $('#pimg_' + $('#edit-user-id-popup').val()).html('<span class="name_txt">' + $('#profile_name-popup').val().charAt(0) + '</span>');
                            }

                            var userId = $('#edit-user-id-popup').val();
                            var pending = [];

                            var roleId = $('#edit-popup-role').val();
                            if (roleId) {
                                pending.push($.ajax({
                                    url: HTTP_ROOT + 'users/update-user-role',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: { user_id: userId, role_id: roleId }
                                }).then(function (resp) {
                                    if (resp && resp.status === 'error') {
                                        showTopErrSucc('error', resp.message || _('Role could not be updated.'));
                                        return $.Deferred().reject(resp);
                                    }
                                    return resp;
                                }));
                            }

                            var checkedProjIds = [];
                            $('#edit-popup-projects-list .edit-popup-project-cb:checked').each(function() {
                                checkedProjIds.push(parseInt($(this).val()));
                            });
                            var originalProjIds = window._editPopupOriginalProjectIds || [];
                            var toAdd = checkedProjIds.filter(function(id) {
                                return originalProjIds.indexOf(id) === -1;
                            });
                            var toRemove = originalProjIds.filter(function(id) {
                                return checkedProjIds.indexOf(id) === -1;
                            });
                            if (toAdd.length > 0) {
                                var postData = 'userid=' + encodeURIComponent(userId) + '&is_invite_user=0';
                                toAdd.forEach(function(pid) { postData += '&projectid%5B%5D=' + encodeURIComponent(pid); });
                                pending.push($.ajax({ url: HTTP_ROOT + 'users/assign_prj', type: 'POST', data: postData }));
                            }
                            toRemove.forEach(function(pid) {
                                pending.push($.ajax({
                                    url: HTTP_ROOT + 'users/ajaxAssignedprojectDelete',
                                    type: 'POST',
                                    data: { id: pid, userId: userId, isInvite: 0 }
                                }));
                            });

                            var rmEl = $('#edit-popup-reporting-manager');
                            var profileIdEl = $('#edit-popup-employee-profile-id');
                            if (rmEl.length && profileIdEl.val()) {
                                var rmUserId = rmEl.val() || null;
                                var epId = profileIdEl.val();
                                pending.push($.ajax({
                                    url: HTTP_ROOT + 'attendance-leave/api/employees/' + epId + '/update',
                                    type: 'POST',
                                    contentType: 'application/json',
                                    data: JSON.stringify({ reporting_to: rmUserId ? parseInt(rmUserId) : null })
                                }));
                            }
                            $.when.apply($, pending).always(function () {
                                closePopup();
                                if (typeof refreshUserList === 'function') {
                                    refreshUserList();
                                } else {
                                    window.location.reload();
                                }
                            });
                        } else {
                            showTopErrSucc('error', result.error);
                        }
                    },
                    complete: function (xhr,status) {
                        $("#subprof2-popup").hide();
                        $("#subprof1-popup").show();
                    }
                });
            }
        });
    });

    function removeImgPopup(img) {
        if (confirm('Are you sure you want to delete?')) {
            $.get(img, function(res) {
                showTopErrSucc('success', res.error);
                $("#imgName1-popup").val('');
                $("#exst_photo-popup").val('');
                $('#IMG-DIV').html('<div id="defaultUserImg-popup" class="fl"><img width="100" height="100" src="' + HTTP_ROOT + 'files/photos/user.png" onClick="openProfilePopup(\'popup\')" id="profphoto-popup"></div>\n\
                            <div id="uploadImgLnk-popup" class="fl mtop20 editDeleteImg-popup"><a href="javascript:void(0);" onClick="openProfilePopup(\'popup\')" ><?php echo __('Choose Profile Image'); ?></a></div><div class="cb"></div><input type="hidden" id="imgName1-popup" name="data[User][photo]" />');
                $('#pimg_' + $('#edit-user-id-popup').val()).html('<span class="name_txt">' + $('#profile_name-popup').val().charAt(0) + '</span>');

            });
        }
    }

    function removeImgPopupTmp() {
        $('#IMG-DIV').html('<div id="defaultUserImg-popup" class="fl"><img width="100" height="100" src="' + HTTP_ROOT + 'files/photos/user.png" onClick="openProfilePopup(\'popup\')" id="profphoto-popup"></div>\n\
                            <div id="uploadImgLnk-popup" class="fl mtop20 editDeleteImg-popup"><a href="javascript:void(0);" onClick="openProfilePopup(\'popup\')" ><?php echo __('Choose Profile Image'); ?></a></div><div class="cb"></div><input type="hidden" id="imgName1-popup" name="data[User][photo]" />');
    }
</script>
