<?
namespace App\Controllers;

use App\Interfaces\Figure;
use Core\Core;

/**
 * Class AppController
 * @package App\Controllers
 */
class AppController {

    private $default_image = [
        [
            'type' => 'circle',
            'params' => [
                'color' => [
                    'r' => 242,
                    'g' => 54,
                    'b' => 41
                ],
                'border' => 3,
                'position' => [
                    'x' => 220,
                    'y' => 80
                ]
            ]
        ],[
            'type' => 'rect',
            'params' => [
                'color' => [
                    'r' => 116,
                    'g' => 53,
                    'b' => 242
                ],
                'border' => 1,
                'position' => [
                    'x' => 400,
                    'y' => 60
                ]
            ]
        ]
    ];

    private $canvas_size = [
        'x' => 500, 'y' => 250
    ];

    private $accepted_props = [
        'color' => null,
        'border' => null,
        'position' => null
    ];

    /**
     * @return string
     */
    public function index(){
        return Core::view('index', [
            'params' => isset($_POST['params']) ? $_POST['params'] : $this->default_image
        ]);
    }

    public function imageBuild()
    {
        $data = isset($_GET['params']) ? $_GET['params'] : $this->default_image;
        $image = imagecreatetruecolor($this->canvas_size['x'], $this->canvas_size['y']);
        $class_namespace = 'App\\Figures\\';

        $color = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, $this->canvas_size['x'], $this->canvas_size['y'], $color);

        if(count($data)) foreach ($data as $figure){
            if(!array_key_exists('type', $figure))
                continue;

            $figure['type'] = strtoupper(substr($figure['type'], 0, 1)).strtolower(substr($figure['type'], 1));

            if(!class_exists($fig_class = "$class_namespace{$figure['type']}"))
                continue;

            /** @var Figure $fig */
            $fig = new $fig_class();
            if(array_key_exists('params', $figure) && is_array($figure['params']) && $figure['params']){
                $params = array_intersect_key($figure['params'], $this->accepted_props);
                foreach ($params as $param => $ops)
                    if(method_exists($fig, ($param_method = "set{$param}"))){
                        call_user_func_array([$fig, $param_method], is_array($ops) ? $ops : [$ops]);
                }
            }
            $fig->draw($image);
        }

        header('Content-type: image/png');
        imagepng($image);
        imagedestroy($image);
    }
}