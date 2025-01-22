<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Add Client';
?>

<div class="add-client">
    <div class="card">
        <div class="card-header">
            <h2><?= Html::encode($this->title) ?></h2>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(['id' => 'add-client-form']); ?>

            <?= $form->field($model, 'company_name')->textInput([
                'maxlength' => true,
                'class' => 'form-control',
                'placeholder' => 'Enter Company Name'
            ]) ?>

            <?= $form->field($model, 'company_email')->textInput([
                'maxlength' => true,
                'class' => 'form-control',
                'placeholder' => 'Enter Company Email'
            ]) ?>

            <div class="form-group">
                <label class="control-label">Modules</label>
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

            <div class="form-group mt-4">
                <?= Html::submitButton('Add Client', [
                    'class' => 'btn btn-primary btn-lg'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div> 