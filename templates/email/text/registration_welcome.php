<?php
/**
 * Registration welcome email - text version
 * Expects $userName, $companyName, $loginUrl
 */
$notifyEmail = \Cake\Core\Configure::read('AppEmail.notify_email')
    ?: \Cake\Core\Configure::read('AppEmail.from_email', '');
?>
<?php echo __('Hi'); ?> <?php echo $userName; ?>,

<?php echo __('Thank you for registering with Orangescrum! Your account has been successfully created.'); ?>

<?php echo __('Your Company:'); ?> <?php echo $companyName; ?>

<?php echo __('You can now log in to your account and start managing your projects, tasks, and team collaboration.'); ?>

<?php if (!empty($loginUrl)): ?>
<?php echo __('Login to Your Account:'); ?> <?php echo $loginUrl; ?>

<?php endif; ?>
<?php echo __('If you have any questions or need assistance, please don\'t hesitate to contact our support team at'); ?> <?php echo $notifyEmail; ?>

<?php echo __('Regards'); ?>,
<?php echo __('The Orangescrum Team'); ?>

