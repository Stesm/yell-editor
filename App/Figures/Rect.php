<?
namespace App\Figures;

use App\Prototypes\Figure;

class Rect extends Figure {

    private $size = [
        'x' => 40,  'y' => 40
    ];

    public function draw(&$image)
    {
        imagefilledrectangle(
            $image,
            $this->position['x'],
            $this->position['y'],
            ($this->position['x'] - $this->size['x']),
            ($this->position['y'] - $this->size['y']),
            $this->getColor($image)
        );

        $this->setColor(0,0,0);
        imagesetthickness($image, $this->border_size);
        imagerectangle(
            $image,
            $this->position['x'],
            $this->position['y'],
            $this->position['x'] - $this->size['x'],
            $this->position['y'] - $this->size['y'],
            $this->getColor($image)
        );
    }
}