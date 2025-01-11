<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        .ticket-assigned {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .ticket-details {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .ticket-details ul {
            list-style: none;
            padding: 0;
        }
        .ticket-details li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?= $content ?>
</body>
</html>
