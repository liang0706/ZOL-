<?php

/*
|---------------------------------------------------------------
| Wrapper for simple HTML views.
|---------------------------------------------------------------
| @package ZOL
|
*/

class ZOL_View_Simple extends ZOL_Abstract_View
{
    /*
    |---------------------------------------------------------------
    | HTML renderer decorator
    |---------------------------------------------------------------
    | @param ZOL_Response $data
    | @param string $templateEngine
    |
    */
    public function __construct(ZOL_Response $response, $templateEngine = null)
    {
        //  prepare renderer class
        if (is_null($templateEngine)) {
            $templateEngine = 'php';
        }
        $templateEngine =  ucfirst($templateEngine);
        $rendererClass  = 'ZOL_OutputRenderer_' . $templateEngine . 'Strategy';

        parent::__construct($response, new $rendererClass);
    }

    public function postProcess(ZOL_View $view)
    {
        // do nothing
    }
}
