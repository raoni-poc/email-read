<?php

use App\App;
use App\Config;
use App\Factory\ConfigFactory;
use App\Modules\IMAP\Factory\IMAPFactory;
use App\Modules\IMAP\HostConfig\GmailHostConfig;
use App\Modules\IMAP\IMAP;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\ServiceManager\ServiceManager;

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


//Zend Service Manager
$serviceManager = new ServiceManager([
    'factories' => [
        Config::class => ConfigFactory::class,
        IMAP::class => IMAPFactory::class,
        GmailHostConfig::class => InvokableFactory::class,
    ],
]);

App::run($serviceManager);

?>
