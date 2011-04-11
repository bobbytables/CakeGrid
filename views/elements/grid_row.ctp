<tr class="cg_zebra_<?php echo $zebra ?>">
	<?php foreach($rowColumns as $column): ?>
	<td class="<?php echo $column['class'] ?>" <?php echo implode(' ', $column['editableOptions']); ?>>
		<?php echo $column['value'] ?>
	</td>
	<?php endforeach; ?>
</tr>