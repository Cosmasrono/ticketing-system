<?php
use yii\helpers\Html;
?>

<div class="ticket-assigned">
    <h2>Ticket Assignment Notification</h2>
    
    <p>Hello <?= Html::encode($developer->name) ?>,</p>
    
    <p>You have been assigned to ticket #<?= Html::encode($ticket->id) ?>.</p>
    
    <div class="ticket-details">
        <h3>Ticket Details:</h3>
        <ul>
            <li><strong>Title:</strong> <?= Html::encode($ticket->title) ?></li>
            <li><strong>Status:</strong> <?= Html::encode($ticket->status) ?></li>
            <li><strong>Priority:</strong> <?= Html::encode($ticket->priority) ?></li>
            <li><strong>Description:</strong> <?= Html::encode($ticket->description) ?></li>
        </ul>
    </div>
    
    <p>Please review and take necessary action.</p>
    
    <p>Best regards,<br>
    System Administrator</p>
</div> 