<?php
/**
 * Database Error Template
 * @var \App\View\AppView $this
 */
$statusCode = $statusCode ?? 500;
$statusLabel = $statusLabel ?? 'Internal Server Error';
$title = $statusCode === 503 ? 'Database Connection Error' : 'Database Error';
$this->assign('title', $title);
?>

<div class="error-icon error-db">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M12 3C7.58 3 4 4.79 4 7v10c0 2.21 3.58 4 8 4s8-1.79 8-4V7c0-2.21-3.58-4-8-4zm0 2c3.87 0 6 1.5 6 2s-2.13 2-6 2-6-1.5-6-2 2.13-2 6-2zm6 12c0 .5-2.13 2-6 2s-6-1.5-6-2v-2.23c1.61.78 3.72 1.23 6 1.23s4.39-.45 6-1.23V17zm0-4c0 .5-2.13 2-6 2s-6-1.5-6-2v-2.23c1.61.78 3.72 1.23 6 1.23s4.39-.45 6-1.23V13zm0-4c0 .5-2.13 2-6 2s-6-1.5-6-2V6.77C7.61 7.55 9.72 8 12 8s4.39-.45 6-1.23V9z"/>
    </svg>
</div>

<div class="error-code error-db"><?= $statusCode ?></div>
<h1><?= $title ?></h1>
<?php if ($statusCode === 503): ?>
    <p>We're having trouble connecting to the database at the moment. This is usually a temporary issue. Please try again in a few minutes.</p>
<?php else: ?>
    <p>A database error occurred while processing your request. Please try again or contact support if the issue persists.</p>
<?php endif; ?>

<div class="error-actions">
    <a href="javascript:location.reload()" class="btn-error btn-error-secondary">Try Again</a>
    <a href="<?= $this->Url->webroot('/') ?>" class="btn-error">Go to Homepage</a>
</div>

<?php
/*
 * Self-hosted installs have no support desk behind them, so "our team has been
 * notified" is not true here. Point the user at the issue tracker instead, and
 * prefill what a maintainer needs to triage. EDITION_SOURCE_URL is the same
 * repository the AGPL section 13 source offer names.
 */
$reportUrl = null;
if (defined('EDITION_SOURCE_URL') && EDITION_SOURCE_URL) {
    $reportUrl = rtrim(EDITION_SOURCE_URL, '/') . '/issues/new?' . http_build_query([
        'title' => '[%CODE%] ' . $this->request->getPath(),
        'labels' => 'bug',
        'body' => "**What I was doing**


"
            . "**What I expected**


"
            . "**What happened**


"
            . "---
"
            . '- Page: `' . $this->request->getPath() . "`
"
            . '- Version: `' . (defined('OS_VERSION') ? OS_VERSION : 'unknown') . "`
"
            . "- PHP: `" . PHP_VERSION . "`
",
    ]);
}
?>
<?php if ($reportUrl) : ?>
<p class="error-report">
    <?= __('Is this a bug?') ?>
    <a href="<?= h(str_replace('%CODE%', 'db-error', $reportUrl)) ?>" target="_blank" rel="noopener noreferrer">
        <?= __('Report it on GitHub') ?>
    </a>
</p>
<?php endif; ?>


<div class="error-details">
    <h3>What you can do</h3>
    <code>
        • Refresh the page and try again<br>
        • Check your internet connection<br>
        • Contact support if the issue persists
    </code>
</div>
