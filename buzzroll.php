<?php
/*
Plugin Name: Buzz Roll
Plugin URI: http://www.linksalpha.com/
Description: Displays Google Buzz button and Google Buzz Comments
Version: 1.0.2
Author: Vivek Puri
Author URI: http://www.linksalpha.com
*/

/*
    Copyright (C) 2010 LinksAlpha.com.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('WIDGET_NAME', 'Buzz Roll');
define('WIDGET_NAME_INTERNAL', 'buzzroll');
define('WIDGET_PREFIX', 'buzzroll');
define('Buzz-Roll', 'Displays Google Buzz button and Google Buzz Comments for each blog post');
define('ERROR_INTERNAL', 'internal error');
define('ERROR_INVALID_URL', 'invalid url');

$buzzroll_settings['buzzroll_count'] = array('label'=>'Show Google Buzz Button with Comment Count:', 'type'=>'checkbox', 'default'=>'on');
$buzzroll_settings['buzzroll_count_loc'] = array('label'=>'Show Google Buzz Button after:', 'type'=>'text', 'default'=>'', 'options'=>array('post_time'=>'Blog Post Time', 'comment_count'=>'Comment Count', 'the_content'=>'Before Content', 'the_content_after'=>'After Content'));
$buzzroll_settings['buzzroll_count_style'] = array('label'=>'Select Style:', 'type'=>'text', 'default'=>'', 'options'=>array('buzzroll_style_1'=>'Style 1', 'buzzroll_style_2'=>'Style 2', 'buzzroll_style_3'=>'Style 3', 'buzzroll_style_4'=>'Style 4', 'buzzroll_style_5'=>'Style 5', 'buzzroll_style_6'=>'Style 6'));
$buzzroll_settings['buzzroll_comments'] = array('label'=>'Show Google Buzz Comments on Blog Posts:', 'type'=>'checkbox', 'default'=>'on');
$buzzroll_settings['buzzroll_comments_loc'] = array('label'=>'Show Google Buzz Comments after:', 'type'=>'text', 'default'=>'', 'options'=>array('the_content'=>'After Content', 'comment_form'=>'After Comment Form'));
$buzzroll_settings['buzzroll_comments_redirect'] = array('label'=>'For viewing comments, redirect user to:', 'type'=>'text', 'default'=>'', 'options'=>array('blog_post'=>'Blog Post', 'google_buzz'=>'Google Buzz'));

$options = get_option(WIDGET_NAME_INTERNAL);
$buzzroll_current_globals = array('buzzroll_count_loc'=>$options['buzzroll_count_loc'], 'buzzroll_count_style'=>$options['buzzroll_count_style'], 'buzzroll_comments_loc'=>$options['buzzroll_comments_loc'], 'buzzroll_comments_redirect'=>$options['buzzroll_comments_redirect']);

function buzzroll_init() {
	wp_enqueue_script('jquery');
	wp_register_script('buzzrolljs', WP_PLUGIN_URL .'/buzz-roll/'.WIDGET_PREFIX.'.js');
	wp_enqueue_script('buzzrolljs');
	wp_register_style('buzzrollcss', WP_PLUGIN_URL .'/buzz-roll/'.WIDGET_PREFIX.'.css');
	wp_enqueue_style('buzzrollcss');
	add_action('admin_menu', 'buzzroll_pages');
	add_action('the_time', 'buzzroll_load_counters_time');
	add_action('comments_number', 'buzzroll_load_counters_comments');	
	add_action('the_content', 'buzzroll_load_counters_content');
	add_action('comment_form', 'buzzroll_load_comments_comments');
	add_action('the_content', 'buzzroll_load_comments_content');	
}

add_action('init', 'buzzroll_init');
add_action('init', 'buzzroll_load_comments_page');


function buzzroll_pages() {
	if ( function_exists('add_submenu_page') ) {
		$page = add_submenu_page('plugins.php', 'Buzz-Roll', 'Buzz-Roll', 'manage_options', 'buzzroll', 'buzzroll_conf');
	}
}


function buzzroll_conf() {
	global $buzzroll_settings, $buzzroll_current_globals;
	$options = get_option(WIDGET_NAME_INTERNAL);
	if ( isset($_POST['submit']) ) {
		if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
			die(__('Cheatin&#8217; uh?'));
		}
		foreach($buzzroll_settings as $key => $field) {
			$options[$key] = $field['default'];
			$field_name = sprintf('%s_%s', WIDGET_PREFIX, $key);
			if ($field['type'] == 'text') {
				$value = strip_tags(stripslashes($_POST[$field_name]));
				$options[$key] = $value;
			} else {				
				$options[$key] = trim($_POST[$field_name]);
			}
			if (in_array($key, array_keys($buzzroll_current_globals))) {
				$buzzroll_current_globals[$key] = $_POST[$field_name];
			}
			if (empty($_POST[$field_name])) {
				$options[$key] = 'off';
			}
		}
		update_option(WIDGET_NAME_INTERNAL, $options);
	}
	$options = get_option(WIDGET_NAME_INTERNAL);
	
	$html  = '<div class="buzzroll_header"><h2><img class="la_image" src="http://www.linksalpha.com/favicon.ico" />&nbsp;'.WIDGET_NAME.'</h2></div>';
	$html .= '<div class="buzzroll_header3"><div class="msg_error">Important: To make Comment Count and Comments to show up, add following link to your template header.php. Replace "your.username" with your <a href="http://www.google.com/support/accounts/bin/answer.py?hl=en&answer=97703">Google Profile username</a><br />&lt;link rel="linksalpha" type="text/html" href="http://www.google.com/profiles/your.username" /&gt;</div></div>';
	$html .= '<table class="buzzroll_tbl_main"><tr><td style="width:70%; padding-right:30px;">';
	$html .= '<div class="buzzroll_header2"><big><strong>Setup</strong></big></div>';
	$html .= '<div class="la_content_box">';
	$html .= '<fieldset class="buzzroll_fieldset">';
	$html .= '<legend>Buzz Button:</legend>';
	$html .= '<form action="" method="post" id="retweeters-conf" style="width:93%;">';
	
	$curr_field = 'buzzroll_count';
	$field_name = sprintf('%s_%s', WIDGET_PREFIX, $curr_field);
	$field_value = $options[$curr_field];
	$checked = '';
	if($field_value == "on") {
		$checked = "checked";
	}
	$html .= '<div style="padding-bottom:15px;"><label for="'.$field_name.'">'.$buzzroll_settings[$curr_field]['label'].'</label>';
	$html .= '<input id="'.$field_name.'" name="'.$field_name.'" type="checkbox" '.$checked.' /></div>';
	
	$curr_field = 'buzzroll_count_loc';
	$field_name = sprintf('%s_%s', WIDGET_PREFIX, $curr_field);
	$field_value = htmlspecialchars($options[$curr_field], ENT_QUOTES);
	$html .= '<div style="padding-bottom:15px;"><div id="box_'.$field_name.'"><label for="'.$field_name.'">'.$buzzroll_settings[$curr_field]['label'].'</label>';
	$html .= '<select id="'.$field_name.'" name="'.$field_name.'"';
	foreach ($buzzroll_settings[$curr_field]['options'] as $key=>$val) {
		if ($key == $buzzroll_current_globals[$curr_field]) {
			$html .= '<option value="'.$key.'" selected>'.$val.'</option>';
		} else {
			$html .= '<option value="'.$key.'">'.$val.'</option>';
		}
	}
	$html .= '</select></div></div>';
	
	$curr_field = 'buzzroll_count_style';
	$field_name = sprintf('%s_%s', WIDGET_PREFIX, $curr_field);
	$field_value = htmlspecialchars($options[$curr_field], ENT_QUOTES);
	$html .= '<table><tr><td><div style="padding:7px 10px 0px 0px;" id="box_'.$field_name.'">'.$buzzroll_settings[$curr_field]['label'].'</div></td>';
	$html .= '<td><table class="buzzroll_style_options">';
	foreach ($buzzroll_settings[$curr_field]['options'] as $key=>$val) {
		$checked = '';
		if ($key == $buzzroll_current_globals[$curr_field]) {
			$checked = 'checked';
		}
		if(in_array($key, array('buzzroll_style_7', 'buzzroll_style_8', 'buzzroll_style_9'))) {
			$html .= '<tr><td><div class="la_style_demo"><input type="radio" name="'.$field_name.'" value="'.$key.'" '.$checked.' /></td><td><a class="'.$key.'" target="_blank" href="http://www.linksalpha.com/"><div id="main"><div id="first">105</div><div id="second">comments</div></div></a></div></td></tr>';
		} else {
			$html .= '<tr><td><div class="la_style_demo"><input type="radio" name="'.$field_name.'" value="'.$key.'" '.$checked.' /></div></td><td>&nbsp;&nbsp;<a class="buzzroll_icon_link" href="http://www.linksalpha.com"><img class="'.$key.'" src="'.WP_PLUGIN_URL .'/buzz-roll/buzzroll_buzz_icon.png" /></a>&nbsp;<a class="'.$key.'" href="'.get_bloginfo('wpurl').'"><div>105 Comments</div></a></td></tr>';	
		}
	}
	$html .= '</table></td></tr></table>';
	
	$html .= '</fieldset><br />';
	$html .= '<fieldset class="buzzroll_fieldset">';
	$html .= '<legend>Buzz Comments:</legend>';
	
	$curr_field = 'buzzroll_comments';
	$field_name = sprintf('%s_%s', WIDGET_PREFIX, $curr_field);
	$field_value = htmlspecialchars($options[$curr_field], ENT_QUOTES);
	$checked = '';
	if($field_value == "on") {
		$checked = "checked";
	}
	$html .= '<div style="padding-bottom:15px;"><label for="'.$field_name.'">'.$buzzroll_settings[$curr_field]['label'].'</label>';
	$html .= '<input id="'.$field_name.'" name="'.$field_name.'" type="checkbox" '.$checked.' /></div>';
	
	$curr_field = 'buzzroll_comments_loc';
	$field_name = sprintf('%s_%s', WIDGET_PREFIX, $curr_field);
	$field_value = htmlspecialchars($options[$curr_field], ENT_QUOTES);
	$html .= '<div style="padding-bottom:15px;" id="'.$field_name.'_box"><div id="box_'.$field_name.'"><label for="'.$field_name.'">'.$buzzroll_settings[$curr_field]['label'].'</label>';
	$html .= '<select id="'.$field_name.'" name="'.$field_name.'"';
	foreach ($buzzroll_settings[$curr_field]['options'] as $key=>$val) {
		if ($key == $buzzroll_current_globals[$curr_field]) {
			$html .= '<option value="'.$key.'" selected>'.$val.'</option>';
		} else {
			$html .= '<option value="'.$key.'">'.$val.'</option>';
		}
	}
	$html .= '</select></div></div>';
	
	$curr_field = 'buzzroll_comments_redirect';
	$field_name = sprintf('%s_%s', WIDGET_PREFIX, $curr_field);
	$field_value = htmlspecialchars($options[$curr_field], ENT_QUOTES);
	$html .= '<div style="padding-bottom:15px;"><div id="box_'.$field_name.'"><label for="'.$field_name.'">'.$buzzroll_settings[$curr_field]['label'].'</label>';
	$html .= '<select id="'.$field_name.'" name="'.$field_name.'"';
	foreach ($buzzroll_settings[$curr_field]['options'] as $key=>$val) {
		if ($key == $buzzroll_current_globals[$curr_field]) {
			$html .= '<option value="'.$key.'" selected>'.$val.'</option>';
		} else {
			$html .= '<option value="'.$key.'">'.$val.'</option>';
		}
	}
	$html .= '</select></div></div>';
	
	$html .= '</fieldset>';
	$html .= '<div style="padding-top:20px;"><input type="submit" name="submit" class="button-primary" value="Update Options" /></div>';
	$html .= '</form>';
	$html .= '</div></td><td style="padding:0px 30px 0px 30px;">';
	
	$html .= '<div class="buzzroll_header2"><big><strong>More Plugins</strong></big></div><div class="la_content_box_3"><div style="padding:0px 0px 5px 0px"><a href="http://wordpress.org/extend/plugins/network-publisher/">Network Publisher</a></div><div><a href="http://wordpress.org/extend/plugins/retweeters/">Retweeters</a></div></div><br />';
	
	$html .= '</td></tr></table>';
	$html .= '<div style="margin-top:40px;margin-right:20px;background-color:#eceff5;padding:5px;">Powered by <a style="vertical-align:baseline;" href="http://www.linksalpha.com"><img src="http://linksalpha.s3.amazonaws.com/static/LALOGO12PX1.png" /></a></div>';
	echo $html;
}


function buzzroll_load_comments_content($content) {
	if (is_single() or is_page()) {
		$options = get_option(WIDGET_NAME_INTERNAL);
		$option_buzzroll_comments = $options['buzzroll_comments'];
		if ($option_buzzroll_comments != 'on') {
			return $content;
		}
		$option_buzzroll_comments_loc = $options['buzzroll_comments_loc'];
		if ($option_buzzroll_comments_loc != 'the_content') {
			return $content;
		}
		$html = buzzroll_load_link_comments(FALSE);
		$content =  $content.'<br />'.$html;
	}
	return $content;
}


function buzzroll_load_comments_comments($content) {
	if (is_single() or is_page()) {
		$options = get_option(WIDGET_NAME_INTERNAL);
		$option_buzzroll_comments = $options['buzzroll_comments'];
		if ($option_buzzroll_comments != 'on') {
			return $content;
		}
		$option_buzzroll_comments_loc = $options['buzzroll_comments_loc'];
		if ($option_buzzroll_comments_loc != 'comment_form') {
			return $content;
		}
		$html = buzzroll_load_link_comments(FALSE);
		$content =  $content.$html;
	}
	return $content;
}


function buzzroll_load_link_comments($show=True) {
	$link = get_permalink();
	$response = buzzroll_fetch_comments($link);
	if (empty($response)) {
		return ;
	}
	if (empty($response->results)) {
		return ;
	}
	$html  = '<div style="padding:10px 0px 0px 0px"  id="buzzroll_comments">';
	$html .= '<div style="padding:0px 0px 5px 0px;"><span><h3><a target="_blank" href="http://www.linksalpha.com/link?id='.$response->link_id.'"><img class="buzzroll_style_2" src="'.WP_PLUGIN_URL .'/buzz-roll/buzzroll_buzz_icon.png" />&nbsp;Google Buzz Comments</a></h3></span><span><input type="hidden" id="buzzroll_blog_url" name="rts_blog_url" value="'.get_bloginfo('url').'" /></span></div>';
	$html .= '<div id="buzzroll_comments_content">';
	$html .= buzzroll_show_comments($response->results);
	if ($response->next_page) {
		$html .= '<div id="buzzroll_comments_load_page_box" class="buzzroll_comments_load_page_box"><input type="hidden" id="buzzroll_comments_load_page" name="buzzroll_comments_load_page" class="buzzroll_comments_load_page" value="'.$response->next_page.'" /></div>';
		$html .= '<div id="buzzroll_comments_load_button" class="buzzroll_comments_load_button"><input type="hidden" id="buzzroll_current_link" name="buzzroll_current_link" value="'.$link.'" /><input type="button" id="buzzroll_comments_load" class="buzzroll_comments_load" name="buzzroll_comments_load" value="View More" /></div>';
	}
	$html .= '</div></div>';	
	if ($show) {
		echo $html;
		return;
	} 
	return $html;
}


function buzzroll_load_comments_page() {
	if (!empty($_POST['buzzroll_link'])) {
		$link = $_POST['buzzroll_link'];
	}
	if (!empty($_POST['buzzroll_comments_page'])) {
		$page = $_POST['buzzroll_comments_page'];
	}
	if (!isset($link) or !isset($page)) {
		return false;
	}
	$response = buzzroll_fetch_comments($link, $page);
	if (empty($response)) {
		return false;
	}
	if (empty($response->results)) {
		return false;
	}
	$html = buzzroll_show_comments($response->results);
	if ($response->next_page) {
		$html .= '<div id="buzzroll_comments_load_page_box" class="buzzroll_comments_load_page_box"><input type="hidden" id="buzzroll_comments_load_page" name="buzzroll_comments_load_page" class="buzzroll_comments_load_page" value="'.$response->next_page.'" /></div>';
	}
	echo $html;
}


function buzzroll_fetch_comments($link, $page=0) {
	if (!$link) {
		return array();
	}
	$url = 'http://www.linksalpha.com/a/buzzcomments?link='.$link.'&page='.$page;
	$response_full = buzzroll_make_http_call($url);
	$response_code = $response_full[0];
	if ($response_code != 200) {
		return array();
	}
	$response = buzzroll_json_decode($response_full[1]);
	if($response->errorCode > 0) {
		return array();
	}
	return $response;
}


function buzzroll_show_comments($comments) {
	$html = '<div>';
	foreach ($comments as $key=>$val) {
		$created_at = date("Y-m-d H:i:s", $val->published);
		$created_at = buzzroll_prettyTime($created_at);
		$html .= '<div class="buzzroll_comment_header"><span style="font-weight:bold"><a target="_blank" href="'.$val->author_url.'">'.$val->username.'</a></span><span class="buzzroll_fontlight">&nbsp;&nbsp;&nbsp;'.$created_at.'</span><span>&nbsp;&nbsp;&nbsp;<a target="_blank" href="http://www.linksalpha.com" class="buzzroll_linklight">via LinksAlpha.com</a></span></div>';
		$html .= '<div class="buzzroll_comment_body">'.$val->content.'</div>';
	}
	$html .= '</div>';
	return $html;
}


function buzzroll_load_counters_time($text) {
	$options = get_option(WIDGET_NAME_INTERNAL);
	$option_buzzroll_count = $options['buzzroll_count'];
	if ($option_buzzroll_count != 'on') {
		echo $text;
		return;
	}
	$option_buzzroll_count_loc = $options['buzzroll_count_loc'];
	if ($option_buzzroll_count_loc != 'post_time') {
		echo $text;
		return;
	}
	$link = get_permalink();
	$html = buzzroll_load_comment_count($link, FALSE);
	echo $text.'&nbsp;&nbsp;'.$html;
}


function buzzroll_load_counters_comments($text) {
	$options = get_option(WIDGET_NAME_INTERNAL);
	$option_buzzroll_count = $options['buzzroll_count'];
	if ($option_buzzroll_count != 'on') {
		echo $text;
		return;
	}
	$option_buzzroll_count_loc = $options['buzzroll_count_loc'];
	if ($option_buzzroll_count_loc != 'comment_count') {
		echo $text;
		return;
	}
	$link = get_permalink();
	$html = buzzroll_load_comment_count($link, FALSE);
	if ($html) {
		echo $text.'&nbsp;&nbsp;'.$html;	
	} else {
		echo $text;
	}
}


function buzzroll_load_counters_content($text) {
	$options = get_option(WIDGET_NAME_INTERNAL);
	$option_buzzroll_count = $options['buzzroll_count'];
	if ($option_buzzroll_count != 'on') {
		echo $text;
		return;
	}
	$option_buzzroll_count_loc = $options['buzzroll_count_loc'];
	if (!in_array($option_buzzroll_count_loc, array('the_content', 'the_content_after'))) {
		echo $text;
		return;
	}
	$link = get_permalink();
	$html = buzzroll_load_comment_count($link, FALSE);
	if ($option_buzzroll_count_loc != 'the_content') {
		echo $text.$html;
	} else {
		echo $html.$text;
	}
	return;
}


function buzzroll_load_comment_count($link=NULL, $show=TRUE) {
	if (!$link) {
		$link = get_permalink();
	}
	if (!$link) {
		return;
	}
	$options = get_option(WIDGET_NAME_INTERNAL);
	$option_buzzroll_count_style= $options['buzzroll_count_style'];
	$option_buzzroll_comments_redirect = $options['buzzroll_comments_redirect'];
	$response = buzzroll_fetch_comment_count($link);
	if ($option_buzzroll_comments_redirect == 'google_buzz' and $response->buzz_link) {
		$redirect_link = $response->buzz_link;
	} else {
		$redirect_link = $link.'#buzzroll_comments';	
	}
	global $post;
	$icon_link = 'http://www.google.com/buzz/post?url='.$link;
	if ($response->count > 0) {
		if ($response->count == 1) {
			$buzzroll_comment_text = $response->count.' Comment';
		} else {
			$buzzroll_comment_text = $response->count.' Comments';	
		}
	} else {
		$buzzroll_comment_text = 'Buzz It';
		$redirect_link = $icon_link;
	}
	if(in_array($option_buzzroll_count_style, array('buzzroll_style_7', 'buzzroll_style_8', 'buzzroll_style_9'))) {
		$html = '<a class="'.$option_buzzroll_count_style.'" target="_blank" href="http://www.linksalpha.com/link?id='.$response->link_id.'"><div id="main"><div id="first">'.$response->count.'</div><div id="second">Comments</div></div></a>';
	} else {
		$html = '&nbsp;&nbsp;<a class="buzzroll_icon_link" href="'.$icon_link.'"><img class="'.$option_buzzroll_count_style.'" src="'.WP_PLUGIN_URL .'/buzz-roll/buzzroll_buzz_icon.png" /></a>&nbsp;<a class="'.$option_buzzroll_count_style.'" href="'.$redirect_link.'"><div>'.$buzzroll_comment_text.'</div></a>';	
	}
	if ($show) {
		echo $html;
	} else {
		return $html;
	}
}


function buzzroll_fetch_comment_count($link) {
	if (!$link) {
		return array();
	}
	$url = 'http://www.linksalpha.com/a/buzzcommentcount?link='.$link;
	$response_full = buzzroll_make_http_call($url);
	$response_code = $response_full[0];
	if ($response_code != 200) {
		return array();
	}
	$response = buzzroll_json_decode($response_full[1]);
	if($response->errorCode > 0) {
		return array();
	}
	return $response;
}


/**
 * Util Functions below 
 */

function buzzroll_json_decode($str) {
	if (function_exists("json_decode")) {
	    return json_decode($str);
	} else {
		if (!class_exists('Services_JSON')) {
			require_once("JSON.php");
		}
	    $json = new Services_JSON();
	    return $json->decode($str);
	}
}

/* 
 * JavaScript Pretty Date 
 * Copyright (c) 2008 John Resig (jquery.com) 
 * Licensed under the MIT license. 
 */ 
// Slight modification to handle datetime. 
function buzzroll_prettyTime($fromTime) {
	$fromTime = strtotime($fromTime);
    $toTime = time();
    $diff = round(abs($toTime - $fromTime));
    $dayDiff = floor($diff / 86400); 
    if(is_nan($dayDiff) || $dayDiff < 0) { 
        return 'few moments ago';
    } 
    if($dayDiff == 0) { 
        if($diff < 60) { 
            return 'Just now'; 
        } elseif($diff < 120) { 
            return '1 minute ago'; 
        } elseif($diff < 3600) { 
            return floor($diff/60) . ' minutes ago'; 
        } elseif($diff < 7200) { 
            return '1 hour ago'; 
        } elseif($diff < 86400) { 
            return floor($diff/3600) . ' hours ago'; 
        } 
    } elseif($dayDiff == 1) { 
        return 'Yesterday'; 
    } elseif($dayDiff < 7) { 
        return $dayDiff . ' days ago'; 
    } elseif($dayDiff == 7) { 
        return '1 week ago'; 
    } elseif($dayDiff < (7*6)) { // Modifications Start Here 
        // 6 weeks at most 
        return ceil($dayDiff/7) . ' weeks ago'; 
    } elseif($dayDiff < 365) { 
        return ceil($dayDiff/(365/12)) . ' months ago'; 
    } else { 
        $years = round($dayDiff/365); 
        return $years . ' year' . ($years != 1 ? 's' : '') . ' ago'; 
    } 
}

function buzzroll_make_http_call($link) {
	if (!$link) {
		return array(500, 'Invalid Link');
	}
	require_once(ABSPATH.WPINC.'/class-snoopy.php');
	$snoop = new Snoopy;
	$snoop->agent = WIDGET_NAME.' - '.get_option('siteurl');
	if($snoop->fetchtext($link)){
		if (strpos($snoop->response_code, '200')) {
			$response = $snoop->results;
			return array(200, $response);
		} 
	}
	if (!class_exists('WP_Http')) {
		return array(500, $snoop->response_code);
	}
	$request = new WP_Http;
	$headers = array( 'Agent' => WIDGET_NAME.' - '.get_option('siteurl') );
	$response_full = $request->request( $link, array('headers' => $headers) );
	$response_code = $response_full['response']['code'];
	if ($response_code === 200) {
		$response = $response_full['body'];
		return array($response_code, $response);
	}
	$response_msg = $response_full['response']['message'];
	return array($response_code, $response_msg);
}

?>