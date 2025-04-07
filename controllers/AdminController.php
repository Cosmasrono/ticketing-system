<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Ticket;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

class AdminController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->role === 'admin';
                        }
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new \yii\web\ForbiddenHttpException('You are not allowed to access this page');
                }
            ],
        ];
    }

    public function actionIndex()
    {
        $query = Ticket::find()
            ->joinWith('createdBy');

        // Calculate ticket counts by status
        $ticketCounts = Ticket::find()
            ->select(['status', 'COUNT(*) as count'])
            ->groupBy('status')
            ->indexBy('status')
            ->column();

        $dataProvider = new ActiveDataProvider([
            'query' => $query->with('creator'),
            'pagination' => [
                'pageSize' => 30,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'ticketCounts' => $ticketCounts,
        ]);
    }

    public function actionDashboard()
    {
        return $this->render('dashboard');
    }

    public function actionTicketMessages()
    {
        // Create query to find all messages (non-internal and internal)
        $query = \app\models\TicketMessage::find()
            ->joinWith(['sender', 'recipient', 'ticket'])
            ->orderBy(['sent_at' => SORT_DESC]);

        // Create data provider with pagination
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'sent_at' => SORT_DESC
                ]
            ],
        ]);

        // Count unread messages (admin_viewed = 0)
        $unreadCount = \app\models\TicketMessage::find()
            ->where(['admin_viewed' => 0])
            ->count();

        return $this->render('ticket-messages', [
            'dataProvider' => $dataProvider,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * View a specific message and mark it as viewed by admin
     * @param integer $id The message ID
     * @return array Response in JSON format
     */
    public function actionViewMessage($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $message = \app\models\TicketMessage::findOne($id);
            
            if (!$message) {
                return [
                    'success' => false,
                    'message' => 'Message not found'
                ];
            }
            
            // Mark as viewed by admin if not already
            if (!$message->admin_viewed) {
                $message->admin_viewed = 1;
                $message->save(false); // Skip validation
            }
            
            // Get sender and recipient details
            $sender = $message->sender;
            $recipient = $message->recipient;
            
            return [
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'ticket_id' => $message->ticket_id,
                    'subject' => $message->subject,
                    'message' => nl2br(\yii\helpers\Html::encode($message->message)),
                    'sent_at' => \Yii::$app->formatter->asDatetime($message->sent_at),
                    'sender_name' => $sender ? $sender->name : 'Unknown',
                    'recipient_name' => $recipient ? $recipient->name : 'Unknown',
                    'message_type' => $message->message_type,
                    'is_internal' => (bool)$message->is_internal,
                ],
            ];
        } catch (\Exception $e) {
            \Yii::error('Error viewing message: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }
}
