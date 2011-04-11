<?php

class GridHelper extends AppHelper {
	public $name = 'Grid';
	public $plugin_name = 'cake_grid';
	
	private $__settings = array();
	private $__columns  = array();
	private $__actions  = array();
	
	private $editableIncluded = false;
	
	var $helpers = array('Html');
	
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
			'type' 	   => 'string'
		);
		
		$options = array_merge($defaults, $options);
		
		//-- If this column is editable we need to make sure we include some extra magic
		if(!empty($options['editable'])){
			
			//-- We need three things to enable editing: editKey, editValuePath, and the model
			if(isset($options['editable']['editKey']) && isset($options['editable']['editKey']) && isset($options['editable']['editKey'])){
				if($this->editableIncluded === false){
					$this->Html->script('/cake_grid/js/cake_grid', array('inline' => false));
				}
			}
			else {
				$options['editable'] = array();
			}
		}
		
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
	 * @param array $trailingParams 
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
				$editableOptions = array();
				
				$columnClass = array($column['options']['type']);
				
				if(!empty($column['options']['editable'])){
					$editable = $column['options']['editable'];
					
					$editKey      = $editable['editKey'];
					$editId = array_pop(Set::extract($editable['editId'], $result));
					
					$editableOptions[] = 'model="'.$editable['model'].'"';
					$editableOptions[] = 'edit_key="'.$editKey.'"';
					$editableOptions[] = 'edit_id="'.$editId.'"';
					$columnClass[] = 'cg_editable';
				}
				
				
				$rowColumns[] = array(
					'value' => $this->__generateColumn($result, $column),
					'options' => $column['options'],
					'class' => implode(' ', $columnClass),
					'editableOptions' => $editableOptions
				);
			}
			
			$rows[] = $View->element('grid_row', array('plugin' => $this->plugin_name, 'rowColumns' => $rowColumns));
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
		$value = array_pop(Set::extract($column['valuePath'], $result));
		
		if(isset($column['options']['type']) && $column['options']['type'] == 'date'){
			$value = date('m/d/Y', strtotime($value));
		}
		else if(isset($column['options']['type']) && $column['options']['type'] == 'money'){
			$value = money_format('%n', $value);
		}
		else if(isset($column['options']['type']) && $column['options']['type'] == 'actions'){
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