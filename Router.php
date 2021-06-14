<?php

namespace Routing;

use Controller;

require_once 'Request.php';
require_once 'Response.php';


class Router
{

	public $routes = array();
	public function get($regex, $action)
	{
		$this->addRoute('GET', $regex, $action);
	}

	public function post($regex, $action)
	{
		$this->addRoute('POST', $regex, $action);
	}

	public function request_method()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	public function parse_path_url()
	{
		if ($_SERVER['REQUEST_URI']) {
			$uri = $_SERVER['REQUEST_URI'];
			$uri = str_replace('/router/v3/', '', $uri);
			return $uri;
		}
	}
	//add route
	public function addRoute($method, $regex, $action)
	{
		$regex = trim($regex, '/');
		$this->routes[] = [
			'method' => $method,
			'regex' => $regex,
			'action' => $action
		];
	}


	//    export all routes
	public function getRoutes()
	{
		return $this->routes;
	}
	public function beautifulRoute($regex_route){
		$str = preg_match_all('/{.*?}/', $regex_route, $match);

	}
	public function get_request_data($http_method, $request)
	{
		if ($http_method == 'GET') {
			if (!empty($request->get_get_data())) {
				parse_str($request->get_get_data(), $get_array);
				return $get_array;
			}
			return null;
		}
		if ($http_method == 'POST') {
			if (!empty($request->get_post_data())) {
				return $request->get_post_data();
			}
			return null;
		}
	}



	public function run()
	{

		foreach ($this->routes as $route) {
			$route_regex = $route['regex'];
			//route : $router->get('/contact/?id=:id&name=:str', 'Controller@name');
			//base :
			$beautiful_route_regex = preg_replace('/{.*?}/', ':str', $route['regex']);
			$pattern = "@^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_]+)', preg_quote($beautiful_route_regex)) . "$@D";
			// ? = $_GET pattern
			//$pattern = "@^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_\&\=]+)', preg_quote($route_regex)) . "$@D";
			//$url_pattern =
			//$url_route_regex = preg_replace('/{.*?}/', ':str', $route['regex']);
			// if(preg_match($url_pattern, $url_route_regex)){

			// }
			if (preg_match($pattern, $this->parse_path_url(), $matches) &&  $this->request_method() == $route['method']) {
				$action = explode('@', $route['action']);
				$controller = ucfirst($action[0]);
				$controller_method = $action[1];
				//$params = array_slice($matches, 1) ? array_slice($matches, 1) : null;
				if (file_exists($controller . '.php')) {
					require_once $controller . '.php';
					if (class_exists("$controller") && method_exists($controller, $controller_method)) {
						$obj = new $controller();
						$request = new Request();
						$response = new Response();
						$params = $this->get_request_data($route['method'], $request);
						if ($route['method'] == 'GET') {
							$obj->$controller_method($request, $response, $params);
						}
						if ($route['method'] == 'POST') {
							$obj->$controller_method($request, $response, $params);
						}
					}
				}
			}
		}


	}
}


//$router = new Router();
//$router->get('/users/:id/:uid/:guid', "Controller@test");
//$router->get('/', "Controller@demo");
//$router->run();
///user/:id/delete
// $demo = new Demo;
// $demo->test
//$router->request->get('/home/contactus:slug', "Demo@test");
//$router->get('/admin/login'); //127.0.0.1/product/cafee/1/2


//$router->response->getBody();
