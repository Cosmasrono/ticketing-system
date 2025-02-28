<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Request Contract Renewal for ' . $company->company_name;
?>
<style>
    /* Container Styling */
    .container {
        max-width: 100%;
        padding: 0px;
        /* Allow full width */
        /* Add padding for mobile */
    }

    .finefooter {
        padding: 0px 60px;
        margin-bottom: -20px;

    }
</style>

<div class="renew-contract " style="padding-top: 20px;">
    <div class="row">
        <div class="col-lg-6 offset-lg-3">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title mb-0">Request Contract Renewal for <?= Html::encode($company->company_name) ?></h3>
                </div>
                
                <div class="card-body">
                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger">
                            <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-success">
                            <?= Yii::$app->session->getFlash('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php $form = ActiveForm::begin([
                        'id' => 'renew-contract-form',
                        'method' => 'post',
                        'options' => ['enctype' => 'multipart/form-data']
                    ]); ?>

                    <!-- Hidden fields -->
                    <input type="hidden" name="ContractRenewal[company_id]" value="<?= (int)$company->id ?>">
                    <input type="hidden" name="ContractRenewal[current_end_date]" value="<?= Html::encode($currentEndDate) ?>">
                    <input type="hidden" name="ContractRenewal[requested_by]" value="<?= (int)Yii::$app->user->id ?>">

                    <div class="mb-3">
                        <label class="form-label">Current Contract End Date</label>
                        <input type="text" class="form-control" value="<?= Yii::$app->formatter->asDate($currentEndDate) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Extension Start Date</label>
                        <?= $form->field($renewal, 'extension_period', [
                            'options' => ['class' => 'form-group'],
                            'errorOptions' => ['class' => 'text-danger']
                        ])->input('date', [
                            'value' => date('Y-m-d', strtotime($currentEndDate . ' +1 day')),
                            'class' => 'form-control',
                            'required' => true
                        ])->label(false) ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Renewal Duration</label>
                        <?= $form->field($renewal, 'renewal_duration', [
                            'options' => ['class' => 'form-group'],
                            'errorOptions' => ['class' => 'text-danger']
                        ])->dropDownList([
                            3 => '3 Months',
                            6 => '6 Months',
                            12 => '1 Year',
                            24 => '2 Years',
                        ], [
                            'prompt' => 'Select Duration',
                            'required' => true,
                            'class' => 'form-control'
                        ])->label(false) ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <?= $form->field($renewal, 'notes', [
                            'options' => ['class' => 'form-group'],
                            'errorOptions' => ['class' => 'text-danger']
                        ])->textarea([
                            'rows' => 3,
                            'class' => 'form-control',
                            'placeholder' => 'Any specific requirements or comments'
                        ])->label(false) ?>
                    </div>

                    <div class="form-group text-center">
                    <?= Html::submitButton('Submit Renewal Request', [
                            'class' => 'btn custom-btn text-white p-2.5 ',
                            'style' => 'background-color: ; max-width: 300px;',
                            'id' => 'submit-button'
                        ]) ?>
                        <?= Html::a('Cancel', ['profile', 'id' => Yii::$app->user->id], [
                            'class' => 'btn btn-secondary ms-2 p-2.5',
                            'style' => 'max-width: 300px;',
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
        /* ... your existing styles ... */
        .custom-btn {
        background-color: #EA5626;
        color: white;
        border: none;
       
    }
.card-header {
    color: #000;
}
.btn-warning {
    color: #000;
}
.required:after {
    content: " *";
    color: red;
}
</style> 