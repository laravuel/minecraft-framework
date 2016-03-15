<?php
namespace Minecraft\Config;

class Config {
    
    static $config = [];
    public static function get($name) {
        $path = \Minecraft\App::$config['config'].$name.'.php';
		if (file_exists($path)) {
			self::$config = include $path;
		}
        return new static;
    }
    
    public function __get($name) {
        return self::$config[$name];
    }
    
    public function allArray() {
        return self::$config;
    }
}