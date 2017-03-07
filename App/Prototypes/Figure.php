<?
namespace App\Prototypes;

class Figure implements \App\Interfaces\Figure {

    protected $position = [
        'x' => 0,   'y' => 0
    ];
    protected $radius = 10;
    protected $color = [
        'r' => 0,  'g' => 0,  'b' => 0
    ];
    protected $border_size = 1;

    /**
     * @param resource $image
     */
    public function draw(&$image)
    {
        # по дефолту это будет круг

        imagefilledellipse(
            $image,
            $this->position['x'],
            $this->position['y'],
            $this->radius * 2,
            $this->radius * 2,
            $this->getColor($image)
        );
    }

    /**
     * @param $x
     * @param $y
     */
    public function setPosition($x, $y)
    {
        $this->position['x'] = (int) $x;
        $this->position['y'] = (int) $y;
    }

    /**
     * @param $r
     * @param $g
     * @param $b
     */
    public function setColor($r, $g, $b)
    {
        foreach (['r', 'g', 'b'] as $channel){
            $val = abs((int) $$channel);
            $this->color[$channel] = $val > 255 ? 255 : $val;
        }
    }

    /**
     * @param $pixels
     */
    public function setBorder($pixels)
    {
        $this->border_size = (int) $pixels;
    }

    /**
     * @param $image
     * @return int
     */
    protected function getColor(&$image)
    {
        return imagecolorallocate($image, $this->color['r'], $this->color['g'], $this->color['b']);
    }
}
