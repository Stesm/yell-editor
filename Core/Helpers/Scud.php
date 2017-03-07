<?
namespace Core\Helpers;

use Core\Interfaces\ViewDataSource;
use Exception;

/**
 * Class Templator
 * @package Core\Helpers
 */
class Scud {
    /** @var array $sources */
    private static $sources = [];

    /** @var string $template */
    private $template = null;
    private $buffer_content = null;
    private $meta = [];

    /**
     * @param $data_source_class
     * @param array $view_list
     * @return bool
     */
    public static function registerDataSource($data_source_class, $view_list = []){
        if(file_exists(APP_ROOT."/App/DataSources/{$data_source_class}.php") && $view_list){
            $class_name = '\App\\DataSources\\'.$data_source_class;

            if(class_exists($class_name) && is_subclass_of($class_name, ViewDataSource::class)){
                /** @var ViewDataSource $class_name */
                if(!is_array($view_list))
                    $views = [$view_list];
                else
                    $views = $view_list;

                foreach($views as $view){
                    if(!array_key_exists($view, self::$sources))
                        self::$sources[$view] = [];

                    self::$sources[$view][] = $class_name;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @param $view
     * @param array $data
     * @return string
     */
    public function render($view, $data = []){
        if(file_exists($file = APP_ROOT."/App/Views/{$view}.php")){
            if(!is_array($data))
                $data = [];

            if(array_key_exists($view, self::$sources)){
                $source_data = [];
                /** @var ViewDataSource $class */
                foreach(self::$sources[$view] as $class){
                    $source_data = array_merge($source_data, $class::data());
                }
            }

            if(isset($source_data))
                $data = array_merge($source_data, $data);

            $html = $this->getContent($file, $data);

            if($this->template && file_exists($extend = APP_ROOT."/App/Views/{$this->template}.php")){
                $this->buffer_content = $html;
                $source_data = [];

                if(array_key_exists($this->template, self::$sources)){
                    /** @var ViewDataSource $class */
                    foreach(self::$sources[$this->template] as $class){
                        $source_data = array_merge($source_data, $class::data());
                    }
                }

                $html = $this->getContent($extend, $source_data);
            }

            return $html;
        }
        return "View {$view} not found.";
    }

    /**
     * @param $path
     * @param array $data
     * @return string
     */
    private function getContent($path, $data = []){
        extract($data);
        unset($data);

        ob_start();
        include $path;
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    /**
     * @param $view
     * @return string
     * @throws Exception
     */
    private function extend($view){
        if(file_exists($file = APP_ROOT."/App/Views/{$view}.php")){
            $this->template = $view;
            return;
        }

        throw new Exception("Template {$view} not exits.");
    }

    private function showMeta(){
        if($this->meta) foreach ($this->meta as $var => $content){
            if($var == 'title'){
                echo "<title>{$content}</title>";
                continue;
            }

            echo "<meta name='{$var}' content='{$content}'>";
        }
    }

    /**
     * @param $var
     * @param $content
     * @return bool
     */
    private function setMeta($var, $content){
        if(preg_match('/^[a-z0-9:-]+$/i', $var)){
            $this->meta[$var] = $content;
            return true;
        }

        return false;
    }

    private function showContent(){
        if($this->buffer_content)
            echo $this->buffer_content;
    }
}