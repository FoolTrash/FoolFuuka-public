<?php

namespace Foolfuuka\Themes\Default_\Views;

if (!defined('DOCROOT'))
	exit('No direct script access allowed');

class Board_comment extends \Theme
{

	public static $permissions = null;

	public function __construct($data, $theme)
	{
		if (static::$permissions === null)
		{
			static::$permissions = array(
				'maccess.mod' => \Auth::has_access('maccess.mod'),
				'media.see_hidden' => \Auth::has_access('media.see_hidden'),
				'media.see_banned' => \Auth::has_access('media.see_banned'),
				'comment.passwordless_deletion' => \Auth::has_access('comment.passwordless_deletion'),
				'fu.sphinx.global' => \Preferences::get('fu.sphinx.global')
			);
		}

		foreach ($data as $key => $item)
		{
			$this->$key = $item;
		}


		$this->_theme = $theme;
	}


	public function __toString()
	{
		$p = $this->p;
		if (isset($this->modifiers))
		{
			$modifiers = $this->modifiers;
		}

		$perm = static::$permissions;

		$num =  $p->num . ( $p->subnum ? '_' . $p->subnum : '' );

		ob_start();

		?>
		<article class="post doc_id_<?= $p->doc_id ?><?php if ($p->subnum > 0) : ?> post_ghost<?php endif; ?><?php if ($p->thread_num === $p->num) : ?> post_is_op<?php endif; ?><?php if (!is_null($p->media)) : ?> has_image<?php endif; ?>" id="<?= $num ?>">
			<div class="post_wrapper">
				<?php if ($p->media !== null) : ?>
				<div class="post_file">
					<span class="post_file_controls">
					<?php if ($p->media->get_media_status() !== 'banned' || $perm['media.see_hidden']) : ?>
						<?php if (!$p->board->hide_thumbnails || $perm['media.see_hidden']) : ?>
						<?php if ($p->media->total > 1) : ?><a href="<?= \Uri::create($p->board->shortname . '/search/image/' . $p->media->get_safe_media_hash()) ?>" class="btnr parent"><?= __('View Same') ?></a><?php endif; ?><a
							href="http://google.com/searchbyimage?image_url=<?= $p->media->get_thumb_link() ?>" target="_blank" class="btnr parent">Google</a><a
							href="http://iqdb.org/?url=<?= $p->media->get_thumb_link() ?>" target="_blank" class="btnr parent">iqdb</a><a
							href="http://saucenao.com/search.php?url=<?= $p->media->get_thumb_link() ?>" target="_blank" class="btnr parent">SauceNAO</a>
						<?php endif; ?>
					<?php endif ?>
					</span>
					<?php if ($p->media->get_media_status() !== 'banned' || $perm['media.see_banned']) : ?>
					<?php if (mb_strlen($p->media->get_media_filename_processed()) > 38) : ?>
						<span class="post_file_filename" rel="tooltip" title="<?= htmlspecialchars($p->media->media_filename) ?>">
							<?= mb_substr($p->media->get_media_filename_processed(), 0, 32) . ' (...)' . mb_substr($p->media->get_media_filename_processed(), mb_strrpos($p->media->get_media_filename_processed(), '.')) . ', ' ?>
						</span>
					<?php else: ?>
						<?= $p->media->get_media_filename_processed() . ', ' ?>
					<?php endif; ?>

					<span class="post_file_metadata">
						<?= \Num::format_bytes($p->media->media_size, 0) . ', ' . $p->media->media_w . 'x' . $p->media->media_h ?>
					</span>
					<?php endif; ?>
				</div>
				<div class="thread_image_box">
					<?php if ($p->media->get_media_status() === 'banned') : ?>
						<img src="<?= \Uri::base() . $this->_theme->fallback_asset('images/banned-image.png') ?>" width="150" height="150" />
					<?php elseif ($p->media->get_media_status() !== 'normal'): ?>
						<a href="<?= ($p->media->get_media_link()) ? $p->media->get_media_link() : $p->media->get_remote_media_link() ?>" target="_blank" rel="noreferrer" class="thread_image_link">
							<img src="<?= \Uri::base() . $this->_theme->fallback_asset('images/missing-image.jpg') ?>" width="150" height="150" />
						</a>
					<?php else: ?>
						<a href="<?= ($p->media->get_media_link()) ? $p->media->get_media_link() : $p->media->get_remote_media_link() ?>" target="_blank" rel="noreferrer" class="thread_image_link">
							<?php if(!$perm['maccess.mod'] && !$p->board->transparent_spoiler && $p->media->spoiler) :?>
							<div class="spoiler_box"><span class="spoiler_box_text"><?= __('Spoiler') ?><span class="spoiler_box_text_help"><?= __('Click to view') ?></span></div>
							<?php elseif (isset($modifiers['lazyload']) && $modifiers['lazyload'] == TRUE) : ?>
							<img src="<?= \Uri::base() . $this->_theme->fallback_asset('images/transparent_pixel.png') ?>" data-original="<?= $p->media->get_thumb_link() ?>" width="<?= $p->media->preview_w ?>" height="<?= $p->media->preview_h ?>" class="lazyload post_image<?= ($p->media->spoiler) ? ' is_spoiler_image' : '' ?>" data-md5="<?= $p->media->media_hash ?>" />
							<noscript>
								<a href="<?= ($p->media->get_media_link()) ? $p->media->get_media_link() : $p->media->get_remote_media_link() ?>" target="_blank" rel="noreferrer" class="thread_image_link">
									<img src="<?= $p->media->get_thumb_link() ?>" style="margin-left: -<?= $p->media->preview_w ?>px" width="<?= $p->media->preview_w ?>" height="<?= $p->media->preview_h ?>" class="lazyload post_image<?= ($p->media->spoiler) ? ' is_spoiler_image' : '' ?>" data-md5="<?= $p->media->media_hash ?>" />
								</a>
							</noscript>
							<?php else : ?>
							<img src="<?= $p->media->get_thumb_link() ?>" width="<?= $p->media->preview_w ?>" height="<?= $p->media->preview_h ?>" class="lazyload post_image<?= ($p->media->spoiler) ? ' is_spoiler_image' : '' ?>" data-md5="<?= $p->media->media_hash ?>" />
							<?php endif; ?>
						</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>
				<header>
					<div class="post_data">
						<?php if (isset($modifiers['post_show_board_name']) &&  $modifiers['post_show_board_name']): ?>
						<span class="post_show_board">/<?= $p->board->shortname ?>/</span>
						<?php endif; ?>

						<?php if ($p->get_title_processed() !== '') : ?><h2 class="post_title"><?= $p->get_title_processed() ?></h2><?php endif; ?>
						<span class="post_poster_data">
							<?php if ($p->email && $p->email !== 'noko') : ?><a href="mailto:<?= rawurlencode($p->email) ?>" class="post_email"><?php endif; ?><span class="post_author"><?= $p->get_name_processed() ?></span><span class="post_tripcode"><?= $p->get_trip_processed() ?></span><?php if ($p->email && $p->email !== 'noko') : ?></a><?php endif ?>

							<?php if ($p->get_poster_hash_processed()) : ?><span class="poster_hash">ID:<?= $p->get_poster_hash_processed() ?></span><?php endif; ?>
							<?php if ($p->capcode != 'N') : ?>
								<?php if ($p->capcode == 'M') : ?><span class="post_level post_level_moderator">## <?= __('Mod') ?></span><?php endif ?>
								<?php if ($p->capcode == 'A') : ?><span class="post_level post_level_administrator">## <?= __('Admin') ?></span><?php endif ?>
								<?php if ($p->capcode == 'D') : ?><span class="post_level post_level_developer">## <?= __('Developer') ?></span><?php endif ?>
							<?php endif; ?>
						</span>
						<span class="time_wrap">
							<time datetime="<?= gmdate(DATE_W3C, $p->timestamp) ?>" <?php if ($p->board->archive) : ?> title="<?= __('4chan Time') . ': ' . gmdate('D M d H:i:s Y', $p->get_original_timestamp()) ?>"<?php endif; ?>><?= gmdate('D M d H:i:s Y', $p->timestamp) ?></time>
						</span>
						<a href="<?= \Uri::create(array($p->board->shortname, $p->_controller_method, $p->thread_num)) . '#'  . $num ?>" data-post="<?= $num ?>" data-function="highlight">No.</a><a href="<?= \Uri::create(array($p->board->shortname, $p->_controller_method, $p->thread_num)) . '#q' . $num ?>" data-post="<?= str_replace('_', ',', $num) ?>" data-function="quote"><?= str_replace('_', ',', $num) ?></a>

						<?php if ($p->poster_country !== null) : ?><span class="post_type"><span title="<?= e($p->poster_country_name) ?>" class="flag flag-<?= strtolower($p->poster_country) ?>"></span></span><?php endif; ?>
						<?php if ($p->subnum > 0)   : ?><span class="post_type"><i class="icon-comment-alt" title="<?= htmlspecialchars(__('This post was submitted as a "ghost" reply.')) ?>"></i></span><?php endif ?>
						<?php if (isset($p->media) && $p->media->spoiler == 1) : ?><span class="post_type"><i class="icon-eye-close" title="<?= htmlspecialchars(__('The image in this post has been marked as a spoiler.')) ?>"></i></span><?php endif ?>
						<?php if ($p->deleted == 1) : ?><span class="post_type"><i class="icon-trash" title="<?= htmlspecialchars(__('This post was delete before its lifetime expired.')) ?>"></i></span><?php endif ?>

						<span class="post_controls">
							<?php if (isset($modifiers['post_show_view_button'])) : ?><a href="<?= \Uri::create($p->board->shortname . '/thread/' . $p->thread_num) . '#' . $num ?>" class="btnr parent"><?= __('View') ?></a><?php endif; ?><a href="#" class="btnr parent" data-post="<?= $p->doc_id ?>" data-post-id="<?= $num ?>" data-board="<?= htmlspecialchars($p->board->shortname) ?>" data-controls-modal="post_tools_modal" data-backdrop="true" data-keyboard="true" data-function="report"><?= __('Report') ?></a><?php if ($p->subnum > 0 || $perm['comment.passwordless_deletion'] || !$p->board->archive) : ?><a href="#" class="btnr parent" data-post="<?= $p->doc_id ?>" data-post-id="<?= $num ?>" data-board="<?= htmlspecialchars($p->board->shortname) ?>" data-controls-modal="post_tools_modal" data-backdrop="true" data-keyboard="true" data-function="delete"><?= __('Delete') ?></a><?php endif; ?>
						</span>
					</div>
				</header>
				<div class="backlink_list"<?= $p->get_backlinks() ? ' style="display:block"' : '' ?>>
					<?= __('Quoted By:') ?> <span class="post_backlink" data-post="<?= $p->num ?>"><?= $p->get_backlinks() ? implode(' ', $p->get_backlinks()) : '' ?></span>
				</div>
				<div class="text<?php if (preg_match('/[\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', $p->get_comment_processed())) echo ' shift-jis'; ?>">
					<?= $p->get_comment_processed() ?>
				</div>
				<?php if ($perm['maccess.mod']) : ?>
				<div class="btn-group" style="clear:both; padding:5px 0 0 0;">
					<button class="btn btn-mini" data-function="activateModeration"><?= __('Mod') ?><?php if ($p->poster_ip) echo ' ' .\Inet::dtop($p->poster_ip) ?></button>
				</div>
				<div class="btn-group post_mod_controls" style="clear:both; padding:5px 0 0 5px;">
					<button class="btn btn-mini" data-function="mod" data-board="<?= $p->board->shortname ?>" data-board-url="<?= \Uri::create(array($p->board->shortname)) ?>" data-id="<?= $p->doc_id ?>" data-action="delete_post"><?= __('Delete Post') ?></button>
					<?php if (!is_null($p->media)) : ?>
						<button class="btn btn-mini" data-function="mod" data-board="<?= $p->board->shortname ?>" data-id="<?= $p->media->media_id ?>" data-doc-id="<?= $p->doc_id ?>" data-action="delete_image"><?= __('Delete Image') ?></button>
						<button class="btn btn-mini" data-function="mod" data-board="<?= $p->board->shortname ?>" data-id="<?= $p->media->media_id ?>" data-doc-id="<?= $p->doc_id ?>" data-action="ban_image_local"><?= __('Ban Image') ?></button>
						<button class="btn btn-mini" data-function="mod" data-board="<?= $p->board->shortname ?>" data-id="<?= $p->media->media_id ?>" data-doc-id="<?= $p->doc_id ?>" data-action="ban_image_global"><?= __('Ban Image Globally') ?></button>
					<?php endif; ?>
					<?php if ($p->poster_ip) : ?>
						<button class="btn btn-mini" data-function="ban" data-controls-modal="post_tools_modal" data-backdrop="true" data-keyboard="true" data-board="<?= $p->board->shortname ?>" data-ip="<?= \Inet::dtop($p->poster_ip) ?>" data-action="ban_user"><?= __('Ban IP:') . ' ' . \Inet::dtop($p->poster_ip) ?></button>
						<button class="btn btn-mini" data-function="searchUser" data-board="<?= $p->board->shortname ?>" data-id="<?= $p->doc_id ?>" data-poster-ip="<?= \Inet::dtop($p->poster_ip) ?>"><?= __('Search IP') ?></button>
						<?php if ($perm['fu.sphinx.global']) : ?>
							<button class="btn btn-mini" data-function="searchUserGlobal" data-board="<?= $p->board->shortname ?>" data-id="<?= $p->doc_id ?>" data-poster-ip="<?= \Inet::dtop($p->poster_ip) ?>"><?= __('Search IP Globally') ?></button>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				<?php if ($p->get_reports()) : ?>
					<?php foreach ($p->get_reports() as $report) : ?>
						<div class="report_reason"><?= '<strong>' . __('Reported Reason:') . '</strong> ' . $report->get_reason_processed() ?>
							<br/>
							<div class="ip_reporter">
								<strong><?= __('Info:') ?></strong>
								<?= \Inet::dtop($report->ip_reporter) ?>, <?= __('Type:') ?> <?= $report->media_id !== null ? __('media') : __('post')?>, <?= __('Time:')?> <?= gmdate('D M d H:i:s Y', $report->created) ?>
								<button class="btn btn-mini" data-function="mod" data-id="<?= $report->id ?>" data-board="<?= htmlspecialchars($p->board->shortname) ?>" data-action="delete_report"><?= __('Delete Report') ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php endif; ?>
			</div>
		</article>
		<?php

		return ob_get_clean();
	}


}
