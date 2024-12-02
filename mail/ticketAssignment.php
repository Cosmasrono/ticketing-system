<?php
/* @var $developer string */
/* @var $ticket object */
?>

<div style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>New Ticket Assignment</h2>
    <p>Hello <?= $developer ?>,</p>
    <p>A new ticket has been assigned to you:</p>
    
    <div style="background: #f5f5f5; padding: 15px; margin: 20px 0;">
        <p><strong>Ticket ID:</strong> <?= $ticket->id ?></p>
        <p><strong>Issue:</strong> <?= $ticket->issue ?></p>
        <p><strong>Module:</strong> <?= $ticket->module ?></p>
        <p><strong>Description:</strong> <?= $ticket->description ?></p>
    </div>
    
    <p>Please review and take necessary action.</p>
</div> 