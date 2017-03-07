<?
namespace Core\Helpers;
use Core\Core;

class DBConn {
    /**
     * @var \mysqli|null $link ссылка на базу
     * @var int $q кол-во запросов
     */
    private
        $link = null,
        $q = 0,
        $db;

    private function connect(){
        $conf = \Core\Core::getConfig('DB')['connect'];

        if(isset($conf) && count(array_intersect(array_keys($conf), ['user', 'password', 'db', 'host'])) == 4){
            if(!$this->link = mysqli_connect($conf['host'], $conf['user'], $conf['password']))
                die('Не удалось соединиться с базой данных! Error: '.mysqli_connect_error()."\n");

            if(!mysqli_select_db($this->link, $conf['db']))
                die('База не найдена! Error:  '.mysqli_error($this->link));

            $this->db = $conf['db'];
            mysqli_query($this->link, 'SET NAMES utf8');
        }else
            die('Файл ~/Core/config.php сформирован не корректно.');
    }

    /**
     * Переключат базу данных
     * @param string $db
     */
    public function switch_db($db){
        $this->db = $db;
    }

    /**
     * Закрывает соединение с базой
     * @return bool
     */
    public function close(){
        return mysqli_close($this->link);
    }

    /**
     * Исполняет запрос
     * @param string $sql
     * @return DBRes
     * @throws \Exception
     */
    public function exec($sql){
        if(!($this->link instanceof \mysqli))
            $this->connect();

        if(!mysqli_select_db($this->link, $this->db))
            die('База не найдена : '.mysqli_error($this->link));

        $res = mysqli_query($this->link, $sql);
        $this->q++;
        if(Core::getConfig('ENV') == 'debug' && mysqli_error($this->link) != '')
            throw new \Exception(mysqli_error($this->link));

        return new DBRes($res, mysqli_affected_rows($this->link), @mysqli_num_rows($res), mysqli_insert_id($this->link), mysqli_error($this->link), $sql);
    }

    /**
     * @return \mysqli|null
     */
    public function get_link(){
        return $this->link;
    }

    /**
     * Экранирует спецсимволы в строке
     * @param string $str
     * @return string
     */
    public function escape($str){
        return mysqli_escape_string($this->link, $str);
    }
}
?>
