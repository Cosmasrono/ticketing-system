<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
?>

<div class="create-developer" style="margin-top: 30px;">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header text-white">
                    <h2 class="text-center mb-0 text-black">Create Developer</h2>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'create-developer-form',
                        'enableClientValidation' => true,
                    ]); ?>

                    <?= $form->field($model, 'name')->textInput([
                        'placeholder' => 'Enter developer name',
                        'required' => true,
                        'autofocus' => true
                    ])->label('Developer Name *') ?>

                    <?= $form->field($model, 'company_name')->textInput([
                        'placeholder' => 'Enter company name',
                        'required' => true
                    ])->label('Company Name *') ?>

                    <?= $form->field($model, 'company_email')->textInput([
                        'type' => 'email',
                        'placeholder' => 'Enter email',
                        'required' => true
                    ])->label('Company Email *') ?>

                    <?= $form->field($model, 'start_date')->input('date', [
                        'min' => date('Y-m-d'),
                        'required' => true,
                        'value' => date('Y-m-d')
                    ])->label('Start Date *') ?>

                    <?= $form->field($model, 'end_date')->input('date', [
                        'min' => date('Y-m-d'),
                        'required' => true,
                        'value' => date('Y-m-d', strtotime('+1 year'))
                    ])->label('End Date *') ?>
                    
                    <?= $form->field($model, 'role')->hiddenInput(['value' => 'developer'])->label(false) ?>

                    <div class="form-group text-center">
                        <?= Html::submitButton('Create Developer', [
                            'class' => 'btn  w-100 p-2 mt-3', 'style' => 'background-color: #EA5626; color:white; max-width: 200px;',
                            'data' => ['confirm' => 'Are you sure you want to create this developer?']
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div> 