<?php
declare(strict_types=1);

namespace EmailTemplating\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EmailTemplateOverrides Model
 *
 * @property \EmailTemplating\Model\Table\CompaniesTable&\Cake\ORM\Association\BelongsTo $Companies
 *
 * @method \EmailTemplating\Model\Entity\EmailTemplateOverride newEmptyEntity()
 * @method \EmailTemplating\Model\Entity\EmailTemplateOverride newEntity(array $data, array $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateOverride[] newEntities(array $data, array $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateOverride get($primaryKey, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateOverride findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateOverride patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateOverride[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateOverride|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateOverride saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateOverride[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateOverride[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateOverride[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \EmailTemplating\Model\Entity\EmailTemplateOverride[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EmailTemplateOverridesTable extends Table
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

        $this->setTable('email_template_overrides');
        $this->setDisplayField('template_key');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Companies', [
            'foreignKey' => 'company_id',
            'joinType' => 'INNER',
            'className' => 'Companies',
        ]);
    }

    /**
     * Lookup an enabled override for a (company, template) pair.
     * Returns null when no override exists or the row is disabled.
     */
    public function findResolved(string $templateKey, ?int $companyId): ?\EmailTemplating\Model\Entity\EmailTemplateOverride
    {
        if ($companyId === null) {
            return null;
        }

        return $this->find()
            ->where([
                'company_id' => $companyId,
                'template_key' => $templateKey,
                'is_enabled' => true,
            ])
            ->first();
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
            ->notEmptyString('company_id');

        $validator
            ->scalar('template_key')
            ->maxLength('template_key', 255)
            ->requirePresence('template_key', 'create')
            ->notEmptyString('template_key');

        $validator
            ->scalar('subject')
            ->maxLength('subject', 255)
            ->allowEmptyString('subject');

        $validator
            ->scalar('body_html')
            ->allowEmptyString('body_html');

        $validator
            ->scalar('body_text')
            ->allowEmptyString('body_text');

        $validator
            ->boolean('is_enabled')
            ->notEmptyString('is_enabled');

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
