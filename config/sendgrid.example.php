<?php
use App\Mailer\Transport\SendGridTransport;

return [
    /*
     * Email configuration.
     *
     * Host and credential configuration in case you are using SmtpTransport
     *
     * See app.php for more configuration options.
     */
    'EmailTransport' => [
        // SendGrid email transport configuration
        'sendgrid' => [
            'className' => SendGridTransport::class,
            'apiKey' => env('EMAIL_API_KEY', '__EMAIL_API_KEY__'),
        ],
    ],

    /**
     * Email configuration.
     *
     * Define the default transport to use for sending emails.
     */
    'AppEmail' => [
        'transport' => env('EMAIL_TRANSPORT', 'sendgrid'),
        'from_email' => env('FROM_EMAIL', '__FROM_EMAIL__'),
        'notify_email' => env('NOTIFY_EMAIL', '__NOTIFY_EMAIL__'),
    ],
];




