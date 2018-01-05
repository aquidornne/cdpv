<div class="field-box<?php echo $this->FormX->hasError($field) ? ' error' : '' ?>">
    <?php echo $this->Form->label($field, $label); ?>
    <?php echo $this->Form->input($field, $inputOpts); ?>
    <?php echo $this->FormX->validationError($field); ?>
</div>