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
    $this->assign('templateName', 'error400.php');

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
<?= $this->element('auto_table_warning') ?>
<?php

$this->end();
endif;

// Determine error code from message
$errorCode = '400';
if (stripos($message, '404') !== false || stripos($message, 'not found') !== false) {
    $errorCode = '404';
} elseif (stripos($message, '403') !== false || stripos($message, 'forbidden') !== false) {
    $errorCode = '403';
} elseif (stripos($message, '401') !== false || stripos($message, 'unauthorized') !== false) {
    $errorCode = '401';
}

$errorTitles = [
    '400' => 'Bad Request',
    '401' => 'Unauthorized',
    '403' => 'Access Forbidden',
    '404' => 'Page Not Found',
];

$errorDescriptions = [
    '400' => 'The request could not be understood by the server. Please check your input and try again.',
    '401' => 'You need to be authenticated to access this resource. Please log in and try again.',
    '403' => 'You don\'t have permission to access this resource. Please contact your administrator.',
    '404' => 'The page you\'re looking for doesn\'t exist or has been moved.',
];

$title = $errorTitles[$errorCode] ?? 'Error';
$description = $errorDescriptions[$errorCode] ?? h($message);

$this->assign('title', $title);
?>

<div class="error-icon">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
    </svg>
</div>

<div class="error-code"><?= h($errorCode) ?></div>
<h1><?= h($title) ?></h1>
<p><?= h($description) ?></p>

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
    <a href="<?= h(str_replace('%CODE%', '400', $reportUrl)) ?>" target="_blank" rel="noopener noreferrer">
        <?= __('Report it on GitHub') ?>
    </a>
</p>
<?php endif; ?>


<?php if (Configure::read('debug') && !empty($url)) : ?>
<div class="error-details">
    <h3>Request Details</h3>
    <code>URL: <?= h($url) ?></code>
</div>
<?php endif; ?>