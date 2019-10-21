<?php
set_time_limit(0);

use App\App;

session_start();

//Mostra erros
error_reporting(E_ALL);
ini_set("display_errors", 1);

//Torna todos caminhos relativos ao diretório raiz.
chdir(dirname(__DIR__));

//Redireciona as requisições quando usando o servidor embutido do php (php -S). faz o mesmo papel do .htaccess / mod_rewrite
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

//Composer autoloading
include __DIR__ . '/../vendor/autoload.php';

App::run();

?>
