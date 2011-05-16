<?php foreach($actions as $title => $action): ?>
	<?php echo $this->Html->link($title, $action['url'], $action['options'] + array('class' => 'cg_action')); ?>
<?php endforeach; ?>