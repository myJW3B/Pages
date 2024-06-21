<?php
use JW3B\plugin\Pages;
use JW3B\gui\form;
use JW3B\erday\Helpful;

$Title = 'Add a New Page';
$go = [
	'id' => '', 'name' => '', 'Sname' => '', 'Sdesc' => '', 'keys' => '', 'contents' => '', 'pics' => '', 'options' => array('formar' => '')
];
$tags = '';
$hideS = [];
$hideH = [];
if(isset($_GET['ins']) && $_GET['ins'] > 0){
	//echo 'wello';
	$Pages = new Pages;
	$go2 = $Pages->getPage($_GET['ins']);
	//print_r($go2);
	if(isset($go2['id'])){
		$Title = 'Editing a Page';
		$go = $go2;
		if($go['options'] != ''){
			$go['options'] = unserialize($go['options']);
			if($go['options']['pHideSide'] == 1){
				$hideS = ['checked' => 'checked'];
			}
			if($go['options']['pHideHead'] == 1){
				$hideH = ['checked' => 'checked'];
			}
		}
	}
}
$Forms = new form([
	'id' => 'uPageSub'
]);
$Body = $Forms
	->floating('mb-3')->set_name('pTitle')
		->element('input', Helpful::clean_text($go['name']), ['placeholder' => 'Page Title', 'required' => 'required'])
		->label('Page Title', 'my-blue')->end_floating()
	->floating('mb-3')->set_name('pUrl')
		->element('input', Helpful::clean_text($go['Sname']), ['placeholder' => 'Url Name', 'required' => 'required'])
		->label('Url Name', 'my-blue')->end_floating()

	->floating('mb-3')->set_name('pKeys')
		->element('tags', Helpful::clean_text($go['keys']), ['placeholder' => 'Keywords / Tags', 'required' => 'required'])
		->label('Keywords / Tags', 'my-blue')
	->end_floating()

	->floating('mb-3')->set_name('pSDesc')
		->element('textarea', Helpful::clean_text($go['Sdesc']),
			['placeholder' => 'Short Description', 'maxlength' => 300, 'required' => 'required'])
		->label('Short Description', 'my-blue')
	->end_floating()
	->new_row('checkbox mb-3')->set_name('pHideSide')->element('input', '1', ['type' => 'checkbox']+$hideS, '<label for="mf-lremember">', ' Hide Side Panel</label>')->end_row()
	->new_row('checkbox mb-3')->set_name('pHideHead')->element('input', '1', ['type' => 'checkbox']+$hideH, '<label for="mf-lremember">', ' Hide Header</label>')->end_row()
	->floating('mb-3')->set_name('pContents')
		->element('markitup', Helpful::clean_text($go['contents']),
			['placeholder' => 'Short Description', 'required' => 'required', 'postFormat' => $go['options']['format']],
			'', l('To add Google Maps use -- ').'<code>[GoogleMaps]YOUR ADDRESS[/GoogleMaps]</code>
			<br>'.l('To add the Contact Form use -- ').'<code>[ContactForm]Message Above Form[/ContactForm]</code>')
		->label('Short Description', 'my-blue')
	->end_floating()
->actions('Save to '.Helpful::clean_text($Sets['website']['name']), 'w-100 btn btn-lg btn-primary');
