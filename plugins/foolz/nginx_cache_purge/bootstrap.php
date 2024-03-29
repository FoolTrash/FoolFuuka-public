<?php

if (!defined('DOCROOT'))
	exit('No direct script access allowed');

\Foolz\Plugin\Event::forge('foolz\plugin\plugin.execute.foolz/nginx_cache_purge')
	->setCall(function($result) {
		\Autoloader::add_classes(array(
			'Foolfuuka\\Plugins\\Nginx_Cache_Purge\\Nginx_Cache_Purge' => __DIR__.'/classes/model/nginx_cache_purge.php',
			'Foolfuuka\\Plugins\\Nginx_Cache_Purge\\Controller_Plugin_Fu_Nginx_Cache_Purge_Admin_Nginx_Cache_Purge'
				=> __DIR__.'/classes/controller/admin/nginx_cache_purge.php'
		));

		// don't add the admin panels if the user is not an admin
		if (\Auth::has_access('maccess.admin'))
		{
			\Router::add('admin/plugins/nginx_cache_purge', 'plugin/fu/nginx_cache_purge/admin/nginx_cache_purge/manage');

			\Plugins::register_sidebar_element('admin', 'plugins', array(
				"content" => array("nginx_cache_purge" => array("level" => "admin", "name" => 'Nginx Cache Purge', "icon" => 'icon-leaf'))
			));
		}

		\Foolz\Plugin\Event::forge('foolfuuka\\model\\media.delete.call.before')
			->setCall('Foolfuuka\\Plugins\\Nginx_Cache_Purge\\Nginx_Cache_Purge::before_delete_media');
	});

