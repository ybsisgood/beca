<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\search\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;


/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'only' => ['delete','index','view','update','create'],
                    'rules' => [
                        [
                            'actions' => ['delete','index','view','update','create'],
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $checkAdmin = User::checkAdmin(Yii::$app->user->identity->id);
        
        if($checkAdmin == false) 
        {
            Yii::$app->session->setFlash('error', "Anda tidak memiliki akses untuk membuat user");
            return $this->redirect(['index']);
        }

        $model = new User();
        $model->scenario = User::SCENARIO_CREATE_USER;
        
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->validate()) {
                $model->status = User::STATUS_ACTIVE;
                $model->auth_key = Yii::$app->security->generateRandomString();
                $model->password_hash = Yii::$app->security->generatePasswordHash($model->password);
                $model->login_failed_attempt = 0;
                $model->save();
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $checkAdmin = User::checkAdmin(Yii::$app->user->identity->id);
        
        if($checkAdmin == false) 
        {
            Yii::$app->session->setFlash('error', "Anda tidak memiliki akses untuk update user");
            return $this->redirect(['index']);
        }

        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "User berhasil di Update");
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionChangePassword($id)
    {
        $checkAdmin = User::checkAdmin(Yii::$app->user->identity->id);
        
        if($checkAdmin == false) 
        {
            Yii::$app->session->setFlash('error', "Anda tidak memiliki akses untuk ganti password user");
            return $this->redirect(['index']);
        }

        $model = $this->findModel($id);
        $model->scenario = User::SCENARIO_CHANGE_PASSWORD;

        if ($this->request->isPost && $model->load($this->request->post()) && $model->validate()) {
            $model->password_hash = Yii::$app->security->generatePasswordHash($model->password);
            $model->save();
            Yii::$app->session->setFlash('success', "User berhasil di Ganti Password");
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('change-password', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $checkAdmin = User::checkAdmin(Yii::$app->user->identity->id);
        
        if($checkAdmin == false) 
        {
            Yii::$app->session->setFlash('error', "Anda tidak memiliki akses untuk menghapus user");
            return $this->redirect(['index']);
        }
        
        if(Yii::$app->user->identity->id == $id)
        {
            Yii::$app->session->setFlash('error', "Anda tidak bisa menghapus diri sendiri");
            return $this->redirect(['index']);
        }   
        Yii::$app->session->setFlash('success', "User berhasil di Delete");
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionUnlockUser($id)
    {
        $checkAdmin = User::checkAdmin(Yii::$app->user->identity->id);
        
        if($checkAdmin == false) 
        {
            Yii::$app->session->setFlash('error', "Anda tidak memiliki akses untuk un-lock user");
            return $this->redirect(['index']);
        }
        $model = $this->findModel($id);
        $model->login_failed_attempt = 0;
        $model->status = User::STATUS_ACTIVE;
        $model->save();
        Yii::$app->session->setFlash('success', "User berhasil di Unlock");

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
