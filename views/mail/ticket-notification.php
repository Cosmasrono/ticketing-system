<?php
use yii\helpers\Html;
?>

<div style="font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 0 auto;">
    <h2 style="color: #333;">New Support Ticket Alert</h2>
    
    <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p>A new support ticket has been raised by <?= Html::encode($ticket->company_email) ?></p>
        <p><strong>Ticket ID:</strong> #<?= $ticket->id ?></p>
        <p><strong>Issue:</strong> <?= Html::encode($ticket->issue) ?></p>
    </div>
    
    <div style="margin-top: 20px;">
        <?= Html::a('View Ticket', Yii::$app->urlManager->createAbsoluteUrl(['/ticket/view', 'id' => $ticket->id]), [
            'style' => 'background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'
        ]) ?>
    </div>
</div> 