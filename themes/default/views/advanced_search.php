<?php
if (!defined('DOCROOT'))
	exit('No direct script access allowed');
?>

<?php
if ( ! isset($radix) && \Preferences::get('fu.sphinx.global'))
{
	// search can work also without a radix selected
	$search_radix = '_';
}
else if (isset($radix))
{
	$search_radix = $radix->shortname;
}
?>

<?php if (isset($search_radix)) : ?>
<div class="advanced_search clearfix">
<?= \Form::open(array('method' => 'POST', 'action' => Uri::create($search_radix.'/search'))); ?>

<div class="comment_wrap">
<?= \Form::input(array(
	'name' => 'text',
	'id' => 'search_form_comment',
	'value' => (isset($search['text'])) ? rawurldecode($search['text']) : '',
	'placeholder' => ($search_radix  !== '_') ? __('Search or insert post number') : __('Search through all the boards'),
));
?>
</div>


<div class="buttons clearfix">
<?= \Form::submit(array(
	'class' => 'btn btn-inverse',
	'value' => __('Search'),
	'name' => 'submit_search',
));
?>

<?= \Form::submit(array(
	'class' => 'btn btn-inverse',
	'value' => __('Search on all boards'),
	'name' => 'submit_search_global',
));
?>

<?php if (isset($radix)) : ?>
<?= \Form::submit(array(
	'class' => 'btn btn-inverse',
	'value' => __('Go to post number'),
	'name' => 'submit_post',
));
?>
<?php endif; ?>

</div>

<?php $search_structure = \Search::structure(); ?>

<div class="column">
<?php
foreach ($search_structure as $element)
{
	if (isset($element['access']) && ! \Auth::has_access($element['access']))
	{
		continue;
	}

	if ($element['type'] === 'input')
	{
		if ($element['name'] === 'text')
		{
			continue;
		}

		echo '<div class="input-prepend">';
		echo '<label class="add-on" for="search_form_'.$element['name'].'">'.e($element['label']).'</label>';
		echo \Form::input(array(
			'name' => $element['name'],
			'id' => 'search_form_'.$element['name'],
			'value' => (isset($search[$element['name']])) ? rawurldecode($search[$element['name']]) : '',
			'placeholder' => (isset($element['placeholder'])) ? $element['placeholder'] : '',
		));
		echo '</div>';
	}

	if ($element['type'] === 'date')
	{
		echo '<div class="input-prepend">';
		echo '<label class="add-on" for="search_form_'.$element['name'].'">'.e($element['label']).'</label>';
		echo \Form::input(
			array('type' => 'date',
				'name' => $element['name'],
				'placeholder' => 'YYYY-MM-DD',
				'autocomplete' => 'off',
				'value' => (isset($search[$element['name']])) ? rawurldecode($search[$element['name']]) : ''
			)
		);
		echo '</div>';
	}
}
?>


<?php if ( ! isset($radix) || $radix->sphinx) : ?>
<div class="radixes">
	<div>
		<?php
		$radixes = \Radix::get_archives();
		foreach($radixes as $key => $r)
		{
			if ( ! $r->sphinx)
			{
				unset($radixes[$key]);
			}
		}
		if ($radixes) :
		?>
		<div><h5><?= e(__('On these archives')) ?></h5>
			<a href="#" data-function="checkAll" class="btn btn-mini pull-right check"><?= e(__('Check all')) ?></a>
			<a href="#" data-function="uncheckAll" class="btn btn-mini pull-right uncheck"><?= e(__('Uncheck all')) ?></a>
		</div>
		<?php
		foreach ($radixes as $r)
		{
			echo '<label>'.\Form::checkbox('boards[]', $r->shortname, ! isset($radix) || $radix->shortname === $r->shortname).' /'.e($r->shortname).'/</label>';
		}
		?>
		<?php endif; ?>
	</div>

	<div style="clear:left; padding-top: 10px">
		<?php
		$radixes = \Radix::get_boards();
		foreach($radixes as $key => $r)
		{
			if ( ! $r->sphinx)
			{
				unset($radixes[$key]);
			}
		}
		if ($radixes):
		?>
		<div><h5><?= e(__('On these boards')) ?></h5>
			<a href="#" data-function="checkAll" class="btn btn-mini pull-right check"><?= e(__('Check all')) ?></a>
			<a href="#" data-function="uncheckAll" class="btn btn-mini pull-right uncheck"><?= e(__('Uncheck all')) ?></a>
		</div>
		<?php
		foreach ($radixes as $r)
		{
			echo '<label>'.\Form::checkbox('boards[]', $r->shortname, ! isset($radix) || $radix->shortname === $r->shortname).' /'.e($r->shortname).'/</label>';
		}
		?>
		<?php endif; ?>
	</div>
</div>
<?php endif ?>

<div class="latest_searches">
<h5><?= e(__('Your latest searches')) ?></h5>
<ul>
<?php
if (isset($latest_searches) || $latest_searches = @json_decode(\Cookie::get('search_latest_5'), TRUE))
{
	// sanitization
	foreach($latest_searches as $item)
	{
		// all subitems must be array, all must have 'radix'
		if (!is_array($item) || !isset($item['board']))
		{
			$latest_searches = array();
			break;
		}
	}

	foreach($latest_searches as $latest_search)
	{
		$uri = ($latest_search['board'] === FALSE ? '' : $latest_search['board']) . '/search/';
		$text = ($latest_search['board'] === FALSE) ? '<strong>global:</strong> ' : '/<strong>' . e($latest_search['board']) . '</strong>/: ';
		unset($latest_search['board']);
		if (isset($latest_search['text']))
		{
			$uri .= 'text/' . $latest_search['text'] . '/';
			$text .= e(urldecode($latest_search['text'])) . ' ';
			unset($latest_search['text']);
		}
		if (isset($latest_search['order']) && $latest_search['order'] == 'desc')
		{
			unset($latest_search['order']);
		}

		$extra_text = '';
		$extra_text_br = '';
		foreach($latest_search as $k => $i)
		{
			$uri .= $k.'/'.$i.'/';
			$extra_text .= '<span class="options">[' . e($k) . '] ' . e(urldecode($i)) . ' </span>';
			$extra_text_br .= '<br/><span class="options">[' . e($k) . '] ' . e(urldecode($i)) . ' </span>';
		}

		echo '<li title="' . htmlspecialchars($text . $extra_text_br) . '" class="latest_search"><a href="' . Uri::create($uri) . '">' . $text . ' ' . $extra_text . '</a></li>';
	}
}
?>
</ul>
</div>
</div>
<div class="column checkboxes"><table class="table"><tbody>
<?php
foreach ($search_structure as $element) :
	if (isset($element['access']) && ! \Auth::has_access($element['access']))
	{
		continue;
	}

	if ($element['type'] === 'radio') : ?>
		<tr><td><?= e($element['label']) ?></td><td>
		<?php foreach ($element['elements'] as $el) : ?>
			<label>
			<?= \Form::radio($element['name'], $el['value'] ? : '', isset($search[$element['name']]) && $el['value'] === $search[$element['name']]) ?>
			<?= e($el['text']); ?>
			</label>
		<?php endforeach; ?>
		</td></tr>
	<?php endif;

endforeach; ?>
</tbody></table></div>

<?= \Form::close() ?>

</div>
<?php endif; ?>