var bindFunctions = function()
{
	// the following block of code deals with drag and drop of images for MD5 hashing
	var search_dropdown = jQuery('#image');
	if(isEventSupported('dragstart') && isEventSupported('drop') && !!window.FileReader)
	{
		search_dropdown.on('dragover', function(e) {
			e.preventDefault();
			e.stopPropagation();
			e.originalEvent.dataTransfer.dropEffect = 'copy';
		});
		search_dropdown.on('dragenter', function(e) {
			e.preventDefault();
			e.stopPropagation();
		});

		search_dropdown.on('drop', function(event) {
			if(event.originalEvent.dataTransfer){
				if(event.originalEvent.dataTransfer.files.length) {
					event.preventDefault();
					event.stopPropagation();

					findSameImageFromFile(event.originalEvent.dataTransfer);
				}
			}
		});
	}

	jQuery("body").click(function(event){
		var search_el = jQuery('.search-dropdown');
		if(search_el.find(event.target).length != 1)
		{
			search_el.find('.search-dropdown-menu').hide();
			search_el.removeClass('active');
		}
	});


	var clickCallbacks = {

		highlight: function(el, post)
		{
			if (post)
			{
				replyHighlight(post);
			}
		},

		quote: function(el, post, event)
		{
			jQuery("#reply_chennodiscursus").val(jQuery("#reply_chennodiscursus").val() + ">>" + post + "\n");
		},

		comment: function(el, post, event)
		{
			// sending an image
			if(jQuery("#file_image").val())
				return true;

			var originalText = el.attr('value');
			el.attr({'value': backend_vars.gettext['submit_state'], 'disabled': 'disabled'});

			// to make sure nobody gets pissed off with a blocked button
			var buttonTimeout = setTimeout(function(){
				el.attr({'value': originalText});
				el.removeAttr('disabled');
			}, 15000);

			var reply_alert = jQuery('#reply_ajax_notices');
			reply_alert.removeClass('error').removeClass('success');
			_data = {
				reply_numero: post,
				reply_bokunonome: jQuery("#reply_bokunonome").val(),
				reply_elitterae: jQuery("#reply_elitterae").val(),
				reply_talkingde: jQuery("#reply_talkingde").val(),
				reply_chennodiscursus: jQuery("#reply_chennodiscursus").val(),
				reply_nymphassword: jQuery("#reply_nymphassword").val(),
				reply_postas: jQuery("#reply_postas").val(),
				reply_gattai: 'Submit',
				reply_last_limit: typeof backend_vars.last_limit === "undefined" ? null : backend_vars.last_limit,
				latest_doc_id: backend_vars.latest_doc_id,
				theme: backend_vars.selected_theme
			};

			_data[backend_vars.csrf_token_key] = getCookie(backend_vars.csrf_token_key);

			jQuery.ajax({
				url: backend_vars.site_url + backend_vars.board_shortname + '/submit/' ,
				dataType: 'json',
				type: 'POST',
				cache: false,
				data: _data,
				success: function(data, textStatus, jqXHR){
					if (typeof data.error !== "undefined")
					{
						reply_alert.html(data.error);
						reply_alert.addClass('error'); // deals with showing the alert
						return false;
					}
					reply_alert.html(data.success);
					reply_alert.addClass('success'); // deals with showing the alert
					jQuery("#reply_chennodiscursus").val("");
					insertPost(data, textStatus, jqXHR);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					reply_alert.html('Connection error.');
					reply_alert.addClass('error');
					reply_alert.show();
				},
				complete: function() {
					// clear button's timeout, we can deal with the rest now
					clearTimeout(buttonTimeout);
					el.attr({'value': originalText});
					el.removeAttr('disabled');
				}
			});
			event.preventDefault();
		},

		realtimethread: function(el, post, event)
		{
			realtimethread();
			event.preventDefault();
		},

		mod: function(el, post, event)
		{
			el.attr({'disabled': 'disabled'});
			_data = {
				board: el.data('board'),
				id: el.data('id'),
				action: el.data('action'),
				theme: backend_vars.selected_theme
			};
			_data[backend_vars.csrf_token_key] = getCookie(backend_vars.csrf_token_key);
			jQuery.ajax({
				url: backend_vars.api_url + '_/api/chan/mod_actions/',
				dataType: 'json',
				type: 'POST',
				cache: false,
				data: _data,
				success: function(data){
					el.removeAttr('disabled');
					if (typeof data.error !== "undefined")
					{
						alert(data.error);
						return false;
					}

					// might need to be upgraded to array support
					switch(el.data('action'))
					{
						case 'remove_post':
							jQuery('.doc_id_' + el.data('id')).remove();
							break;
						case 'delete_image':
							jQuery('.doc_id_' + el.data('doc-id')).find('.thread_image_box:eq(0) img')
								.attr('src', backend_vars.images['missing_image'])
								.css({
									width: backend_vars.images['missing_image_width'],
									height: backend_vars.images['missing_image_height']
								});
							break;
						case 'remove_report':
							jQuery('.doc_id_' + el.data('id')).removeClass('reported')
							break;
						case 'ban_user':
							jQuery('.doc_id_' + el.data('id')).find('[data-action=ban_user]').text('Banned');
							break;
						case 'ban_image_global':
						case 'ban_image_local':
							jQuery('.doc_id_' + el.data('doc-id')).find('.thread_image_box:eq(0) img')
								.attr('src', backend_vars.images['banned_image'])
								.css({
									width: backend_vars.images['banned_image_width'],
									height: backend_vars.images['banned_image_height']
								});
							break;
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {

				},
				complete: function() {
				}
			});
			return false;
		},

		activateModeration: function(el, post, event)
		{
			jQuery('button[data-function=activateModeration]').parent().hide();
			jQuery('.post_mod_controls button[data-function]').attr({'disabled': 'disabled'});
			setTimeout(function(){
				jQuery('.post_mod_controls button[data-function]').removeAttr('disabled');
			}, 700);
			jQuery('.post_mod_controls').show();
		},

		closeModal: function(el, post)
		{
			el.closest(".modal").modal('hide');
			return false;
		},

		'delete': function(el, post, event)
		{
			var modal = jQuery("#post_tools_modal");
			var foolfuuka_reply_password = getCookie('foolfuuka_reply_password');
			modal.find(".title").html('Delete &raquo; Post No. ' + el.data("post-id"));
			modal.find(".modal-loading").hide();
			modal.find(".modal-information").html('\
			<span class="modal-label">Password</span>\n\
			<input type="hidden" class="modal-post-id" value="' + el.data("post") + '" />\n\
			<input type="hidden" class="modal-board" value="' + el.data("board") + '" />\n\
			<input type="password" class="modal-password" />');
			modal.find(".submitModal").data("action", 'delete');
			modal.find(".modal-password").val(backend_vars.user_pass);
		},


		report: function(el, post, event)
		{
			var modal = jQuery("#post_tools_modal");
			modal.find(".title").html('Report &raquo; Post No.' + el.data("post-id"));
			modal.find(".modal-loading").hide();
			modal.find(".modal-information").html('\
			<span class="modal-label">Post ID</span>\n\
			<input type="text" class="modal-post-id" value="' + el.data("post") + '" />\n\
			<input type="hidden" class="modal-board" value="' + el.data("board") + '" />\n\
			<br>\n\
			<span class="modal-field">Comment</span>\n\
			<textarea class="modal-comment"></textarea>');
			modal.find(".submitModal").data("action", 'report');
		},

		report_media: function(el, post, event)
		{
			var modal = jQuery("#post_tools_modal");
			modal.find(".title").html('Report &raquo; Media No.' + el.data("post-media-id"));
			modal.find(".modal-loading").hide();
			modal.find(".modal-information").html('\
			<span class="modal-label">Media ID</span>\n\
			<input type="text" class="modal-post-id" value="' + el.data("media-id") + '" />\n\
			<input type="hidden" class="modal-board" value="' + el.data("board") + '" />\n\
			<span class="modal-field">Comment</span>\n\
			<textarea class="modal-comment"></textarea>');
			modal.find(".submitModal").data("action", 'repor_media');
		},

		ban: function(el, post, event)
		{
			var modal = jQuery("#post_tools_modal");
			modal.find(".title").html('Ban user with IP ' + el.data("ip"));
			modal.find(".modal-loading").hide();
			modal.find(".modal-information").html('\
			<span class="modal-label">IP</span>\n\
			<input type="text" class="modal-ip" value="' + el.data("ip") + '" /><br/>\n\
			<span class="modal-label">Days</span>\n\
			<input type="text" class="modal-days" value="3" /><br/>\n\
			<span class="modal-label modal-board-ban" style="text-align:left">Only this board</span>\n\
			<input type="radio" name="board" checked value="board" /><br/>\n\
			<span class="modal-label modal-global-ban">Global</span>\n\
			<input type="radio" name="board" value="global" /><br/>\n\
			<span class="modal-field">Comment</span>\n\
			<input type="hidden" class="modal-board" value="' + el.data("board") + '" />\n\
			<textarea class="modal-comment"></textarea>');
			modal.find(".submitModal").data("action", 'ban');
		},

		submitModal: function(el, post, event)
		{
			var modal = jQuery("#post_tools_modal");
			var loading = modal.find(".modal-loading");
			var action = el.data("action");
			var _board = modal.find(".modal-board").val();
			var _doc_id = modal.find(".modal-post-id").val();
			var _href = backend_vars.api_url+'_/api/chan/user_actions/';
			var _data = {};

			if (action == 'report') {
				_data = {
					action: 'report',
					board: _board,
					doc_id: _doc_id,
					reason: modal.find(".modal-comment").val(),
					csrf_fool: backend_vars.csrf_hash
				};
			}
			else if (action == 'report_media') {
				_data = {
					action: 'report_media',
					board: _board,
					media_id: modal.find(".modal-media-id").val(),
					reason: modal.find(".modal-comment").val(),
					csrf_fool: backend_vars.csrf_hash
				};
			}
			else if (action == 'delete') {
				_data = {
					action: 'delete',
					board: _board,
					doc_id: _doc_id,
					password: modal.find(".modal-password").val(),
					csrf_fool: backend_vars.csrf_hash
				};
			}
			else if (action == 'ban')
			{
				_href = backend_vars.api_url+'_/api/chan/mod_actions/';
				_data = {
					action: 'ban_user',
					board: modal.find('.modal-board').val(),
					board_ban: modal.find('input:radio[name=board]:checked').val(),
					length: modal.find('.modal-days').val() * 24 * 60 * 60,
					ip: modal.find('.modal-ip').val(),
					reason: modal.find('.modal-comment').val()
				};
			}
			else {
				// Stop It! Unable to determine which action to use.
				return false;
			}

			_data[backend_vars.csrf_token_key] = getCookie(backend_vars.csrf_token_key);

			jQuery.post(_href, _data, function(result) {
				loading.hide();
				if (typeof result.error !== 'undefined') {
					modal.find(".modal-error").html('<div class="alert alert-error" data-alert="alert"><a class="close" href="#">&times;</a><p>' + result.error + '</p></div>').show();
					return false;
				}
				modal.modal('hide');

				if (action == 'report') {
					toggleHighlight('.doc_id_' + _doc_id, 'reported', false);
				}
				else if (action == 'delete') {
					jQuery('.doc_id_' + _doc_id).hide();
				}
			}, 'json');
			return false;
		},

		searchShow: function(el, post, event)
		{
			el.parent().find('.search-dropdown-menu').show();
			el.parent().parent().addClass('active');
		},

		clearLatestSearches: function(el, post, event)
		{
			setCookie('search_latest_5', '', 0, '/', backend_vars.cookie_domain);
			jQuery('li.latest_search').each(function(idx){
				jQuery(this).remove();
			});
		},

		searchUser: function(el, post, event)
		{
			window.location.href = backend_vars.site_url + el.data('board') +
				'/search/poster_ip/' + el.data('poster-ip');
		},

		searchUserGlobal: function(el, post, event)
		{
			window.location.href = backend_vars.site_url + '_/search/poster_ip/' + el.data('poster-ip');
		}
	}


	// unite all the onclick functions in here
	jQuery("body").on("click", "a[data-function], button[data-function], input[data-function]", function(event) {
		var el = jQuery(this);
		var post = el.data("post");
		return clickCallbacks[el.data("function")](el, post, event);
	});

	// how could we make it working well on cellphones?
	if(/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent))
	{
		return false;
	}


	// variable for ajax backlinks that we can clear them if the mouse hovered out
	var backlink_jqxhr;
	var backlink_spin;

	// hover functions go here
	jQuery("#main").on("mouseover mouseout", "article a.[data-backlink]", function(event) {
		if(event.type == "mouseover")
		{
			var backlink = jQuery("#backlink");
			var that = jQuery(this);

			var pos = that.offset();
			var height = that.height();
			var width = that.width();

			if(that.attr('data-backlink') != 'true')
			{
				// gallery
				var thread_id = that.attr('data-backlink');
				quote = backend_vars.threads_data[thread_id];
				backlink.css('display', 'block');
				backlink.html(quote.formatted);
			}
			else if(jQuery('#' + that.data('post')).hasClass('post'))
			{
				// normal posts
				var toClone = jQuery('#' + that.data('post'));
				if (toClone.length == 0)
					return false;
				backlink.css('display', 'block');
				backlink.html(toClone.clone());
			}
			else if (typeof backend_vars.loaded_posts[that.data('post')] !== 'undefined')
			{
				if(backend_vars.loaded_posts[that.data('post')] === false)
				{
					shakeBacklink(that);
					return false;
				}
				var data = backend_vars.loaded_posts[that.data('post')];
				backlink.html(data.formatted);
				backlink.css('display', 'block');
			}
			else
			{
				backlink_spin = that;
				backlink_spin.spin('small');
				backlink_jqxhr = jQuery.ajax({
					url: backend_vars.api_url + '_/api/chan/post/' ,
					dataType: 'json',
					type: 'GET',
					cache: false,
					data: {
						board: that.data('board'),
						num: that.data('post'),
						theme: backend_vars.selected_theme
					},
					beforeSend: function(xhr) {
						xhr.withCredentials = true;
					},
					success: function(data){
						backlink_spin.spin(false);
						if (typeof data.error !== "undefined")
						{
							backend_vars.loaded_posts[that.data('post')] = false;
							shakeBacklink(that);
							return false;
						}
						backend_vars.loaded_posts[that.data('post')] = data;
						backlink.html(data.formatted);
						backlink.css('display', 'block');
						showBacklink(backlink, pos, height, width);
					}
				});
				return false;
			}
			showBacklink(backlink, pos, height, width);
		}
		else
		{
			// kill the ajax call so the backlink doesn't appear
			if(typeof backlink_jqxhr === 'object')
			{
				backlink_spin.spin(false);
				backlink_jqxhr.abort()
			}
			jQuery("#backlink").css('display', 'none').html('');
		}
	});
}

var shakeBacklink = function(el)
{
	el.css({position:'relative'});
	el.animate({left: '-5px'},100)
		.animate({left: '+5px'}, 100)
		.animate({left: '-5px'}, 100)
		.animate({left: '+5px'}, 100)
		.animate({left: '+0px'}, 100, 'linear', function(){
			el.css({position:'static'});
		});

}

var showBacklink = function(backlink, pos, height, width)
{
	if(jQuery(window).width()/2 < pos.left + width/2)
	{
		backlink.css({
			right: (jQuery(window).width() - pos.left - width) + 'px',
			top: (pos.top + height + 3) + 'px',
			left: 'auto'
		});
	}
	else
	{
		backlink.css({
			left: (pos.left) + 'px',
			top: (pos.top + height + 3) + 'px',
			right: 'auto'
		});
	}

	backlink.find("article").removeAttr("id").find(".post_controls").remove();
	backlink.find(".post_file_controls").remove();

	var swap_image = backlink.find('[data-original]');
	if(swap_image.length > 0)
	{
		swap_image.attr('src', swap_image.attr('data-original'));
	}
}

var backlinkify = function(elem, post_id, subnum)
{
	var backlinks = {};
	if(subnum > 0)
		post_id += "_" + subnum;

	elem.find("a[data-backlink=true]").each(function(idx, post) {
		if (jQuery(post).text().indexOf('/') >= 0)
		{
			return true;
		}

		p_id = jQuery(post).text().replace('>>', '').replace(',', '_');

		if (typeof backlinks[p_id] === "undefined")
		{
			backlinks[p_id] = [];
		}

		if (typeof backend_vars.last_limit === "undefined")
		{
			backlinks[p_id].push('<a href="' + backend_vars.site_url + backend_vars.board_shortname + '/thread/' + backend_vars.thread_id + '/#' + post_id + '" data-function="highlight" data-backlink="true" data-post="' + post_id + '">&gt;&gt;' + post_id.replace('_', ',') + '</a>');
		}
		else
		{
			backlinks[p_id].push('<a href="' + backend_vars.site_url + backend_vars.board_shortname + '/last/' + backend_vars.last_limit + '/' + backend_vars.thread_id + '/#' + post_id + '" data-function="highlight" data-backlink="true" data-post="' + post_id + '">&gt;&gt;' + post_id.replace('_', ',') + '</a>');
		}

		backlinks[p_id] = eliminateDuplicates(backlinks[p_id]);
	});

	jQuery.each(backlinks, function(key, val){
		var post = jQuery("#" + key);
		if(post.length == 0)
			return false;

		var post_backlink = post.find(".post_backlink:eq(0)");
		var already_backlinked = post_backlink.text().replace('>>', '').split(' ');
		jQuery.each(already_backlinked, function(i,v){
			if(typeof val[v] !== "undefined")
			{
				delete val[v];
			}
		});

		post_backlink.html(post_backlink.html() + ((post_backlink.html().length > 0)?" ":"") + val.join(" "));
		post_backlink.parent().show();
	});
}

var timelapse = 10;
var currentlapse = 0;
var realtimethread = function(){
	clearTimeout(currentlapse);
	jQuery.ajax({
		url: backend_vars.api_url + '_/api/chan/thread/',
		dataType: 'json',
		type: 'GET',
		data: {
			num : backend_vars.thread_id,
			board: backend_vars.board_shortname,
			latest_doc_id: backend_vars.latest_doc_id,
			theme: backend_vars.selected_theme,
			last_limit: typeof backend_vars.last_limit === "undefined" ? null : backend_vars.last_limit
		},
		success: insertPost,
		error: function(jqXHR, textStatus, errorThrown) {
			timelapse = 10;
		},
		complete: function() {
			currentlapse = setTimeout(realtimethread, timelapse*1000);
		}
	});

	return false;
}


var insertPost = function(data, textStatus, jqXHR)
{
	var w_height = jQuery(document).height();
	var found_posts = false;

	if(jqXHR.status == 200 && typeof data[backend_vars.thread_id] !== "undefined" && typeof data[backend_vars.thread_id].posts !== "undefined")
	{
		jQuery.each(data[backend_vars.thread_id].posts, function(idx, value){

			found_posts = true;
			var post = jQuery(value.formatted)
			post.find("time").localize('ddd mmm dd HH:MM:ss yyyy');
			post.find('[rel=tooltip]').tooltip({
				placement: 'top',
				delay: 200
			});
			post.find('[rel=tooltip_right]').tooltip({
				placement: 'right',
				delay: 200
			});

			// avoid inserting twice
			if(jQuery('.doc_id_' + value.doc_id).length == 0)
			{
				backlinkify(jQuery('<div>' + value.comment_processed + '</div>'), value.num, value.subnum);
				jQuery('article.thread aside.posts').append(post);
			}

			if(backend_vars.latest_doc_id < value.doc_id)
				backend_vars.latest_doc_id = value.doc_id;
		});
	}

	if(found_posts)
	{
		if(jQuery('#reply :focus').length > 0)
		{
			window.scrollBy(0, jQuery(document).height() - w_height);
		}

		timelapse = 10;
	}
	else
	{
		if(timelapse < 30)
		{
			timelapse += 5;
		}
	}
}

var toggleSearch = function(mode)
{
	var search;
	if (!(search = document.getElementById('search_' + mode))) return;
	search.style.display = search.style.display ? "" : "none";
}


var findSameImageFromFile = function(obj)
{
	var reader = new FileReader();
	reader.onloadend = function(evt){
		if (evt.target.readyState == FileReader.DONE) {
			var fileContents = evt.target.result;
			var digestBytes = Crypto.MD5(Crypto.charenc.Binary.stringToBytes(fileContents), {
				asBytes: true
			});
			var digestBase64 = Crypto.util.bytesToBase64(digestBytes);
			var digestBase64URL = digestBase64.replace('==', '').replace(/\//g, '_').replace(/\+/g, '-');
			jQuery('#image').val(digestBase64URL);
		}
	}

	reader.readAsBinaryString(obj.files[0]);
}

var getPost = function(postForm)
{
	if (postForm.post.value == "") {
		alert('Sorry, you must insert a valid post number.');
		return false;
	}
	var post = postForm.post.value.match(/(?:^|\/)(\d+)(?:[_,]([0-9]*))?/);
	window.location = postForm.action + encodeURIComponent(((typeof post[1] != 'undefined') ? post[1] : '') + ((typeof post[2] != 'undefined') ? '_' + post[2] : '')) + '/';
}

function toggleHighlight(id, classn, single)
{
	jQuery("article").each(function() {
		var post = jQuery(this);

		if (post.hasClass(classn) && single)
		{
			post.removeClass(classn);
		}

		if (post.attr("id") == id)
		{
			post.addClass(classn);
		}
	})
}

function replyHighlight(id)
{
	toggleHighlight(id, 'highlight', true);
}

var changeTheme = function(theme)
{
	setCookie('theme', theme, 30, '/');
	window.location.reload();
}

var changeLanguage = function(language)
{
	setCookie('language', language, 30, '/');
	window.location.reload();
}


function setCookie( name, value, expires, path, domain, secure )
{
	name = backend_vars.cookie_prefix + name;
	var today = new Date();
	today.setTime( today.getTime() );
	if ( expires )
	{
		expires = expires * 1000 * 60 * 60 * 24;
	}
	var expires_date = new Date( today.getTime() + (expires) );

	document.cookie = name + "=" +escape( value ) +
		( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) +
		( ( path ) ? ";path=" + path : "" ) +
		( ( domain ) ? ";domain=" + domain : "" ) +
		( ( secure ) ? ";secure" : "" );
}


function getCookie( check_name ) {
	check_name = backend_vars.cookie_prefix + check_name;
	var a_all_cookies = document.cookie.split( ';' );
	var a_temp_cookie = '';
	var cookie_name = '';
	var cookie_value = '';
	var b_cookie_found = false;
	for ( i = 0; i < a_all_cookies.length; i++ )
	{
		a_temp_cookie = a_all_cookies[i].split( '=' );
		cookie_name = a_temp_cookie[0].replace(/^\s+|\s+$/g, '');
		if ( cookie_name == check_name )
		{
			b_cookie_found = true;
			if ( a_temp_cookie.length > 1 )
			{
				cookie_value = unescape( a_temp_cookie[1].replace(/^\s+|\s+$/g, '') );
			}
			return cookie_value;
			break;
		}
		a_temp_cookie = null;
		cookie_name = '';
	}
	if ( !b_cookie_found )
	{
		return null;
	}
}

function fuel_set_csrf_token(form)
{
	if (document.cookie.length > 0 && typeof form != undefined)
	{
		var c_name = backend_vars.csrf_token_key;
		c_start = document.cookie.indexOf(c_name + "=");
		if (c_start != -1)
		{
			c_start = c_start + c_name.length + 1;
			c_end = document.cookie.indexOf(";" , c_start);
			if (c_end == -1)
			{
				c_end=document.cookie.length;
			}
			value=unescape(document.cookie.substring(c_start, c_end));
			if (value != "")
			{
				for(i=0; i<form.elements.length; i++)
				{
					if (form.elements[i].name == c_name)
					{
						form.elements[i].value = value;
						break;
					}
				}
			}
		}
	}
}


function eliminateDuplicates(arr) {
	var i,
		len=arr.length,
		out=[],
		obj={};

	for (i=0;i<len;i++) {
		obj[arr[i]]=0;
	}
	for (i in obj) {
		out.push(i);
	}
	return out;
}

var isEventSupported = (function() {

	var TAGNAMES = {
		'select': 'input',
		'change': 'input',
		'submit': 'form',
		'reset': 'form',
		'error': 'img',
		'load': 'img',
		'abort': 'img'
	};

	function isEventSupported( eventName, element ) {

		element = element || document.createElement(TAGNAMES[eventName] || 'div');
		eventName = 'on' + eventName;

		// When using `setAttribute`, IE skips "unload", WebKit skips "unload" and "resize", whereas `in` "catches" those
		var isSupported = eventName in element;

		if ( !isSupported ) {
			// If it has no `setAttribute` (i.e. doesn't implement Node interface), try generic element
			if ( !element.setAttribute ) {
				element = document.createElement('div');
			}
			if ( element.setAttribute && element.removeAttribute ) {
				element.setAttribute(eventName, '');
				isSupported = typeof element[eventName] == 'function';

				// If property was created, "remove it" (by setting value to `undefined`)
				if ( typeof element[eventName] != 'undefined' ) {
					element[eventName] = undefined;
				}
				element.removeAttribute(eventName);
			}
		}

		element = null;
		return isSupported;
	}
	return isEventSupported;
})();

jQuery(document).ready(function() {

	// settings
	jQuery.support.cors = true;
	backend_vars.loaded_posts = [];

	var lazyloaded = jQuery('img.lazyload');
	if(lazyloaded.length > 149)
	{
		lazyloaded.lazyload({
			threshold: 1000,
			event: 'scroll'
		});
	}

	// check if input[date] is supported, so we can use by default input[text] with placeholder without breaking w3
	var i = document.createElement("input");
	i.setAttribute("type", "date");
	if(i.type !== "text")
	{
		jQuery('#date_end').replaceWith(jQuery('<input>').attr({id: 'date_end', name: 'end', type: 'date'}));
		jQuery('#date_start').replaceWith(jQuery('<input>').attr({id: 'date_start', name: 'start', type: 'date'}));
	}

	// firefox sucks at styling input, so we need to add size="", that guess what? It's not w3 compliant!
	jQuery('#file_search').attr({size: '4'});
	jQuery('#file_image').attr({size: '16'});

	// destroy the file search box if the browser doesn't support file API
	if(!window.FileReader)
	{
		jQuery('.file_search_remove').remove();
	}

	var post = location.href.split(/#/);
	if (post[1]) {
		if (post[1].match(/^q\d+(_\d+)?$/)) {
			post[1] = post[1].replace('q', '').replace('_', ',');
			jQuery("#reply_chennodiscursus").append(">>" + post[1] + "\n");
			post[1] = post[1].replace(',', '_');

		}
		replyHighlight(post[1]);
	}

	if (typeof backend_vars.thread_id !== "undefined" && (Math.round(new Date().getTime() / 1000) - backend_vars.latest_timestamp < 24 * 60 * 60))
	{
		jQuery('.js_hook_realtimethread').html(backend_vars.gettext['thread_is_real_time'] + ' <a class="btnr" href="#" onClick="realtimethread(); return false;">' + backend_vars.gettext['update_now'] + '</a>');
		setTimeout(realtimethread, 10000);
	}

	bindFunctions();

	// localize and add 4chan tooltip where title
	jQuery("article time").localize('ddd mmm dd HH:MM:ss yyyy').filter('[title]').tooltip({
		placement: 'top',
		delay: 300,
		animation: false
	});

	jQuery('input[title]').tooltip({
		placement: 'right',
		delay: 200,
		animation: false
	});

	jQuery('li.latest_search').tooltip({
		placement: 'left',
		animation: false
	});

	jQuery('#thread_o_matic .thread_image_box').tooltip({
		placement: 'bottom',
		animation: true
	});
});
