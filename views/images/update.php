<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Images */

$this->title = 'Update Images: {nameAttribute}';
$this->params['breadcrumbs'][] = ['label' => 'Images', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->image_id, 'url' => ['view', 'id' => $model->image_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="images-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
