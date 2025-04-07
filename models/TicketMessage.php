<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "ticket_message".
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $sender_id
 * @property int $recipient_id
 * @property string $subject
 * @property string $message
 * @property int $sent_at
 * @property int|null $read_at
 * @property int $admin_viewed
 * @property string $message_type
 * @property int $is_internal
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $recipient
 * @property User $sender
 * @property Ticket $ticket
 */
class TicketMessage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ticket_message}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ticket_id', 'sender_id', 'recipient_id', 'subject', 'message'], 'required'],
            [['ticket_id', 'sender_id', 'recipient_id', 'sent_at', 'read_at', 'admin_viewed', 'is_internal'], 'integer'],
            [['message'], 'string'],
            [['subject', 'message_type'], 'string', 'max' => 255],
            [['message_type'], 'default', 'value' => 'user_message'],
            [['admin_viewed', 'is_internal'], 'default', 'value' => 0],
            [['sent_at'], 'default', 'value' => time()],
            [['ticket_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ticket::class, 'targetAttribute' => ['ticket_id' => 'id']],
            [['sender_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['sender_id' => 'id']],
            [['recipient_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['recipient_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ticket_id' => 'Ticket ID',
            'sender_id' => 'From',
            'recipient_id' => 'To',
            'subject' => 'Subject',
            'message' => 'Message',
            'sent_at' => 'Sent At',
            'read_at' => 'Read At',
            'admin_viewed' => 'Admin Viewed',
            'message_type' => 'Message Type',
            'is_internal' => 'Internal Note',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Recipient]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipient()
    {
        return $this->hasOne(User::class, ['id' => 'recipient_id']);
    }

    /**
     * Gets query for [[Sender]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSender()
    {
        return $this->hasOne(User::class, ['id' => 'sender_id']);
    }

    /**
     * Gets query for [[Ticket]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTicket()
    {
        return $this->hasOne(Ticket::class, ['id' => 'ticket_id']);
    }
}
