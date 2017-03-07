<?
namespace Core\Prototypes;

abstract class Migration implements \Core\Interfaces\Migration {
    public function migrate(){}
    public function rollback(){}
}