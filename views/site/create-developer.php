<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
?>

<div class="create-developer">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white">
                    <h2 class="text-center mb-0">Create Developer</h2>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(['id' => 'create-developer-form']); ?>

                    <?= $form->field($model, 'name')->textInput(['placeholder' => 'Enter company name (e.g., Kabarak-Husein)']) ?>
                    <?= $form->field($model, 'company_email')->textInput(['type' => 'email', 'placeholder' => 'Enter email']) ?>
                    <?= $form->field($model, 'start_date')->input('date', ['min' => date('Y-m-d')]) ?>
                    <?= $form->field($model, 'end_date')->input('date', ['min' => date('Y-m-d')]) ?>
                    
                    <?= $form->field($model, 'role')->hiddenInput(['value' => 'developer'])->label(false) ?>

                    <div class="form-group text-center">
                        <?= Html::submitButton('Create Developer', ['class' => 'btn btn-success btn-lg mt-3']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div> 