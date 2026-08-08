<style type="text/css">
    .profile_email_upd_inf {
        position: absolute;
        right: -29px;
        top: 8px;
        color: #959090;
        cursor: pointer;
        color: #ff0000;
    }

    .profile_email_upd_inf:hover {
        color: #ff0000;
    }

    .tipsy-inner {
        max-width: 280px;
    }

    .multiselect_formgroup {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 10px;
        /* Adjust the gap between the parts as needed */
    }

    .fifty {
        flex: 0 0 60%;
    }

    .select_field_wrapper.thirty {
        flex: 0 0 25%;

    }

    .select_field_wrapper.twenty {
        flex: 0 0 15%;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .btn-position {
        height: 2vw;
        width: 2vw;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #2e2e2e;
        color: white;
        border-radius: 50%;
        text-align: center;
        box-sizing: border-box;
    }

    /* .mbtn {
        margin-bottom: 25px;
    } */
    .myprofile-sett-page .form-group {
    padding-bottom: 0px;
}
.myprofile-sett-page .mtop10 {
    margin-top: 14px;
    color: #333;
    font-size: 14px;
}
.myprofile-sett-page .select_field_wrapper.up_select_control .form-group label.control-label {
    left: 26px !important;
    top: -5px !important;
    width: auto;
    margin: 0;
}
.myprofile-sett-page .field_wrapper .field_placeholder{left: 5px;top: 8px;}
.no-arrows {
  -moz-appearance: textfield; /* For Firefox */
  -webkit-appearance: none; /* For Safari and Chrome */
  appearance: none; /* Standard */
}

.myprofile-sett-page .no-arrows::-webkit-inner-spin-button, 
.myprofile-sett-page .no-arrows::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0; /* Optional to reset any margins */
}
.myprofile-sett-page #uploadImgLnk a{color: #fff;text-decoration: none;}
.myprofile-sett-page .email-field-wrapper input#email{
    padding-right: 42px;
}
.myprofile-sett-page .email-field-wrapper .verify-yes-icon{
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.myprofile-sett-page .email-field-wrapper .verify-yes-icon .material-icons{
    font-size: 20px;
    color: #c3c7d1;
    transition: color .2s ease;
}
.myprofile-sett-page .email-field-wrapper .verify-yes-icon.is-valid .material-icons{
    color: var(--primary);
}


</style>
<div class="setting_wrapper task_listing myprofile-sett-page">
    <?php echo $this->form->create(null, array('url' => '/users/profile', 'id' => 'UserProfileForm', 'onsubmit' => 'return submitProfile()', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal', 'autocomplete' => 'off')); ?>
    <div class="row m_0">
        <div class="col-md-5 col-sm-5 col-xs-12 profile_wrapper">
            <!-- <input type="hidden" name="data[User][csrftoken]" class="csrftoken" readonly="true" value="" /> -->
            <div class="mtop20">
                <div class="field_wrapper nofloat_wrapper">
                    <input type="text" name="data[User][name]" id="profile_name" class="" value="<?php echo $userdata['name']; ?>" onkeypress="return /^[a-zA-Z\s]*$/.test(event.key)" />
                    <div class="field_placeholder mark_mandatory" for="profile_name"><span><?php echo __('First Name'); ?></span></div>
                </div>
            </div>
            <div class="mtop30">
                <div class="field_wrapper nofloat_wrapper">
                    <input type="text" name="data[User][last_name]" id="profile_last_name" class="" value="<?php echo $userdata['last_name']; ?>" onkeypress="return /^[a-zA-Z\s]*$/.test(event.key)" />
                    <div class="field_placeholder mark_mandatory" for="profile_last_name"><span><?php echo __('Last Name'); ?></span></div>
                </div>
            </div>
            <div class="mtop30">
                <div class="field_wrapper nofloat_wrapper">
                    <input type="text" name="data[User][short_name]" id="short_name" class="" value="<?php echo $userdata['short_name']; ?>" onkeypress="return /^[a-zA-Z\s]*$/.test(event.key)" />
                    <div class="field_placeholder mark_mandatory" for="short_name"><span><?php echo __('Short Name'); ?></span></div>
                </div>
            </div>
            <div class="mtop30">
                <div class="field_wrapper nofloat_wrapper email-field-wrapper">
                    <input type="text" name="data[User][email]" id="email" class="" value="<?php echo $userdata['email']; ?>" />
                    <div class="field_placeholder mark_mandatory" for="email"><span><?php echo __('Email'); ?></span></div>

                    <?php if (!empty($userdata['update_email']) && !empty($userdata['update_random'])) { ?>
                        <span id="toltip_prof">
                            <i class="material-icons profile_email_upd_inf" title="<?php echo _('Please login to') . ' ' . ' \'' . $userdata['update_email'] . '\' ' . __('and change your email address'); ?>." rel="tooltip">info</i>
                        </span>
                    <?php } ?>

                    <div class="verify-yes-icon" id="profile-email-verify-icon">
                        <span class="material-icons" id="profile-email-verify-icon-symbol" title="<?php echo __('Enter a valid email address'); ?>" rel="tooltip">verified</span>
                    </div>
                </div>
            </div>
            <div class="mtop30">
                <div class="field_wrapper nofloat_wrapper">
                    <input type="text" name="data[User][phone]" id="phone_num" class="" value="<?php echo ($userdata['phone']) ? $userdata['phone'] : ''; ?>" maxlength="12" onkeypress="return restrictAlpha(event)" />
                    <div class="field_placeholder" for="phone_num"><span><?php echo __('Phone Number'); ?></span></div>
                </div>
            </div>
        </div>
        <div class="col-md-1 col-sm-1 col-xs-12"></div>
        <div class="col-md-5 col-sm-5 col-xs-12 profile_wrapper">
            <div class="mtop20">
                <div class="select_field_wrapper up_select_control">
                    <select name="data[User][timezone_id]" id="timezone_id" class="select form-control floating-label" placeholder="<?php echo __('Time Zone'); ?>" data-dynamic-opts=true>
                        <?php foreach ($timezones as $get_timezone) { ?>
                            <option <?php if ($get_timezone['id'] == $userdata['timezone_id']) { ?> selected <?php } ?> value="<?php echo $get_timezone['id']; ?>"><?php echo $get_timezone['gmt']; ?> <?php echo $get_timezone['zone']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="d-flex align-item-center font13 font-regular mtop10">
                    <?php echo __('Do you want to set daylight saving time(DST)'); ?> ?
                    <div class="togglebutton ml-15">
                        <label>
                            <input type="checkbox" name="data[User][is_dst]" id="user_is_dst" <?php echo (isset($userdata['is_dst']) && $userdata['is_dst']) ? 'checked="checked"' : ''; ?> style="cursor:pointer;" onclick="showDstMessage();" value="<?php echo (isset($userdata['is_dst']) && $userdata['is_dst']) ? 1 : 0; ?>">
                        </label>
                    </div>
                </div>
            </div>
            <div class="mtop20">
                <div class="select_field_wrapper up_select_control">
                    <?= $this->Form->control('data.User.language', [
                        'id'=>'language_id',
                        'type' => 'select',
                        'class' => 'select form-control floating-label',
                        'label' => false,
                        'placeholder' => __('Language'),
                        'data-dynamic-opts' => true,
                        'options' => $languages,
                        'value' => $userdata['language']
                    ]); ?>
                    
                </div>
            </div>
            <div class="mtop30">
                <div class="select_field_wrapper up_select_control">
                    <select name="data[User][time_format]" id="timeformat_id" class="select form-control floating-label" placeholder="<?php echo __('Time Format'); ?>" data-dynamic-opts=true>
                        <option <?php if ($userdata['time_format'] == '12') { ?> selected <?php } ?> value="12">12 Hour Format</option>
                        <option <?php if ($userdata['time_format'] == '24') { ?> selected <?php } ?> value="24">24 Hour Format</option>
                    </select>
                </div>
            </div>
            <!--            <div class="mtop30">-->
            <!--                <div class="multiselect_formgroup">-->
            <!--                    <div class="select_field_wrapper up_select_control">-->
            <!--                        <select class="select form-control floating-label skills" placeholder="--><?php //echo __('Skills');
                                                                                                                    ?><!--" multiple="multiple" data-dynamic-opts=true>-->
            <!--                        </select>-->
            <!--                    </div>-->
            <!--                </div>-->
            <!--            </div>-->
            <label class="mtop10 mbtn">Enter Skills</label>
            <div class="skillContainer">
				<?php if(empty($skillCount)){?>
					<div class="multiselect_formgroup rowTemplate" id="new_skill_div_0">
                    <div class="field_wrapper nofloat_wrapper fifty">
                        <input type="text" name="data[User][skillset][0][skill]" id="user_skill_0" class="" value="" maxlength="15" ?>
                        <div class="field_placeholder">
                            <span><?php echo __('Skill'); ?></span>
                        </div>
                    </div>

                    <div class="select_field_wrapper up_select_control thirty">
                        <div class="field_wrapper up_select_control thirty">
                            <div class="">
                                <div class="field_wrapper nofloat_wrapper">
                                    <input type="number" name="data[User][skillset][0][experience]" id="user_exp_0" class="no-arrows" value="" min="1" max="35" />
                                    <div class="field_placeholder" for="user_exp_0">
                                        <span><?php echo __('Experience'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field_wrapper up_select_control twenty btn-position">
                        <i class="material-icons cursor-pointer addRow" id="addRow_0">add</i>
                    </div>
                </div>
				<?php }else{ ?>
					<?php foreach ($allUserSkills as $key => $skills) { ?>
						<div class="multiselect_formgroup rowTemplate" id="new_skill_div_<?php echo $key;?>">
                    <div class="field_wrapper nofloat_wrapper fifty">
                        <input type="text" name="data[User][skillset][<?php echo $key;?>][skill]" id="user_skill_<?php echo $key;?>" class="" value="<?php echo $skills['Skills']['name'];?>" maxlength="15" ?>
                        <div class="field_placeholder">
                            <span><?php echo __('Skill'); ?></span>
                        </div>
                    </div>

                    <div class="select_field_wrapper up_select_control thirty">
                        <div class="field_wrapper up_select_control thirty">
                            <div class="">
                                <div class="field_wrapper nofloat_wrapper">
                                    <input type="number" name="data[User][skillset][<?php echo $key;?>][experience]" id="user_exp_<?php echo $key;?>" class="" value="<?php echo empty($skills['years_of_experience']) ? "" : $skills['years_of_experience'];?>" min="1" max="35" />
                                    <div class="field_placeholder" for="user_exp_<?php echo $key;?>">
                                        <span><?php echo __('Experience'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
					<?php if(empty($key)){?>
						<div class="field_wrapper up_select_control twenty btn-position">
							<i class="material-icons cursor-pointer addRow" id="addRow_0">add</i>
						</div>
					<?php }else{ ?>
						<div class="field_wrapper up_select_control twenty btn-position">
							<i class="material-icons cursor-pointer deleteRow" onclick="deleteSkill(this)" id="deleteRow_<?php echo $skills['skill_id'];?>" data-id="<?php echo $skills['skill_id'];?>">remove</i>
						</div>
					<?php } ?>
                </div>
					<?php } ?>
				<?php } ?>

            </div>




            <div class="form-group custom-drop-lebel pro-img">
                <div class="upload_ospfl_image pl-15">
                    <label class="control-label" for="short_name"><?php echo __('Profile Image'); ?></label>
                    <div id="profDiv"></div>
                    <?php
                    $is_storage = !empty(\Cake\Core\Configure::read('Storage'));
                    $user_img_exists = 0;
                    if ($is_storage) {
                        if ($userdata['photo']) {
                            $user_img_exists = 1;
                        }
                    } elseif ($this->Format->imageExists(DIR_USER_PHOTOS, $userdata['photo'])) {
                        $user_img_exists = 1;
                    }
                    if ($user_img_exists) {
                    ?>
                        <div id="existProfImg" onmouseover="showEditDeleteImg()" onmouseout="hideEditDeleteImg()">
                            <?php
                            $file_path = DIR_USER_PHOTOS_S3_FOLDER . $userdata['photo'];
                            $fileurl = $is_storage ? $this->Storage->generateTemporaryURL($file_path) : $file_path;
                            ?>
                            <div>
                                <a data-href="<?php echo $fileurl; ?>" href="javascript:void(0);" onClick="openProfilePopup()" class="d-flex-column align-item-center justify-center placeholder_user">
                                    <img src="<?php echo HTTP_ROOT; ?>users/image_thumb/?type=photos&file=<?php echo $userdata['photo']; ?>&sizex=100&sizey=100&quality=100" border="0" id="profphoto" />
                                </a>
                                <?php echo $this->Form->hidden('photo', array('class' => 'text_field', 'id' => 'imgName1', 'name' => 'data[User][photo]')); ?>
                                <?php echo $this->Form->hidden('exst_photo', array('value' => $userdata['photo'], 'class' => 'text_field', 'name' => 'data[User][exst_photo]')); ?>
                            </div>
                            <div style="display:none" id="editDeleteImg">
                                <span id="uploadImgLnk">
                                    <a title="Edit Profile Image" class="custom-t-type" href="javascript:void(0);" onClick="openProfilePopup()">
                                        <i class="material-icons size13 edit-link" title="Edit">&#xE254;</i>
                                    </a>
                                </span>
                                <a title="Delete Profile Image" class="custom-t-type" href="<?php echo HTTP_ROOT; ?>users/profile/<?php echo urlencode($userdata['photo']); ?>">
                                    <span onclick="return confirm('<?php echo __('Are you sure you want to delete'); ?>?')"> <i class="material-icons size13 delete-link" title="Delete">&#xE872;</i> </span>
                                </a>
                            </div>
                            <div class="cb"></div>
                        </div>
                    <?php } else { ?>
                        <div class="d-flex align-item-center justify-left">
                            <div id="defaultUserImg" class="placeholder_user">
                                <img width="100" height="100" src="<?php echo HTTP_ROOT; ?>files/photos/user.png" onClick="openProfilePopup()">
                            </div>
                        </div>
                        <input type="hidden" id="imgName1" name="data[User][photo]" />
                    <?php } ?>
                </div>
            </div>
            <div class="btn_row mbtm20 fr">
                <div id="subprof1">
                    <div class="fl"><a class="btn btn-default btn_hover_link cmn_size fl" onclick="cancelProfile('<?php echo $_SERVER['HTTP_REFERER'] ?? ''; ?>');"><?php echo __('Cancel'); ?></a></div>
                    <div class="fl btn-margin"><button type="button" value="Update" name="submit_Profile" id="submit_Profile" class="btn btn_cmn_efect cmn_bg btn-info cmn_size fl" onclick="getRecap();labelVal();"><?php echo __('Update'); ?></button></div>
                    <div class="cb"></div>
                </div>
                <span id="subprof2" style="display:none">
                    <img src="<?php echo HTTP_IMAGES; ?>images/case_loader2.gif" alt="Loading..." />
                </span>
            </div>
            <div class="cb"></div>
        </div>
    </div>
    <?php echo $this->Form->end(); ?>
</div>
<script type="text/javascript">

    let skillCount = parseInt('<?php echo $skillCount;?>');
    let rowCount = (skillCount == 0) ? 0 : skillCount;

    function getRecap() {
        checkCsrfToken('UserProfileForm');
    }
    $(function() {
        /*select2 js customize event*/
        $('.up_select_control').click(function() {
            if ($(this).find('span').hasClass("select2-container--open")) {
                $(this).addClass('open_label');
            } else {
                $('.up_select_control').removeClass('open_label');
            }
        });
        /*end*/
        $(".fixed-n-bar").hide();
        $('body').removeClass("open_hellobar");
        $('#language_id').select2();
        $('#timezone_id').select2();
        $('#timeformat_id').select2();
        bindProfileEmailValidation();
    });

    function isProfileEmailValid(email) {
        email = $.trim(email);
        if (email === '' || email.search(/\.\./) !== -1) {
            return false;
        }
        return emlRegExpRFC.test(email);
    }

    function updateProfileEmailVerifyIcon() {
        var email = $('#email').val();
        var $iconWrapper = $('#profile-email-verify-icon');
        var $icon = $('#profile-email-verify-icon-symbol');
        var isValid = isProfileEmailValid(email);

        $iconWrapper.toggleClass('is-valid', isValid);
        $icon.attr('title', isValid ? "<?php echo __('Valid email address'); ?>" : "<?php echo __('Enter a valid email address'); ?>");
    }

    function bindProfileEmailValidation() {
        updateProfileEmailVerifyIcon();
        $('#email').on('input blur', function() {
            updateProfileEmailVerifyIcon();
        });
    }

    function restrictAlpha(e) {
        var unicode = e.charCode ? e.charCode : e.keyCode;
        if (unicode != 8) {
            if (unicode < 9 || unicode > 9 && unicode <= 46 || unicode > 57 || unicode == 47 || unicode == 186 || unicode == 58) {
                if (unicode == 40 || unicode == 41 || unicode == 45) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    function showDstMessage() {
        if (document.getElementById('user_is_dst').checked) {
            $('#user_is_dst').val(1);
        } else {
            $('#user_is_dst').val(0);
        }
        showTopErrSucc('success', "<?php echo __('Please update your changes'); ?>.");
    }
    $(function() {
        var userId = '<?php echo SES_ID; ?>';
        var mode = 'profile';


        $('.addRow').click(function() {
            addRow();
        });
        $(document).on('click', '.deleteRow', function() {
            $(this).closest('.multiselect_formgroup').remove();
            updateRowIds();
        });
        /* To fetch all skills for user profile page --Start-- */
        getSkillUserProfile(userId, mode);
        /* --End-- */

        $(".skills")
            .select2({

                tags: true,
                createTag: function(params) {

                    var term = $.trim(params.term);
                    var regex = new RegExp("^[a-zA-Z0-9\-\.\+\\s\/]+$");
                    if (term === "") {
                        return null;
                    }
                    if (!regex.test(term)) {
                        var msg = "'Skill' must not contain Special characters!";
                        showTopErrSucc("error", msg);
                        return null;
                    }
                    if (term.length > 30) {
                        var msg = "Skill must not exceed 30 characters!";
                        showTopErrSucc("error", msg);
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                },
            })
            .off("select2:select")
            .on("select2:select", function(evt) {
                if (evt.params.data.newTag == true) {
                    var name = evt.params.data.id;

                    $("#caseLoader").show();

                    $.post(
                        HTTP_ROOT + "users/validateskill", {
                            name: evt.params.data.id
                        },
                        function(res) {
                            $("#caseLoader").hide();
                            if (res.status == "duplicate") {
                                showTopErrSucc("error", _("Skill already added. Please add another Skill."));
                                $('.skills option[value="' + name + '"]').remove();
                                $(".skills").trigger("change");
                            } else if (res.status == "success") {
                                $(".skills")
                                    .find('option[value="' + name + '"]')
                                    .remove();
                                $(".skills").append("<option value='" + res.id + "' selected>" + name + "</option>");
                                $('.skills option[value="' + res.id + '"]').prop("selected", true);
                                $(".skills").trigger("change");
                            } else if (res.status == "error") {
                                showTopErrSucc("error", _("Skill can not be added"));
                                $('.skills option[value="' + name + '"]').remove();
                                $(".skills").trigger("change");
                            }
                        },
                        "json"
                    );
                }
            });
    });

    function labelVal() {
        var value = $(".skills").val();
        var userId = '<?php echo SES_ID; ?>';
        $.post(
            HTTP_ROOT + "users/saveUserSkill", {
                skillID: value,
                userId: userId
            },
            function(resdat) {},
            "json"
        );
    }
    // function addRow() {
    //     var newRow = $('#rowTemplate').clone().removeAttr('id');
    //     newRow.attr('id', 'row_' + rowCount);
    //     newRow.find('.skills').attr('id', 'skills' + rowCount);
    //     newRow.find('input[name="exp"]').attr('id', 'exp' + rowCount);
    //     newRow.find('.field_placeholder').attr('for', 'exp' + rowCount);
    //     newRow.find('.addRow').removeClass('addRow').addClass('deleteRow').text('');
    //     rowCount++;
    //     $('#rowsContainer').append(newRow);
    // }

    function addRow() {
        $item_block = $('.skillContainer');
        $newClone = $('.rowTemplate').first().clone();
        $newCount = ++rowCount;
        $newClone.find('input,i').each(function(index, el) {
            $(this).val('');
            $oldname = $(this).attr('name');
            $oldid = $(this).attr('id');
            if (!empty($oldname)) {
                $newname = $oldname.replace(/\d+/, $newCount);
                $(this).attr('name', $newname);
            }
            if (!empty($oldid)) {
                $newid = $oldid.replace(/\d+/, $newCount);
                $(this).attr('id', $newid);
            }
        });
        $newClone.find('i.addRow').replaceWith('<i class="material-icons cursor-pointer deleteRow" onclick="deleteSkill(this)" id="deleteRow_' + $newCount + '">remove</i>');
        $newClone.attr('data-id', $newCount);
        $newClone.attr('id', "new_skill_div_" + $newCount);
        $newClone.insertAfter($(event.target).closest('.rowTemplate'));
    }

    function deleteSkill(obj) {
        if ($(obj).attr('data-id') !== undefined) {
            $.post(
                HTTP_ROOT + "users/deleteSkill", {
                    skill_id: $(obj).attr('data-id')
                },
                function(res) {
                    if(res.status){
                        $(obj).closest('.rowTemplate').remove();
                    }
                },
                "json"
            );
        } else {
            $(obj).closest('.rowTemplate').remove();
        }
    }

    function updateRowIds() {
        $('#rowsContainer .multiselect_formgroup').each(function(index) {
            $(this).attr('id', 'row_' + (index + 1));
            $(this).find('.skills').attr('id', 'skills' + (index + 1));
            $(this).find('input[name="exp"]').attr('id', 'exp' + (index + 1));
            $(this).find('.field_placeholder').attr('for', 'exp' + (index + 1));
            $(this).find('.deleteRow').text('');
        });
        rowCount = $('#rowsContainer .multiselect_formgroup').length;
    }
</script>
