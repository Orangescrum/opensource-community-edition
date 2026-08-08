<?php
declare(strict_types=1);

namespace EmailTemplating\Mailer;

/**
 * Outcome record returned by TemplatedMailer.
 * Drives observability and admin "send test" feedback.
 */
final class SendResult
{
    public const PATH_OVERRIDE = 'override';
    public const PATH_FILE = 'file';
    public const PATH_FAILED = 'failed';

    public const SUBJECT_OVERRIDE = 'override';
    public const SUBJECT_CALLSITE = 'callsite';
    public const SUBJECT_MANIFEST = 'manifest';

    public function __construct(
        public bool $sent,
        public string $path,
        public string $templateKey,
        public ?int $companyId,
        public ?string $subjectSource = null,
        public ?string $error = null,
        public int $durationMs = 0,
    ) {
    }

    public static function override(string $key, ?int $cid, string $subjectSource, int $ms): self
    {
        return new self(true, self::PATH_OVERRIDE, $key, $cid, $subjectSource, null, $ms);
    }

    public static function file(string $key, ?int $cid, int $ms): self
    {
        return new self(true, self::PATH_FILE, $key, $cid, self::SUBJECT_CALLSITE, null, $ms);
    }

    public static function failed(string $key, ?int $cid, string $error, int $ms): self
    {
        return new self(false, self::PATH_FAILED, $key, $cid, null, $error, $ms);
    }
}
