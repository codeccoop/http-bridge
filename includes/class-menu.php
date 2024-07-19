<?php

namespace WPCT_HTTP;

use WPCT_ABSTRACT\Menu as BaseMenu;

class Menu extends BaseMenu
{
    protected static $settings_class = '\WPCT_HTTP\Settings';

    protected function render_page($echo = true)
    {
        ob_start();
        ?><div class="wrap">
            <h1><?= $this->name ?></h1>
            <form action="options.php" method="post"><?php
				settings_fields($this->settings->get_group_name());
				do_settings_sections($this->settings->get_group_name());
				submit_button();
			?></form>
        </div><?php
        $output = ob_get_clean();
        echo apply_filters('wpct_http_menu_page_content', $output);
    }
}
