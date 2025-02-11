<?php

namespace app\assets;

use yii\web\AssetBundle;

class LandingAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'assets/css/landing.css',
        'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css',
        'https://unpkg.com/aos@2.3.1/dist/aos.css',
        'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css',
        'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css',
    ];
    
    public $js = [
        'assets/js/main.js',
        'https://unpkg.com/aos@2.3.1/dist/aos.js',
        'https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js',
        'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js',
        'https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js',
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
} 