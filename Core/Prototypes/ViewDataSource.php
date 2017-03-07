<?
namespace Core\Prototypes;

/**
 * Class ViewDataSource
 * @package Core\Prototypes
 */
abstract class ViewDataSource implements \Core\Interfaces\ViewDataSource {
    /**
     * @return array
     */
    public static function data(){
        return [];
    }
}