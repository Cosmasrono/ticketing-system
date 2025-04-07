<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\TicketMessage;

/**
 * TicketMessageSearch represents the model behind the search form of `app\models\TicketMessage`.
 */
class TicketMessageSearch extends TicketMessage
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'ticket_id', 'sender_id', 'recipient_id', 'sent_at', 'read_at', 'admin_viewed', 'is_internal'], 'integer'],
            [['subject', 'message', 'message_type'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = TicketMessage::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'sent_at' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'sender_id' => $this->sender_id,
            'recipient_id' => $this->recipient_id,
            'sent_at' => $this->sent_at,
            'read_at' => $this->read_at,
            'admin_viewed' => $this->admin_viewed,
            'is_internal' => $this->is_internal,
        ]);

        $query->andFilterWhere(['like', 'subject', $this->subject])
            ->andFilterWhere(['like', 'message', $this->message])
            ->andFilterWhere(['like', 'message_type', $this->message_type]);

        return $dataProvider;
    }
} 