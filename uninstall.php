<?php
if (!defined('ABSPATH')) wp_die('Sorry you can not access here.');
if (!defined('WP_UNINSTALL_PLUGIN')) exit('Sorry you can not access here.');

//Add uninstall scripts here ...

wp_cache_flush();