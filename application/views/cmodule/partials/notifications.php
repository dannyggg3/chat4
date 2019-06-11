<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success">
        <a href="#" class="close" aria-label="close" data-dismiss="alert">&times;</a>
        <div><strong> CONFIRMATION : </strong> <?php echo $this->session->flashdata('success'); ?></div>
    </div>
<?php elseif ($this->session->flashdata('error')) : ?>
    <div class="alert alert-danger">
        <a href="#" class="close" aria-label="close" data-dismiss="alert">&times;</a>
        <div><strong> ERROR : </strong> <?php echo $this->session->flashdata('error'); ?></div>
    </div>
<?php endif; ?>