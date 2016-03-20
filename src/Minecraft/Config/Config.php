<?php
namespace Minecraft\Config;

class Config {
    
    static $config = [];
    static $path = '';
    public static function get($name) {
        self::$path = $path = \Minecraft\App::$config['config'].$name.'.php';
		if (file_exists($path)) {
			self::$config = include $path;
		}
        return new static;
    }
    
    public function __get($name) {
        return self::$config[$name];
    }
    
    public function __set($name, $value) {
        self::$config[$name] = $value;
    }
    
    public function save() {
        if(count(self::$config) > 0 && self::$path) {
            $string = "<?php\r\n return [\r\n";
            foreach(self::$config as $name=>$value) {
                $string .= "\t'".$name."' => '".$value."',\r\n";
            }
            $string .= "];";
            file_put_contents(self::$path, $string);
            return true;
        }
        return flase;
    }
    
    public function allArray() {
        return self::$config;
    }
}