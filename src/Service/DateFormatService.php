<?php

declare(strict_types=1);

namespace App\Service;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;

/**
 * Company-wide date display format.
 *
 * This is the one place that knows which display formats exist and how each one
 * maps to a PHP date() pattern, a day-and-month-only pattern, and the
 * bootstrap-datepicker and client-side equivalents. Views and helpers ask this
 * service to format a date; they never hard-code a pattern.
 *
 * Presentation only: nothing here touches stored values, API payloads or the
 * machine-readable dates the backend parses (Y-m-d, Y-m-d H:i:s).
 */
class DateFormatService
{
    /** Cache key prefix, one entry per company. */
    private const CACHE_PREFIX = 'company_date_format_';

    /**
     * Allowed formats keyed by their stored name.
     *
     * php: pattern for date(). Uses only the tokens d, m, M, Y plus literal
     * separators, so the client-side formatter can mirror it exactly.
     * datepicker: bootstrap-datepicker format for the same layout.
     *
     * @var array<string, array{label: string, php: string, datepicker: string}>
     */
    public const FORMATS = [
        'DD-MM-YYYY' => ['label' => 'DD-MM-YYYY', 'php' => 'd-m-Y', 'datepicker' => 'dd-mm-yyyy'],
        'MM-DD-YYYY' => ['label' => 'MM-DD-YYYY', 'php' => 'm-d-Y', 'datepicker' => 'mm-dd-yyyy'],
        'YYYY-MM-DD' => ['label' => 'YYYY-MM-DD', 'php' => 'Y-m-d', 'datepicker' => 'yyyy-mm-dd'],
        'DD/MM/YYYY' => ['label' => 'DD/MM/YYYY', 'php' => 'd/m/Y', 'datepicker' => 'dd/mm/yyyy'],
        'MM/DD/YYYY' => ['label' => 'MM/DD/YYYY', 'php' => 'm/d/Y', 'datepicker' => 'mm/dd/yyyy'],
        'YYYY/MM/DD' => ['label' => 'YYYY/MM/DD', 'php' => 'Y/m/d', 'datepicker' => 'yyyy/mm/dd'],
        'DD-MMM-YYYY' => ['label' => 'DD-MMM-YYYY', 'php' => 'd-M-Y', 'datepicker' => 'dd-M-yyyy'],
        'MMM DD, YYYY' => ['label' => 'MMM DD, YYYY', 'php' => 'M d, Y', 'datepicker' => 'M dd, yyyy'],
    ];

    /** Used for new companies and any company whose stored value is unknown. */
    public const DEFAULT_FORMAT = 'MMM DD, YYYY';

    /**
     * Options for a select control: stored name => "Label (example)".
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        // Example is 17 Nov 2026 at noon, so no timezone can shift the day.
        $example = mktime(12, 0, 0, 11, 17, 2026);
        $options = [];
        foreach (self::FORMATS as $key => $def) {
            $options[$key] = $def['label'] . '  (' . date($def['php'], $example) . ')';
        }

        return $options;
    }

    public static function isValid(?string $format): bool
    {
        return $format !== null && array_key_exists($format, self::FORMATS);
    }

    /**
     * Stored format name for a company, falling back to the default.
     * Cached per company; the cache is cleared whenever the setting changes.
     */
    public static function getCompanyFormat(?int $companyId = null): string
    {
        $companyId = self::resolveCompanyId($companyId);
        if ($companyId <= 0) {
            return self::DEFAULT_FORMAT;
        }

        $key = self::CACHE_PREFIX . $companyId;
        $cached = Cache::read($key);
        if (is_string($cached) && self::isValid($cached)) {
            return $cached;
        }

        $stored = TableRegistry::getTableLocator()->get('Companies')
            ->find()
            ->select(['date_format'])
            ->where(['id' => $companyId])
            ->disableHydration()
            ->first();

        $format = self::isValid($stored['date_format'] ?? null) ? $stored['date_format'] : self::DEFAULT_FORMAT;
        Cache::write($key, $format);

        return $format;
    }

    /**
     * Saves the company's format. Returns false when the format is not allowed.
     * Authorisation is the caller's job.
     */
    public static function setCompanyFormat(int $companyId, string $format): bool
    {
        if ($companyId <= 0 || !self::isValid($format)) {
            return false;
        }

        TableRegistry::getTableLocator()->get('Companies')
            ->updateAll(['date_format' => $format], ['id' => $companyId]);
        Cache::delete(self::CACHE_PREFIX . $companyId);

        return true;
    }

    /** PHP date() pattern for the company, e.g. "d-m-Y". */
    public static function phpPattern(?int $companyId = null): string
    {
        return self::FORMATS[self::getCompanyFormat($companyId)]['php'];
    }

    /**
     * Same layout without the year, e.g. "d-m" or "M d". Used where the year is
     * implied (the current year) or shown elsewhere in the same string.
     */
    public static function dayMonthPattern(?int $companyId = null): string
    {
        $pattern = self::phpPattern($companyId);

        // Drop the year token together with the separator in front of it.
        return trim(preg_replace('/[^A-Za-z]*Y[^A-Za-z]*/', '', $pattern) ?? $pattern);
    }

    /**
     * Formats a date for display. Accepts a unix timestamp or any string that
     * strtotime() understands. Returns '' for empty or zero dates so callers can
     * substitute their own placeholder.
     *
     * @param int|string|null $value
     */
    public static function format($value, ?int $companyId = null): string
    {
        return self::formatWith(self::phpPattern($companyId), $value);
    }

    /** Day and month only, e.g. "17-11" or "Nov 17". */
    public static function formatDayMonth($value, ?int $companyId = null): string
    {
        return self::formatWith(self::dayMonthPattern($companyId), $value);
    }

    /** Formats with an explicit date() pattern. Shared by the methods above. */
    public static function formatWith(string $pattern, $value): string
    {
        $ts = self::toTimestamp($value);

        return $ts === null ? '' : date($pattern, $ts);
    }

    /**
     * Config handed to the browser as window.COMPANY_DATE_FORMAT, so JavaScript
     * formats dates with the same pattern as PHP.
     *
     * @return array{name: string, php: string, dayMonth: string, datepicker: string}
     */
    public static function clientConfig(?int $companyId = null): array
    {
        $name = self::getCompanyFormat($companyId);

        return [
            'name' => $name,
            'php' => self::FORMATS[$name]['php'],
            'dayMonth' => self::dayMonthPattern($companyId),
            'datepicker' => self::FORMATS[$name]['datepicker'],
        ];
    }

    /**
     * Reads a user-entered date that is written exactly in the company's format and
     * returns it as Y-m-d. Returns null for anything else, so values in other layouts
     * ("Nov 17, 2026", ISO) pass through untouched and keep their existing handling.
     * The round-trip check rejects loose input such as "11/5/2026" for MM/DD/YYYY.
     */
    public static function parseStrict($value, ?int $companyId = null): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $pattern = self::phpPattern($companyId);
        $dt = \DateTimeImmutable::createFromFormat('!' . $pattern, $value);
        if ($dt === false || $dt->format($pattern) !== $value) {
            return null;
        }

        return $dt->format('Y-m-d');
    }

    /**
     * Converts company-format date fields in a request array to Y-m-d, recursively.
     * Only keys that look like date fields are considered, and only values that pass
     * parseStrict(), so other inputs are never changed.
     *
     * @param array<mixed> $data
     * @return array<mixed>
     */
    public static function normalizeInputs(array $data, ?int $companyId = null): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::normalizeInputs($value, $companyId);
                continue;
            }
            if (is_string($value) && is_string($key) && self::isDateFieldName($key)) {
                $parsed = self::parseStrict($value, $companyId);
                if ($parsed !== null) {
                    $data[$key] = $parsed;
                }
            }
        }

        return $data;
    }

    /** Field names that carry dates in this app's forms and filters. */
    private static function isDateFieldName(string $key): bool
    {
        return (bool)preg_match('/dat|dt|due|frm|^to$|_to$|^start|^end|strt/i', $key);
    }

    /** @param int|string|null $value */
    private static function toTimestamp($value): ?int
    {
        if (is_int($value)) {
            return $value;
        }
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '' || str_starts_with($value, '0000-00-00') || $value === '1970-01-01') {
            return null;
        }
        $ts = strtotime($value);

        return $ts === false ? null : $ts;
    }

    private static function resolveCompanyId(?int $companyId): int
    {
        if ($companyId !== null) {
            return $companyId;
        }

        return defined('SES_COMP') ? (int)SES_COMP : 0;
    }
}
