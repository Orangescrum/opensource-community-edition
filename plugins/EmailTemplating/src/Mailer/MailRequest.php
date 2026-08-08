<?php
declare(strict_types=1);

namespace EmailTemplating\Mailer;

/**
 * Immutable description of an email to send.
 *
 * Mutators return a new instance — never mutate $this.
 */
final class MailRequest
{
    public string $key;
    public ?int $companyId;
    public array $to = [];
    public array $cc = [];
    public array $bcc = [];
    public ?string $replyTo = null;
    public ?string $subjectDefault = null;
    public array $vars = [];
    public array $attachments = [];
    public array $headers = [];
    public string $format = 'html';
    public ?string $fromOverride = null;
    public bool $isTest = false;

    private function __construct(string $key, ?int $companyId)
    {
        $this->key = $key;
        $this->companyId = $companyId;
    }

    public static function for(string $key, ?int $companyId): self
    {
        return new self($key, $companyId);
    }

    public function to(string|array $addr): self
    {
        $clone = clone $this;
        $clone->to = is_array($addr) ? $addr : [$addr];

        return $clone;
    }

    public function cc(string|array $addr): self
    {
        $clone = clone $this;
        $clone->cc = is_array($addr) ? $addr : [$addr];

        return $clone;
    }

    public function bcc(string|array $addr): self
    {
        $clone = clone $this;
        $clone->bcc = is_array($addr) ? $addr : [$addr];

        return $clone;
    }

    public function replyTo(string $addr): self
    {
        $clone = clone $this;
        $clone->replyTo = $addr;

        return $clone;
    }

    public function subjectDefault(string $subject): self
    {
        $clone = clone $this;
        $clone->subjectDefault = $subject;

        return $clone;
    }

    public function vars(array $vars): self
    {
        $clone = clone $this;
        $clone->vars = $vars;

        return $clone;
    }

    public function attach(array $files): self
    {
        $clone = clone $this;
        $clone->attachments = $files;

        return $clone;
    }

    public function headers(array $headers): self
    {
        $clone = clone $this;
        $clone->headers = $headers;

        return $clone;
    }

    public function format(string $format): self
    {
        $clone = clone $this;
        $clone->format = $format;

        return $clone;
    }

    public function from(string $addr): self
    {
        $clone = clone $this;
        $clone->fromOverride = $addr;

        return $clone;
    }

    public function isTest(bool $value = true): self
    {
        $clone = clone $this;
        $clone->isTest = $value;

        return $clone;
    }
}
