<?php $this->assign('title', 'Setting Up Account'); ?>

<style>
    .signup-processing-wrap {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: #f7f9fb url('<?= HTTP_ROOT; ?>images/signup-bg.png') no-repeat center center;
        background-size: cover;
        position: relative;
        overflow: hidden;
    }
    .signup-processing-wrap::before {
        content: "";
        position: absolute;
        inset: 0;
        background: inherit;
        background-image: inherit;
        background-size: cover;
        background-position: center;
        filter: blur(12px) brightness(0.92);
        transform: scale(1.05);
        z-index: 0;
    }
    .signup-processing-card {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 520px;
        background: rgba(255, 255, 255, 0.55);
        -webkit-backdrop-filter: blur(20px) saturate(1.6);
        backdrop-filter: blur(20px) saturate(1.6);
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0 16px 40px rgba(12, 25, 52, 0.18);
        text-align: center;
        padding: 32px 28px;
    }

    .signup-processing-card h2 {
        margin: 0 0 10px;
        color: #0e1b3d;
        font-weight: 700;
        font-size: 30px;
        line-height: 1.2;
    }

    .signup-processing-card p {
        margin: 0;
        color: #4e5f83;
        font-size: 17px;
        line-height: 1.6;
    }

    .signup-processing-loader {
        margin: 22px auto 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
    }

    .signup-processing-loader img {
        width: 32px;
        height: 32px;
    }

    .signup-processing-note {
        margin-top: 16px;
        font-size: 14px;
        color: #6f7fa1;
    }
</style>

<div class="signup-processing-wrap">
    <div class="signup-processing-card">
        <h2>Just a moment</h2>
        <p>We're setting up your account. Please do not refresh or close this page.</p>
        <div class="signup-processing-loader" aria-hidden="true">
            <img src="<?= HTTP_ROOT; ?>img/payment_loading.gif" alt="Loading">
        </div>
        <div class="signup-processing-note">Redirecting to your workspace...</div>
    </div>
</div>

<script>
    $(document).ready(function () {
        var nextUrl = <?= json_encode($nextUrl ?? '') ?>;
        var fallbackUrl = "<?= $this->Url->build(['controller' => 'Projects', 'action' => 'manage']) ?>";
        var destination = nextUrl || fallbackUrl;

        setTimeout(function () {
            window.location.href = destination;
        }, 1400);
    });
</script>
