<?php
use JW3B\plugin\Pages;
use JW3B\Tags;

if(isset($_SESSION['Uid']) && $_SESSION['Uid'] > 0){
	$Pages = new Pages;
	$opts['format'] = isset($_POST['pContents_style']) ? $_POST['pContents_style'] : 'bbcode';
	$opts['pHideSide'] = isset($_POST['pHideSide']) ? 1 : 0;
	$opts['pHideHead'] = isset($_POST['pHideHead']) ? 1 : 0;
	if(isset($_POST['Pid']) && $_POST['Pid'] > 0){
		//die('editing page<pre>'.print_r($_POST,1).'</pre>');
		$go = $Pages->editPage($_POST['Pid'], stripslashes($_POST['pTitle']), stripslashes($_POST['pUrl']), stripslashes($_POST['pSDesc']), stripslashes($_POST['pKeys']), stripslashes($_POST['pContents']), '', $opts);
		if($go > 0){
			echo l('Page has been Updated!!').'<br>';
			$Tags = new Tags;
			$go2 = $Tags->editTags('page', $_POST['Pid'], stripslashes($_POST['pKeys']), stripslashes($_POST['pSDesc']));
		}
	} else {
		//die('creating page<pre>'.print_r($_POST,1));
		$go = $Pages->createPage(stripslashes($_POST['pTitle']), stripslashes($_POST['pUrl']), stripslashes($_POST['pSDesc']), stripslashes($_POST['pKeys']), stripslashes($_POST['pContents']), '', $opts);
		if($go > 0){
			echo l('Page has been Created!');
			$Tags = new Tags;
			$go = $Tags->addHashTags('page', $go, stripslashes($_POST['pKeys']), stripslashes($_POST['pSDesc']));
		}
	}
	if($go > 0){
		//echo 'Page has been Updated!';
	} else {
		echo l('There was an error adding the page.');
	}
} else { echo l('There was an error finding out if you were logged in..'); }