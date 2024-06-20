<?php

namespace app\models;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string|null $password_hash
 * @property int $status
 * @property int $login_failed_attempt
 * @property int $isadmin
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;

    public $password;
    public $retypePassword;

    const SCENARIO_CREATE_USER = 'create';
    const SCENARIO_CHANGE_PASSWORD = 'change-password';

    const IS_ADMIN_YES = 1;
    const IS_ADMIN_NO = 0;

    const LIMIT_GAGAL_LOGIN = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password', 'retypePassword'], 'required', 'on' => self::SCENARIO_CREATE_USER],
            [['password','retypePassword'], 'required', 'on' => self::SCENARIO_CHANGE_PASSWORD],
            [['status', 'login_failed_attempt', 'isadmin'], 'integer'],
            [['username', 'password_hash'], 'string', 'min' => 4, 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['retypePassword'], 'compare', 'compareAttribute' => 'password', 'on' => self::SCENARIO_CREATE_USER, 'message' => 'Password tidak sama'],
            [['retypePassword'], 'compare', 'compareAttribute' => 'password', 'on' => self::SCENARIO_CHANGE_PASSWORD, 'message' => 'Password tidak sama'],
            [['password'], 'string', 'min' => 4],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_LOCK]],
            [['isadmin'], 'in', 'range' => [self::IS_ADMIN_NO, self::IS_ADMIN_YES]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'status' => 'Status',
            'login_failed_attempt' => 'Jumlah Gagal Login',
            'isadmin' => 'Isadmin',
            'password' => 'Password',
            'retypePassword' => 'Ulangi Password',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\UserQuery(get_called_class());
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
	{
        $check = Yii::$app->security->validatePassword($password, $this->password_hash);
        // tambah gagal login
        if($check == false) {
            $this->login_failed_attempt = $this->login_failed_attempt + 1;
            if($this->login_failed_attempt >= self::LIMIT_GAGAL_LOGIN)
            {
                $this->status = self::STATUS_LOCK;
            }
            $this->save();
            $check = false;
        }
        return $check;
	}

    public static function checkAdmin($id)
    {
        $user = User::findOne($id);
        if($user->isadmin == User::IS_ADMIN_YES) {
            return true;
        }
        return false;
    }

    public static function getIsAdminList()
    {
        return [
            self::IS_ADMIN_NO => 'No',
            self::IS_ADMIN_YES => 'Yes',
        ];
    }

    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_LOCK => 'Lock',
        ];
    }
}
