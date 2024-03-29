<?php

class App{

    protected static $router;

    public static $db;

    /**
     * @return mixed
     */
    public static function getRouter(){
        return self::$router;
    }

    public static function run($uri){
        self::$router = new Router($uri);

        self::$db = new DB(Config::get('db.host'), Config::get('db.db_port'), Config::get('db.user'), Config::get('db.password'), Config::get('db.db_name'));

        Lang::load(self::$router->getLanguage());

        $controller_class = ucfirst(self::$router->getController()).'Controller';
        $controller_method = strtolower(self::$router->getMethodPrefix().self::$router->getAction());

        $layout = self::$router->getRoute();
        Session::set('pages',$layout);
        Session::set('methods',$controller_method);
        if ( $layout == 'admin' && Session::get('role') != 'admin' ){
            if ( $controller_method != 'admin_login' ){
                Router::redirect(Config::get('url_path').'admin/users/login');
            }
        }

        // Calling controller's method
        $controller_object = new $controller_class();
        if ( method_exists($controller_object, $controller_method) ){
            // Controller's action may return a view path
            $view_path = $controller_object->$controller_method();
            $view_object = new View($controller_object->getData(), $view_path);
            $content = $view_object->render();
        } else {
            throw new Exception('Method '.$controller_method.' of class '.$controller_class.' does not exist.');
        }

        $header_path = VIEWS_PATH.DS.'templates/header.html';
        $layout_header_object = new View(compact('header'), $header_path);

        //Content layout
        $layout_path = VIEWS_PATH.DS.$layout.'.html';
        $layout_view_object = new View(compact('content'), $layout_path);

        echo $layout_header_object->render(); //header
        echo $layout_view_object->render();  //content
        
        $footer_path = VIEWS_PATH.DS.'templates/footer.html';
        $layout_footer_object = new View(compact('footer'), $footer_path);
        
        echo $layout_footer_object->render();  //footer

    }

}