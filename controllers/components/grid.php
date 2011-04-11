<?php

class GridComponent extends Object {
	/**
	 * Controller variable
	 *
	 * @var string
	 */
	public $controller = null;
	
	/**
	 * Component startup
	 *
	 * @param Controller $controller 
	 * @param string $settings 
	 * @return void
	 * @author Robert Ross
	 */
	function initialize(Controller $controller, $settings = array()){
		$this->controller &= $controller;
		
		$this->controller->helpers[] = 'Grid';
	}
}

?>