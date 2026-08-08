<?php
/**
 * Custom Error Layout
 * Extends default_inner for logged-in users, default_outer for guests
 *
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;

// Check if user is logged in
$isLoggedIn = defined('SES_ID') && SES_ID > 0;

// Get locale and direction
$locale = \Cake\I18n\I18n::getLocale();
$direction = 'ltr';
if (in_array($locale, ['ar', 'ara', 'he', 'fa', 'ur'])) {
    $direction = 'rtl';
}
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>" dir="<?= $direction ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex,nofollow">
    <title><?= $this->fetch('title') ?> - Orangescrum</title>
    <?= $this->Html->meta('icon') ?>
    <link rel="shortcut icon" href="<?= $this->Url->webroot('favicon.ico') ?>">
    
    <?php if ($isLoggedIn): ?>
        <!-- Logged-in user styles -->
        <?php
        echo $this->Html->css(['material-icon.css']);
        echo $this->Html->css([
            'bootstrap.min.css',
            'bootstrap-material-design.min.css',
            'ripples.min.css',
            'custom.css',
            'custom_new.css',
            'custom_theme.css',
        ]);
        ?>
    <?php else: ?>
        <!-- Guest user styles -->
        <?php
        echo $this->Html->css(['bootstrap.min.css', 'bootstrap-material-design.min.css', 'ripples.min.css', 'custom_outer.css']);
        echo $this->Html->css('css_outer/style.css');
        echo $this->Html->css(['css_outer/animate.css', 'css_outer/fonts.css']);
        echo $this->Html->css(['material-icon']);
        ?>
    <?php endif; ?>
    
    <style>
        /* Error page specific styles */
        .error-page-wrapper {
            min-height: <?= $isLoggedIn ? 'calc(100vh - 60px)' : '100vh' ?>;
            display: flex;
            align-items: center;
            justify-content: center;
            background: <?= $isLoggedIn ? '#f5f7fa' : 'transparent radial-gradient(closest-side at 56% 63%, #FDF4EA 0%, #FFFFFF 49%, #FDF4EA 100%) 0% 0% no-repeat padding-box' ?>;
            padding: 40px 20px;
        }
        
        .error-page-container {
            text-align: center;
            max-width: 550px;
            width: 100%;
            background: #fff;
            border-radius: 16px;
            padding: 50px 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }
        
        .error-page-container .logo {
            margin-bottom: 35px;
        }
        
        .error-page-container .logo img {
            max-width: 200px;
            height: auto;
        }
        
        .error-page-container .error-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 25px;
            background: #2e2e2e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 30px rgba(46, 46, 46, 0.3);
        }
        
        .error-page-container .error-icon svg {
            width: 50px;
            height: 50px;
            fill: #fff;
        }
        
        .error-page-container .error-icon.error-500 {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            box-shadow: 0 8px 30px rgba(231, 76, 60, 0.3);
        }
        
        .error-page-container .error-icon.error-db {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            box-shadow: 0 8px 30px rgba(155, 89, 182, 0.3);
        }
        
        .error-page-container .error-icon.error-expired {
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
            box-shadow: 0 8px 30px rgba(230, 126, 34, 0.3);
        }
        
        .error-page-container .error-code {
            font-size: 64px;
            font-weight: 700;
            color: #2e2e2e;
            margin-bottom: 8px;
            line-height: 1;
            font-family: 'Muli', sans-serif;
        }
        
        .error-page-container .error-code.error-500 {
            color: #e74c3c;
        }
        
        .error-page-container .error-code.error-db {
            color: #9b59b6;
        }
        
        .error-page-container .error-code.error-expired {
            color: #e67e22;
        }
        
        .error-page-container h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            font-family: 'Muli', sans-serif;
        }
        
        .error-page-container p {
            font-size: 15px;
            color: #666;
            margin-bottom: 28px;
            line-height: 1.6;
            font-family: 'Muli', sans-serif;
        }
        
        .error-page-container .error-actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 25px;
        }
        
        .error-page-container .btn-error {
            display: inline-block;
            padding: 12px 28px;
            background: #2e2e2e;
            color: #fff !important;
            text-decoration: none !important;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(46, 46, 46, 0.3);
            border: none;
            cursor: pointer;
        }
        
        .error-page-container .btn-error:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 46, 46, 0.4);
        }
        
        .error-page-container .error-report {
            margin-top: 22px;
            font-size: 13px;
            color: #6b7280;
        }

        .error-page-container .error-report a {
            color: #1a73e8;
            text-decoration: none;
            font-weight: 500;
        }

        .error-page-container .error-report a:hover {
            text-decoration: underline;
        }

        .error-page-container .btn-error-secondary {
            background: transparent;
            color: #2e2e2e !important;
            border: 2px solid #2e2e2e;
            box-shadow: none;
        }
        
        .error-page-container .btn-error-secondary:hover {
            background: #2e2e2e;
            color: #fff !important;
        }
        
        .error-page-container .btn-error.btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        }
        
        .error-page-container .btn-error.btn-success:hover {
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
        }
        
        .error-page-container .error-details {
            margin-top: 20px;
            padding: 18px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #eee;
            text-align: left;
        }
        
        .error-page-container .error-details h3 {
            font-size: 12px;
            color: #999;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .error-page-container .error-details code {
            display: block;
            padding: 12px;
            background: #fff;
            border-radius: 6px;
            font-size: 13px;
            color: #555;
            word-break: break-word;
            border: 1px solid #eee;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .error-page-container {
                padding: 35px 25px;
            }
            
            .error-page-container .error-code {
                font-size: 48px;
            }
            
            .error-page-container h1 {
                font-size: 20px;
            }
            
            .error-page-container .error-actions {
                flex-direction: column;
            }
            
            .error-page-container .btn-error {
                width: 100%;
            }
        }
        
        /* Simple error header for logged-in users */
        .error-header {
            background: #2e2e2e;
            padding: 15px 20px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .error-header-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .error-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none !important;
            color: #fff !important;
            font-weight: 600;
            font-size: 18px;
            font-family: 'Muli', sans-serif;
        }
        
        .error-logo:hover {
            opacity: 0.9;
        }
        
        .error-page-body .error-page-wrapper {
            padding-top: 80px;
        }
    </style>
    
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
    <?php echo $this->Html->css("typography.css"); ?>
</head>

<?php if ($isLoggedIn): ?>
    <body class="error-page-body">
        <!-- Simple error header - don't use header_inner as it requires session/database data -->
        <header class="error-header">
            <div class="error-header-content">
                <a href="<?= $this->Url->build('/') ?>" class="error-logo">
                    <img src="<?= $this->Url->build('/img/header/orangescrum-logo-white.svg') ?>" alt="Orangescrum" height="32">
                </a>
            </div>
        </header>
        
        <div class="error-page-wrapper">
            <div class="error-page-container">
                <?= $this->Flash->render() ?>
                <?= $this->fetch('content') ?>
            </div>
        </div>
        
        <?php
        echo $this->Html->script('jquery.min.js');
        echo $this->Html->script('bootstrap.min.js');
        echo $this->Html->script('material.min.js');
        echo $this->Html->script('ripples.min.js');
        ?>
        <script>
            $(function() {
                if (typeof $.material !== 'undefined') {
                    $.material.init();
                }
            });
        </script>
    </body>
<?php else: ?>
    <body class="head_back" id="headbody">
        <div class="error-page-wrapper">
            <div class="error-page-container">
                <div class="logo">
                    <a href="<?= $this->Url->webroot('/') ?>">
                        <img src="<?= $this->Url->webroot('img/header/new-os-logo.svg') ?>" alt="Orangescrum" title="Orangescrum">
                    </a>
                </div>
                <?= $this->Flash->render() ?>
                <?= $this->fetch('content') ?>
            </div>
        </div>
        
        <?php
        echo $this->Html->script('jquery/1.9.1/jquery.min.js');
        echo $this->Html->script('bootstrap.min.js');
        echo $this->Html->script('material.min.js');
        echo $this->Html->script('ripples.min.js');
        ?>
        <script>
            $(function() {
                if (typeof $.material !== 'undefined') {
                    $.material.init();
                }
            });
        </script>
    </body>
<?php endif; ?>
</html>
