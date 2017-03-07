<?
namespace App\Models;

use Core\Core;
use Core\Prototypes\Model;

/**
 * Class User
 * @package App\Models
 */
class User extends Model {
    protected $table = 'user';
    protected $fields = ['id', 'name', 'last_name', 'login', 'password', 'email', 'chekword', 'auth_code'];

    /**
     * @return bool
     * @throws \Exception
     */
    public function isAuthorized(){
        if(isset($_SESSION['user']) && $_SESSION['user'] != ''){
            $user = $this->getList(['login' => $_SESSION['user']])->fetch();
            if(!$user){
                $this->logout();
                throw new \Exception('Что-то не так в сесси, возможно попытка взлома.');
            }

            if($_SESSION['auth_code'] != $user['auth_code']) {
                $this->logout();
                return false;
            }else
                return true;
        }else
            return false;
    }

    public function logout(){
        unset($_SESSION['user']);
        unset($_SESSION['auth_code']);
        unset($_SESSION['user_id']);
    }

    /**
     * @param $login
     * @param $pass
     * @return bool
     */
    public function authorize($login, $pass){
        $user = $this->getList(['login' => $login])->fetch();
        if(!$user)
            return false;

        if($this->comparePass($user['password'], $pass)) {
            $user['auth_code'] = Core::rand_str(32);
            $this->update($user['id'], $user);

            $_SESSION['user'] = $user['login'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['auth_code'] = $user['auth_code'];
            return true;
        }else
            return false;
    }

    /**
     * @param $fields
     * @return bool
     * @throws \Exception
     */
    public function add($fields){
        if(!@$fields['password'] || !@$fields['email'] || !@$fields['name'] || !@$fields['login'])
            throw new \Exception('Заполнены не все обязательные поля.');

        if($this->getList(['email' => $fields['email']])->fetch())
            throw new \Exception('Пользователь с таким E-mail адресом уже зарегистрирован.');

        if($this->getList(['login' => $fields['login']])->fetch())
            throw new \Exception('Пользователь с таким логином уже зарегистрирован.');

        if(!preg_match('/^[a-f0-9]{32}&/i', $fields['password']))
            $fields['password'] =$this->cryptPass($fields['password']);

        $fields['chekword'] = Core::rand_str(32);

        return parent::add($fields);
    }

    /**
     * @param $str
     * @return string
     */
    public function cryptPass($str){
        $pass = md5($str);
        $pass = substr($pass, 16).substr($pass, 0, 16);
        return $pass;
    }

    /**
     * @param $hash
     * @param $pass
     * @return bool
     */
    public function comparePass($hash, $pass){
        $pass = $this->cryptPass($pass);
        return $pass == $hash;
    }
}