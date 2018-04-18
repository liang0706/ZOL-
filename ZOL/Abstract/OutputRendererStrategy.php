<?php

/*
|---------------------------------------------------------------
| output renderer strategy
|---------------------------------------------------------------
| @package ZOL
|
*/

abstract class ZOL_Abstract_OutputRendererStrategy
{

    public function __construct()
    {
    }

    abstract protected function _initEngine(ZOL_Response $data);


    abstract public function render(ZOL_Abstract_View $view);
}
