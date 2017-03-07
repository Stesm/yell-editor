<?

/** @var \Core\Helpers\Route $this */

$this->route('/', function() {
    return (new App\Controllers\AppController)->index();
});

$this->route('/image', function() {
    (new App\Controllers\AppController)->imageBuild();
});