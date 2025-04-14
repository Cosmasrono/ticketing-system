<?php

namespace app\jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class SendTicketAssignmentEmail extends BaseObject implements JobInterface
{
    public $ticketId;
    public $developerId;

    public function execute($queue)
    {
        $ticket = Ticket::findOne($this->ticketId);
        $developer = User::findOne($this->developerId);

        if ($ticket && $developer && $developer->email) {
            Yii::$app->mailer->compose(['html' => 'ticketAssigned-html'], [
                'ticket' => $ticket,
                'developer' => $developer,
            ])
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setTo($developer->email)
            ->setSubject('Ticket #' . $ticket->id . ' Assigned to You')
            ->send();
        }
    }
} 