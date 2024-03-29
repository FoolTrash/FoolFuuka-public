<?php

namespace Foolfuuka\Themes\Fuuka;

if (!defined('DOCROOT'))
	exit('No direct script access allowed');

class Controller_Theme_Fu_Fuuka_Chan extends \Foolfuuka\Controller_Chan
{

	/**
	 * @param int $page
	 */
	public function radix_page($page = 1)
	{
		$options = array(
			'per_page' => 24,
			'per_thread' => 6,
			'order' => ($this->_radix->archive ? 'by_thread' : 'by_post')
		);

		return $this->latest($page, $options);
	}


	public function radix_gallery($page = 1)
	{
		throw new \HttpNotFoundException;
	}

	/**
	 * @return bool
	 */
	public function radix_submit()
	{
		// adapter
		if(!\Input::post())
		{
			return $this->error(__('You aren\'t sending the required fields for creating a new message.'));
		}

		if ( ! \Security::check_token())
		{
			return $this->error(__('The security token wasn\'t found. Try resubmitting.'));
		}


		if (\Input::post('reply_delete'))
		{
			foreach (\Input::post('delete') as $idx => $doc_id)
			{
				try
				{
					$comments = \Board::forge()
						->get_post()
						->set_options('doc_id', $doc_id)
						->set_radix($this->_radix)
						->get_comments();

					$comment = current($comments);
					$comment->delete(\Input::post('delpass'));
				}
				catch (Model\BoardException $e)
				{
					return $this->response(array('error' => $e->getMessage()), 404);
				}
				catch (Model\CommentDeleteWrongPassException $e)
				{
					return $this->response(array('error' => $e->getMessage()), 404);
				}
			}

			$this->_theme->set_layout('redirect');
			$this->_theme->set_title(__('Redirecting...'));
			$this->_theme->bind('url', \Uri::create(array($this->_radix->shortname, 'thread', $comment->thread_num)));
			return \Response::forge($this->_theme->build('redirection'));
		}


		if (\Input::post('reply_report'))
		{

			foreach (\Input::post('delete') as $idx => $doc_id)
			{
				try
				{
					\Report::add($this->_radix, $doc_id, \Input::post('KOMENTO'));
				}
				catch (Model\ReportException $e)
				{
					return $this->response(array('error' => $e->getMessage()), 404);
				}
			}

			$this->_theme->set_layout('redirect');
			$this->_theme->set_title(__('Redirecting...'));
			$this->_theme->bind('url', \Uri::create($this->_radix->shortname.'/thread/'.\Input::post('parent')));
			return \Response::forge($this->_theme->build('redirection'));
		}

		// Determine if the invalid post fields are populated by bots.
		if (isset($post['name']) && mb_strlen($post['name']) > 0)
			return $this->error();
		if (isset($post['reply']) && mb_strlen($post['reply']) > 0)
			return $this->error();
		if (isset($post['email']) && mb_strlen($post['email']) > 0)
			return $this->error();

		$data = array();

		$post = \Input::post();

		if(isset($post['parent']))
			$data['thread_num'] = $post['parent'];
		if(isset($post['NAMAE']))
			$data['name'] = $post['NAMAE'];
		if(isset($post['MERU']))
			$data['email'] = $post['MERU'];
		if(isset($post['subject']))
			$data['title'] = $post['subject'];
		if(isset($post['KOMENTO']))
			$data['comment'] = $post['KOMENTO'];
		if(isset($post['delpass']))
			$data['delpass'] = $post['delpass'];
		if(isset($post['reply_spoiler']))
			$data['spoiler'] = true;
		if(isset($post['reply_postas']))
			$data['capcode'] = $post['reply_postas'];

		$media = null;

		if (count(\Upload::get_files()))
		{
			try
			{
				$media = \Media::forge_from_upload($this->_radix);
				$media->spoiler = isset($data['spoiler']) && $data['spoiler'];
			}
			catch (\Model\MediaUploadNoFileException $e)
			{
				$media = null;
			}
			catch (\Model\MediaUploadException $e)
			{
				return $this->error($e->getMessage());
			}
		}

		return $this->submit($data, $media);
	}

}
