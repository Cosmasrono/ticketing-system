<?php
use yii\helpers\Html;

/* @var $model app\models\Invitation */
?>

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h2>Welcome to <?= Yii::$app->name ?></h2>
    
    <p>Dear <?= Html::encode($model->company_name) ?>,</p>
    
    <p>You have been invited to join our platform as a <strong><?= Html::encode($model->role) ?></strong> 
       for the <strong><?= Html::encode($model->module) ?></strong> module.</p>
    
    <div style="margin: 25px 0;">
        <a href="<?= $model->getSignupUrl() ?>" 
           style="background-color: #4CAF50; color: white; padding: 12px 25px; 
                  text-decoration: none; border-radius: 5px; display: inline-block;">
            Complete Your Registration
        </a>
    </div>
    
    <p style="color: #666;">
        If the button doesn't work, copy and paste this link into your browser:<br>
        <span style="color: #0066cc;"><?= $model->getSignupUrl() ?></span>
    </p>
    
    <p style="color: #999; font-size: 0.9em;">
        This invitation will expire in <?= $model->getRemainingTime() ?> minutes.
    </p>
</div> 