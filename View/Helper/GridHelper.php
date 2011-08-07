<?php

class GridHelper extends AppHelper {
	public $name = 'Grid';
	public $plugin_name = 'CakeGrid';
	
	/**
	 * Load html helper for links and such
	 *
	 * @var string
	 */
	var $helpers = array('Html');
	
	/**
	 * Settings for html classes and such
	 *
	 * @var string
	 */
	private $__settings = array();
	
	/**
	 * THe columns for the grid
	 *
	 * @var string
	 */
	private $__columns  = array();
	
	/**
	 * Actions column (if any)
	 *
	 * @var string
	 */
	private $__actions  = array();
	
	/**
	 * Totals for columns (if any)
	 *
	 * @var string
	 */
	private $__totals   = array();
	
	/**
	 * Directory where the grid elements are located. Makes for easy switching around.
	 * Out of the box we'll support table and csv.
	 *
	 * @var string
	 */
	var $elemDir;
	
	
	/**
	 * Settings setup
	 *
	 * @author Robert Ross
	 */
	function __construct(View $View, $settings = array()){
	     parent::__construct( $View,$settings);
		$this->options(array());
	}
	
	/**
	 * Set options for headers and such
	 *
	 * @param string $options 
	 * @return void
	 * @author Robert Ross
	 */
	function options($options){
		$defaults = array(
			'class_header'  => 'cg_header',
			'class_row'     => 'cg_row',
			'class_table'   => 'cg_table',
			'empty_message' => 'No Results',
			'separator'     => ' ',
			'type'          => 'table'
		);
		
		$options = array_merge($defaults, $options);
		
		$this->__settings = $options;
		
		//-- Set the directory we'll be looking for elements
		$this->elemDir = $this->__settings['type'];
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
			'element'  => false,
			'linkable' => false,
			'total'    => false
		);
		
		$options = array_merge($defaults, $options);
		
		$titleSlug = Inflector::slug($title);
		
		$this->__columns[$titleSlug] = array(
			'title'     => $title,
			'valuePath' => $valuePath,
			'options'   => $options
		);
		
		if($options['total'] == true){
			$this->__totals[$title] = 0;
		}
		
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
	function addAction($name, array $url, array $trailingParams = array(), array $options = array()){
		$this->__actions[$name] = array(
			'url'  			 => $url,
			'trailingParams' => $trailingParams,
			'options'        => $options
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
		$View = $this->_View;

		$directory = $this->__settings['type'];
		
		if($this->__settings['type'] == 'csv' && !empty($this->__totals)){
			array_unshift($this->__columns, array(
				'title' => '',
				'valuePath' => '',
				'options' => array(
					'type' => 'empty'
				)
			));
		}
				
		//-- Build the columns
		$headers = $View->element($this->elemDir . DS . 'grid_headers', array(
			'plugin'  => $this->plugin_name, 
			'headers' => $this->__columns,
			'options' => $this->__settings
		),
        array(			'plugin'  => $this->plugin_name)
        );
	
        $results = $this->results($results);	
		$generated = $View->element($this->elemDir . DS . 'grid_full', array(
         //   'plugin'  => $this->plugin_name,
			'headers' => $headers,
			'results' => $results,
			'options' => $this->__settings
		),
        array(	'plugin'  => $this->plugin_name)
        );
        
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
		$View = $this->_View;
		
		foreach($results as $key => $result){
			//-- Loop through columns
			$rowColumns = array();
			
			foreach($this->__columns as $column){
				$rowColumns[] = $this->__generateColumn($result, $column);
			}
			
			$rows[] = $View->element($this->elemDir . DS . 'grid_row', array(
				'plugin'     => $this->plugin_name, 
				'zebra'      => $key % 2 == 0 ? 'odd' : 'even', 
				'rowColumns' => $rowColumns,
				'options'    => $this->__settings
			),
            array(	'plugin'  => $this->plugin_name)
            );
		}
		
		if(!empty($this->__totals)){
			$totalColumns = array();
			
			$i = 0;
			foreach($this->__columns as $column){
				if($i == 0){
					$totalColumns[] = 'Total';
					$i++;
					continue;
				}
				$i++;
				
				if(isset($this->__totals[$column['title']])){
					if($column['options']['type'] == 'money'){
						$total = money_format("%n", $this->__totals[$column['title']]);
					} else if($column['options']['type'] == 'number'){
						$total = number_format($this->__totals[$column['title']]);
					}
					
					if($this->__settings['type'] == 'csv'){
						$total = floatval(str_replace(array('$', ','), '', $total));
						$totalColumns[] = $total;
						continue;
					}
					
					$totalColumns[] = $total . ' (total)';
					continue;
				}
				
				$totalColumns[] = '';
			}
			
			$rows[] = $View->element($this->elemDir . DS . 'grid_row', array(
				'plugin' 	 => $this->plugin_name,
				'rowColumns' => $totalColumns,
				'options'    => $this->__settings,
				'zebra'		 => 'totals'
			),
            array(	'plugin'  => $this->plugin_name)
            );
		}
		
		//-- Upon review, this if statement is hilarious
		if(empty($rows) && !empty($this->__settings['empty_message'])){
			$rows[] = $View->element($this->elemDir . DS . 'grid_empty_row', array(
				'plugin' => $this->plugin_name,
				'colspan' => sizeof($this->__columns) + (sizeof($this->__actions) ? 1 : 0),
				'options'    => $this->__settings
			),
            array(	'plugin'  => $this->plugin_name));
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
		if($column['options']['type'] == 'empty'){
			return '';
		}
		
		if(!isset($column['valuePath'])){
			$value = $result;
		} else if(!is_array($column['valuePath'])) {
			$value = Set::extract($column['valuePath'], $result);
			$value = array_pop($value);
		} else if(is_array($column['valuePath'])){
			$valuePath = $column['valuePath'];
			
			if($valuePath['type'] == 'concat'){
				$separator = isset($valuePath['separator']) ? $valuePath['separator'] : $this->__settings['separator'];
				unset($valuePath['type'], $valuePath['separator']);
				
				$values = array();
				foreach($valuePath as $path){
					$extracted = Set::extract($path, $result);
					$values[] = array_pop($extracted);
				}
				
				$value = implode($separator, $values);
			} else if($valuePath['type'] == 'format'){
				$format = $valuePath['with'];
				unset($valuePath['type'], $valuePath['with']);
				
				$values = array($format);
				foreach($valuePath as $path){
					$extracted = (array) Set::extract($path, $result);
					$values[] = array_pop($extracted);
				}
				
				$value = call_user_func_array('sprintf', $values);
			}
		}
		
		//-- Total things up if needed
		if(isset($column['options']['total']) && $column['options']['total'] == true){
			$this->__totals[$column['title']] += $value;
		}
		
		if(isset($column['options']['element']) && $column['options']['element'] != false){
			$View = $this->_View;
				
			return  $View->element($this->elemDir . DS . $column['options']['element'], array('result' => $value));
		} else {
			if(isset($column['options']['type']) && $column['options']['type'] == 'date'){
				$value = date('m/d/Y', strtotime($value));
			} else if(isset($column['options']['type']) && $column['options']['type'] == 'datetime'){
				$value = date('m/d/Y h:ia', strtotime($value));
			} else if(isset($column['options']['type']) && $column['options']['type'] == 'money' && $this->__settings['type'] != 'csv'){
				$value = money_format('%n', $value);
			} else if(isset($column['options']['type']) && $column['options']['type'] == 'actions'){
           		$View = $this->_View;
				$actions = array();
			
				//-- Need to retrieve the results of the trailing params
				foreach($this->__actions as $name => $action){
					//-- Check to see if the action is supposed to be hidden for this result (set in the controller)
					if(isset($result['show_actions']) && is_array($result['show_actions']) && !in_array($name, $result['show_actions'])){
						continue;
					}
					
					//-- Need to find the trailing parameters (id, action type, etc)
					$trailingParams = array();
					if(!empty($action['trailingParams'])){
						foreach($action['trailingParams'] as $key => $param){
							$trailingParams[$key] = array_pop(Set::extract($param, $result));
						}
					}
				
					$actions[$name] = array(
						'url' => Router::url($action['url'] + $trailingParams),
						'options' => $action['options']
					);
				}
			
				return $View->element($this->elemDir . DS . 'column_actions', array( 'actions' => $actions), array('plugin' => $this->plugin_name));
			}
		}
		
		//-- Check if it's linkable
		if(is_array($column['options']['linkable']) && !empty($column['options']['linkable'])){
			$trailingParams = array();
			
			$linkable = $column['options']['linkable'];
			
			if(!empty($linkable['trailingParams']) && is_array($linkable['trailingParams'])){
				foreach($linkable['trailingParams'] as $key => $param){
					$trailingParams[$key] = array_pop(Set::extract($param, $result));
				}
			}
			
			$url = $linkable['url'] + $trailingParams;
			$linkable['options'] = !isset($linkable['options']) ? array() : $linkable['options'];
			
			$value = $this->Html->link($value, $url, $linkable['options']);
		}
		
		return $value;
	}
	
	/**
	 * Function to return escaped csv data since PHP doesn't have a function out of the box
	 * Taken from http://php.net/manual/en/function.fputcsv.php comment
	 *
	 * @param string $data 
	 * @return void
	 * @author Robert Ross
	 */
	function csvData($data){
		$fp  = false;
		$eol = "\n";
		
		if ($fp === false) {
		    $fp = fopen('php://temp', 'r+');
		} else {
		    rewind($fp);
		}

		if (fputcsv($fp, $data) === false) {
		    return false;
		}

		rewind($fp);
		$csv = fgets($fp);

		if ($eol != PHP_EOL){
		    $csv = substr($csv, 0, (0 - strlen(PHP_EOL))) . $eol;
		}
		
		//-- For out purpose... we don't want another \n
		$csv = substr($csv, 0, strlen($eol) * -1);
		
		return $csv;
	}
	
	/**
	 * Retrieves the view instance from the registry
	 *
	 * @return void
	 * @author Robert Ross
	 */
//	private function __view() {
//		if (!empty($this->globalParams['viewInstance'])) {
//			$View = $this->globalParams['viewInstance'];
//		} else {
//			$View = ClassRegistry::getObject('view');
//		}
//		
//		return $View;
//	}
}