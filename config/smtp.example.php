<?php
use Cake\Mailer\Transport\SmtpTransport;

return [
    /*
     * Email configuration.
     *
     * Host and credential configuration in case you are using SmtpTransport
     *
     * See app.php for more configuration options.
     */
    'EmailTransport' => [
        'smtp' => [
            // SMTP email transport configuration
            'className' => SmtpTransport::class,
            'host' => env('SMTP_HOST', '__SMTP_HOST__'),
            'port' => env('SMTP_PORT', '__SMTP_PORT__'),
            'username' => env('SMTP_USERNAME', '__SMTP_USERNAME__'),
            'password' => env('SMTP_PASSWORD', '__SMTP_PASSWORD__'),
            'tls' => filter_var(env('SMTP_TLS', '__SMTP_TLS__'), FILTER_VALIDATE_BOOLEAN),
        ],
    ],
    /**
     * Email configuration.
     *
     * Define the default transport to use for sending emails.
     */
    'AppEmail' => [
        'transport' => env('EMAIL_TRANSPORT', 'smtp'),
        'from_email' => env('FROM_EMAIL', '__FROM_EMAIL__'),
        'notify_email' => env('NOTIFY_EMAIL', '__NOTIFY_EMAIL__'),
    ],
];
