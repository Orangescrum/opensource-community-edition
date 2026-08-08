<style>
    .about-page .about-card {
        border: 1px solid #e6e8eb;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 20px;
        background: #fff;
    }
    .about-page .about-card h3 {
        margin: 0 0 4px 0;
        font-size: 16px;
        font-weight: 600;
        color: #2e2e2e;
    }
    .about-page .about-card .about-card-note {
        margin: 0 0 18px 0;
        font-size: 13px;
        color: #80868b;
        line-height: 1.5;
    }
    .about-page .about-table {
        width: 100%;
        border-collapse: collapse;
    }
    .about-page .about-table th,
    .about-page .about-table td {
        text-align: left;
        padding: 10px 0;
        font-size: 14px;
        border-bottom: 1px solid #f1f3f4;
        vertical-align: top;
    }
    .about-page .about-table tr:last-child th,
    .about-page .about-table tr:last-child td {
        border-bottom: 0;
    }
    .about-page .about-table th {
        width: 220px;
        font-weight: 400;
        color: #80868b;
    }
    .about-page .about-table td {
        color: #2e2e2e;
        font-weight: 500;
        word-break: break-word;
    }
    .about-page .edition-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        background: #e8f0fe;
        color: #1a73e8;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .3px;
    }
    .about-page .about-copy {
        font-size: 13px;
        color: #5f6368;
        line-height: 1.7;
        margin: 0 0 12px 0;
    }
    .about-page .about-copy:last-child { margin-bottom: 0; }
    .about-page a { color: #1a73e8; }
</style>

<div class="setting_wrapper task_listing cmn_tbl_widspace width_hover_tbl about-page">
    <div class="row">
        <div class="col-lg-9">

            <div class="about-card">
                <h3><?php echo __('Orangescrum Open Source Community Edition'); ?></h3>
                <p class="about-card-note"><?php echo __('This installation and the software it runs.'); ?></p>
                <table class="about-table">
                    <tr>
                        <th><?php echo __('Edition'); ?></th>
                        <td><span class="edition-badge"><?php echo h(EDITION_NAME); ?></span></td>
                    </tr>
                    <tr>
                        <th><?php echo __('Version'); ?></th>
                        <td><?php echo 'v' . h($version); ?></td>
                    </tr>
                    <?php if ($installedOn) { ?>
                        <tr>
                            <th><?php echo __('Installed on'); ?></th>
                            <td><?php echo h($installedOn); ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <th><?php echo __('Copyright'); ?></th>
                        <td>
                            &copy; <?php echo date('Y'); ?>
                            <a href="<?php echo h(EDITION_VENDOR_URL); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo h(EDITION_VENDOR); ?>
                            </a>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="about-card">
                <h3><?php echo __('Licence'); ?></h3>
                <p class="about-card-note">
                    <?php echo __('The Community Edition is free software. No commercial licence key is required to install or run it, and there are no user, project or storage limits.'); ?>
                </p>
                <table class="about-table">
                    <tr>
                        <th><?php echo __('Licence'); ?></th>
                        <td>
                            <a href="<?php echo h(EDITION_LICENSE_URL); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo h(EDITION_LICENSE_NAME); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo __('SPDX identifier'); ?></th>
                        <td><?php echo h(EDITION_LICENSE_SPDX); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __('Source code'); ?></th>
                        <td>
                            <a href="<?php echo h(EDITION_SOURCE_URL); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo h(EDITION_SOURCE_URL); ?>
                            </a>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="about-card">
                <h3><?php echo __('Your rights and obligations'); ?></h3>
                <p class="about-copy">
                    <?php echo __('This edition is free software under the AGPL v3. If you modify Orangescrum and let others use it over a network, the licence requires you to publish the corresponding source of your modified version to those users.'); ?>
                </p>
                <p class="about-copy">
                    <?php
                    echo __(
                        'Need to use Orangescrum without these obligations, or want the Enterprise edition? {0} is available from {1}.',
                        '<a href="' . h(EDITION_COMMERCIAL_URL) . '" target="_blank" rel="noopener noreferrer">' . __('A commercial licence') . '</a>',
                        h(EDITION_VENDOR)
                    );
                    ?>
                </p>
                <p class="about-copy">
                    <?php echo __('No warranty — see the full licence text for terms.'); ?>
                </p>
            </div>

            <?php if (!empty($thirdParty['available'])) { ?>
                <div class="about-card">
                    <h3><?php echo __('Third-party software'); ?></h3>
                    <p class="about-card-note">
                        <?php echo __('This product bundles open-source components, each under its own licence.'); ?>
                    </p>
                    <table class="about-table">
                        <tr>
                            <th><?php echo __('Bundled packages'); ?></th>
                            <td><?php echo h((string)$thirdParty['total']); ?></td>
                        </tr>
                        <?php if (!empty($thirdParty['generated_at'])) { ?>
                            <tr>
                                <th><?php echo __('Notices generated'); ?></th>
                                <td><?php echo h((string)$thirdParty['generated_at']); ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                    <p class="about-card-note" style="margin-top:12px;">
                        <a href="<?php echo $this->Url->build('/about/notices'); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo __('View third-party notices'); ?>
                        </a>
                    </p>
                </div>
            <?php } ?>

        </div>
    </div>
</div>
