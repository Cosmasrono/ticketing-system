<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $ticket app\models\Ticket */
/* @var $developer app\models\User */
?>

<div style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>New Ticket Assignment</h2>
    <p>Hello <?= Html::encode($developer->name) ?>,</p>
    
    <p>A new ticket has been assigned to you:</p>
    
    <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p><strong>Ticket ID:</strong> #<?= Html::encode($ticket->id) ?></p>
        <p><strong>Module:</strong> <?= Html::encode($ticket->module) ?></p>
        <p><strong>Issue:</strong> <?= Html::encode($ticket->issue) ?></p>
        <p><strong>Description:</strong> <?= Html::encode($ticket->description) ?></p>
        <p><strong>Company:</strong> <?= Html::encode($ticket->company->name) ?></p>
    </div>

    <p>Please review and begin working on this ticket at your earliest convenience.</p>
    
    <p>You can view the ticket details by clicking the button below:</p>
    
    <a href="<?= Url::to(['ticket/view', 'id' => $ticket->id], true) ?>" 
       style="background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
        View Ticket
    </a>
</div> 