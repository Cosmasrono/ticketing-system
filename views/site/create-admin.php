<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
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

<div class="create-admin" style="margin-top: 30px;">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card ">
                <div class="card-header  text-black">
                    <h2 class="text-center text-black mb-0">Create Administrator</h2>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(['id' => 'create-admin-form']); ?>

                    <?= $form->field($model, 'name')->textInput(['placeholder' => 'Enter name']) ?>
                    <?= $form->field($model, 'company_email')->textInput(['type' => 'email', 'placeholder' => 'Enter email']) ?>
                    <?= $form->field($model, 'start_date')->input('date', ['min' => date('Y-m-d')]) ?>
                    <?= $form->field($model, 'end_date')->input('date', ['min' => date('Y-m-d')]) ?>
                    
                    <?= $form->field($model, 'role')->hiddenInput(['value' => 'admin'])->label(false) ?>

                    <div class="form-group text-center">
                        <?= Html::submitButton('Create Administrator', ['class' => 'btn w-100 p-2 mt-3', 'style' => 'background-color: #EA5626; color:white; max-width: 200px;']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div> 