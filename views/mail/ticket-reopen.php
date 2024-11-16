<?php
use yii\helpers\Html;
?>

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
        <h2 style="color: #0056b3; margin-top: 0;">Ticket #<?= $ticket->id ?> Has Been Reopened</h2>
        
        <?php if ($recipientType === 'Assigned Developer'): ?>
            <p>A ticket assigned to you has been reopened.</p>
        <?php else: ?>
            <p>A ticket has been reopened in the system.</p>
        <?php endif; ?>
    </div>

    <div style="margin: 20px 0;">
        <h3 style="color: #495057;">Ticket Details:</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><strong>Module:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><?= Html::encode($ticket->module) ?></td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><strong>Issue:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><?= Html::encode($ticket->issue) ?></td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><strong>Description:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><?= Html::encode($ticket->description) ?></td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><strong>Status:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><?= Html::encode($ticket->status) ?></td>
            </tr>
        </table>
    </div>

    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h3 style="color: #495057; margin-top: 0;">Reopen Reason:</h3>
        <p style="margin-bottom: 0;"><?= nl2br(Html::encode($reason)) ?></p>
    </div>

    <div style="text-align: center;">
        <a href="<?= $viewUrl ?>" 
           style="display: inline-block; padding: 10px 20px; background-color: #0056b3; color: white; text-decoration: none; border-radius: 5px;">
            View Ticket Details
        </a>
    </div>

    <div style="margin-top: 20px; text-align: center; color: #6c757d; font-size: 0.9em;">
        <p>This is an automated notification. Please do not reply to this email.</p>
    </div>
</div> 