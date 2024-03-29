<?php

if (!defined('DOCROOT'))
	exit('No direct script access allowed');

\Foolz\Plugin\Event::forge('foolz\plugin\plugin.execute.foolz/geoip_region_lock')
	->setCall(function($result) {
		\Autoloader::add_classes(array(
			'Foolfuuka\\Plugins\\Geoip_Region_Lock\\Geoip_Region_Lock' => __DIR__.'/classes/model/geoip_region_lock.php',
			'Foolfuuka\\Plugins\\Geoip_Region_Lock\\Controller_Plugin_Fu_Geoip_Region_Lock_Admin_Geoip_Region_Lock'
				=> __DIR__.'/classes/controller/admin/geoip_region_lock.php'
		));

		// don't add the admin panels if the user is not an admin
		if (\Auth::has_access('maccess.admin'))
		{
			\Router::add('admin/plugins/geoip_region_lock', 'plugin/fu/geoip_region_lock/admin/geoip_region_lock/manage');

			\Plugins::register_sidebar_element('admin', 'plugins', array(
				"content" => array("geoip_region_lock" => array("level" => "admin", "name" => 'GeoIP Region Lock', "icon" => 'icon-flag'))
			));
		}

		if ( ! \Auth::has_access('maccess.mod') && !(\Preferences::get('fu.plugins.geoip_region_lock.allow_logged_in') && \Auth::has_access('access.user')))
		{
			\Foolfuuka\Plugins\Geoip_Region_Lock\Geoip_Region_Lock::block_country_view();

			\Foolz\Plugin\Event::forge('fu.comment.insert.call.before')
				->setCall('Foolfuuka\\Plugins\\Geoip_Region_Lock\\Geoip_Region_Lock::block_country_comment')
				->setPriority(4);
		}

		\Foolz\Plugin\Event::forge('fu.radix.structure.structure_alter')
			->setCall(function($result){
			$structure = $result->getParam('structure');

			$structure['plugin_geo_ip_region_lock_allow_comment'] = array(
				'database' => TRUE,
				'boards_preferences' => TRUE,
				'type' => 'input',
				'class' => 'span3',
				'label' => 'Nations allowed to post comments',
				'help' => __('Comma separated list of GeoIP 2-letter nation codes.'),
				'default_value' => FALSE
			);

			$structure['plugin_geo_ip_region_lock_disallow_comment'] = array(
				'database' => TRUE,
				'boards_preferences' => TRUE,
				'type' => 'input',
				'class' => 'span3',
				'label' => 'Nations disallowed to post comments',
				'help' => __('Comma separated list of GeoIP 2-letter nation codes.'),
				'default_value' => FALSE
			);
			$result->setParam('structure', $structure)->set($structure);
		})->setPriority(8);
	});

