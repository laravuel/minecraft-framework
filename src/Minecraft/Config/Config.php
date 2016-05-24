<?php
namespace Minecraft\Config;

class Config {
    public $config = [];
    public $path = '';

    public function __construct($name = '') {
        return $this->get($name);
    }

    public function get($name) {
        $this->path = \Minecraft\App::$config['config'].$name.'.php';
        if (file_exists($this->path)) {
            $this->config = include $this->path;
        }
        return $this;
    }
    
    public function __get($name) {
        $value = $this->config[$name];
        if(!is_array($value) && !is_null(json_decode($value))) {
            $value = json_decode($value);
        }
        return $value;
    }
    
    public function __set($name, $value) {
        if(is_array($value)) {
            $value = json_encode($value);
        }
        $this->config[$name] = $value;
    }
    
    public function save() {
        if(count($this->config) > 0 && $this->path) {
            $string = "<?php\r\n return [\r\n";
            foreach($this->config as $name=>$value) {

                $string .= "\t'".$name."' => '".$value."',\r\n";
            }
            $string .= "];";
            file_put_contents($this->path, $string);
            return true;
        }
        return flase;
    }
    
    public function allArray() {
        foreach($this->config as $k=>$v) {
            if(!is_null(json_decode($v))) {
                $v = json_decode($v, true);
            }
            $this->config[$k] = $v;
        }
        return $this->config;
    }
}