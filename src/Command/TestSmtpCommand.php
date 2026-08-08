<?php

declare(strict_types=1);

/**
 * Orangescrum Community Edition
 *
 * Copyright (c) 2026 Andolasoft Inc.
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Throwable;

/**
 * TestSmtp command.
 *
 * Sends a test email using the application's configured Mailer ('default')
 * so an operator can verify SMTP host, port, credentials and TLS settings
 * work end-to-end from the same PHP runtime the app uses.
 *
 * Usage (host or container):
 *   bin/cake test_smtp
 *   bin/cake test_smtp recipient@example.com
 *   bin/cake test_smtp -t smtp_alt recipient@example.com
 *
 * Inside the running container:
 *   docker exec -it orangescrum-app bin/cake test_smtp
 */
class TestSmtpCommand extends Command
{
    /**
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->setDescription('Send a test email through the configured SMTP transport.');

        $parser->addArgument('email', [
            'help' => 'Recipient email address. If omitted, you will be prompted.',
            'required' => false,
        ]);

        $parser->addOption('transport', [
            'short' => 't',
            'help' => "Name of the EmailTransport config to use (default: AppEmail.transport from config).",
            'default' => null,
        ]);

        $parser->addOption('from', [
            'short' => 'f',
            'help' => 'From address (default: AppEmail.from_email from config).',
            'default' => null,
        ]);

        return $parser;
    }

    /**
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|int Exit code (Command::CODE_SUCCESS / CODE_ERROR)
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $transportName = $args->getOption('transport') ?: Configure::read('AppEmail.transport');
        if (empty($transportName)) {
            $io->error("No transport specified and AppEmail.transport is not configured. Pass -t <name>.");
            return static::CODE_ERROR;
        }

        $fromEmail = $args->getOption('from') ?: Configure::read('AppEmail.from_email');
        if (empty($fromEmail)) {
            $io->error("No From address available. Pass -f <email> or set AppEmail.from_email in config.");
            return static::CODE_ERROR;
        }

        $to = $this->resolveRecipient($args, $io);
        if ($to === null) {
            return static::CODE_ERROR;
        }

        $transportConfig = TransportFactory::getConfig((string)$transportName);
        $sesRegion = $this->detectSesRegion($transportConfig['host'] ?? null);

        $this->printTransportSummary($io, (string)$transportName, $sesRegion);

        $timestamp = date('Y-m-d H:i:s T');
        $hostname = gethostname() ?: 'unknown-host';
        $subject = 'Orangescrum SMTP test ' . $timestamp;
        $body = "This is a test email from your Orangescrum instance.\n\n"
              . "If you can read this, SMTP is configured correctly.\n\n"
              . "Sent at : {$timestamp}\n"
              . "Host    : {$hostname}\n"
              . "From    : {$fromEmail}\n"
              . "To      : {$to}\n";

        $io->out("\nSending test email from <info>{$fromEmail}</info> to <info>{$to}</info> via <info>{$transportName}</info> ...");

        try {
            $mailer = new Mailer();
            $mailer->setTransport((string)$transportName)
                ->setEmailFormat('text')
                ->setFrom($fromEmail)
                ->setTo($to)
                ->setSubject($subject)
                ->deliver($body);
        } catch (Throwable $e) {
            $this->reportFailure($io, $e, $sesRegion);
            return static::CODE_ERROR;
        }

        $io->success("Email accepted by SMTP server and sent to {$to}.");
        $io->out('Check the recipient inbox (and spam folder) to confirm delivery.');

        return static::CODE_SUCCESS;
    }

    /**
     * Resolve recipient from the CLI argument or prompt up to 3 times.
     *
     * @param \Cake\Console\Arguments $args
     * @param \Cake\Console\ConsoleIo $io
     * @return string|null Validated email, or null if the user gave up.
     */
    private function resolveRecipient(Arguments $args, ConsoleIo $io): ?string
    {
        $email = $args->getArgument('email');
        if ($email !== null) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
            $io->error("'{$email}' is not a valid email address.");
            return null;
        }

        for ($i = 0; $i < 3; $i++) {
            $email = (string)$io->ask('Enter recipient email address:');
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
            $io->warning("'{$email}' is not a valid email address. Try again.");
        }

        $io->error('No valid recipient provided after 3 attempts.');
        return null;
    }

    /**
     * Print the resolved transport config (without the password) so the
     * operator can confirm which SMTP settings are actually in effect.
     *
     * @param \Cake\Console\ConsoleIo $io
     * @param string $transportName
     * @param string|null $sesRegion AWS SES region if the host is an SES endpoint
     * @return void
     */
    private function printTransportSummary(ConsoleIo $io, string $transportName, ?string $sesRegion = null): void
    {
        try {
            $config = TransportFactory::getConfig($transportName);
        } catch (Throwable $e) {
            $io->warning("Could not read transport config '{$transportName}': " . $e->getMessage());
            return;
        }

        if ($config === null) {
            $io->warning("No EmailTransport config named '{$transportName}' is registered.");
            return;
        }

        $safe = [
            'className' => $config['className'] ?? '(unset)',
            'host'      => $config['host']      ?? '(unset)',
            'port'      => $config['port']      ?? '(unset)',
            'username'  => $config['username']  ?? '(unset)',
            'tls'       => $config['tls']       ?? false,
            'timeout'   => $config['timeout']   ?? '(default)',
        ];

        $io->out("Using EmailTransport <info>{$transportName}</info>:");
        foreach ($safe as $k => $v) {
            $printable = is_bool($v) ? ($v ? 'true' : 'false') : (string)$v;
            $io->out(sprintf('  %-9s = %s', $k, $printable));
        }

        if ($sesRegion !== null) {
            $io->out("  <info>AWS SES detected (region: {$sesRegion})</info>");
        }
    }

    /**
     * Detect whether the SMTP host points at AWS SES and return its region.
     *
     * Matches the documented SES SMTP endpoint pattern:
     *   email-smtp.<region>.amazonaws.com
     *   email-smtp-fips.<region>.amazonaws.com
     *
     * @param string|null $host
     * @return string|null Region name (e.g. 'us-east-1') or null if not SES.
     */
    private function detectSesRegion(?string $host): ?string
    {
        if (empty($host)) {
            return null;
        }
        if (preg_match('/^email-smtp(?:-fips)?\.([a-z0-9-]+)\.amazonaws\.com$/i', $host, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Format and print a detailed failure report with hints for common causes.
     *
     * @param \Cake\Console\ConsoleIo $io
     * @param \Throwable $e
     * @param string|null $sesRegion AWS SES region if the host is an SES endpoint
     * @return void
     */
    private function reportFailure(ConsoleIo $io, Throwable $e, ?string $sesRegion = null): void
    {
        $io->error('SMTP send failed: ' . $e->getMessage());
        $io->out('');
        $io->out('Exception class: ' . get_class($e));
        if ($e->getPrevious() !== null) {
            $io->out('Caused by      : ' . $e->getPrevious()->getMessage());
        }
        $io->verbose("\nFull trace:\n" . $e->getTraceAsString());

        $io->out('');
        $io->out('Common causes:');
        $io->out('  - Wrong host/port in EmailTransport config (config/app.php / .env)');
        $io->out('  - Bad credentials (username/password) or app-password required');
        $io->out("  - TLS/SSL mismatch (try toggling 'tls' => true/false)");
        $io->out('  - Firewall / egress blocking outbound port 25/465/587');
        $io->out("  - 'From' address not authorised on the sending domain");
        $io->out('  - Re-run with -v to see the full stack trace.');

        if ($sesRegion !== null) {
            $io->out('');
            $io->out("AWS SES-specific checks (region: {$sesRegion}):");
            $io->out('  - SMTP credentials must be SES SMTP creds, NOT raw AWS access keys');
            $io->out('    (generate via IAM > SES > SMTP settings; username starts with "AKIA"');
            $io->out("    but the password is a long base64-ish string, NOT your AWS secret key)");
            $io->out('  - From address must be a verified identity in this region');
            $io->out("    (check: SES console > Verified identities, region '{$sesRegion}')");
            $io->out('  - If the account is still in SES sandbox, the To address must also be verified');
            $io->out('    and you are limited to 200 emails/day, 1/sec');
            $io->out('  - Port must be 587 (STARTTLS), 465 (TLS wrapper), 2587, or 2465');
            $io->out("  - Endpoint region must match the SES region your identity is verified in");
            $io->out('    (sending via us-east-1 with a eu-west-1-verified identity will fail)');
            $io->out('  - 554 "Message rejected: Email address is not verified" → identity not verified');
            $io->out('  - 535 "Authentication Credentials Invalid" → wrong SMTP creds (see first bullet)');
        }
    }
}
