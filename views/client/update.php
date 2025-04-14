<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Update Client: ' . $model->company_name;
?>

<style>
.client-update {
    padding: 20px;
}

.update-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.update-form-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}
</style>

<div class="client-update">
    <div class="update-header">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><?= Html::encode($this->title) ?></h1>
            <p class="mb-0 text-gray-600">Update Client Information</p>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-arrow-left"></i> Back', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="card update-form-card">
        <div class="card-body">
            <?php $form = ActiveForm::begin(['id' => 'update-client-form']); ?>

            <?= $form->field($model, 'company_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'company_email')->textInput(['maxlength' => true]) ?>

            <div class="form-group">
                <label class="control-label">Modules</label>
                <div class="row">
                    <?php
                    $selectedModules = explode(',', $model->module);
                    $selectedModules = array_map('trim', $selectedModules);

                    foreach ($model->getAvailableModules() as $value => $label): ?>
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input type="checkbox" 
                                       name="Client[modules][]" 
                                       value="<?= $value ?>" 
                                       class="form-check-input" 
                                       id="module-<?= $value ?>"
                                       <?= in_array($value, $selectedModules) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="module-<?= $value ?>">
                                    <?= $label ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group mt-4">
                <?= Html::submitButton('<i class="fas fa-save"></i> Save Changes', [
                    'class' => 'btn btn-primary btn-lg'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div> 