<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Request Contract Renewal for ' . $company->company_name;
?>

<div class="renew-contract">
    <div class="row">
        <div class="col-lg-6 offset-lg-3">
            <div class="card shadow">
                <div class="card-header bg-warning">
                    <h3 class="card-title mb-0"><?= Html::encode($this->title) ?></h3>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(['id' => 'renew-contract-form']); ?>

                    <div class="mb-3">
                        <label class="form-label">Current End Date</label>
                        <input type="text" class="form-control" value="<?= $company->end_date ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Requested Extension Period</label>
                        <?= $form->field($renewal, 'extension_period')->dropDownList([
                            3 => '3 Months',
                            6 => '6 Months',
                            12 => '1 Year',
                            24 => '2 Years',
                        ], ['prompt' => 'Select Period'])->label(false); ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <?= $form->field($renewal, 'notes')->textarea(['rows' => 3, 'placeholder' => 'Any specific requirements or comments'])->label(false); ?>
                    </div>

                    <div class="form-group text-center">
                        <?= Html::submitButton('Submit Renewal Request', [
                            'class' => 'btn btn-warning btn-lg',
                            'data' => [
                                'confirm' => 'Are you sure you want to submit this renewal request?'
                            ]
                        ]) ?>
                        <?= Html::a('Cancel', ['profile', 'id' => Yii::$app->user->id], [
                            'class' => 'btn btn-secondary btn-lg ms-2'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card-header {
    color: #000;
}
.btn-warning {
    color: #000;
}
</style> 