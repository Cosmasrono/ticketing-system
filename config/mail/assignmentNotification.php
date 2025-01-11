<?php
/* @var $developer_name string */
/* @var $ticket_id int */
/* @var $company_name string */
/* @var $description string */
/* @var $module string */
/* @var $issue string */
?>

<div style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>New Ticket Assignment</h2>
    
    <p>Dear <?= $developer_name ?>,</p>
    
    <p>A new ticket has been assigned to you:</p>
    
    <div style="background: #f5f5f5; padding: 15px; margin: 20px 0;">
        <p><strong>Ticket ID:</strong> #<?= $ticket_id ?></p>
        <p><strong>Company:</strong> <?= $company_name ?></p>
        <p><strong>Module:</strong> <?= $module ?></p>
        <p><strong>Issue:</strong> <?= $issue ?></p>
        <p><strong>Description:</strong> <?= $description ?></p>
    </div>
    
    <p>Please review and start working on this ticket.</p>
    
    <p>Best regards,<br>Support Team</p>
</div> 