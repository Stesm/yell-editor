<?
namespace App\Interfaces;

interface Figure {
    public function draw(&$image);
    public function setPosition($x, $y);
    public function setColor($r, $g, $b);
    public function setBorder($pixels);
}