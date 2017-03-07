<?
namespace Core;
use Core\Helpers\Route;
use Core\Helpers\Scud;

/**
 * Class Core
 * @package Core
 */
class Core {
    /** @var \Core\Helpers\Route $router */
    public static $route = null;
    /** @var \Core\Helpers\DBConn $router */
    public static $db = null;
    /** @var \Core\Helpers\Scud $router */
    public static $template = null;

    /** @var array $params */
    private static $params = [];
    /** @var string $version */
    private static $version = '3.0.000';

    public static function load(){
        if(!isset($_SESSION))
            session_start();

        if(is_file($file = APP_ROOT.'/Core/config.php'))
            self::$params = @include($file);
        else
            die('No config, check "Core/config.php"');

        if(php_sapi_name() != 'cli'){
            self::$route = new Route();
            self::$template = new Scud();
        }

        if(self::$params['DB'] && self::$params['DB']['driver'] && class_exists(self::$params['DB']['driver']))
            self::$db = new self::$params['DB']['driver']();

        if(@self::$params['ENV'] && self::$params['ENV'] == 'debug'){
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }else{
            error_reporting(0);
            ini_set('display_errors', 0);
        }

        if(file_exists($file = APP_ROOT.'/App/Includes/data.sources.php'))
            @include_once $file;
    }

    /**
     * @param $var
     * @param bool|false $dump_name
     */
    public static function dump($var, $dump_name = false){
        $GLOBALS['dump']++;
        if($dump_name)
            echo '<strong>'.$GLOBALS['dump'].'. '.$dump_name.'</strong>';
        else
            echo '<strong>Dump : '.$GLOBALS['dump'].'</strong>';
        echo '<pre>'.print_r($var, true).'</pre>';
    }

    /**
     * @param string $string
     * @return string
     */
    public static function translit($string = ''){
        if(!trim($string))
            return $string;

        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
            ' ' => '_',   '-' => '_'
        );
        return strtr($string, $converter);
    }

    /**
     * @param $symbols_count
     * @param string $str
     * @return bool|string
     */
    public static function rand_str($symbols_count, $str = ''){
        if(!intval($symbols_count))
            return false;
        if(!trim($str))
            $str = 'qwertyuiopasdfghjklzxcvbnm0123456789';
        $rand_str = '';
        for($i = 0; $i < $symbols_count; $i++){
            $rand = rand(0, strlen($str)-1);
            $rand_str .= $str[$rand];
        }
        return $rand_str;
    }

    /**
     * @param $path
     * @return bool
     */
    public static function deleteRecursive($path){
        if(is_dir($path)){
            $dh = opendir($path);
            while(($file = readdir($dh)) !== false){
                if(!in_array($file,['.','..'])){
                    if(is_file($path.$file)){
                        if(!unlink($path.$file))
                            return false;
                    }else
                        self::deleteRecursive($path.$file.'/');
                }
            }
            closedir($dh);
            rmdir($path);
        }
        return true;
    }

    /**
     * @param $from
     * @param $to
     * @return bool
     */
    public static function copyRecursive($from, $to){
        if(is_dir($from)){
            if(!is_dir($to)){
                if(!mkdir($to, 0777, true))
                    return false;
            }

            $dh = opendir($from);
            while(($file = readdir($dh)) !== false){
                if(!in_array($file,['.','..'])){
                    if(is_file($from.$file)){
                        if(!copy($from.$file, $to.$file)){
                            if(is_file($to.$file)){
                                if(!unlink($to.$file))
                                    return false;
                                if(!copy($from.$file, $to.$file))
                                    return false;
                            }
                        }
                    }else
                        self::copyRecursive($from.$file.'/', $to.$file.'/');
                }
            }
            return true;
        }else
            return false;
    }

    /**
     * @param $name
     * @param bool|false $default
     * @return bool
     */
    public static function getConfig($name, $default = false){
        return isset(self::$params[$name]) ? self::$params[$name] : $default;
    }

    /**
     * @param $view_name
     * @param array $data
     * @return string
     */
    public static function view($view_name, $data = []){
        return self::$template->render($view_name, $data);
    }

    /**
     * @return string
     */
    public static function version(){
        return self::$version;
    }
}