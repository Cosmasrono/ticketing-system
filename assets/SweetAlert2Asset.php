<?php

namespace app\assets;

use yii\web\AssetBundle;

class SweetAlert2Asset extends AssetBundle
{
    public $sourcePath = null;
    public $baseUrl = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
    
    public $css = [
        'sweetalert2.min.css',
    ];
    
    public $js = [
        'sweetalert2.all.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];

    public function init()
    {
        parent::init();
        // Add our custom helper JS
        $this->js[] = $this->registerCustomJs();
    }

    protected function registerCustomJs()
    {
        $jsCode = <<<JS
// Global SweetAlert function with automatic 3-second fade
window.showAlert = function(title, message, type = 'success') {
    return Swal.fire({
        title: title,
        text: message,
        icon: type, // 'success', 'error', 'warning', 'info', 'question'
        timer: 4000,
        timerProgressBar: true,
        showConfirmButton: false,
        position: 'top-end',
        toast: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
};

// Helper functions for common SweetAlert use cases
window.confirmAction = function(title, message, callback) {
    Swal.fire({
        title: title,
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed && typeof callback === 'function') {
            callback();
        }
    });
};

window.showLoading = function(message = 'Processing...') {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};

window.hideLoading = function() {
    Swal.close();
};
JS;

        // Create a temporary file
        $fileName = md5($jsCode) . '.js';
        $filePath = \Yii::getAlias('@webroot/assets/js');
        
        // Create directory if it doesn't exist
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }
        
        $fullPath = $filePath . '/' . $fileName;
        
        // Only write the file if it doesn't exist
        if (!file_exists($fullPath)) {
            file_put_contents($fullPath, $jsCode);
        }
        
        return '/assets/js/' . $fileName;
    }
} 