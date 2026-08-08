<style>
    .ajx-selctd {
        color: #2e2e2e;
        font-weight: bold;
    }
</style>
<div id="id2_new" class="ajx-cs-srch" style="max-height:400px;overflow-x:auto;">
    <div id="id_ajx1" class="ajx-cntnt">
        <table cellpadding="0" cellspacing="0" class="col-lg-12 ajx-srch-tbl">
            <tr class="nohover">
                <td colspan="2">
                    <div><span class="src_txt search_font"><?php echo __('Search Results'); ?></span>
                        <div class="fr close close-icon" onclick="document.getElementById('id_ajx1').style.display = 'none';document.getElementById('id2_new').style.display = 'none';"><i class="material-icons">&#xE14C;</i></div>
                    </div>
                </td>
            </tr>
            <?php if ((!empty($results['cases'])) || (!empty($results['projects'])) || (!empty($results['defect'])) || (!empty($results['users'])) || (!empty($results['files'])) || (!empty($results['milestone']))) {
                if (!empty($results['cases'])) { ?>
                    <?php $c = 0;
                    $uniqId = NULL;
                    $shortName = NULL;
                    foreach ($results['cases'] as $getcase) {
                        if (isset($projectsArr) && isset($projectsArr[$getcase['project_id']])) {
                            $projectsArr = $projectsArr[$getcase['project_id']];
                        } else {
                            // $projectsArr = $this->Casequery->caseProject($getcase['project_id']);
                        }
                        if (!empty($projectsArr)) {
                            $uniqId = trim($projectsArr['Projects']['uniq_id'] ?? '');
                            $shortName = trim($projectsArr['Projects']['short_name'] ?? '');
                        } ?>
                        <tr class="alltrcls cmn_link_color anchor" data-id="<?php echo $getcase['uniq_id']; ?>" data-case-no="<?php echo $getcase['case_no']; ?>" onclick="searchTasks('<?php echo $getcase['case_no']; ?>', '<?php echo $getcase['uniq_id']; ?>')">
                            <td class="ttu max-width75">
                                <?php echo str_ireplace($srchstr, "<span class='ajx-selctd'>" . $srchstr . "</span>", $shortName); ?>
                                - <?php echo str_ireplace($srchstr, "<span class='ajx-selctd'>" . $srchstr . "</span>", $getcase['case_no']); ?>
                            </td>
                            <td class="wrap-txt ttc">
                                <?php $data = $this->Format->formatSearchText($getcase['title']);
                                echo str_ireplace($srchstr, "<span class='ajx-selctd'>" . $srchstr . "</span>", $data);
                                ?>
                            </td>
                        </tr>
                    <?php }
                }
                if (!empty($results['milestone'])) { ?>
                    <?php $c = 0;
                    $uniqId = NULL;
                    $shortName = NULL;
                    $projectsArr = array();
                    foreach ($results['milestone'] as $getcase) {
                        $projectsArr = $this->Casequery->caseProject($getcase['project_id']);
                        if (!empty($projectsArr)) {
                            $uniqId = $projectsArr['Projects']['uniq_id'];
                            $shortName = $projectsArr['Projects']['short_name'];
                        } ?>
                        <tr class="alltrcls cmn_link_color anchor" data-id="<?php echo $getcase['uniq_id']; ?>" data-case-no="<?php echo $getcase['id']; ?>" onclick="searchMilestones('<?php echo $getcase['id']; ?>', '<?php echo $getcase['uniq_id']; ?>')">
                            <td class="ttu max-width75">
                                <?php echo str_ireplace($srchstr, "<span class='ajx-selctd'>" . $srchstr . "</span>", $shortName); ?>
                                - <?php echo str_ireplace($srchstr, "<span class='ajx-selctd'>" . $srchstr . "</span>", $getcase['id']); ?>
                            </td>
                            <td class="wrap-txt ttc">
                                <?php $data = $this->Format->formatSearchText($getcase['title']);
                                echo str_ireplace($srchstr, "<span class='ajx-selctd'>" . $srchstr . "</span>", $data);
                                ?>
                            </td>
                        </tr>
                    <?php }
                }

                if (!empty($results['projects'])) { ?>
                    <?php foreach ($results['projects'] as $getproject) {
                        $role = '';
                        if ($getproject['isactive'] == 2) {
                            $role = 'inactive';
                        }
                    ?>
                        <tr class="alltrcls cmn_link_color anchor" data-id="<?php echo $getproject['uniq_id']; ?>" data-role="<?php echo $role; ?>" onclick="searchProject('<?php echo $role; ?>','<?php echo $getproject['uniq_id']; ?>')">
                            <td colspan="2" class="ttc">
                                <?php $data = ucfirst($getproject['name']) . " (" . strtoupper($getproject['short_name']) . ")";
                                echo str_ireplace($srchstr, "<span class='ajx-selctd'>" . $srchstr . "</span>", $data);
                                ?>
                            </td>
                        </tr>
                    <?php }
                }
                if (!empty($results['defect'])) { ?>
                    <?php foreach ($results['defect'] as $getdefect) {
                        $role = '';
                        if ($getdefect['Defect']['is_active'] == 0) {
                            $role = 'inactive';
                        }
                    ?>
                        <tr class="alltrcls cmn_link_color anchor" data-id="<?php echo $getdefect['Defect']['uniq_id']; ?>" data-role="<?php echo $role; ?>" onclick="searchDefectSrchBr('<?php echo $role; ?>','<?php echo $getdefect['Defect']['uniq_id']; ?>')">
                            <td colspan="2" class="ttc">
                                <?php $data = ucfirst($getdefect['Defect']['title']);
                                echo str_ireplace($srchstr, "<span class='ajx-selctd'>" . $srchstr . "</span>", $data);
                                ?>
                            </td>
                        </tr>
                    <?php }
                }
                if (!empty($results['users'])) { ?>
                    <?php foreach ($results['users'] as $getuser) {
                        if ($getuser['CompanyUsers']['is_active'] == 1 || $getuser['CompanyUsers']['is_active'] == 0) {
                            if ($getuser['CompanyUsers']['is_active'] == 0) {
                                $role = 'disable';
                            } else {
                                $role = 'all';
                            }
                            $data = ucfirst($getuser['name']) . " " . ucfirst($getuser['last_name']) . " (" . strtoupper($getuser['short_name']) . ")";
                        } elseif ($getuser['UserInvitations']['is_active'] == 1) {
                            $role = 'invited';
                            $data = $getuser['email'];
                        }
                    ?>
                        <tr class="alltrcls cmn_link_color anchor" data-id="<?php echo $getuser['uniq_id']; ?>" data-role="<?php echo $role; ?>" onclick="searchUser('<?php echo $role; ?>', '<?php echo $getuser['uniq_id']; ?>', '')">
                            <td colspan="2" class="ttc">
                                <?php echo str_ireplace($srchstr, "<span class='ajx-selctd'>" . $srchstr . "</span>", $data); ?>
                            </td>
                        </tr>
                    <?php }
                }

                if (!empty($results['files'])) { ?>
                    <?php foreach ($results['files'] as $getfile) { ?>
                        <tr class="alltrcls cmn_link_color anchor" data-id="<?php echo $getfile['uniq_id']; ?>" data-role="<?php echo $getfile['CaseFiles']['id']; ?>" onclick="searchFile('<?php echo $getfile['CaseFiles']['id']; ?>','<?php echo $getfile['uniq_id']; ?>','<?php echo $srchstr; ?>')">
                            <td colspan="2" class="ttc">
                                <?php $data = $getfile['CaseFiles']['file'];
                                echo str_ireplace($srchstr, "<span class='ajx-selctd'>" . $srchstr . "</span>", $data);
                                ?>
                            </td>
                        </tr>
                <?php }
                }
            } else {
                ?>
                <tr class="nohover noborder">
                    <td colspan="2">
                        <?php if ($results['page'] == 'tasks') { ?>
                            <span class="colr_red"><?php echo __('No Task Found'); ?>.</span>
                        <?php } else { ?>
                            <span class="colr_red"><?php echo __('No result found'); ?>.</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>