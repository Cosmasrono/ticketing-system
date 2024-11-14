<?php
use yii\helpers\Html;

$this->title = 'Developer Ticket Assignments';
?>

<div class="developer-tickets">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <?php foreach ($developerTicketCounts as $developer): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <?= Html::encode($developer['name']) ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Assigned Tickets: <?= $developer['ticket_count'] ?></h5>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div> 