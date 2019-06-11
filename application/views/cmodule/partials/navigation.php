<div class="page-sidebar">
    <header class="site-header">
        <div class="site-logo">
            <a href="<?php echo site_url(); ?>" title="<?php echo $this->settings->site_name; ?>">
                <img ng-src="{{settings.site_logo}}" alt="<?php echo $this->settings->site_name; ?>" title="<?php echo $this->settings->site_name; ?>">
            </a>
        </div>
        <div class="sidebar-collapse">
            <a class="sidebar-collapse-icon" href="#" ng-click="toogle_collapsed_sidebar($event)"><i class="collapse-icon fa fa-bars"></i></a> 
        </div>
    </header>
    <div class="sidebar-navigation mCustomScrollbar">
        <ul class="main-menu">	
            <li><a href="<?php echo site_url(''); ?>" class="<?php echo is_active('admin'); ?>"><i class="fa fa-dashboard"></i><span class="title"><?php echo $this->lang->line('dashboard'); ?></span></a></li>
            <li><a href="<?php echo site_url('c=tags'); ?>" class="<?php echo is_active('tags'); ?>"><i class="fa fa-sitemap"></i><span class="title"><?php echo $this->lang->line('departments'); ?></span></a></li>
            <li><a href="<?php echo site_url('c=users'); ?>" class="<?php echo is_active('users'); ?>"><i class="fa fa-users"></i><span class="title"><?php echo $this->lang->line('agents_and_visitors'); ?></span></a></li>
            <li><a href="<?php echo site_url('c=canned_messages'); ?>" class="<?php echo is_active('canned_messages'); ?>"><i class="fa fa-envelope"></i><span class="title"><?php echo $this->lang->line('canned_messages'); ?></span></a></li>
            <li><a href="<?php echo site_url('c=chat_history'); ?>" class="<?php echo is_active('chat_history'); ?>"><i class="fa fa-history"></i><span class="title"><?php echo $this->lang->line('chat_history'); ?></span></a></li>
            <li><a href="<?php echo site_url('c=orequests'); ?>" class="<?php echo is_active('orequests'); ?>"><i class="fa fa-bell"></i><span class="title"><?php echo $this->lang->line('offline_requests'); ?></span></a></li>
            <li><a href="<?php echo site_url('c=feedbacks'); ?>" class="<?php echo is_active('feedbacks'); ?>"><i class="fa fa-comment"></i><span class="title"><?php echo $this->lang->line('feedbacks'); ?></span></a></li>
            <li><a href="<?php echo site_url('c=settings'); ?>" class="<?php echo is_active('settings'); ?>"><i class="fa fa-gear"></i><span class="title"><?php echo $this->lang->line('settings'); ?></span></a></li>
            <?php if ($this->config->item('validated_code') == 'yes'): ?>
                <li><a href="<?php echo site_url('d=agents&c=agents'); ?>" target="_blank" class="<?php echo is_active('agents'); ?> agent-link"><i class="fa fa-user-secret"></i><span class="title"><?php echo $this->lang->line('agent_panel'); ?></span></a></li>
            <?php endif; ?>
                
            <?php if (PRODUCT_NAME != 'chatbull'): ?>
                <li ng-init="upgrade_available()" ng-show="is_upgradable"><a href="<?php echo site_url('c=upgrade'); ?>" class="<?php echo is_active('upgrade'); ?> upgrade-link"><i class="fa fa-cogs"></i><span class="title"><?php echo $this->lang->line('upgrade'); ?></span></a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>