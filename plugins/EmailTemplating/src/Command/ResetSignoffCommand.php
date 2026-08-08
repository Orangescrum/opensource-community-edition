<?php
declare(strict_types=1);

namespace EmailTemplating\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Locator\LocatorAwareTrait;

class ResetSignoffCommand extends Command
{
    use LocatorAwareTrait;

    public static function defaultName(): string
    {
        return 'email_templating reset_signoff';
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription(
            'Replace literal "Orangescrum" in saved sender signoffs with the {{ companyName }} token.'
        );
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $settings = $this->fetchTable('EmailTemplating.EmailTemplateSettings');
        $updated = 0;

        foreach ($settings->find()->where(['sender_signoff LIKE' => '%Orangescrum%']) as $row) {
            $row->sender_signoff = str_replace('The Orangescrum Team', 'The {{ companyName }} Team', $row->sender_signoff);
            $row->sender_signoff = str_replace('Orangescrum Team', '{{ companyName }} Team', $row->sender_signoff);
            $settings->saveOrFail($row);
            $updated++;
        }

        $overrides = $this->fetchTable('EmailTemplating.EmailTemplateOverrides');
        $overrideCount = 0;
        foreach ($overrides->find()->where(['regions LIKE' => '%Orangescrum%']) as $row) {
            $regions = $row->getRegions();
            $touched = false;
            foreach ($regions as $key => $val) {
                if (is_string($val) && str_contains($val, 'Orangescrum')) {
                    $regions[$key] = str_replace(
                        ['The Orangescrum Team', 'Orangescrum Team'],
                        ['The {{ companyName }} Team', '{{ companyName }} Team'],
                        $val
                    );
                    $touched = true;
                }
            }
            if ($touched) {
                $row->regions = json_encode($regions);
                $overrides->saveOrFail($row);
                $overrideCount++;
            }
        }

        $io->success("Updated {$updated} settings row(s) and {$overrideCount} override row(s).");

        return self::CODE_SUCCESS;
    }
}
