<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Add Client';
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

<div class="row justify-content-center add-client" style="margin-top: 30px;">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h2><?= Html::encode($this->title) ?></h2>
            </div>
            <div class="card-body gap-3">
                <?php $form = ActiveForm::begin(['id' => 'add-client-form']); ?>

                <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class' => 'form-control mb-2', 'placeholder' => 'Enter Name']) ?>
                <?= $form->field($model, 'company_name')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control mb-2',
                    'placeholder' => 'Enter Company Name'
                ]) ?>

                <?= $form->field($model, 'company_email')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control mb-2',
                    'placeholder' => 'Enter Company Email'
                ]) ?>

                <div class="form-group">
                    <label class="control-label mb-2">Modules</label>
                    <div class="row">
                        <?php foreach ($model->getAvailableModules() as $value => $label): ?>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input type="checkbox"
                                        name="Client[modules][]"
                                        value="<?= Html::encode($value) ?>"
                                        class="form-check-input"
                                        id="module-<?= Html::encode($value) ?>">
                                    <label class="form-check-label" for="module-<?= Html::encode($value) ?>">
                                        <?= Html::encode($label) ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group d-flex justify-content-center">
                    <?= Html::submitButton('Add Client', ['class' => 'btn w-100 p-2 mt-3', 'style' => 'background-color: #EA5626; color:white; max-width: 200px;']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>