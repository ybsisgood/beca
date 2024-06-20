<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = 'Ganti Password User: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form_change_password', [
        'model' => $model,
    ]) ?>

</div>
