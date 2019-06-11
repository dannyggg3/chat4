<div class="login-section">
  <div class="login-container">
    <div class="login-content">
      <div class="login-form">
        <form accept-charset="utf-8" action="<?php echo site_url('c=admin&m=login'); ?>" method="post">
          <fieldset>
            <div class="form-group">
              <input type="email" name="email" id="email" value="<?php echo $this->input->post('email'); ?>" class="form-control" placeholder="Email">
              <?php echo form_error('email'); ?> </div>
            <div class="form-group">
              <input type="password" name="password" id="password" class="form-control" placeholder="Password">
              <?php echo form_error('password'); ?> </div>
            <div class="checkbox">
              <?php $remember_token = $this->input->cookie('remember_token');?>
              <label>
                <input type="checkbox" name="remember_me" <?php if($remember_token) echo ' checked="checked"';?> />
                <?php echo $this->lang->line('remember_me');?></label>
            </div>
            <div class="form-group"><a href="<?php echo site_url('c=admin&m=forget_password') ?>" title="<?php echo $this->lang->line('reset_password');?>"><?php echo $this->lang->line('forgot_your_password');?></a></div>
            <input type="submit" value="<?php echo $this->lang->line('login');?>" class="btn btn-block btn-lg btn-primary">
          </fieldset>
        </form>
      </div>
    </div>
  </div>
</div>
