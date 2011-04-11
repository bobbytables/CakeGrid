<?php foreach($actions as $title => $action): ?>
	<?php echo $this->Html->link($title, $action, array('class' => 'cg_action')); ?>
<?php endforeach; ?>