<?
namespace Core\Helpers;
class Route {

    private $host;
    private $routes = [];
    private $middlewares = [];
    private $run_stack = [];
    private $uri = [];

    public function __construct(){
        $this->host = @$_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : 'localhost';
        $this->uri = parse_url($_SERVER['REQUEST_URI']);

        if(is_file($file = APP_ROOT.'/App/Includes/http.php'))
            include $file;
        else
            die('No rotes file');
    }

    public function route($path, $handler){
        $this->routes[] = [
            'path' => $path,
            'callable' => $handler
        ];
    }

    public function middleware($path, \Closure $middleware){
        $this->middlewares[$path] = [
            'path' => $path,
            'callable' => $middleware
        ];
    }

    public function start(){
        if(!count($this->routes))
            throw new \Exception('No one route on this site.');

        foreach($this->routes as $rout)
            if($this->check_masks(['rs' => $rout['path']], $vars))
                if($rout['callable'] instanceof \Closure || function_exists($rout['callable'])){
                    $route = $rout + [
                        'data' => $vars
                    ];
                    break;
                }

        if(!isset($route))
            throw new \Exception('No one route for this path.');

        foreach($this->middlewares as $middleware)
            if($middleware['path'] == '*' || $r = $this->check_masks(['rs' => $middleware['path']]))
                if($middleware['callable'] instanceof \Closure || function_exists($middleware['callable']))
                    $this->run_stack[] = $middleware;

        $this->run_stack[] = $route;
        $this->next();
    }

    private function next(){
        $action = array_shift($this->run_stack);
        if(($closure = ($action['callable'] instanceof \Closure)) || function_exists($action['callable'])){
            if(isset($action['data']) && is_array($action['data']) && count($action['data'])){
                extract($action['data']);
                $str = "echo ";
                $str .= $closure ? "\$action['callable'](\$" : "\$\$action['callable'](\$";
                $str .= implode(', $', array_keys($action['data'])).");";

                eval($str);
            }elseif($closure)
                echo $action['callable']();
            else
                echo $$action['callable']();
        }
    }

    private function check_masks($masks = [], &$vars = [], $greed = true){

        $vars = [];
        if(count($masks) > 0){
            foreach($masks as $m_name => $_masks){
                if(!is_array($_masks))
                    $_masks = [$_masks];

                foreach($_masks as $mask){
                    if(preg_match_all('/(#([a-z0-9_-]+)#)+/i', $mask, $matches)){
                        $needles = array_fill(0, count($matches[1]), '([\s_a-zа-я0-9-]+)');
                        $mask = str_replace($matches[1], $needles, $mask);
                    }

                    $mask = '/^'.str_replace('/', '\/', $mask).''.($greed ? '$' : '').'/iu';

                    if(preg_match_all($mask, urldecode($this->uri['path']), $_matches)){
                        foreach($matches[2] as $n => $key)
                            $vars[$key] = $_matches[$n+1][0];
                        return $m_name;
                    }
                }
            }
        }
        return '';
    }

    public function getCurrentPath(){
        return $this->uri['path'];
    }

    public function redirect($path, $code = 301){
         return header('Location: '.$path, 1, intval($code) ? intval($code) : 301) and die();
    }
}
?>