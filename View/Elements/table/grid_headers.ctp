<tr class="<?php echo $options['class_header'] ?>">
	<?php foreach($headers as $header) { ?>
		<th>
	<?php	if ($header['options']['paginate']) {
			echo $this->Paginator->sort(strtolower($header['title']));
		} else {
			echo $header['title'];
		}?>
		</th>
	<?php } ?>
</tr>