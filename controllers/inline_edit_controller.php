<?php

/**
 * Controller to enable inline editing of grid items
 *
 * @package default
 * @author Robert Ross
 */
class InlineEditController extends CakeGridAppController {
	
	function index(){
		if(empty($this->params['named']['model'])){
			$this->set('success', false);
			return;
		}
		
		if(!empty($this->data)){
			$this->loadModel($this->params['named']['model']);
			
			if($this->{$this->params['named']['model']}->save($this->data)){
				$this->set('success', true);
			}
			else {
				$this->set('success', false);
			}
		}
		else {
			$this->set('success', false);
		}
	}
}