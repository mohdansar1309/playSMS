<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

switch ($op) {
	case "user_incoming":
		$fields = array('in_uid' => $uid, 'flag_deleted' => 0);
		if ($kw = themes_search_keyword()) {
			$keywords = array(
				'in_message' => '%'.$kw.'%',
				'in_sender' => '%'.$kw.'%',
				'in_datetime' => '%'.$kw.'%',
				'in_feature' => '%'.$kw.'%',
				'in_keyword' => '%'.$kw.'%');
		}
		$count = dba_count(_DB_PREF_.'_tblSMSIncoming', $fields, $keywords);
		$nav = themes_nav($count, 'index.php?app=menu&inc=user_incoming&op=user_incoming');
		$extras = array('ORDER BY' => 'in_id DESC', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$list = dba_search(_DB_PREF_.'_tblSMSIncoming', $fields, $keywords, $extras);
		$search = themes_search();
		
		$content = "
			<h2>"._('Incoming SMS')."</h2>
			<p>".$search['form']."</p>
			<p>".$nav['form']."</p>
			<form name=\"fm_incoming\" action=\"index.php?app=menu&inc=user_incoming&op=act_del\" method=post onSubmit=\"return SureConfirm()\">
			<table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
			<thead>
			<tr>
				<th align=center width=4>*</th>
				<th align=center width=20%>"._('Time')."</th>
				<th align=center width=10%>"._('From')."</th>
				<th align=center width=10%>"._('Keyword')."</th>
				<th align=center width=40%>"._('Content')."</th>
				<th align=center width=10%>"._('Feature')."</th>
				<th align=center width=10%>"._('Status')."</th>
				<th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_incoming)></td>
			</tr>
			</thead>
			<tbody>";

		$i = $nav['top'];
		$j = 0;
		for ($j=0;$j<count($list);$j++) {
			$in_message = core_display_text($list[$j]['in_message'], 25);
			$list[$j] = core_display_data($list[$j]);
			$in_id = $list[$j]['in_id'];
			$in_sender = $list[$j]['in_sender'];
			$p_desc = phonebook_number2name($in_sender);
			$current_sender = $in_sender;
			if ($p_desc) {
				$current_sender = "$in_sender<br>($p_desc)";
			}
			$in_keyword = $list[$j]['in_keyword'];
			$in_datetime = core_display_datetime($list[$j]['in_datetime']);
			$in_feature = $list[$j]['in_feature'];
			$in_status = ( $list[$j]['in_status'] == 1 ? '<p><font color=green>'._('handled').'</font></p>' : '<p><font color=red>'._('unhandled').'</font></p>' );
			$i--;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr>
					<td valign=top class=$td_class align=left>$i.</td>
					<td valign=top class=$td_class align=center>$in_datetime</td>
					<td valign=top class=$td_class align=center>$current_sender</td>
					<td valign=top class=$td_class align=center>$in_keyword</td>
					<td valign=top class=$td_class align=left>$in_message</td>
					<td valign=top class=$td_class align=center>$in_feature</td>
					<td valign=top class=$td_class align=center>$in_status</td>
					<td class=$td_class width=4>
						<input type=hidden name=itemid".$j." value=\"$in_id\">
						<input type=checkbox name=checkid".$j.">
					</td>
				</tr>";
		}
		$item_count = $j;

		$content .= "
			</tbody>
			</table>
			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tbody><tr>
				<td width=100% colspan=2 align=right>
					<input type=submit value=\""._('Delete selection')."\" class=button />
				</td>
			</tr></tbody>
			</table>
			</form>
			<p>".$nav['form']."</p>";

		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div><br><br>";
		}
		echo $content;
		break;
	case "act_del":
		$nav = themes_nav_session();
		$search = themes_search_session();
		for ($i=0;$i<$nav['limit'];$i++) {
			$checkid = $_POST['checkid'.$i];
			$itemid = $_POST['itemid'.$i];
			if(($checkid=="on") && $itemid) {
				$up = array('c_timestamp' => mktime(), 'flag_deleted' => '1');
				dba_update(_DB_PREF_.'_tblSMSIncoming', $up, array('in_uid' => $uid, 'in_id' => $itemid));
			}
		}
		$ref_url = $nav['url'].'&search_keyword='.$search['keyword'].'&page='.$nav['page'].'&nav='.$nav['nav'];
		$_SESSION['error_string'] = _('Selected incoming SMS has been deleted');
		header("Location: ".$ref_url);
		exit();
		break;
}

?>