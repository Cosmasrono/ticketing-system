<?php
/* @var $developer_name string */
/* @var $ticket_id integer */
/* @var $company_name string */
/* @var $description string */
/* @var $module string */
/* @var $issue string */
?>

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h2>New Ticket Assignment</h2>
    
    <p>Hello <?= $developer_name ?>,</p>
    
    <p>You have been assigned a new support ticket:</p>
    
    <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p><strong>Ticket ID:</strong> #<?= $ticket_id ?></p>
        <p><strong>Company:</strong> <?= $company_name ?></p>
        <p><strong>Module:</strong> <?= $module ?></p>
        <p><strong>Issue:</strong> <?= $issue ?></p>
        <p><strong>Description:</strong> <?= $description ?></p>
    </div>
    
    <p>Please review and begin working on this ticket as soon as possible.</p>
    
    <div style="margin-top: 20px;">
        <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['ticket/view', 'id' => $ticket_id]) ?>" 
           style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            View Ticket
        </a>
    </div>
    
    <p style="margin-top: 20px; color: #666;">
        This is an automated message. Please do not reply to this email.
    </p>
</div> 