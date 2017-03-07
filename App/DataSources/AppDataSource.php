<?
namespace App\DataSources;

use Core\Core;
use Core\Prototypes\ViewDataSource;

/**
 * Class HeaderData
 * @package App\DataSources
 */
class AppDataSource extends ViewDataSource {

    /**
     * @return array
     */
    public static function data(){
        return [
            'core_version' => Core::version()
        ];
    }
}