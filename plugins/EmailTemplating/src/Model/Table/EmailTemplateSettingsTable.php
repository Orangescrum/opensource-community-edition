<?php
declare(strict_types=1);

namespace EmailTemplating\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EmailTemplateSettings Model
 *
 * @property \EmailTemplating\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 *
 * @method \EmailTemplating\Model\Entity\EmailTemplateSetting newEmptyEntity()
 * @method \EmailTemplating\Model\Entity\EmailTemplateSetting newEntity(array $data, array $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateSetting[] newEntities(array $data, array $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateSetting get($primaryKey, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateSetting findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateSetting[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateSetting|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateSetting saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateSetting[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateSetting[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateSetting[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateSetting[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EmailTemplateSettingsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('email_template_settings');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
            'className' => 'Companies',
        ]);
    }

    /**
     * Fetch the settings row for a company, or null if none exists.
     */
    public function forCompany(?int $companyId): ?\EmailTemplating\Model\Entity\EmailTemplateSetting
    {
        if ($companyId === null) {
            return null;
        }

        return $this->find()->where(['company_id' => $companyId])->first();
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('company_id')
            ->notEmptyString('company_id')
            ->add('company_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('sender_signoff')
            ->allowEmptyString('sender_signoff');

        $validator
            ->scalar('sender_name')
            ->maxLength('sender_name', 255)
            ->allowEmptyString('sender_name');

        $validator
            ->scalar('brand_color')
            ->maxLength('brand_color', 16)
            ->allowEmptyString('brand_color');

        $validator
            ->scalar('logo_url')
            ->maxLength('logo_url', 500)
            ->allowEmptyString('logo_url');

        $validator
            ->integer('updated_by')
            ->allowEmptyString('updated_by');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn('company_id', 'Companies'), ['errorField' => 'company_id']);

        return $rules;
    }
}
