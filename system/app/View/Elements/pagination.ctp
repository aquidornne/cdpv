<div class="box-footer clearfix">
    <ul class="pagination pagination-sm no-margin pull-right">
        <?php
        echo $this->Paginator->prev('‹', array('tag' => 'li'), null, array('disabledTag' => 'a', 'tag' => 'li'));
        echo $this->Paginator->numbers(array('separator' => '', 'tag' => 'li', 'currentTag' => 'a', 'currentClass' => 'active'));
        echo $this->Paginator->next('›', array('tag' => 'li'), null, array('disabledTag' => 'a', 'tag' => 'li'));
        ?>
    </ul>
</div>