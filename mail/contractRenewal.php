<?php
use yii\helpers\Html;

/* @var $company app\models\Company */
/* @var $renewal app\models\ContractRenewal */
/* @var $admin app\models\User */
?>

<div>
    <h2>Contract Renewal Approved</h2>
    
    <p>Dear <?= Html::encode($admin->name) ?>,</p>
    
    <p>Your contract renewal request has been approved. Here are the details:</p>
    
    <ul>
        <li>Company: <?= Html::encode($company->company_name) ?></li>
        <li>Previous End Date: <?= Yii::$app->formatter->asDate($renewal->current_end_date) ?></li>
        <li>New End Date: <?= Yii::$app->formatter->asDate($renewal->new_end_date) ?></li>
        <li>Extension Period: <?= Html::encode($renewal->extension_period) ?> months</li>
    </ul>
    
    <p>Thank you for continuing to work with us!</p>
    
    <p>Best regards,<br><?= Html::encode(Yii::$app->name) ?></p>
</div> 