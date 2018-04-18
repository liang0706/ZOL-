<?php

/*
|---------------------------------------------------------------
| Container for output data and renderer strategy.
|---------------------------------------------------------------
| @package ZOL
|
*/

abstract class ZOL_Abstract_View
{

    /*
    |---------------------------------------------------------------
    | Response object.
    |---------------------------------------------------------------
    | @var ZOL_Response
    */
    public $data;

    /*
    |---------------------------------------------------------------
    | Reference to renderer strategy.
    |---------------------------------------------------------------
    | @var ZOL_OutputRendererStrategy
    */
    protected $_rendererStrategy;

    /*
    |---------------------------------------------------------------
    | Constructor.
    |---------------------------------------------------------------
    | @param ZOL_Response $data
    | @param ZOL_OutputRendererStrategy $rendererStrategy
    | @return ZOL_View
    */
    public function __construct(ZOL_Response $response, ZOL_Abstract_OutputRendererStrategy $rendererStrategy)
    {
        $this->data = $response;
        $this->_rendererStrategy = $rendererStrategy;
    }

    /*
    |---------------------------------------------------------------
    | Post processing tasks specific to view type.
    |---------------------------------------------------------------
    | @param ZOL_View $view
    | @return boolean
    */
    abstract public function postProcess(ZOL_View $view);

    /*
    |---------------------------------------------------------------
    | Delegates rendering strategy based on view.
    |---------------------------------------------------------------
    | @param ZOL_View $this
    | @return string   Rendered output data
    */
    public function render()
    {
        return $this->_rendererStrategy->render($this);
    }
}
