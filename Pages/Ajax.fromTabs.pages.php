<?php
use JW3B\plugin\Pages;
use JW3B\erday\Helpful;

$Pages = new Pages;
$s = $Pages->getAllPages();
echo '
<div class="well well-small">
	<h4>'.l('Current Pages').'</h4>
	<div class="well well-small">
		<ul>
			<li>'.l('Below are your currently pages which you can edit a page by clicking on the page name').'</li>
			<li>'.l('You cannot delete your error page because you need some sort of default error page for your visitors to see.').'</li>
			<li>'.l('Pages are not sent to any social networks').'</li>
			<li>'.l('They only show up on your website when you add them to one of your link menus.').'</li>
		</ul>
	</div>
	<div class="centered">
		<a href="#addPage" data-fire="addNewPage" data-toggle="modal" class="fire-modal btn btn-primary"><em class="icon-plus"></em> '.l('Add New Page').'</a>
	</div>
	<hr>
	<table class="table table-striped table-hover" id="pages-table">
		<thead>
			<th>'.l('Page Name').'</th>
			<th>'.l('View Page').'</th>
			<th>'.l('Delete').'</td>
		</thead>
		<tfoot>
			<th>'.l('Page Name').'</th>
			<th></th>
			<th></td>
		</tfoot>
		<tbody>';
		//<ul class="nav nav-pills nav-stacked">
		// lets first grab all the pages, and display them, then also have an add a page shit thing.
		if(isset($s[0])){
			foreach($s as $k => $v){
				//echo '<li><a href="#editPage" data-toggle="modal" data-fire="editPage" data-info="'.$v['id'].'" class="fire-wide-modal">'.stripslashes(htmlentities($v['name'])).'</a></li>';
				$delButt = $v['id'] == 1 ? '' : '<button type="button" class="btn btn-danger btn-mini remove-page" data-pid="'.$v['id'].'" data-complete-text="Deleted!"><em class="icon-remove"></em> '.l('Remove').'</button>';
				/*echo '<tr>
					<td colspan="3">
					<pre>'.print_r($v,1).'</pre>
					</td>
				</tr>'; */
				echo '<tr>
					<td><a href="#editPage" data-toggle="modal" data-fire="editPage" data-info="'.$v['id'].'" class="fire-modal btn-block">'.Helpful::clean_text($v['name']).'</a></td>
					<td><a href="/pg/'.$v['id'].'/'.Helpful::clean_url($v['name']).'" class="btn btn-mini btn-info"><em class="icon-share-alt"></em> '.l('View Page').'</a></td>
					<td>'.$delButt.'</td>
				</tr>';
			}
		}
		?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
$(function(){
	var oTable = $('#pages-table').dataTable( {
		"aoColumns": [
			null,
			{ "bSearchable": false, "bSortable": false },
			{ "bSearchable": false, "bSortable": false }
		],
		"sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span12'p>>", //
		"sPaginationType": "bootstrap",
		"oLanguage": {
			"sLengthMenu": "_MENU_ per page"
		}
	} ).columnFilter({
		aoColumns: [ {
			type: "text",
			bRegex: true,
			bSmart: true
		}, null, null ]
	})
})
</script>