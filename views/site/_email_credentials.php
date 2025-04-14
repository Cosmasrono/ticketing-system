<?php 
use yii\helpers\Html;

/* @var $company */
/* @var $password */
/* @var $token */
/* @var $resetUrl */
?>

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h2>Welcome to <?= Html::encode(Yii::$app->name) ?></h2>
    
    <?php if (isset($company) && $company !== null): ?>
        <p>Dear <?= Html::encode($company->company_name ?? 'User') ?>,</p>
    <?php else: ?>
        <p>Dear User,</p>
    <?php endif; ?>

    <p>Your account has been created successfully. Please click the button below to set your password:</p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?= Html::encode($resetUrl) ?>"
           style="background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px;">
            Set Your Password
        </a>
    </div>

    <p><strong>Your temporary password:</strong> <?= Html::encode($password) ?></p>
    <p>(You'll need this temporary password when setting your new password)</p>

    <p style="color: #dc3545;"><strong>Important:</strong> This link will only work once and you must set your password immediately for security purposes.</p>
</div>