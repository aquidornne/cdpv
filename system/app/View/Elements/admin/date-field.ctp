<div class="span12 field-box<?php echo $this->FormX->hasError($field) ? ' error' : '' ?>">
    <?php echo $this->Form->label($field, $label); ?>
    <div class="datetime-fields">
    	<div class="ui-select">
			<?php echo $this->Form->day($field, $dayInputOpts); ?>
        </div>
    	<div class="ui-select">
		<?php echo $this->Form->month($field, $monthInputOpts); ?>
        </div>
    	<div class="ui-select">
		<?php echo $this->Form->year($field, $minYear, $maxYear, $yearInputOpts); ?>
        </div>
        <?php echo $this->FormX->validationError($field); ?>
    </div>
</div>