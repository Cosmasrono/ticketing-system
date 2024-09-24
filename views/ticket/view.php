<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Company Tickets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ticket-view">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php Pjax::begin(['id' => 'ticket-pjax']); ?>
    <?php if ($hasResults): ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id',
                'title',
                'status',
                [
                    'attribute' => 'company_email',
                    'value' => function ($model) use ($companyEmail) {
                        return $companyEmail;
                    },
                ],
                'created_at:datetime',
                [
                    'label' => 'Remaining Time',
                    'value' => function ($model) {
                        return '<span class="remaining-time" data-seconds="' . $model->getRemainingTimeInSeconds() . '"></span>';
                    },
                    'format' => 'raw',
                ],
            ],
        ]); ?>
    <?php else: ?>
        <div class="alert alert-info">
            No tickets found for <?= Html::encode($companyEmail) ?>. 
            <?= Html::a('Create a new ticket', ['create'], ['class' => 'alert-link']) ?>
        </div>
    <?php endif; ?>
    <?php Pjax::end(); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateTimers() {
        document.querySelectorAll('.remaining-time').forEach(function(element) {
            let seconds = parseInt(element.getAttribute('data-seconds'));
            if (seconds > 0) {
                seconds--;
                element.setAttribute('data-seconds', seconds);
                let hours = Math.floor(seconds / 3600);
                let minutes = Math.floor((seconds % 3600) / 60);
                let secs = seconds % 60;
                element.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            } else {
                element.textContent = '00:00:00';
            }
        });
    }

    setInterval(updateTimers, 1000);
    updateTimers(); // Initial call to set the timers immediately
});
</script>