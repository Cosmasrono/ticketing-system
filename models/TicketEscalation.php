<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class TicketEscalation extends ActiveRecord
{
    public static function tableName()
    {
        return 'ticket_escalation';
    }

    public function rules()
    {
        return [
            [['ticket_id', 'escalated_by', 'escalated_to'], 'required'],
            [['ticket_id', 'escalated_by', 'escalated_to'], 'integer'],
            [['reason'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['ticket_id'], 'exist', 'skipOnError' => true, 
                'targetClass' => Ticket::class, 
                'targetAttribute' => ['ticket_id' => 'id']],
            [['escalated_by'], 'exist', 'skipOnError' => true, 
                'targetClass' => User::class, 
                'targetAttribute' => ['escalated_by' => 'id']],
            [['escalated_to'], 'exist', 'skipOnError' => true, 
                'targetClass' => User::class, 
                'targetAttribute' => ['escalated_to' => 'id']]
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function getTicket()
    {
        return $this->hasOne(Ticket::class, ['id' => 'ticket_id']);
    }

    public function getEscalatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'escalated_by']);
    }

    public function getEscalatedToUser()
    {
        return $this->hasOne(User::class, ['id' => 'escalated_to']); 
    }
}