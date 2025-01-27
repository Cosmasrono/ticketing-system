<?php

namespace app\assets;

use yii\web\AssetBundle;

class SweetAlert2Asset extends AssetBundle
{
    public $sourcePath = '@npm/sweetalert2/dist';
    
    public $css = [
        'sweetalert2.min.css',
    ];
    
    public $js = [
        'sweetalert2.all.min.js',
    ];
} 