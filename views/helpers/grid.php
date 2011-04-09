<?php

class GridHelper extends AppHelper {
	public $name = 'Grid';
	public $plugin_name = 'cake_grid';
	
	private $__settings = array();
	private $__columns  = array();
	private $__actions  = array();
	
	function addColumn($title, $valuePath, array $options = array()){
		$defaults = array(
			'editable' => false,
			'type' 	   => 'string'
		);
		
		$options = array_merge($defaults, $options);
		
		$titleSlug = Inflector::slug($title);
		
		$this->__columns[$titleSlug] = array(
			'title'     => $title,
			'valuePath' => $valuePath,
			'options'   => $options
		);
		
		return $titleSlug;
	}
	
	function addAction($name, array $url, array $trailingParams = array()){
		$this->__actions[$name] = array(
			'url'  			 => $url,
			'trailingParams' => $trailingParams
		);
		
		if(!isset($this->__columns['actions'])){
			$this->addColumn('Actions', null, array('type' => 'actions'));
		}
		
		return true;
	}
	
	function generate($results){
		$View = $this->__view();
		
		//-- Build the columns
		$headers = $View->element('grid_headers', array(
			'plugin' => $this->plugin_name, 
			'headers' => $this->__columns
		));
		
		$results = $this->results($results);
		
		$generated = $View->element('grid_full', array(
			'plugin'  => $this->plugin_name,
			'headers' => $headers,
			'results' => $results
		));
		
		return $generated;
	}
	
	function results($results = array()){
		$rows = array();
		$View = $this->__view();
		
		foreach($results as $key => $result){
			//-- Loop through columns
			$rowColumns = array();
			
			foreach($this->__columns as $column){
				$rowColumns[] = $this->__generateColumn($result, $column);
			}
			
			$rows[] = $View->element('grid_row', array('plugin' => $this->plugin_name, 'rowColumns' => $rowColumns));
		}
		
		return implode("\n", $rows);
	}
	
	private function __generateColumn($result, $column){
		$value = array_pop(Set::extract($column['valuePath'], $result));
		
		if(isset($column['type']) && $column['type'] == 'date'){
			$value = date('m/d/Y', strtotime($value));
		}
		else if(isset($column['type']) && $column['type'] == 'money'){
			$value = money_format('%n', $value);
		}
		else if(isset($column['type']) && $column['type'] == 'actions'){
			$View = $this->__view();
			$actions = array();
			
			//-- Need to retrieve the results of the trailing params
			foreach($this->__actions as $name => $action){
				
				//-- Need to find the trailing parameters (id, action type, etc)
				$trailingParams = array();
				if(!empty($action['trailingParams'])){
					foreach($action['trailingParams'] as $key => $param){
						$trailingParams[$key] = array_pop(Set::extract($param, $result));
					}
				}
				
				$actions[$name] = Router::url($action['url'] + $trailingParams);
			}
			
			return $View->element('column_actions', array('plugin' => $this->plugin_name, 'actions' => $actions), array('Html'));
		}
		
		return $value;
	}
	
	private function __view() {
		if (!empty($this->globalParams['viewInstance'])) {
			$View = $this->globalParams['viewInstance'];
		} else {
			$View = ClassRegistry::getObject('view');
		}
		
		return $View;
	}
}