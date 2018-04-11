<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Images */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="images-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'image_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'storage')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'image_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ident')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'l_ident')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'l_url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm_ident')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm_url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 's_ident')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 's_url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'width')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'height')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'watermark')->dropDownList([ 'true' => 'True', 'false' => 'False', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'last_modified')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
