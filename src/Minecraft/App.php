<?php
/**
 * Minecraft 引擎
 * 
 */

namespace Minecraft;
use Minecraft\Config\Config;

class App {
    static $config = [];
    
    public static function init($config) {
        self::$config = $config;
        
        self::$config['fronts'] = $config['AppPath'].$config['Fronts'];
        self::$config['interfaces'] = '\\'.str_replace('/', '\\', $config['App'].$config['Interfaces']);
        self::$config['pretreats'] = '\\'.str_replace('/', '\\', $config['App'].$config['Pretreats']);
        self::$config['routes'] = $config['AppPath'].$config['Routes'];
        self::$config['models'] = $config['AppPath'].$config['Models'];
        self::$config['config'] = $config['AppPath'].$config['Config'];
        
        $databaseConfig         = Config::get('database')->allArray();
        $frontRouteConfig       = include self::$config['routes'].'front.php';
        $interfaceRouteConfig   = include self::$config['routes'].'interface.php';
        
        // 初始化数据模型
        // 目前采用laravel的Eloquent ORM
        Model\Model::init($databaseConfig);
        
        Router\Route::register(['FrontRoute', 'InterfaceRoute'], [$frontRouteConfig, $interfaceRouteConfig]);
        $route = Router\Route::init()->search();

        if($route instanceof Router\FrontRoute) {
            Front\Front::init($route)->load(
                new Front\Pretreat(), 
                new Iinterface\AutoRequest()
            );
        }
        else if($route instanceof Router\InterfaceRoute) {
            Iinterface\Iinterface::init($route)->load(
                new Front\Pretreat()
            );
        }
    }
}

header("Content-type: text/html; charset=utf-8");
function_exists('date_default_timezone_set') && date_default_timezone_set('Etc/GMT-8');
define('BASE_PATH', substr(dirname(__FILE__), 0, 0 - strlen('Minecraft')));
spl_autoload_register(function($class) {
    $app = str_replace('/', '\\\\', App::$config['App']);
    $path = BASE_PATH;
    if(preg_match('/^'.$app.'/', $class, $match)){
        $path = App::$config['AppPath'].'../';
    }
    require_once $path.str_replace('\\', '/', $class).'.php';
});
?>