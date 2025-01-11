<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Create New User';
?>

<div class="create-user">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white py-3">
                    <h2 class="text-center mb-0"><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(['id' => 'create-user-form']); ?>

             

                    <!-- Common Fields -->
                    <div class="row g-3">
                        <div class="col-md-12 mb-3">
                            <?= $form->field($model, 'name')->textInput([
                                'class' => 'form-control',
                                'placeholder' => 'Enter name'
                            ]) ?>
                        </div>

                        <div class="col-md-12">
                            <?= $form->field($model, 'company_email')->textInput([
                                'type' => 'email',
                                'class' => 'form-control',
                                'placeholder' => 'Enter email'
                            ]) ?>
                        </div>

                        <!-- Company-specific fields -->
                        <div id="company-fields" style="display: none;" class="col-12">
                            <div class="col-md-12 mb-3">
                                <?= $form->field($model, 'company_name')->textInput([
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter company name'
                                ]) ?>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <?= $form->field($model, 'start_date')->input('date', [
                                        'class' => 'form-control',
                                        'min' => date('Y-m-d')
                                    ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $form->field($model, 'end_date')->input('date', [
                                        'class' => 'form-control',
                                        'min' => date('Y-m-d')
                                    ]) ?>
                                </div>
                            </div>

                            <!-- Modules Selection -->
                            <div class="modules-section">
                                <h4 class="section-title">Select Modules</h4>
                                <div class="row g-3">
                                    <?php foreach ($modules as $module): ?>
                                        <div class="col-md-4">
                                            <div class="module-card">
                                                <div class="form-check">
                                                    <?= Html::checkbox(
                                                        "Company[modules][]",
                                                        false,
                                                        [
                                                            'value' => $module['module_code'],
                                                            'id' => 'module-' . $module['module_code'],
                                                            'class' => 'form-check-input module-checkbox'
                                                        ]
                                                    ) ?>
                                                    <label class="form-check-label" for="module-<?= $module['module_code'] ?>">
                                                        <?= Html::encode($module['module_name']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-center mt-4">
                        <?= Html::submitButton('Create User', [
                            'class' => 'btn btn-primary btn-lg px-5',
                            'name' => 'create-user-button'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
$(document).ready(function() {
    // Show all company fields and modules by default
    $('#company-fields').show();
    $('.modules-section').show();

    // Date validation
    $('#company-end-date').on('change', function() {
        var startDate = new Date($('#company-start-date').val());
        var endDate = new Date($(this).val());
        
        if (endDate <= startDate) {
            alert('End date must be after start date');
            $(this).val('');
        }
    });
});
JS;
$this->registerJs($js);
?> 