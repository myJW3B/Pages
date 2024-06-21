<?php

/*
	Pages class, for creating static pages

	id = the page id
	name = the pages name / title
	Sname = the name for the link in the navbar
	Sdesc = short description
	keys = keywords
	contents = page contents
	pics = serialized list of images for the page

	options = serialized
		allow comments
		include social links
*/

namespace JW3B\plugin;
use JW3B\Websites;
use JW3B\core\Config;
use JW3B\security\cleanUserInput;

class Pages {

	var $DBAL;
	var $Sets;
	var $db;

	public function __construct(){
		global $DBAL;
		$this->Sets = Config::$c;
		$this->DBAL = $DBAL;
		$this->db = Config::$t;
	}

	public function getPage($id, $Wid = ''){
		$Wid = $Wid > 0 ? $Wid : $this->Sets['website']['id'];
		if($id > 1){
			$what = 'id'; //($id > 0) ? 'id' : 'Sname';
			//die('Yo I need to redo the sql query here to work correctly with the website');
			$go = $this->DBAL->select("SELECT `id`, `webid`, `name`, `Sname`, `Sdesc`, `keys`, `contents`, `pics`, `options`, `created`, `edited` FROM ".$this->db['pages']."
				WHERE ".$what." = :".$what." AND webid = :webid LIMIT 1", 1, [
					[':'.$what, $id, \PDO::PARAM_INT],
					[':webid', $this->Sets['website']['id'], \PDO::PARAM_INT]
				]);
			return $go;
		} else {
			$dir = Websites::website_dir($this->Sets['website']['id']);
			if(is_file($dir.'pg_1_error_pg.php')){
				$file = file($dir.'pg_1_error_pg.php');
				return [
					'id' => 1, 'webid' => $Wid,
					'name' => urldecode(trim($file[1])), 'Sname' => urldecode(trim($file[2])),
					'Sdesc' => urldecode(trim($file[3])),
					'keys' => urldecode(trim($file[4])),
					'contents' => urldecode(trim($file[5])),
					'pics' => urldecode(trim($file[6])),
					'options' => urldecode(trim($file[7])),
					'edited' => urldecode(trim($file[8]))
				];
			} else {
				// send then the default page not found file..
				return [
					'id' => 1, 'webid' => $Wid,
					'name' => 'Error Page', 'Sname' => 'Error-Page', 'Sdesc' => 'Looks like you found a page that is not here.',
					'keys' => 'error,page,not,found,missing', 'contents' => "[h2]Error[/h2]\r\nIt looks like the page you\'re looking for was not found. If you believe this is an error, please report it.",
					'pics' => '', 'options' => '', 'edited' => ''
				];
			}
		}
	}

	public function getAllPages($Wid=''){
		$Wid = $Wid > 0 ? $Wid : $this->Sets['website']['id'];
		$errorPg = $this->getPage(1, $Wid);
		$go = $this->DBAL->select("SELECT `id`, `name`, `Sname`, `Sdesc`, `keys`, `contents`, `pics`, `options` FROM ".$this->db['pages']." WHERE webid = :webid", 10, [
			[':webid', $Wid, \PDO::PARAM_INT],
		]);
		$go[] = $errorPg;
		return $go;
	}

	public function createPage($name, $Sname, $Sdesc, $keys, $contents, $pics, $options){
		if(isset($this->Sets['website']['admins'][$_SESSION['Uid']])){
			$Tim = time();
			$format = isset($options['format']) ? $options['format'] : 'bbcode';
			if(is_array($options)) $options = serialize($options);
			$go = $this->DBAL->insert($this->db['pages'], [
				['webid', $this->Sets['website']['id'], \PDO::PARAM_INT],
				['name', trim($name)],
				['Sname', trim($Sname)],
				['Sdesc', trim($Sdesc)],
				['keys', trim($keys)],
				['contents', trim($contents)],
				['pics', $pics],
				['options', $options],
				['created', $Tim],
				['edited', $Tim],
				['ip', $_SERVER['REMOTE_ADDR']]
			]);
			if($go > 0){
				// update the cache for the pages content..
				$UserInput = new cleanUserInput;
				$saveInput = $UserInput->format(trim($contents), $format, 'page_'.$go);
			}
			return $go;
		} else {
			return 'Only admins can add new pages to this website';
		}
	}

	public function editPage($id, $name, $Sname, $Sdesc, $keys, $contents, $pics, $options){
		if(isset($this->Sets['website']['admins'][$_SESSION['Uid']])){
			$format = isset($options['format']) ? $options['format'] : 'bbcode';
			//die(print_r($options));
			if(is_array($options)) $options = serialize($options);
			if($id > 1){
				$go = $this->DBAL->update($this->db['pages'], [
					['name', trim($name)],
					['Sname', trim($Sname)],
					['Sdesc', trim($Sdesc)],
					['keys', trim($keys)],
					['contents', trim($contents)],
					['pics', $pics],
					['options', $options],
					['edited', time()],
				], [['id', $id, \PDO::PARAM_INT]]);
				// update the cache with the formatted contents.
				$UserInput = new cleanUserInput;
				$saveInput = $UserInput->format(trim($contents), $format, 'page_'.$id);
				return $go;
			} else if($id == 1) {
				$dir = Websites::website_dir($this->Sets['website']['id']);
				$PIF = "<?php die(); ?>\n".urlencode($name)."\n".urlencode($Sname)."\n".urlencode($Sdesc)."\n".urlencode($keys)."\n".urlencode($contents)."\n".urlencode($pics)."\n".urlencode($options)."\n".time();
				$fp = fopen($dir.'pg_1_error_pg.php', 'w');
				flock($fp, 2);
				fwrite($fp, $PIF);
				flock($fp, 3);
				fclose($fp);
				// update the cache with the formatted contents.
				$UserInput = new cleanUserInput;
				$saveInput = $UserInput->format(trim($contents), $format, 'page_'.$id);
				return 1;
			} else {
				return false;
			}
		} else {
			return 'Only admins can edit pages on this website.';
		}
	}

	function deletePage($id){
		if($id > 1){
			if(isset($this->Sets['website']['admins'][$_SESSION['Uid']])){
				$go = $this->DBAL->delete($this->db['pages'], [['id', $id, \PDO::PARAM_INT], ['webid', $this->Sets['website']['id'], \PDO::PARAM_INT]]);
				return $go;
			} else { return 'Only admins can do this'; }
		} else {
			return 'We cannot delete your error page for the site.';
		}
	}
}