<?php
/**
 * News overview
 * @license GNU GPLv3 http://opensource.org/licenses/gpl-3.0.html
 * @package Kinokpk.com releaser
 * @author ZonD80 <admin@kinokpk.com>
 * @copyright (C) 2008-now, ZonD80, Germany, TorrentsBook.com
 * @link http://dev.kinokpk.com
 */
require "include/bittorrent.php";
INIT();
loggedinorreturn();

$newsid = (int) $_GET['id'];
if (!is_valid_id($newsid)) 			$REL_TPL->stderr($REL_LANG->say_by_key('error'), $REL_LANG->say_by_key('invalid_id'));
//$action = $_GET["action"];
//$returnto = $_GET["returnto"];

$REL_TPL->stdhead($REL_LANG->_('News commenting'));


if (isset($_GET['id'])) {

	$sql = $REL_DB->query("SELECT * FROM news WHERE id = {$newsid} ORDER BY id DESC");
	$news = mysql_fetch_assoc($sql);
	if (!$news) $REL_TPL->stderr($REL_LANG->say_by_key('error'),$REL_LANG->say_by_key('invalid_id'));
	if (!pagercheck()) {
		$added = mkprettytime($news['added']) . " (" . (get_elapsed_time($news["added"],false)) . " {$REL_LANG->say_by_key('ago')})";
		print("<h1>{$news['subject']}</h1>\n");
		print("<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n" .
 "<tr><td class=\"colhead\">{$REL_LANG->_('News text')}&nbsp;<a href=\"".$REL_SEO->make_link('newsoverview','id',$newsid)."#comments\">{$REL_LANG->_('Add new comment')}</a></td></tr>\n");
		







print("<tr><td style=\"vertical-align: top; text-align: left;\"><span class=\"fl mr10\"><img src=\"".$news['image']."\" class=\"corners\" height=\"120px\" width=\"140px\"></span><h5>".format_comment($news['body'])."</h5></td></tr>\n");








	








	print("<tr align=\"right\"><td class=\"colhead\">{$REL_LANG->_('Added')}:&nbsp;{$added}</td></tr>\n");

		print("</table><br />\n");
	}
		



	
	$subres = $REL_DB->query("SELECT SUM(1) FROM comments WHERE toid = ".$newsid." AND type='news'");
	$subrow = mysql_fetch_array($subres);
	$count = $subrow[0];

	if (!$count) {

		print('<div id="newcomment_placeholder">'."<table style=\"margin-top: 2px;\" cellpadding=\"5\" width=\"100%\">");
		print("<tr><td class=colhead align=\"left\" colspan=\"2\">");
		print("<div style=\"float: left; width: auto;\" align=\"left\"> :: {$REL_LANG->_('Comments list')}</div>");
		print("<div align=\"right\"><a href=\"".$REL_SEO->make_link('newsoverview','id',$newsid)."#comments\" class=altlink_white>{$REL_LANG->_('Add new comment')}</a></div>");
		print("</td></tr><tr><td align=\"center\">");
		print("{$REL_LANG->_('No comments')}. <a href=\"".$REL_SEO->make_link('newsoverview','id',$newsid)."#comments\">{$REL_LANG->_('Add new comment')}</a>");
		print("</td></tr></table><br /></div>");

	}
	else {
		

		$limit = ajaxpager(25, $count, array('newsoverview','id',$newsid), 'comments-table');

		$subres = $REL_DB->query("SELECT nc.type, nc.id, nc.ip, nc.text, nc.ratingsum, nc.user, nc.added, nc.editedby, nc.editedat, u.avatar, u.warned, ".
                  "u.username, u.title, u.info, u.class, u.donor, u.enabled, u.ratingsum AS urating, u.gender, sessions.time AS last_access, e.username AS editedbyname FROM comments AS nc LEFT JOIN users AS u ON nc.user = u.id LEFT JOIN sessions ON nc.user=sessions.uid LEFT JOIN users AS e ON nc.editedby = e.id WHERE nc.toid = " .
                  "".$newsid." AND nc.type='news' GROUP BY nc.id ORDER BY nc.id ASC $limit");
		$allrows = prepare_for_commenttable($subres,$news['subject'],$REL_SEO->make_link('newsoverview','id',$newsid));
		if (!pagercheck()) {
			print("<div id=\"pager_scrollbox\"><table id=\"comments-table\" cellspacing=\"0\" cellPadding=\"5\" width=\"100%\" style=\"float:left;\">");
			print("<tr><td class=\"colhead\" align=\"center\" >");
			print("<div style=\"float: left; width: auto;\" align=\"left\"> :: {$REL_LANG->_('Comments list')}</div>");
			print("<div align=\"right\"><a href=\"".$REL_SEO->make_link('newsoverview','id',$newsid)."#comments\" class=altlink_white>{$REL_LANG->_('Add new comment')}</a></div>");
			print("</td></tr>");
			
			print("<tr><td>");

			commenttable($allrows);
			print("</td></tr>");

			print("</table></div>");
		} else {
			print("<tr><td>");
			commenttable($allrows);
			print("</td></tr>");
			die();
		}
	}
	$REL_TPL->assignByRef('to_id',$newsid);
	$REL_TPL->assignByRef('is_i_notified',is_i_notified ( $newsid, 'newscomments' ));
	$REL_TPL->assign('textbbcode',textbbcode('text'));
	$REL_TPL->assignByRef('FORM_TYPE_LANG',$REL_LANG->_('News'));
	$FORM_TYPE = 'news';
	$REL_TPL->assignByRef('FORM_TYPE',$FORM_TYPE);
	$REL_TPL->display('commenttable_form.tpl');
}

$REL_TPL->stdfoot();
?>