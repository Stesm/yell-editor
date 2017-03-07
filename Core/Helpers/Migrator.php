<?
namespace Core\Helpers;
use Core\Core;

class Migrator {

    private $table  = 'migrations';
    private $dir    = '/Migrations/';

    public function init(){
        if(!$this->check_install())
            $this->install();

        $path = APP_ROOT.$this->dir;
        if(!is_dir($path))
            if(!mkdir($path, 0777, true))
                echo "\033[1;31mCan't create migrations dir.\033[0m\n";
    }

    private function check_install(){
        $sql = "
            SELECT
                count(*) as cnt
            FROM
                `information_schema`.`TABLES`
            WHERE
                `TABLE_NAME` = '{$this->table}'
                AND `TABLE_SCHEMA` = '".Core::getConfig('DB')['connect']['db']."'";
        return boolval(Core::$db->exec($sql)->fetch()['cnt']);
    }

    private function install(){
        Core::$db->exec("
            CREATE TABLE {$this->table} (
                id INT(5) PRIMARY KEY AUTO_INCREMENT,
                filename VARCHAR(255),
                stage INT(5),
                date_migrate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function migrate(){
        $already = $this->getIsset();
        $stage = ($r = Core::$db->exec("SELECT stage + 1 m FROM {$this->table} ORDER BY stage DESC LIMIT 1")->fetch()) ? $r['m'] : 0;
        $already = array_keys($already);
        $path = APP_ROOT.$this->dir;

        if(is_dir($path)){
            $list = scandir($path);
            foreach($list as $k => $file)
                if(!$this->check_file($file))
                    unset($list[$k]);

            usort($list, function($a, $b){
                $a = $this->parse_name($a);
                $b = $this->parse_name($b);
                if($a['date'] == $b['date'])
                    return 0;

                return ($a['date'] < $b['date']) ? -1 : 1;
            });

            foreach($list as $file){
                if(in_array($file, $already, 1) || !$this->check_file($file))
                    continue;

                $content = file_get_contents($path.$file);
                if(
                    preg_match('/class\s+([a-z0-9_]+)/i', $content, $class) &&
                    preg_match('/namespace\s+([a-z_\\\\]+);/i', $content, $namespace)
                ){
                    require_once $path.$file;
                    /** @var \Core\Prototypes\Migration $migration */

                    $name = "{$namespace[1]}\\{$class[1]}";

                    if($migration = @new $name()){
                        $migration->migrate();
                        Core::$db->exec("
                            INSERT INTO
                                {$this->table} (
                                    filename,
                                    stage
                                )
                            VALUES
                                (
                                    '{$file}',
                                    {$stage}
                                )
                        ");
                    }
                }
                echo "\033[1;32m{$file}\033[0m ... successful.\n";
            }
        }else
            echo "\033[1;31mCan't detect migrations dir.\033[0m\n";
    }

    public function rollback(){
        $stage = ($r = Core::$db->exec("SELECT stage m FROM {$this->table} ORDER BY stage DESC LIMIT 1")->fetch()) ? $r['m'] : 0;
        $files = $this->getByStage($stage);
        $path = APP_ROOT.$this->dir;

        if(!is_dir($path)){
            echo "\033[1;31mCan't detect migrations dir.\033[0m\n";
            return;
        }

        if($files)
            foreach($files as $file){
                $content = file_get_contents($path.$file['filename']);

                if(
                    preg_match('/class\s+([a-z0-9_]+)/i', $content, $class) &&
                    preg_match('/namespace\s+([a-z_\\\\]+);/i', $content, $namespace)
                ){
                    require_once $path.$file['filename'];
                    /** @var \Core\Prototypes\Migration $migration */

                    $name = "{$namespace[1]}\\{$class[1]}";

                    if($migration = @new $name()){
                        $migration->rollback();
                        Core::$db->exec("DELETE FROM {$this->table} WHERE ID = {$file['id']}");
                    }
                }
                echo "\033[1;32m{$file['filename']}\033[0m ... rolled back.\n";
            }
        else
            echo "\033[1;32mNothing to rollback.\033[0m\n";
    }

    public function make($name){
        $path = APP_ROOT.$this->dir;

        foreach(scandir($path) as $file)
            if($this->check_file($file)){
                $info = $this->parse_name($file);
                if($info['name'] == $name){
                    echo "\033[1;31mFile already exists.\033[0m\n";
                    return;
                }
            }

        $fp = fopen($path.$name.'_'.date('Ymd').'.php', 'w');
        if($fp){
            fwrite($fp,
<<<DOC
<?
namespace Core\Migrations;

use Core\Core;
use Core\Prototypes\Migration;

class {$name} extends Migration {

    public function migrate(){

    }

    public function rollback(){

    }
}
DOC
            );
            fclose($fp);
            echo $name.'_'.date('Ymd').".php\033[1;32m create success.\033[0m\n";
        }else
            echo "\033[1;31mCan't get access to migrations dir.\033[0m\n";
    }

    private function check_file($name){
        if(in_array($name, ['.', '..']))
            return false;

        if(!preg_match('/[a-z0-9_]+?_[0-9]{8}\.php/i', $name))
            return false;

        return true;
    }

    private function parse_name($name){
        if(!preg_match('/([a-z0-9_]+?)_([0-9]{8})\.php/i', $name, $m))
            return [];
        else
            return [
                'name' => $m[1],
                'date' => $m[2]
            ];
    }

    private function getIsset(){
        return (array) Core::$db->exec("SELECT * FROM {$this->table}")->absorb(DBRes::ASSOC_KEY_FORCE, 'filename');
    }

    private function getByStage($stage){
        return (array) Core::$db->exec("SELECT * FROM {$this->table} WHERE stage = {intval($stage)} ORDER BY id DESC")->absorb(DBRes::ASSOC_KEY_FORCE, 'filename');
    }
}