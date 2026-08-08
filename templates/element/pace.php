<?php echo $this->Html->script('pace.min.js', array('defer')); ?>
<script>
    paceOptions = { elements: true };
    $(document).ajaxStart(function () {
        if (typeof Pace !== 'undefined') {
            Pace.restart();
        }
    });
    $(document).ajaxStop(function () {
        if (typeof Pace !== 'undefined') {
            Pace.stop();
        }
    });
</script>