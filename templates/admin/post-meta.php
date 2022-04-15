<?php if(!empty($meta)): ?>
<table class="widefat striped">
	<?php foreach($meta as $key => $values): ?>
		<tr>
			<th><?php echo esc_attr($key); ?></th>
			<td>
				<?php if (count($values) == 1): ?>
					<?php echo esc_html($values[0]); ?>
				<?php else: ?>
					<ol>
						<?php foreach($values as $value): ?>
							<?php echo esc_html($value); ?>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<?php else: ?>
	<p><?php _e('No post meta has been added yet', $this->config->textDomain()); ?></p>
<?php endif; ?>
