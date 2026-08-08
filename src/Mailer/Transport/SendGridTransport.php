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

namespace App\Mailer\Transport;

use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Message;
use SendGrid;
use SendGrid\Mail\Mail;
use Exception;

class SendGridTransport extends AbstractTransport
{
    /**
     * Send email using SendGrid API
     *
     * @param \Cake\Mailer\Message $message The email message.
     * @return array
     * @throws \Exception
     */
    public function send(Message $message): array
    {
        // Load API key from configuration
        $config = $this->getConfig();
        $apiKey = $config['apiKey'] ?? null;

        if (!$apiKey) {
            throw new Exception('SendGrid API key is missing.');
        }

        $email = new Mail();
        // Cake's Message::getFrom() returns ['email' => 'display name'].
        // SendGrid's Mail::setFrom signature is ($email, $name) — easy to
        // swap by accident, and only obvious once a non-trivial display
        // name is set (with no name, value == key and the bug is silent).
        $fromAddresses = $message->getFrom();
        $fromEmail = (string) key($fromAddresses);
        $fromName = (string) ($fromAddresses[$fromEmail] ?? '');
        // SendGrid rejects empty / blank names, and falls back to email
        // when the name is unset; mirror that behaviour explicitly.
        if ($fromName === '' || $fromName === $fromEmail) {
            $email->setFrom($fromEmail);
        } else {
            $email->setFrom($fromEmail, $fromName);
        }
        $email->setSubject($message->getSubject());

        // Set recipient(s)
        foreach ($message->getTo() as $emailAddress => $name) {
            $email->addTo($emailAddress, $name);
        }

        // Set CC
        foreach ($message->getCc() as $emailAddress => $name) {
            $email->addCc($emailAddress, $name);
        }

        // Set BCC
        foreach ($message->getBcc() as $emailAddress => $name) {
            $email->addBcc($emailAddress, $name);
        }

        // Set email content
        $bodyText = $message->getBodyText();
        $bodyHtml = $message->getBodyHtml();

        if ($bodyHtml) {
            $email->addContent('text/html', $bodyHtml);
        }
        if ($bodyText) {
            $email->addContent('text/plain', $bodyText);
        }

        // Attach files if any
        foreach ($message->getAttachments() as $filename => $file) {
            $email->addAttachment(
                base64_encode(file_get_contents($file['file'])),
                $file['mimetype'],
                $filename
            );
        }

        // Send the email using SendGrid API
        $sendgrid = new SendGrid($apiKey);
        try {
            $response = $sendgrid->send($email);
        } catch (Exception $e) {
            throw new Exception('SendGrid error: ' . $e->getMessage());
        }

        $statusCode = (int)$response->statusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            $body = (string)$response->body();
            $detail = '';
            $decoded = json_decode($body, true);
            if (is_array($decoded) && !empty($decoded['errors'][0]['message'])) {
                $detail = ': ' . $decoded['errors'][0]['message'];
            } elseif ($body !== '') {
                $detail = ': ' . mb_substr($body, 0, 200);
            }
            throw new Exception("SendGrid rejected the request (HTTP {$statusCode}){$detail}");
        }

        return [
            'headers' => $response->headers(),
            'statusCode' => $statusCode,
            'body' => $response->body(),
        ];
    }
}
