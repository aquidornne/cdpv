<div class="field-box<?php echo $this->FormX->hasError($field) ? ' error' : '' ?>">
	<?php echo $this->Form->label($field, $label); ?>
	<label class="checkbox-inline" for="<?php echo $fieldId ?>">
		<div class="checker"><span><?php echo $this->Form->checkbox($field, $inputOpts); ?></span></div> <?php echo $labelCheck; ?>
	</label>
	<?php echo $this->FormX->validationError($field); ?>
</div>