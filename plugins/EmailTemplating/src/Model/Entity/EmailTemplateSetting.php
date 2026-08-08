<?php
declare(strict_types=1);

namespace EmailTemplating\Model\Entity;

use Cake\ORM\Entity;

/**
 * EmailTemplateSetting Entity
 *
 * @property int $id
 * @property int $company_id
 * @property string|null $sender_signoff
 * @property string|null $sender_name
 * @property string|null $brand_color
 * @property string|null $logo_url
 * @property bool $include_header
 * @property bool $include_footer
 * @property string|null $header_html
 * @property string|null $footer_html
 * @property int|null $updated_by
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \EmailTemplating\Model\Entity\Company $company
 */
class EmailTemplateSetting extends Entity
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
        'sender_signoff' => true,
        'sender_name' => true,
        'brand_color' => true,
        'logo_url' => true,
        'include_header' => true,
        'include_footer' => true,
        'header_html' => true,
        'footer_html' => true,
        'updated_by' => true,
        'created' => true,
        'modified' => true,
        'company' => true,
    ];
}
