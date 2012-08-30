<?php
if (!defined('DOCROOT'))
	exit('No direct script access allowed');

if (isset($thread_id))
{
	echo \Form::open(array('enctype' => 'multipart/form-data', 'onsubmit' => 'fuel_set_csrf_token(this);', 'action' => $radix->shortname . '/submit'));
	echo \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());
	echo \Form::hidden('id', 'postform');
}
?>

<div class="content">
<?php
foreach ($board->get_comments() as $key => $post) :
	if (isset($post['op'])) :
		$op = $post['op'];
?>
	<div id="<?= $op->num ?>">
		<?php if ($op->media !== null) : ?>
			<span><?= __('File:') . ' ' . \Num::format_bytes($op->media->media_size, 0) . ', ' . $op->media->media_w . 'x' . $op->media->media_h . ', ' . $op->media->media_filename_processed ?> <?= '<!-- ' . substr($op->media->media_hash, 0, -2) . '-->' ?></span>
			<?php if ($op->media->media_status != 'banned') : ?>
				<?php if (!$op->board->hide_thumbnails || Auth::has_access('maccess.mod')) : ?>
					[<a href="<?= Uri::create($op->board->shortname . '/search/image/' . $op->safe_media_hash) ?>"><?= __('View Same') ?></a>]
					[<a href="http://google.com/searchbyimage?image_url=<?= $op->media->thumb_link ?>">Google</a>]
					[<a href="http://iqdb.org/?url=<?= $op->media->thumb_link ?>">iqdb</a>]
					[<a href="http://saucenao.com/search.php?url=<?= $op->media->thumb_link ?>">SauceNAO</a>]
				<?php endif; ?>
			<?php endif; ?>
			<br />
			<?php if ($op->media->media_status == 'banned') : ?>
				<img src="<?= Uri::base() . $this->fallback_asset('images/banned-image.png') ?>" width="150" height="150" class="thumb"/>
			<?php elseif ($op->media->thumb_link === false) : ?>
				<a href="<?= ($op->media->media_link) ? $op->media->media_link : $op->remote_media_link ?>" rel="noreferrer">
					<img src="<?= Uri::base() . $this->fallback_asset('images/missing-image.jpg') ?>" width="150" height="150" class="thumb"/>
				</a>
			<?php else: ?>
				<a href="<?= ($op->media->media_link) ? $op->media->media_link : $op->remote_media_link ?>" rel="noreferrer">
					<img src="<?= $op->media->thumb_link ?>" width="<?= $op->preview_w ?>" height="<?= $op->preview_h ?>" class="thumb" alt="<?= $op->num ?>" />
				</a>
			<?php endif; ?>
		<?php endif; ?>

		<label>
			<input type="checkbox" name="delete[]" value="<?= $op->doc_id ?>" />
			<span class="filetitle"><?= $op->title_processed ?></span>
			<span class="postername<?= ($op->capcode == 'M') ? ' mod' : '' ?><?= ($op->capcode == 'A') ? ' admin' : '' ?><?= ($op->capcode == 'D') ? ' developer' : '' ?>"><?= (($op->email && $op->email !== 'noko') ? '<a href="mailto:' . htmlspecialchars(urlencode($op->email)) . '">' . $op->name_processed . '</a>' : $op->name_processed) ?></span>
			<span class="postertrip<?= ($op->capcode == 'M') ? ' mod' : '' ?><?= ($op->capcode == 'A') ? ' admin' : '' ?><?= ($op->capcode == 'D') ? ' developer' : '' ?>"><?= $op->trip_processed ?></span>
			<span class="poster_hash"><?php if ($op->poster_hash_processed) : ?>ID:<?= $op->poster_hash_processed ?><?php endif; ?></span>
			<?php if ($op->capcode == 'M') : ?>
				<span class="postername mod">## <?= __('Mod') ?></span>
			<?php endif ?>
			<?php if ($op->capcode == 'A') : ?>
				<span class="postername admin">## <?= __('Admin') ?></span>
			<?php endif ?>
			<?php if ($op->capcode == 'D') : ?>
				<span class="postername admin">## <?= __('Developer') ?></span>
			<?php endif ?>
			<?= gmdate('D M d H:i:s Y', $op->original_timestamp) ?>
			<?php if ($op->poster_country !== null) : ?><span class="poster_country"><span title="<?= e($op->poster_country_name) ?>" class="flag flag-<?= strtolower($op->poster_country) ?>"></span></span><?php endif; ?>
		</label>

		<?php if (!isset($thread_id)) : ?>
			<a class="js" href="<?= Uri::create($op->board->shortname . '/thread/' . $op->num) ?>">No.<?= $op->num ?></a>
		<?php else : ?>
			<a class="js" href="<?= Uri::create($op->board->shortname . '/thread/' . $op->num) ?>">No.</a><a class="js" href="javascript:replyQuote('>><?= $op->num ?>\n')"><?= $op->num ?></a>
		<?php endif; ?>

		<?php if ($op->deleted == 1) : ?><img class="inline" src="<?= Uri::base() . $this->fallback_asset('images/icons/file-delete-icon.png'); ?>" alt="[DELETED]" title="<?php _('This post was deleted before its lifetime has expired.') ?>"/><?php endif ?>
		<?php if (isset($op->media) && $op->media->spoiler == 1) : ?><img class="inline" src="<?= Uri::base() . $this->fallback_asset('/images/icons/spoiler-icon.png'); ?>" alt="[SPOILER]" title="<?php _('The image in this post is marked as spoiler.') ?>"/><?php endif ?>

		[<a href="<?= Uri::create($op->board->shortname . '/thread/' . $op->num) ?>"><?= __('Reply') ?></a>]
		<?php if (isset($post['omitted']) && $post['omitted'] > 50) : ?> [<a href="<?= Uri::create($op->board->shortname . '/last50/' . $op->num) ?>"><?= __('Last 50') ?></a>]<?php endif; ?>
		<?php if ($op->board->archive) : ?> [<a href="http://boards.4chan.org/<?= $op->board->shortname . '/res/' . $op->num ?>"><?= __('Original') ?></a>]<?php endif; ?>

		<div class="quoted-by" style="display: <?= (isset($p->backlinks)) ? 'block' : 'none' ?>">
			<?= __('Quoted By:') ?> <?= (isset($p->backlinks)) ? implode(' ', $p->backlinks) : '' ?>
		</div>

		<blockquote><p><?= $op->comment_processed ?></p></blockquote>
		<?php if (isset($post['omitted']) && $post['omitted'] > 0) : ?>
		<span class="omitted">
			<?php if (isset($post['images_omitted']) && $post['images_omitted'] > 0) : ?>
			<?= $post['omitted'] + $post['images_omitted'] . ' ' . _ngettext('post', 'posts', $post['omitted'] + $post['images_omitted']) ?>
			<?= ' ' . _ngettext('omitted', 'omitted', $post['omitted'] + $post['images_omitted']) ?>.
			<?php else : ?>
			<?= $post['omitted'] . ' ' . _ngettext('post', 'posts', $post['omitted']) ?>
			<?= ' ' . _ngettext('omitted', 'omitted', $post['omitted'] + $post['images_omitted']) ?>.
			<?php endif; ?>
		</span>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php
		if (isset($post['posts']))
		{
			foreach ($post['posts'] as $p)
			{
				if(!isset($thread_id))
					$thread_id = NULL;

				if ($p->thread_num == 0)
					$p->thread_num = $p->num;

				echo $this->build('board_comment', array('p' => $p, 'modifiers' => $modifiers), TRUE, TRUE);
			}
		}
	?>

	<?php if (isset($thread_id)) : ?>
	<?= $template['partials']['tools_reply_box'] ?>
	<?php endif; ?>

	<br class="newthr" />
	<hr />
<?php endforeach; ?>
</div>

<?php if (isset($thread_id)) echo \Form::close(); ?>