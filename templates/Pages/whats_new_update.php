<?php
/**
 * What's New / release notes for the Community Edition.
 *
 * A self-contained changelog rendered in-app. Each release is a card with a
 * version, date and the notable changes. No external or pricing links — this
 * is the Product Updates page the header menu points at.
 *
 * To add a release: prepend an entry to $releases below.
 */
$releases = [
    [
        'version' => $version ?? '0.1.0',
        'date' => 'August 2026',
        'title' => __('First open-source release'),
        'summary' => __('Orangescrum is now available as an open-source, self-hosted Community Edition under the AGPL-3.0 licence — no user, project or storage limits, and no commercial licence key.'),
        'changes' => [
            __('Open-source edition: free to install and run, with the full source available.'),
            __('Removed the proprietary plugins (Label Customizer, Two-Factor Auth, Cloud Storage) and the Scrum/Epic/Feature module so the edition ships clean under the AGPL.'),
            __('Replaced commercially licensed front-end libraries with MIT/OFL equivalents — charts render with Chart.js, the image lightbox uses Magnific Popup, and only open-licensed fonts are bundled.'),
            __('Bundled third-party licence notices are viewable from the About page.'),
            __('Fixes to the first-run experience: registration, the dashboard and every task view now load cleanly for a brand-new account.'),
        ],
    ],
];
?>
<style type="text/css">
    .whatsnew-page .wn-card {
        border: 1px solid #e6e8eb;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 20px;
        background: #fff;
    }
    .whatsnew-page .wn-head {
        display: flex;
        align-items: baseline;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }
    .whatsnew-page .wn-head h3 {
        margin: 0;
        font-size: 17px;
        font-weight: 600;
        color: #2e2e2e;
    }
    .whatsnew-page .wn-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        background: #e8f0fe;
        color: #1a73e8;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .3px;
    }
    .whatsnew-page .wn-date {
        font-size: 12px;
        color: #80868b;
    }
    .whatsnew-page .wn-summary {
        font-size: 13px;
        color: #5f6368;
        line-height: 1.7;
        margin: 8px 0 16px 0;
    }
    .whatsnew-page .wn-card ul {
        margin: 0;
        padding-left: 18px;
    }
    .whatsnew-page .wn-card li {
        font-size: 14px;
        color: #2e2e2e;
        line-height: 1.6;
        margin-bottom: 8px;
    }
    .whatsnew-page .wn-intro {
        font-size: 14px;
        color: #5f6368;
        margin: 0 0 20px 0;
    }
</style>

<div class="setting_wrapper task_listing cmn_tbl_widspace width_hover_tbl whatsnew-page">
    <div class="row">
        <div class="col-lg-9">

            <h1 style="font-size:22px;font-weight:600;margin:0 0 6px;"><?php echo __("What's New"); ?></h1>
            <p class="wn-intro"><?php echo __('Product updates and release notes for the Orangescrum Open Source Community Edition.'); ?></p>

            <?php foreach ($releases as $release) { ?>
                <div class="wn-card">
                    <div class="wn-head">
                        <h3><?php echo h($release['title']); ?></h3>
                        <span class="wn-badge">v<?php echo h($release['version']); ?></span>
                        <span class="wn-date"><?php echo h($release['date']); ?></span>
                    </div>
                    <p class="wn-summary"><?php echo h($release['summary']); ?></p>
                    <ul>
                        <?php foreach ($release['changes'] as $change) { ?>
                            <li><?php echo h($change); ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

        </div>
    </div>
</div>
