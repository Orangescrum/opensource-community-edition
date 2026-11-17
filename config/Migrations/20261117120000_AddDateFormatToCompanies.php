<?php

use Migrations\AbstractMigration;

/**
 * Company-wide date display format, chosen by the company admin on My Company.
 *
 * Only the presentation of dates depends on this column. Stored values, API
 * payloads and date arithmetic keep their existing formats. The default is the
 * format the application already shows most often ("Nov 17, 2026"), so existing
 * companies see no change until an admin picks something else.
 */
class AddDateFormatToCompanies extends AbstractMigration
{
    public function up(): void
    {
        $this->table('companies')
            ->addColumn('date_format', 'string', [
                'limit' => 20,
                'null' => false,
                'default' => 'MMM DD, YYYY',
            ])
            ->update();
    }

    public function down(): void
    {
        $this->table('companies')
            ->removeColumn('date_format')
            ->update();
    }
}
