<?php
namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
  
  
 
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset'
    ];
 
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'vendor/bootstrap/css/bootstrap.min.css',
        'vendor/bootstrap-icons/bootstrap-icons.css',
        'vendor/aos/aos.css',
        'vendor/glightbox/css/glightbox.min.css',
        'vendor/swiper/swiper-bundle.min.css',
        'css/main.css',
        'css/site.css',
    ];
    
    public $js = [
        'vendor/bootstrap/js/bootstrap.bundle.min.js',
        'vendor/aos/aos.js',
        'vendor/glightbox/js/glightbox.min.js',
        'vendor/swiper/swiper-bundle.min.js',
        'vendor/isotope-layout/isotope.pkgd.min.js',
        'vendor/vanilla-tilt/vanilla-tilt.min.js',
        'js/main.js',
    ];
    
}
