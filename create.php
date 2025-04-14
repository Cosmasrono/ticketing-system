<?php

// Register JavaScript for handling file upload and base64 conversion
$this->registerJs("
    let base64String = '';
    let fileInput = document.getElementById('ticket-screenshot');
    let base64Input = document.getElementById('screenshot-base64');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    base64String = e.target.result;
                    base64Input.value = base64String;
                    
                    if (document.getElementById('screenshot-preview')) {
                        document.getElementById('screenshot-preview').src = base64String;
                        document.getElementById('screenshot-preview-container').style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
");
?>

<!-- Form elements for file upload -->
<?= $form->field($model, 'screenshot')->fileInput([
    'id' => 'ticket-screenshot',
    'class' => 'form-control'
]) ?>
<?= Html::hiddenInput('screenshot_base64', '', ['id' => 'screenshot-base64']) ?>

<!-- Preview container -->
<div id="screenshot-preview-container" style="display:none; margin-top:10px;">
    <img id="screenshot-preview" style="max-width:100%; max-height:300px;" />
</div>
