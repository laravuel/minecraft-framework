<?php
namespace Minecraft\Front;

class Front {
    
    static $route;
    
    public static function init(\Minecraft\Router\Route $route) {
        
        if(!$route->route) {
            // 抛出一个错误异常类 ??????
            exit('<h3 style="color:#666; font-family:\'微软雅黑\'; text-align:center; margin-top:20%; font-size:80px; font-weight:bold;">404 Page</h3>');
        }
        self::$route = $route;
        return new static;
    }
    
    public function load(\Minecraft\Front\Pretreat $pretreat, \Minecraft\Iinterface\AutoRequest $autoRequest) {
        
        
        $fronts = array_merge(self::$route->beforeFronts, self::$route->fronts);
        $fronts = array_merge($fronts, self::$route->afterFronts);
        $this->removeElement($fronts);
        
        
        /* 是否需要预处理 */
        if(self::$route->pretreats) {
            $this->removeElement(self::$route->pretreats);
            $pretreat->init(self::$route, $this);
        }
        
        foreach($fronts as $nFront) {
            $this->import($nFront);
        }
        
        /* 是否需要自动请求接口 */
        if(self::$route->autoRequests) {
            $autoRequest->init(self::$route);
        }
    }
    
    /**
     * 当预处理return数据时，执行该方法
     * @data 预处理所返回的数据
     */
    public function resPretreat($data) {
        if($data['res_tpl']) {
            $this->import($data['res_tpl']);
        }
        else {
            if($data['url']) {
                $script = '<script>setTimeout(function(){location.href=\''.\Minecraft\App::$config['WebPath'].$data['url'].'\';}, 2000)</script>';
            }
            else {
                $script = '<a href="javascript:history.back();" style="display:block; font-size:25px; color:#999; text-decoration:none;"><< 返回上一页</a>';
            }
            echo '<h3 style="color:#666; font-family:\'微软雅黑\'; text-align:center; margin-top:20%; font-size:80px; font-weight:normal;">'.$data['msg'].'...'.$script.'</h3>';
        }
        if($data['res'] > 0) {
            exit();
        }
    }
    
    /**
     * 移除路由所定义的元素
     */
    protected function removeElement(&$datas) {
        $newFronts = [];
        foreach($datas as $key=>$data) {
            if($data[0] == self::$route->keys['del_flg']) {
                $delFront = substr($data, 1, strlen($data));
                for($i = 0; $i < $key; $i++) {
                    if($datas[$i] == $delFront) {
                        unset($datas[$i]);
                    }
                }
                unset($datas[$key]);
            }
        }
    }
    
    public function import($frontPath = '') {
        $path = \Minecraft\App::$config['fronts'];
        $path .= str_replace('.', '/', $frontPath);
        $tpl = $path.'.php';
        if(!file_exists($tpl)) {
            // 抛出一个错误异常类 暂时用exit临时代替 ??????
            echo '<h3 style="color:#666; font-family:\'微软雅黑\'; text-align:center; margin:10% 0; font-size:30px; font-weight:bold;">Front Error : '.$frontPath.'</h3>';
            return false;
        }
        include $tpl;
    }
}