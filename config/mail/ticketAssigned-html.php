<?php
use yii\helpers\Html;
?>

<div class="ticket-assigned">
    <h2>New Ticket Assignment</h2>
    
    <p>Hello <?= Html::encode($developer->username) ?>,</p>
    
    <p>You have been assigned to Ticket #<?= $ticket->id ?>.</p>
    
    <h3>Ticket Details:</h3>
    <ul>
        <li><strong>Title:</strong> <?= Html::encode($ticket->title) ?></li>
        <li><strong>Description:</strong> <?= Html::encode($ticket->description) ?></li>
        <li><strong>Status:</strong> <?= Html::encode($ticket->status) ?></li>
    </ul>
    
    <p>Please review and take necessary action.</p>
    
    <p>Best regards,<br>
    System Administrator</p>
</div> 