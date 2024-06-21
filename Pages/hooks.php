<?php

use JW3B\SiteHead;
use JW3B\Tags;
use JW3B\plugin\Pages;
use JW3B\core\Config;
use JW3B\core\Plugable;
use JW3B\security\cleanUserInput;
use JW3B\gui\form;

Plugable::addHook('Route_pg', 'pg_display_page');
Plugable::addHook('Ajax.trigger_editPage', 'pg_addEditNewPage');
Plugable::addHook('Ajax.trigger_addNewPage', 'pg_addEditNewPage');
Plugable::addHook('Ajax.sub_uPageSub', 'pg_uPageSub');
Plugable::addHook('Ajax.sub_removePage', 'pg_removePage');
Plugable::addHook('Ajax.sub_cContact', 'pg_cContact');
Plugable::addHook('Ajax.fromTabs', 'pg_aj_FromTabs');
Plugable::addFilter('Ajax.TabLinks',
		[
			'url' => '#pages',
			'icon' => 'columns',
			'txt' => l('Pages'),
			'callback' => 'pg_aj_FromTabs'
		]
);

function pg_cContact(){
	global $Sets;
	if(!isset($_POST['cPopQuiz'])) $errors[] = l('We\'re missing the popquiz answer');
	if(!isset($_SESSION['PopQuiz'])) $errors[] = l('Cookies have to be enabled to use this feature');
	if($_POST['cPopQuiz'] != $_SESSION['PopQuiz']) $errors[] = l('You failed the robot test.. Please answer the math question correctly.');
	if(filter_var($_POST['cEmail'], FILTER_VALIDATE_EMAIL) == false) $errors[] = l('Your email does not seem to be valid. Please check your email address, or try again with a different address');
	if($_POST['cSubject'] == '') $errors[] = l('We need a subject for your message.');
	if($_POST['cMessage'] == '') $errors[] = l('How you gonna send a message with nothing in it?');
	if($_POST['cName'] == '') $errors[] = l('Please include your name..');
	if(empty($errors)){
		$go = sendEmail(stripslashes($_POST['cName']), stripslashes($_POST['cEmail']), stripslashes($_POST['cSubject']), stripslashes($_POST['cMessage']));
		if($go == 'ok'){
			echo l('Email has been sent! We will get back to you ASAP');
		} else { echo $go; }
	} else {
		echo '<ul>';
		foreach($errors as $k){
			echo '<li>'.$k.'</li>';
		} echo '</ul>';
	}
}

function pg_removePage(){
	global $Sets;
	if(isset($_SESSION['Uid']) && $_SESSION['Uid'] > 0 && $_POST['page'] > 0){
		$Pages = new Pages;
		$go = $Pages->deletePage($_POST['page']);
		if($go > 0){
			echo l('ok');
			$Tags = new Tags;
			$go = $Tags->removeTags('page', $_POST['page']);
		} else { echo l('There was an error finding the page'); }
	} else {
		echo l('There was an error checking everything\'s.. either you dont have permissions to do this, or the page wasnt found');
	}
}

function pg_aj_FromTabs(){
	include(Config::$c['PluginDir'].'Pages/Ajax.fromTabs.pages.php');
}

function pg_addEditNewPage($arg){
	global $Title, $Body, $Footer;
	include(Config::$c['PluginDir'].'Pages/Ajax.trigger.addReditPage.php');
}

function pg_uPageSub($arg){
	include(Config::$c['PluginDir'].'Pages/Ajax.sub.uPageSub.php');
}

function pg_display_page($arg){
	global $inc;
	$Urls = $arg[0];
	if(isset($Urls[1])){
		$Pages = new Pages;
		$pg = $Pages->getPage($Urls[1]);
		if(isset($pg['id'])){
			if(isset($_SESSION['UserType']) && $_SESSION['UserType'] == 10){

				Plugable::addFilter('Sidepanel_AdminLinks', ['fire' => 'editPage', 'info' => $pg['id'], 'btn' => 'warning', 'icon' => 'edit', 'txt' => 'Edit Page']);
				Plugable::addFilter('Sidepanel_AdminLinks', ['fire' => 'addNewPage', 'info' => '', 'btn' => 'primary', 'icon' => 'plus', 'txt' => 'Add New Page']);
				/*$AdminSideNav = '<div class="well well-small">
					<a href="#editPage" data-toggle="modal" data-fire="editPage" data-info="'.$pg['id'].'" class="btn btn-warning btn-block fire-modal"><em class="icon-edit"></em> '.l('Edit Page').'</a>
					<a href="#newPage" data-toggle="modal" data-fire="addNewPage" data-info="" class="btn btn-primary btn-block fire-modal"><em class="icon-plus"></em> '.l('Add New Page').'</a>
				</div>'; */
			}
			if($pg['options'] != ''){
				$pg['options'] = unserialize($pg['options']);
			}
			// `id`, `name`, `Sname`, `Sdesc`, `keys`, `contents`, `pics`, `options`
			SiteHead::addKeywords(str_replace('-', ' ', $pg['keys']));
			SiteHead::addMetaDesc(str_replace('"', "'", $pg['Sdesc']));
			SiteHead::pageTitle(htmlentities($pg['name']));
			if(isset($pg['options']['pHideSide']) && $pg['options']['pHideSide'] == 1){
				Plugable::addFilter('HideSidePanel', 'yes');
			}
			if(isset($pg['options']['pHideHead']) && $pg['options']['pHideHead'] == 1){
				Plugable::addFilter('HideCustomHeader', 'yes');
			}
			$inc = formatPage($pg);
		}
	}
}

function formatPage($pg){
	global $Sets; // ['contents'], $pg['options']
	$str = stripslashes($pg['contents']);
	$cleanUserInput = new cleanUserInput;
	$format = isset($pg['options']['format']) ? $pg['options']['format'] : 'bbcode';
	$str = $cleanUserInput->getFormat($str, $format, 'page_'.$pg['id']);
	$str = preg_replace_callback("#\[GoogleMaps\](.+?)\[/GoogleMaps\]#is", 'GoogleMapsDisplay', $str);
	$str = preg_replace_callback("#\[ContactForm\](.+?)\[/ContactForm\]#is", 'ContactFormDisplay', $str);
	return $str;
}

function GoogleMapsDisplay($str){
	$address = trim(str_replace("<br>", '', $str[1]));
	$addEn = urlencode($address);
	return '<hr><div class="centered">
		<form action="https://maps.google.com/maps" method="get" target="_blank" class="form-inline">
			<h4>'.l('Get Directions').'</h4>
			'.l('Enter Your Address:').'
			<input type="text" name="saddr" placeholder="'.l('1234 My St. My Town ST 56789').'">
			<input type="hidden" name="daddr" value="'.$address.'">
			<button type="submit" class="btn btn-primary"><em class="icon-map-marker"></em> '.l('Get Directions').'</button>
		</form>
		<a alt="map" href="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q='.$addEn.'&amp;z=16" target="_blank"><img alt="map" src="https://maps.google.com/maps/api/staticmap?center='.$addEn.'&amp;zoom=15&amp;size=400x400&amp;maptype=roadmap&amp;markers=color:red|'.$addEn.'&amp;sensor=false"></a>
	</div>';
}

function ContactFormDisplay($r1){
	$keys = [1 => 1, 2 => 2,3 => 3,4 => 4,5 => 5];
	$key1 = array_rand($keys);
	$key2 = array_rand($keys);
	$answer = $key1+$key2;
	$_SESSION['PopQuiz'] = $answer;
	$Form = new form([
		'id' => 'cContact'
	]);
	return $Form->add_html('<div class="alert alert-primary" role="alert">'.Helpful::clean_text($r1[1]))
				->floating('mb-3')->set_name('cName')
					->element('input', '', ['placeholder' => 'Your Name', 'required' => 'required'])
					->label('Your Name', 'my-blue')
				->end_floating()
				->floating('mb-3')->set_name('cEmail')
					->element('input', '', ['placeholder' => 'Your Email', 'type' => 'email', 'required' => 'required'])
					->label('Email', 'my-blue')
				->end_floating()
				->floating('mb-3')->set_name('cSubject')
					->element('input', '', ['placeholder' => 'Subject', 'required' => 'required'])
					->label('Subject', 'my-blue')
				->end_floating()
				->floating('mb-3')->set_name('cMessage')
					->element('textarea', '', ['placeholder' => 'Your message to send', 'required' => 'required', 'rows' => 5])
					->label('Message', 'my-blue')
				->end_floating()
				->new_row('mb-3')->set_name('cPopQuiz')->label('What is '.$key1.'+'.$key2.'?')
				->element('input', '', ['type' => 'number', 'placeholder' => 'Complete the Math Problem Above'])
			->add_html('</div>')
			->actions('Login', 'w-100 btn btn-lg btn-primary');
}