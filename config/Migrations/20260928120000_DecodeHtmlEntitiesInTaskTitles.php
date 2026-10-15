<?php

use Migrations\AbstractMigration;

/**
 * Store task titles as plain text. Titles are escaped when they are output, so a
 * title saved already encoded ("&quot;My Tasks&quot; ...") was escaped a second
 * time and showed its entities in the task list. This decodes the quote,
 * apostrophe and ampersand entities in easycases.title back to the characters
 * they stand for, repeating until stable so "&amp;quot;" also comes back as '"'.
 *
 * &lt; and &gt; are left alone: turning them into real angle brackets would only
 * be safe if every place a title is printed escaped it, and that is not worth
 * betting on for a cosmetic fix.
 */
class DecodeHtmlEntitiesInTaskTitles extends AbstractMigration
{
    /** Entity => character. strtr() replaces them all in one pass per loop. */
    private const ENTITIES = [
        '&quot;' => '"',
        '&#34;' => '"',
        '&#034;' => '"',
        '&#39;' => "'",
        '&#039;' => "'",
        '&apos;' => "'",
        '&amp;' => '&',
    ];

    public function up(): void
    {
        $rows = $this->fetchAll(
            "SELECT id, title FROM public.easycases WHERE title ~ '&(quot|apos|amp|#0?3[49]);'"
        );

        foreach ($rows as $row) {
            $title = (string)$row['title'];
            $decoded = $this->decode($title);
            if ($decoded === $title) {
                continue;
            }
            $this->getQueryBuilder()
                ->update('public.easycases')
                ->set('title', $decoded)
                ->where(['id' => (int)$row['id']])
                ->execute();
        }
    }

    public function down(): void
    {
        // Irreversible by design: the encoded form was the bug.
    }

    private function decode(string $title): string
    {
        // A handful of passes covers anything encoded repeatedly; a legitimate
        // title will not nest deeper than that.
        for ($i = 0; $i < 5; $i++) {
            $next = strtr($title, self::ENTITIES);
            if ($next === $title) {
                break;
            }
            $title = $next;
        }

        return $title;
    }
}
