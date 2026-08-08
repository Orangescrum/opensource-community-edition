<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title><?= $this->fetch('title') ?: 'Orangescrum Setup' ?></title>
    <link rel="shortcut icon" href="<?= $this->Url->webroot('favicon.ico') ?>" />
    <link rel="stylesheet" href="<?= $this->Url->webroot('css/fonts-local.css') ?>" />
    <meta name="robots" content="noindex,nofollow" />
    <?php
    echo $this->Html->meta('icon');
    echo $this->Html->css(['auth_page.css?v=']);
    ?>
    <style>
        /* Installer-specific overrides */
        .form-side { max-width: 100%; }
        .form-container { max-width: 600px; margin: 0 auto; padding: 40px 30px; }
        .form-container table { width: 100%; }
        .form-container table td { padding: 8px 4px; vertical-align: middle; }
        .form-container input[type="text"],
        .form-container input[type="password"],
        .form-container input[type="email"],
        .form-container input[type="file"],
        .form-container select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--slate-300, #cbd5e1);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        .form-container input:focus {
            outline: none;
            border-color: var(--orange-500, #f97316);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }
        .form-container input[type="submit"] {
            display: block;
            width: 100%;
            padding: 12px;
            background: var(--orange-500, #f97316);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: background 0.2s;
        }
        .form-container input[type="submit"]:hover {
            background: var(--orange-600, #ea580c);
        }
        .form-container h2, .form-header h2 {
            font-family: 'Inter', sans-serif;
            font-size: 28px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 8px;
        }
        .form-container small { color: var(--slate-500, #64748b); font-size: 12px; }
        .form-container .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .form-container .alert-success { background: #dcfce7; color: #166534; }
        .form-container .alert-danger { background: #fef2f2; color: #991b1b; }
        .form-container a { color: var(--orange-500, #f97316); text-decoration: none; font-weight: 600; }
        .form-container a:hover { text-decoration: underline; }
        .install-step-label { font-size: 13px; color: var(--slate-500, #64748b); text-align: center; margin-bottom: 20px; }
        /* Disable ripple/material effects and hover shift */
        .ripple, .withripple, .btn .ink { display: none !important; }
        .btn::after, .btn::before { display: none !important; }
        .btn-submit:hover { transform: none !important; }
        /* Flash message styling */
        .form-container .message,
        .form-container div[class*="alert"],
        .form-container .success,
        .form-container .error { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; font-family: 'Inter', sans-serif; }
        .form-container .message.success,
        .form-container .alert-success,
        .form-container .success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .form-container .message.error,
        .form-container .alert-danger,
        .form-container .error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .form-container .message.warning,
        .form-container .alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .form-container .message.info,
        .form-container .alert-info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    </style>
    <?= $this->Html->script('jquery/1.9.1/jquery.min.js'); ?>
    <?= $this->fetch('script-block-first'); ?>
    <?php echo $this->Html->css("typography.css"); ?>
</head>

<body class="head_back" id="headbody">
    <div class="app-container" style="min-height: 100vh;">
        <!-- Left Panel: Brand -->
        <div class="brand-side">
            <div>
                <a href="<?= $this->Url->webroot('/') ?>" class="orangescrum-logo">
                    <img src="<?= $this->Url->webroot('img/header/os-white-logo.svg') ?>" alt="Orangescrum"
                        style="height: 40px; width: auto;" />
                </a>

                <h1 class="brand-h1">Setup<br><span>Orangescrum</span></h1>
                <p class="brand-p">
                    Configure your self-hosted project management platform in just a few steps.
                </p>

                <div class="feature-list">
                    <div class="feature-item">
                        <div class="check-icon"><span style="color:#22c55e; font-size:18px;">&#10003;</span></div>
                        <div>
                            <strong style="display: block; font-size: 15px;">Quick Setup</strong>
                            <span style="color: rgba(255,255,255,0.6); font-size: 13px;">Database, mail, and you're ready to go.</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="check-icon"><span style="color:#22c55e; font-size:18px;">&#10003;</span></div>
                        <div>
                            <strong style="display: block; font-size: 15px;">Your Data, Your Server</strong>
                            <span style="color: rgba(255,255,255,0.6); font-size: 13px;">Full control over your project data.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Installer Content -->
        <div class="form-side">
            <div class="form-container">
                <?= $this->fetch('content') ?>
            </div>
        </div>
    </div>
</body>

</html>
