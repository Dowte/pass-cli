<?php
/**
 * Password - A command-line tool to help you manage your password
 *
 * @author  admin@dowte.com
 * @link    https://github.com/dowte/password
 * @license https://opensource.org/licenses/MIT
 */

namespace Dowte\Password\forms;


use Dowte\Password\pass\BaseForm;
use Dowte\Password\pass\Password;
use Dowte\Password\models\PasswordModel;

class PasswordForm extends BaseForm
{
    /**
     * @return PasswordForm
     */
    public static function pass()
    {
        return (new self());
    }

    /**
     * @param $userId
     * @param $name
     * @return bool
     */
    public function findPassword($userId, $name)
    {
        $model = new PasswordModel();
        $passwords = $model::find()->select('password, keyword, description')->where(['user_id' => $userId])->all();
        foreach ($passwords as $password) {
            if (Password::decryptedPasswordKey($password['keyword']) == $name) {
                return $password;
            }
        }
        return false;
    }

    /**
     * @param $fields
     * @param array $where
     * @return array
     */
    public function findModels($fields, $where = [])
    {
        $model = new PasswordModel();
        return $model::find()->select($fields)->where($where)->all();
    }

    /**
     * @param array $where
     * @param string $fields
     * @return array
     */
    public function findOne($where = [], $fields = '*')
    {
        $model = new PasswordModel();
        return $model::find()->select($fields)->where($where)->one();
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        return self::deleteByConditions(['id' => $id]);
    }

    /**
     * @param $conditions
     * @return bool
     */
    public function deleteByConditions($conditions)
    {
        return (new PasswordModel())->delete($conditions);
    }

    /**
     * @param $id
     * @param string $keyword
     * @param string $password
     * @param string $description
     * @return int
     */
    public function update($id, $keyword = '', $password = '', $description = '')
    {
        $model = new PasswordModel();
        $model->id = $id;

        ! $keyword      or $model->keyword = $keyword;
        ! $password     or $model->password = $password;
        ! $description  or $model->description = $description;

        return $model->save();
    }

    /**
     * @param integer $userId
     * @param string $password
     * @param string $keyword
     * @param string $description
     * @return int
     */
    public function createPass($userId, $password, $keyword = '', $description = '')
    {
        $model = new PasswordModel();
        $model->user_id = $userId;
        $model->keyword = $keyword;
        $model->password = $password;
        $model->description = $description;

        return $model->save();
    }

    /**
     * @param string $sprintf the sprintf string has a %s to set name
     * @return string | array
     */
    public function getDecryptedKey($sprintf = '')
    {
        $user = UserForm::user()->findOne(['username' => Password::getUser()]);
        if (! $user) return [];
        $keys = $this->findModels('keyword', ['user_id' => $user['id']]);
        $lists = '';
        $i = 0;
        if ($sprintf) {
            foreach ($keys as $key) {
                $lists .= sprintf($sprintf, Password::decryptedPasswordKey($key['keyword']));
                if (++$i % 4 == 0) {
                    $lists .= PHP_EOL;
                }
            }
            return $lists;
        } else {
            return array_map(function($arr) {
                return Password::decryptedPasswordKey($arr['keyword']);
            }, $keys);
        }
    }

    private function __construct()
    {
    }
}