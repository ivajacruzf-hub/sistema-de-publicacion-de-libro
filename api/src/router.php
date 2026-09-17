<?php

namespace App;
class Router{
    protected $routes=[];
    public function add($method,$route,$handler)
    {
        $this->routes[]=[
            'method'=>$method,
            'route'=>$route,
            'handler'=>$handler,
        ];
    }
    public function run()
    {
        $uri=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
        // Apache puede conservar el prefijo /api en REQUEST_URI. Normalizamos
        // las barras y lo removemos para comparar con las rutas registradas.
            $scriptDirectory = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
            if ($scriptDirectory !== '' && $scriptDirectory !== '/') {
                $uri = preg_replace('#^' . preg_quote($scriptDirectory, '#') . '#', '', $uri);
            }
            $uri = '/' . trim($uri, '/');
            if ($uri === '//') {
                $uri = '/';
            }
        if (str_starts_with($uri, '/api/')) {
            $uri = substr($uri, 4);
        }
        $method=$_SERVER['REQUEST_METHOD'];
        foreach($this->routes as $route)
            {
                $pattern=str_replace(['{id}','/'],['([0-9]+)','\/'],$route['route']);
                $pattern='/^'.$pattern.'$/';
                if($method===$route['method'] && preg_match($pattern,$uri,$matches))
                    { array_shift($matches);
                        list($controllerName,$methodName)=explode('@',$route['handler']);
                        $controller=new $controllerName();
                        return call_user_func_array([$controller,$methodName],$matches);
                    }
            }
            //si no encontro
            http_response_code(404);
            echo json_encode(["error"=>'Ruta no encontrada']);
    }
}






	
