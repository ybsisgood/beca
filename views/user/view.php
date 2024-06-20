<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\User;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Ganti Password', ['change-password', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
        <?php if($model->login_failed_attempt >= User::LIMIT_GAGAL_LOGIN): ?>
            <?= Html::a('Unlock User', ['unlock-user', 'id' => $model->id], [
            'class' => 'btn btn-info',
            'data' => [
                'confirm' => 'Apakah anda yakin igin Unlock User ini?',
                'method' => 'post',
            ],
        ]) ?>
        <?php endif; ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            'auth_key',
            'password_hash',
            'login_failed_attempt',
            [
                'attribute' => 'status',
                'value' => $model->status == User::STATUS_ACTIVE ? 'Active' : 'Lock',
            ],
            [
                'attribute' => 'isadmin',
                'value' => $model->isadmin == User::IS_ADMIN_YES ? 'Yes' : 'No',
            ],
        ],
    ]) ?>

</div>
