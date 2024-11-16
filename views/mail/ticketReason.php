<?php
/* @var $ticket app\models\Ticket */
/* @var $reason string */
?>

<div style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>New Reason Added to Ticket #<?= $ticket->id ?></h2>
    
    <div style="margin: 20px 0;">
        <strong>Ticket Details:</strong>
        <ul>
            <li>Module: <?= $ticket->module ?></li>
            <li>Issue: <?= $ticket->issue ?></li>
            <li>Status: <?= $ticket->status ?></li>
        </ul>
    </div>

    <div style="margin: 20px 0; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">
        <strong>New Reason:</strong>
        <p style="margin-top: 10px;"><?= nl2br(Html::encode($reason)) ?></p>
    </div>

    <div style="margin-top: 20px;">
        <p>You can view the ticket details by clicking the link below:</p>
        <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['ticket/view', 'id' => $ticket->id]) ?>" 
           style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">
            View Ticket
        </a>
    </div>
</div> 