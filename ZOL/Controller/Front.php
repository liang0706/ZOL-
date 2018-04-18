<?php

/*
|---------------------------------------------------------------
| 控制器
|---------------------------------------------------------------
| @package ZOL
|
*/
class ZOL_Controller_Front
{
	public static function run()
	{
		ZOL_Registry::set('request', new ZOL_Request);
		$request = ZOL_Registry::get('request');

		ZOL_Registry::set('response', new ZOL_Response());
		$response = ZOL_Registry::get('response');

		$controller = $request->getControllerName();
		$action = $request->getActionName();

		$controller = ZOL_String::toValidVariableName($controller);
		$action = ZOL_String::toValidVariableName($action);

		if (empty($controller))
		{
			throw new ZOL_Exception("The controller of '$controller' is empty in request!");
		}
		if (empty($action))
		{
			throw new ZOL_Exception("The action of '$action' is empty in request!");
		}

		$controller =  APP_NAME . '_Page_' . ucfirst($controller);
//		var_dump($controller);
		$page = new $controller($request, $response);
		if ($page->validate($request, $response)) {

			$actionMap = $page->getActionMapping();
			if (empty($actionMap)) {
				$action = 'do' . ucfirst($action);
				if (method_exists($page, $action)) {
					$page->$action($request, $response);
				} else {
					throw new ZOL_Exception("The function of '{$action}' does not exist in class '$controller'!");
				}
			} else {
				foreach($actionMap[$action] as $methodName) {
					$methodName = 'do' . ucfirst($methodName);
					if (method_exists($page, $methodName)) {
						$page->$methodName($request, $response);
					} else {
						throw new ZOL_Exception(' the function dose not exist:' . $methodName );
					}
				}
			}
		}
		$response->display();
	}
}
