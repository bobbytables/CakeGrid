<?php

class GridHelper extends AppHelper {
	public $name = 'Grid';
	public $plugin_name = 'cake_grid';
	
	private $__settings = array();
	private $__columns  = array();
	private $__actions  = array();
	
	/**
	 * Set options for headers and such
	 *
	 * @param string $options 
	 * @return void
	 * @author Robert Ross
	 */
	function options($options){
		$defaults = array(
			'class_header' => 'cg_header',
			'class_row'    => 'cg_row',
			'class_table'  => 'cg_table'
		);
		
		$options = array_merge($defaults, $options);
		
		$this->__settings = $options;
	}
	
	/**
	 * Resets columns and actions so multiple grids may be created
	 *
	 * @return void
	 * @author Robert Ross
	 */
	function reset(){
		$this->__columns = array();
		$this->__actions = array();
	}
	
	/**
	 * Adds a column to the grid
	 *
	 * @param string $title 
	 * @param string $valuePath 
	 * @param array $options 
	 * @return void
	 * @author Robert Ross
	 */
	function addColumn($title, $valuePath, array $options = array()){
		$defaults = array(
			'editable' => false,
			'type' 	   => 'string',
			'element' => false
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
	
	/**
	 * Adds an actions column if it doesnt exist, then creates 
	 *
	 * @param string $name 
	 * @param array $url 
	 * @param array $trailingParams - This is the stuff after /controller/action. Such as /orders/edit/{id}. It's the action parameters in other words
	 * @return void
	 * @author Robert Ross
	 */
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
	
	/**
	 * Generates the entire grid including headers and results
	 *
	 * @param string $results 
	 * @return void
	 * @author Robert Ross
	 */
	function generate($results){
		$View = $this->__view();
		
		//-- Build the columns
		$headers = $View->element('grid_headers', array(
			'plugin' => $this->plugin_name, 
			'headers' => $this->__columns,
			'options' => $this->__settings
		));
		
		$results = $this->results($results);
		
		$generated = $View->element('grid_full', array(
			'plugin'  => $this->plugin_name,
			'headers' => $headers,
			'results' => $results,
			'options' => $this->__settings
		));
		
		return $generated;
	}
	
	/**
	 * Creates the result set inclusive of the actions column (if applied)
	 *
	 * @param string $results 
	 * @return void
	 * @author Robert Ross
	 */
	function results($results = array()){
		$rows = array();
		$View = $this->__view();
		
		foreach($results as $key => $result){
			//-- Loop through columns
			$rowColumns = array();
			
			foreach($this->__columns as $column){
				$rowColumns[] = $this->__generateColumn($result, $column);
			}
			
			$rows[] = $View->element('grid_row', array(
				'plugin' => $this->plugin_name, 
				'zebra' => $key % 2 == 0 ? 1 : 0, 
				'rowColumns' => $rowColumns,
				'options' => $this->__settings
			));
		}
		
		return implode("\n", $rows);
	}
	
	/**
	 * Creates the column based on the type. If there's no type, just a plain ol' string.
	 *
	 * @param string $result 
	 * @param string $column 
	 * @return void
	 * @author Robert Ross
	 */
	private function __generateColumn($result, $column){
		if(!isset($column['valuePath'])){
			$value = $result;
		}
		else {
			$value = Set::extract($column['valuePath'], $result);
			$value = array_pop($value);
		}
		
		if(isset($column['options']['element']) && $column['options']['element'] != false){
			$View = $this->__view();
				
			return $View->element($column['options']['element'], array('result' => $value));
		} else {
			if(isset($column['options']['type']) && $column['options']['type'] == 'date'){
				$value = date('m/d/Y', strtotime($value));
			} else if(isset($column['options']['type']) && $column['options']['type'] == 'money'){
				$value = money_format('%n', $value);
			} else if(isset($column['options']['type']) && $column['options']['type'] == 'actions'){
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
		}
		
		return $value;
	}
	
	/**
	 * Retrieves the view instance from the registry
	 *
	 * @return void
	 * @author Robert Ross
	 */
	private function __view() {
		if (!empty($this->globalParams['viewInstance'])) {
			$View = $this->globalParams['viewInstance'];
		} else {
			$View = ClassRegistry::getObject('view');
		}
		
		return $View;
	}
}