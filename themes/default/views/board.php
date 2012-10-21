<?php
if (!defined('DOCROOT'))
	exit('No direct script access allowed');

foreach ($board->get_comments() as $key => $post) :
	if (isset($post['op'])) :
		$op = $post['op'];
		$num =  $op->num . ( $op->subnum ? '_' . $op->subnum : '' );
?>
<article id="<?= $num ?>" class="clearfix thread doc_id_<?= $op->doc_id ?> board_<?= $op->board->shortname ?>" data-doc-id="<?= $op->doc_id ?>" data-thread-num="<?= $op->thread_num ?>">
	<?php if ( ! isset($disable_default_after_op_open) || $disable_default_after_op_open !== true) : ?>
	<?php \Foolz\Plugin\Hook::forge('fu.themes.default_after_op_open')->setParam('board', $op->board)->execute(); ?>
	<?php endif; ?>
	<?php if ($op->media !== null) : ?>
		<div class="thread_image_box">
			<?php if ($op->media->get_media_status() === 'banned') : ?>
				<img src="<?= Uri::base() . $this->fallback_asset('images/banned-image.png')?>" width="150" height="150" />
			<?php elseif ($op->media->get_media_status() !== 'normal') : ?>
				<a href="<?= ($op->media->get_media_link()) ? $op->media->get_media_link() : $op->media->get_remote_media_link() ?>" target="_blank" rel="noreferrer" class="thread_image_link">
					<img src="<?= Uri::base() . $this->fallback_asset('images/missing-image.jpg')?>" width="150" height="150" />
				</a>
			<?php else : ?>
				<a href="<?= ($op->media->get_media_link()) ? $op->media->get_media_link() : $op->media->get_remote_media_link() ?>" target="_blank" rel="noreferrer" class="thread_image_link">
					<?php if(!Auth::has_access('maccess.mod') && !$op->board->transparent_spoiler && $op->media->spoiler) :?>
					<div class="spoiler_box"><span class="spoiler_box_text"><?= __('Spoiler') ?><span class="spoiler_box_text_help"><?= __('Click to view') ?></span></div>
					<?php else : ?>
					<img src="<?= $op->media->get_thumb_link() ?>" width="<?= $op->media->preview_w ?>" height="<?= $op->media->preview_h ?>" class="thread_image<?= ($op->media->spoiler) ? ' is_spoiler_image' : '' ?>" data-md5="<?= $op->media->media_hash ?>" />
					<?php endif; ?>
				</a>
			<?php endif; ?>
			<?php if ($op->media->get_media_status() !== 'banned') : ?>
				<div class="post_file" style="padding-left: 2px;<?php if ($op->media->preview_w > 149) : ?> max-width:<?= $op->media->preview_w .'px'; endif; ?>;">
					<?= \Num::format_bytes($op->media->media_size, 0) . ', ' . $op->media->media_w . 'x' . $op->media->media_h . ', ' . $op->media->get_media_filename_processed(); ?>
				</div>
			<?php endif; ?>
			<div class="post_file_controls">
				<?php if ($op->media->get_media_status() !== 'banned' || \Auth::has_access('media.see_banned')) : ?>
					<?php if (!$op->board->hide_thumbnails || Auth::has_access('maccess.mod')) : ?>
						<?php if ($op->media->total > 1) : ?><a href="<?= Uri::create($op->board->shortname . '/search/image/' . $op->media->get_safe_media_hash()) ?>" class="btnr parent"><?= __('View Same') ?></a><?php endif; ?><a
						href="http://google.com/searchbyimage?image_url=<?= $op->media->get_thumb_link() ?>" target="_blank"
						class="btnr parent">Google</a><a
						href="http://iqdb.org/?url=<?= $op->media->get_thumb_link() ?>" target="_blank"
						class="btnr parent">iqdb</a><a
						href="http://saucenao.com/search.php?url=<?= $op->media->get_thumb_link() ?>" target="_blank"
						class="btnr parent">SauceNAO</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
	<header>
		<div class="post_data">
			<?php if ($op->get_title_processed() !== '') : ?><h2 class="post_title"><?= $op->get_title_processed() ?></h2><?php endif; ?>
			<span class="post_poster_data">
				<?php if ($op->email && $op->email !== 'noko') : ?><a href="mailto:<?= rawurlencode($op->email) ?>" class="post_email"><?php endif; ?>	<span class="post_author"><?= $op->get_name_processed() ?></span><span class="post_tripcode"><?= $op->get_trip_processed() ?></span><?php if ($op->email && $op->email !== 'noko') : ?></a><?php endif ?>

				<?php if ($op->get_poster_hash_processed()) : ?><span class="poster_hash">ID:<?= $op->get_poster_hash_processed() ?></span><?php endif; ?>
				<?php if ($op->capcode != 'N') : ?>
					<?php if ($op->capcode == 'M') : ?><span class="post_level post_level_moderator">## <?= __('Mod') ?></span><?php endif ?>
					<?php if ($op->capcode == 'A') : ?><span class="post_level post_level_administrator">## <?= __('Admin') ?></span><?php endif ?>
					<?php if ($op->capcode == 'D') : ?><span class="post_level post_level_developer">## <?= __('Developer') ?></span><?php endif ?>
				<?php endif; ?>
			</span>
			<span class="time_wrap">
				<time datetime="<?= gmdate(DATE_W3C, $op->timestamp) ?>" class="show_time" <?php if ($op->board->archive) : ?> title="<?= __('4chan Time') . ': ' . gmdate('D M d H:i:s Y', $op->get_original_timestamp()) ?>"<?php endif; ?>><?= gmdate('D M d H:i:s Y', $op->timestamp) ?></time>
			</span>
			<a href="<?= Uri::create(array($op->board->shortname, $op->_controller_method, $op->thread_num)) . '#'  . $num ?>" data-post="<?= $num ?>" data-function="highlight">No.</a><a href="<?= Uri::create(array($op->board->shortname, $op->_controller_method, $op->thread_num)) . '#q' . $num ?>" data-post="<?= $num ?>" data-function="quote"><?= $num ?></a>

			<?php if ($op->poster_country !== null) : ?><span class="post_type"><span title="<?= e($op->poster_country_name) ?>" class="flag flag-<?= strtolower($op->poster_country) ?>"></span></span><?php endif; ?>
			<?php if (isset($op->media) && $op->media->spoiler == 1) : ?><span class="post_type"><i class="icon-eye-close" title="<?= htmlspecialchars(__('The image in this post has been marked as a spoiler.')) ?>"></i></span><?php endif ?>
			<?php if ($op->deleted == 1) : ?><span class="post_type"><i class="icon-trash" title="<?= htmlspecialchars(__('This post was delete before its lifetime expired.')) ?>"></i></span><?php endif ?>

			<span class="post_controls">
				<a href="<?= Uri::create(array($op->board->shortname, 'thread', $num)) ?>" class="btnr parent"><?= __('View') ?></a><a href="<?= Uri::create(array($op->board->shortname, $op->_controller_method, $num)) . '#reply' ?>" class="btnr parent"><?= __('Reply') ?></a><?= (isset($post['omitted']) && $post['omitted'] > 50) ? '<a href="' . Uri::create($op->board->shortname . '/last/50/' . $num) . '" class="btnr parent">' . __('Last 50') . '</a>' : '' ?><?= ($op->board->archive) ? '<a href="//boards.4chan.org/' . $op->board->shortname . '/res/' . $num . '" class="btnr parent">' . __('Original') . '</a>' : '' ?><a href="#" class="btnr parent" data-post="<?= $op->doc_id ?>" data-post-id="<?= $num ?>" data-board="<?= htmlspecialchars($op->board->shortname) ?>" data-controls-modal="post_tools_modal" data-backdrop="true" data-keyboard="true" data-function="report"><?= __('Report') ?></a><?php if (Auth::has_access('maccess.mod') || !$op->board->archive) : ?><a href="#" class="btnr parent" data-post="<?= $op->doc_id ?>" data-post-id="<?= $num ?>" data-board="<?= htmlspecialchars($op->board->shortname) ?>" data-controls-modal="post_tools_modal" data-backdrop="true" data-keyboard="true" data-function="delete"><?= __('Delete') ?></a><?php endif; ?>
			</span>

			<div class="backlink_list"<?= $op->get_backlinks() ? ' style="display:block"' : '' ?>>
				<?= __('Quoted By:') ?> <span class="post_backlink" data-post="<?= $num ?>"><?= $op->get_backlinks() ? implode(' ', $op->get_backlinks()) : '' ?></span>
			</div>

			<?php if (Auth::has_access('maccess.mod')) : ?>
				<div class="btn-group" style="clear:both; padding:5px 0 0 0;">
					<button class="btn btn-mini" data-function="activateModeration"><?= __('Mod') ?><?php if ($op->poster_ip) echo ' ' .\Inet::dtop($op->poster_ip) ?></button>
				</div>
				<div class="btn-group post_mod_controls" style="clear:both; padding:5px 0 0 0;">
					<button class="btn btn-mini" data-function="mod" data-board="<?= $op->board->shortname ?>" data-id="<?= $op->doc_id ?>" data-action="delete_post"><?= __('Delete Thread') ?></button>
					<?php if (!is_null($op->media)) : ?>
						<button class="btn btn-mini" data-function="mod" data-board="<?= $op->board->shortname ?>" data-id="<?= $op->media->media_id ?>" data-doc-id="<?= $op->doc_id ?>" data-action="delete_image"><?= __('Delete Image') ?></button>
						<button class="btn btn-mini" data-function="mod" data-board="<?= $op->board->shortname ?>" data-id="<?= $op->media->media_id ?>" data-doc-id="<?= $op->doc_id ?>" data-action="ban_image_local"><?= __('Ban Image') ?></button>
						<button class="btn btn-mini" data-function="mod" data-board="<?= $op->board->shortname ?>" data-id="<?= $op->media->media_id ?>" data-doc-id="<?= $op->doc_id ?>" data-action="ban_image_global"><?= __('Ban Image Globally') ?></button>
					<?php endif; ?>
					<?php if ($op->poster_ip) : ?>
						<button class="btn btn-mini" data-function="ban" data-controls-modal="post_tools_modal" data-backdrop="true" data-keyboard="true" data-board="<?= $op->board->shortname ?>" data-ip="<?= \Inet::dtop($op->poster_ip) ?>" data-action="ban_user"><?= __('Ban IP:') . ' ' . \Inet::dtop($op->poster_ip) ?></button>
						<button class="btn btn-mini" data-function="searchUser" data-board="<?= $op->board->shortname ?>" data-board-url="<?= Uri::create(array($op->board->shortname)) ?>" data-id="<?= $op->doc_id ?>" data-poster-ip="<?= \Inet::dtop($op->poster_ip) ?>"><?= __('Search IP') ?></button>
						<?php if (Preferences::get('fu.sphinx.global')) : ?>
						<button class="btn btn-mini" data-function="searchUserGlobal" data-board="<?= $op->board->shortname ?>" data-board-url="<?= Uri::create(array($op->board->shortname)) ?>" data-id="<?= $op->doc_id ?>" data-poster-ip="<?= \Inet::dtop($op->poster_ip) ?>"><?= __('Search IP Globally') ?></button>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</header>

	<div class="text<?php if (preg_match('/[\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', $op->get_comment_processed())) echo ' shift-jis'; ?>">
		<?= $op->get_comment_processed() ?>
	</div>
	<div class="thread_tools_bottom">
		<?php if (isset($post['omitted']) && $post['omitted'] > 0) : ?>
		<span class="omitted">
			<a style="display:inline-block" href="<?= Uri::create(array($op->board->shortname, $op->_controller_method, $op->thread_num))?>" data-function="expandThread" data-thread-num="<?= $op->thread_num ?>"><i class="icon icon-resize-full"></i></a>
			<span class="omitted_text">
				<span class="omitted_posts"><?= $post['omitted'] ?></span> <?= _ngettext('post', 'posts', $post['omitted']) ?>
				<?php if (isset($post['images_omitted']) && $post['images_omitted'] > 0) : ?>
					<?= __('and') ?> <span class="omitted_images"><?= $post['images_omitted'] ?></span> <?= _ngettext('image', 'images', $post['images_omitted']) ?>
				<?php endif; ?>
				<?= _ngettext('omitted', 'omitted', $post['omitted'] + $post['images_omitted']) ?>
		</span>
		<?php endif; ?>
	</div>

	<?php if ($op->get_reports()) : ?>
		<?php foreach ($op->get_reports() as $report) : ?>
			<div class="report_reason"><?= '<strong>' . __('Reported Reason:') . '</strong> ' . $report->get_reason_processed() ?>
				<br/>
				<div class="ip_reporter">
					<strong><?= __('Info:') ?></strong>
					<?= \Inet::dtop($report->ip_reporter) ?>, <?= __('Type:') ?> <?= $report->media_id !== null ? __('media') : __('post')?>, <?= __('Time:')?> <?= gmdate('D M d H:i:s Y', $report->created) ?>
					<button class="btn btn-mini" data-function="mod" data-id="<?= $report->id ?>" data-board="<?= htmlspecialchars($op->board->shortname) ?>" data-action="delete_report"><?= __('Delete Report') ?></button>
				</div>
			</div>
		<?php endforeach ?>
	<?php endif; ?>
<?php elseif (isset($post['posts'])): ?>
<article class="clearfix thread">
	<?php if ( ! isset($disable_default_after_headless_open) || $disable_default_after_headless_open !== true) : ?>
	<?php \Foolz\Plugin\Hook::forge('fu.themes.default_after_headless_open')->setParam('board', array(isset($radix) ? $radix : null))->execute(); ?>
	<?php endif; ?>
<?php endif; ?>

	<aside class="posts">
		<?php
		if (isset($post['posts'])) :
			$post_counter = 0;
			$image_counter = 0;
			foreach ($post['posts'] as $p)
			{
				$post_counter++;
				if ($p->media !== null)
					$image_counter++;

				if ($image_counter == 150)
					$modifiers['lazyload'] = TRUE;

				if ($p->thread_num == 0)
					$p->thread_num = $p->num;

				echo $this->build('board_comment', array(
					'p' => $p,
					'modifiers' => $modifiers,
					'post_counter' => $post_counter,
					'image_counter' => $image_counter
				), true);
			}

		endif; ?>
	</aside>

	<?php if (isset($thread_id)) : ?>
	<div class="js_hook_realtimethread"></div>
	<?= isset($template['partials']['tools_reply_box']) ? $template['partials']['tools_reply_box'] : '' ?>
	<?php endif; ?>
<?php if (isset($post['op']) || isset($post['posts'])) : ?>
</article>
<?php endif; ?>
<?php endforeach; ?>
<article class="clearfix thread backlink_container">
	<div id="backlink" style="position: absolute; top: 0; left: 0; z-index: 5;"></div>
</article>
