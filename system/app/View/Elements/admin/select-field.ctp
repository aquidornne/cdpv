<div class="span12 field-box<?php echo $this->FormX->hasError($field) ? ' error' : '' ?>">
    <?php echo $this->Form->label($field, $label); ?>
    <div class="ui-select span5">
		<?php echo $this->Form->input($field, $inputOpts); ?>
        <?php echo $this->FormX->validationError($field); ?>
    </div>
</div>