<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tickets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ticket-index">

    <h1><?= Html::encode($this->title) ?></h1>

    

    <p>
        <?= Html::a('Create Ticket', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'module',
            'issue',
            'description',
            'status',
            // 'screenshot',

            [
                'attribute' => 'screenshot',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->screenshot) {
                        return Html::img('data:image/png;base64,' . $model->screenshot, [
                            'alt' => 'Ticket Screenshot',
                            'style' => 'max-width: 100px; max-height: 100px;'
                        ]);
                    }
                    return 'No screenshot';
                },
            ],
            'created_at',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete} {reopen}',
                'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        return Html::a('Delete', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-xs',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this ticket?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                    'reopen' => function ($url, $model, $key) {
                        if ($model->status === 'closed') {
                            return Html::a('Reopen', '#', [
                                'class' => 'btn btn-primary btn-sm',
                                'onclick' => new \yii\web\JsExpression("
                                    reopenTicket(this, {$model->id});
                                    return false;
                                "),
                                'data-id' => $model->id,
                            ]);
                        }
                        return '';
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
<script>
function reopenTicket(button, id) {
    Swal.fire({
        title: 'Reopen Ticket',
        input: 'textarea',
        inputLabel: 'Please provide a reason for reopening',
        inputPlaceholder: 'Enter your reason here...',
        showCancelButton: true,
        confirmButtonText: 'Submit',
        showLoaderOnConfirm: true,
        preConfirm: (reason) => {
            if (!reason) {
                Swal.showValidationMessage('Please enter a reason');
                return false;
            }
            
            console.log('Sending reopen request:', { id, reason });
            
            return $.ajax({
                url: '<?= Yii::$app->urlManager->createUrl(['ticket/reopen']) ?>',
                type: 'POST',
                data: {
                    id: id,
                    reason: reason,
                    _csrf: '<?= Yii::$app->request->csrfToken ?>'
                },
                dataType: 'json'
            })
            .then(response => {
                console.log('Server response:', response);
                if (!response.success) {
                    throw new Error(response.message || 'Failed to reopen ticket');
                }
                return response;
            })
            .catch(error => {
                console.error('Ajax error:', error);
                throw new Error(error.message || 'Failed to reopen ticket');
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.value.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        }
    }).catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to reopen ticket'
        });
    });
}
</script>

<style>
#reopenTicketModal .modal-dialog {
    max-width: 500px;
}

#reopenTicketModal textarea {
    resize: vertical;
    min-height: 120px;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

#reopenTicketForm .form-label {
    font-weight: bold;
}
</style>

<!-- Add this modal form -->
<div class="modal fade" id="reopenTicketModal" tabindex="-1" aria-labelledby="reopenTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reopenTicketModalLabel">Add Reopen Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reopenTicketForm">
                    <input type="hidden" name="ticket_id" id="reopen_ticket_id">
                    <div class="mb-3">
                        <label for="reopen_reason" class="form-label">Reason:</label>
                        <textarea 
                            class="form-control" 
                            id="reopen_reason" 
                            name="reopen_reason" 
                            rows="4" 
                            required 
                            placeholder="Enter your reason..."
                        ></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitReopenReason()">Submit</button>
            </div>
        </div>
    </div>
</div>
