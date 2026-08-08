<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit User'), ['action' => 'edit', $user->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete User'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Users'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New User'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="users view content">
            <h3><?= h($user->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Uniq Id') ?></th>
                    <td><?= h($user->uniq_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Btprofile Id') ?></th>
                    <td><?= h($user->btprofile_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Credit Cardtoken') ?></th>
                    <td><?= h($user->credit_cardtoken) ?></td>
                </tr>
                <tr>
                    <th><?= __('Card Number') ?></th>
                    <td><?= h($user->card_number) ?></td>
                </tr>
                <tr>
                    <th><?= __('Expiry Date') ?></th>
                    <td><?= h($user->expiry_date) ?></td>
                </tr>
                <tr>
                    <th><?= __('Email') ?></th>
                    <td><?= h($user->email) ?></td>
                </tr>
                <tr>
                    <th><?= __('Username') ?></th>
                    <td><?= h($user->username) ?></td>
                </tr>
                <tr>
                    <th><?= __('Update Email') ?></th>
                    <td><?= h($user->update_email) ?></td>
                </tr>
                <tr>
                    <th><?= __('Update Random') ?></th>
                    <td><?= h($user->update_random) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($user->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Last Name') ?></th>
                    <td><?= h($user->last_name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Short Name') ?></th>
                    <td><?= h($user->short_name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Photo') ?></th>
                    <td><?= h($user->photo) ?></td>
                </tr>
                <tr>
                    <th><?= __('Photo Reset') ?></th>
                    <td><?= h($user->photo_reset) ?></td>
                </tr>
                <tr>
                    <th><?= __('Query String') ?></th>
                    <td><?= h($user->query_string) ?></td>
                </tr>
                <tr>
                    <th><?= __('Google Id') ?></th>
                    <td><?= h($user->google_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ip') ?></th>
                    <td><?= h($user->ip) ?></td>
                </tr>
                <tr>
                    <th><?= __('Sig') ?></th>
                    <td><?= h($user->sig) ?></td>
                </tr>
                <tr>
                    <th><?= __('Verify String') ?></th>
                    <td><?= h($user->verify_string) ?></td>
                </tr>
                <tr>
                    <th><?= __('Language') ?></th>
                    <td><?= h($user->language) ?></td>
                </tr>
                <tr>
                    <th><?= __('Phone') ?></th>
                    <td><?= h($user->phone) ?></td>
                </tr>
                <tr>
                    <th><?= __('Linkedin Id') ?></th>
                    <td><?= h($user->linkedin_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($user->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Beta') ?></th>
                    <td><?= $this->Number->format($user->is_beta) ?></td>
                </tr>
                <tr>
                    <th><?= __('Istype') ?></th>
                    <td><?= $this->Number->format($user->istype) ?></td>
                </tr>
                <tr>
                    <th><?= __('Isactive') ?></th>
                    <td><?= $this->Number->format($user->isactive) ?></td>
                </tr>
                <tr>
                    <th><?= __('Timezone Id') ?></th>
                    <td><?= $user->timezone_id === null ? '' : $this->Number->format($user->timezone_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Isemail') ?></th>
                    <td><?= $this->Number->format($user->isemail) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Agree') ?></th>
                    <td><?= $this->Number->format($user->is_agree) ?></td>
                </tr>
                <tr>
                    <th><?= __('Usersub Type') ?></th>
                    <td><?= $user->usersub_type === null ? '' : $this->Number->format($user->usersub_type) ?></td>
                </tr>
                <tr>
                    <th><?= __('Est Billing Amount') ?></th>
                    <td><?= $user->est_billing_amount === null ? '' : $this->Number->format($user->est_billing_amount) ?></td>
                </tr>
                <tr>
                    <th><?= __('Desk Notify') ?></th>
                    <td><?= $this->Number->format($user->desk_notify) ?></td>
                </tr>
                <tr>
                    <th><?= __('Active Dashboard Tab') ?></th>
                    <td><?= $this->Number->format($user->active_dashboard_tab) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Moderator') ?></th>
                    <td><?= $this->Number->format($user->is_moderator) ?></td>
                </tr>
                <tr>
                    <th><?= __('Show Default Inner') ?></th>
                    <td><?= $this->Number->format($user->show_default_inner) ?></td>
                </tr>
                <tr>
                    <th><?= __('Updated By') ?></th>
                    <td><?= $this->Number->format($user->updated_by) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Online') ?></th>
                    <td><?= $user->is_online === null ? '' : $this->Number->format($user->is_online) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Dst') ?></th>
                    <td><?= $this->Number->format($user->is_dst) ?></td>
                </tr>
                <tr>
                    <th><?= __('Language Id') ?></th>
                    <td><?= $this->Number->format($user->language_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Agree Tosp') ?></th>
                    <td><?= $this->Number->format($user->is_agree_tosp) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Receive Update') ?></th>
                    <td><?= $this->Number->format($user->is_receive_update) ?></td>
                </tr>
                <tr>
                    <th><?= __('Outer Signup') ?></th>
                    <td><?= $this->Number->format($user->outer_signup) ?></td>
                </tr>
                <tr>
                    <th><?= __('Time Format') ?></th>
                    <td><?= $this->Number->format($user->time_format) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Dummy') ?></th>
                    <td><?= $this->Number->format($user->is_dummy) ?></td>
                </tr>
                <tr>
                    <th><?= __('Keep Hover Effect') ?></th>
                    <td><?= $this->Number->format($user->keep_hover_effect) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Zapaction') ?></th>
                    <td><?= $user->is_zapaction === null ? '' : $this->Number->format($user->is_zapaction) ?></td>
                </tr>
                <tr>
                    <th><?= __('Dt Created') ?></th>
                    <td><?= h($user->dt_created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Dt Updated') ?></th>
                    <td><?= h($user->dt_updated) ?></td>
                </tr>
                <tr>
                    <th><?= __('Dt Last Login') ?></th>
                    <td><?= h($user->dt_last_login) ?></td>
                </tr>
                <tr>
                    <th><?= __('Dt Last Logout') ?></th>
                    <td><?= h($user->dt_last_logout) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Gaccess Token') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($user->gaccess_token)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('One Tap Token') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($user->one_tap_token)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
