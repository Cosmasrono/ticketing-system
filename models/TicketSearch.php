<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Ticket;
use Yii;

class TicketSearch extends Model
{
    public $company_name;
    
    public function rules()
    {
        return [
            [['company_name'], 'safe'],
        ];
    }
    
    public function search($params)
    {
        $query = Ticket::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // Load the search parameters
        $this->load($params);

        // Debugging: Log the company name
        Yii::info("Searching for company name: " . $this->company_name, __METHOD__);

        // Validate the input
        if (!$this->validate()) {
            // If validation fails, return an empty data provider
            return new ActiveDataProvider([
                'query' => Ticket::find()->where('0=1'), // No results
            ]);
        }

        // Filter by company name
        $query->andFilterWhere(['like', 'company_name', $this->company_name]);

        return $dataProvider;
    }
}