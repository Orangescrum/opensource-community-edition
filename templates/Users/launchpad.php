<?php
/**
 * Launchpad - Company switcher (cloud/multi-tenant feature).
 * In self-hosted mode with a single company, this page is typically
 * bypassed via redirect in UsersController::launchpad().
 *
 * If reached, redirect to the default dashboard.
 */
?>
<script>
    window.location.href = '<?= $this->Url->build(['controller' => 'Projects', 'action' => 'manage']) ?>';
</script>
<div style="text-align:center; padding:60px;">
    <h3>Redirecting...</h3>
    <p>If you are not redirected, <a href="<?= $this->Url->build(['controller' => 'Projects', 'action' => 'manage']) ?>">click here</a>.</p>
</div>
