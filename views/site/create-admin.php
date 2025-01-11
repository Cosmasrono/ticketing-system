<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
?>

<div class="create-admin">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-danger text-white">
                    <h2 class="text-center mb-0">Create Administrator</h2>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(['id' => 'create-admin-form']); ?>

                    <?= $form->field($model, 'name')->textInput(['placeholder' => 'Enter name']) ?>
                    <?= $form->field($model, 'company_email')->textInput(['type' => 'email', 'placeholder' => 'Enter email']) ?>
                    <?= $form->field($model, 'start_date')->input('date', ['min' => date('Y-m-d')]) ?>
                    <?= $form->field($model, 'end_date')->input('date', ['min' => date('Y-m-d')]) ?>
                    
                    <?= $form->field($model, 'role')->hiddenInput(['value' => 'admin'])->label(false) ?>

                    <div class="form-group text-center">
                        <?= Html::submitButton('Create Administrator', ['class' => 'btn btn-danger btn-lg mt-3']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div> 