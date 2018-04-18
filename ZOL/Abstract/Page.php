<?php

abstract class ZOL_Abstract_Page
{
	/*
	|---------------------------------------------------------------
	| Array of action permitted by mgr subclass.
	|---------------------------------------------------------------
	| @access  private
	| @var     array
	|
	*/
	protected $_aActionsMapping = array();

	public function addActionMapping(array $aActionMap)
	{
		$this->_aActionsMapping = $aActionMap;
	}

	public function getActionMapping()
	{
		return $this->_aActionsMapping;
	}

	/*
	|---------------------------------------------------------------
	| Specific validations are implemented in sub classes.
	|---------------------------------------------------------------
	| @param   ZOL_Request     $req    ZOL_Request object received from user agent
	| @return  boolean
	|
	*/
	public function validate(ZOL_Request $input, ZOL_Response $output)
	{
		return true;
	}
}
