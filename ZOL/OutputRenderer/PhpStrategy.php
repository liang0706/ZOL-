<?php

class ZOL_OutputRenderer_PhpStrategy extends ZOL_Abstract_OutputRendererStrategy
{

	public function render(ZOL_Abstract_View $view)
	{
		$php = $this->_initEngine($view->data);
		if (!ZOL_File::exists($php->getTemplate()))
		{
			throw new ZOL_Exception('The template dose not exist or is not readable: ' . $php->getTemplate());
		}
		$variables = $php->getBody();
		if (!empty($variables))
		{
			extract($variables);
		}
		ob_start();
		include $php->getTemplate();
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	protected function _initEngine(ZOL_Response $response)
	{
		return $response;
	}
}
