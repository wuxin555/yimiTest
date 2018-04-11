<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ImagesSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="images-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'image_id') ?>

    <?= $form->field($model, 'storage') ?>

    <?= $form->field($model, 'image_name') ?>

    <?= $form->field($model, 'ident') ?>

    <?= $form->field($model, 'url') ?>

    <?php // echo $form->field($model, 'l_ident') ?>

    <?php // echo $form->field($model, 'l_url') ?>

    <?php // echo $form->field($model, 'm_ident') ?>

    <?php // echo $form->field($model, 'm_url') ?>

    <?php // echo $form->field($model, 's_ident') ?>

    <?php // echo $form->field($model, 's_url') ?>

    <?php // echo $form->field($model, 'width') ?>

    <?php // echo $form->field($model, 'height') ?>

    <?php // echo $form->field($model, 'watermark') ?>

    <?php // echo $form->field($model, 'last_modified') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
