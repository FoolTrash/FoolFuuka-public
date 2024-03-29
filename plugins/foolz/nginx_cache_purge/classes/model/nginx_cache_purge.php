<?php

namespace Foolfuuka\Plugins\Nginx_Cache_Purge;

if (!defined('DOCROOT'))
	exit('No direct script access allowed');

class Nginx_Cache_Purge extends \Plugins
{

	public static function before_delete_media($result)
	{
		$post = $result->getObject();
		$params = $result->getParams();
		$media = isset($params[0]) ? $params[0] : true;
		$thumb = isset($params[1]) ? $params[1] : true;

		$dir = array();

		if($media)
		{
			try
			{
				$dir['full'] = $post->get_link(false, true);
			}
			catch (\Foolfuuka\Model\MediaException $e)
			{

			}
		}

		if($thumb)
		{
			try
			{
				$dir['thumb'] = $post->get_link(true, true);
			}
			catch (\Foolfuuka\Model\MediaException $e)
			{

			}
		}

		$url_user_password = static::parse_urls();

		foreach($url_user_password as $item)
		{
			foreach($dir as $d)
			{
				$options = array('driver' => 'curl');
				if(isset($item['password']))
				{
					$options['user'] = $item['user'];
					$options['pass'] = $item['pass'];
					$options['auth'] = 'any';
				}

				\Request::forge($item['url'] . parse_url($d, PHP_URL_PATH), $options)->execute();
			}
		}

		return;
	}


	public static function parse_urls()
	{
		$text = \Preferences::get('fu.plugins.nginx_cache_purge.urls');

		if(!$text)
		{
			return array();
		}

		$lines = preg_split('/\r\n|\r|\n/', $text);

		$lines_exploded = array();

		foreach($lines as $key => $line)
		{
			$explode = explode(':', $line);

			if(count($explode) == 0)
			{
				continue;
			}

			if(count($explode) > 1)
				$lines_exploded[$key]['url'] = rtrim(array_shift($explode) . ':' . array_shift($explode), '/');

			if(count($explode) > 1)
			{
				$lines_exploded[$key]['user'] = array_shift($explode);
				$lines_exploded[$key]['pass'] = implode(':', $explode);
			}
		}

		return $lines_exploded;
	}

}