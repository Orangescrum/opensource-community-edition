<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Database\StatementInterface $error
 * @var string $message
 * @var string $url
 */
use Cake\Core\Configure;
use Cake\Error\Debugger;

$this->layout = 'custom_error';

if (Configure::read('debug')) :
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error500.php');

    $this->start('file');
?>
<?php if (!empty($error->queryString)) : ?>
    <p class="notice">
        <strong>SQL Query: </strong>
        <?= h($error->queryString) ?>
    </p>
<?php endif; ?>
<?php if (!empty($error->params)) : ?>
    <strong>SQL Query Params: </strong>
    <?php Debugger::dump($error->params) ?>
<?php endif; ?>
<?php if ($error instanceof Error) : ?>
    <strong>Error in: </strong>
    <?= sprintf('%s, line %s', str_replace(ROOT, 'ROOT', $error->getFile()), $error->getLine()) ?>
<?php endif; ?>
<?php
    echo $this->element('auto_table_warning');

    $this->end();
endif;

$this->assign('title', 'Internal Server Error');
?>

<div class="error-icon error-500">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/>
    </svg>
</div>

<div class="error-code error-500">500</div>
<h1>Internal Server Error</h1>
<p>Oops! Something went wrong on our end. Our team has been notified and we're working to fix it. Please try again later.</p>

<div class="error-actions">
    <a href="javascript:history.back()" class="btn-error btn-error-secondary">Go Back</a>
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
    <a href="<?= h(str_replace('%CODE%', '500', $reportUrl)) ?>" target="_blank" rel="noopener noreferrer">
        <?= __('Report it on GitHub') ?>
    </a>
</p>
<?php endif; ?>


<?php if (Configure::read('debug') && !empty($message)) : ?>
<div class="error-details">
    <h3>Error Details</h3>
    <code><?= h($message) ?></code>
</div>
<?php endif; ?>
