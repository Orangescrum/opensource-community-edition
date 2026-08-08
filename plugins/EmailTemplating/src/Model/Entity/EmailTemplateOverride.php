<?php
declare(strict_types=1);

namespace EmailTemplating\Model\Entity;

use Cake\ORM\Entity;

/**
 * EmailTemplateOverride Entity
 *
 * @property int $id
 * @property int $company_id
 * @property string $template_key
 * @property string|null $subject
 * @property string|null $body_html
 * @property string|null $body_text
 * @property bool $is_enabled
 * @property int|null $updated_by
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \EmailTemplating\Model\Entity\Company $company
 */
class EmailTemplateOverride extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'company_id' => true,
        'template_key' => true,
        'subject' => true,
        'body_html' => true,
        'body_text' => true,
        'regions' => true,
        'is_enabled' => true,
        'updated_by' => true,
        'created' => true,
        'modified' => true,
        'company' => true,
    ];

    public function getRegions(): array
    {
        if (empty($this->regions)) {
            return [];
        }
        $decoded = json_decode((string)$this->regions, true);

        return is_array($decoded) ? $decoded : [];
    }
}
