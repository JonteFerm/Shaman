<?php
class CContent{
	private $db;
	
	public function __construct($db){
		$this->db = $db;
		$this->Initiate();
	}

	public function Initiate(){
		$sql = "
			CREATE TABLE IF NOT EXISTS Content(
				id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
				slug CHAR(80),
				url CHAR(80),
				type CHAR(80),
				title VARCHAR(80),
				data TEXT,
				filter CHAR(80),
				published DATETIME,
				created DATETIME,
				updated DATETIME,
				deleted DATETIME,
				user VARCHAR(80)
			)ENGINE INNODB CHARACTER SET utf8;
		";
		
		$this->db->ExecuteQuery($sql);
		
	}
	
	public function slugify($str) {
		$str = mb_strtolower(trim($str));
		$str = str_replace(array('å','ä','ö'), array('a','a','o'), $str);
		$str = preg_replace('/[^a-z0-9-]/', '-', $str);
		$str = trim(preg_replace('/-+/', '-', $str), '-');
		return $str;
	}

	
	public function Add($newType,$newTitle, $user){
		$sql = "
			INSERT INTO Content (slug,url,type,title,filter,published,created,user) VALUES (?,?,?,?,?,NOW(),NOW(),?);
		";
		
		if($newType == "post"){
			$slug = $this->slugify($newTitle);
			$url = null;
			$filter = "markdown";
		}else if($newType == "page"){
			$slug = null;
			$url = $this->slugify($newTitle);
			$filter = "bbcode";
		}
		
		
		$res = $this->db->ExecuteQuery($sql,array($slug, $url, $newType, $newTitle, $filter, $user));
		
		$lastId = $this->db->LastInsertId();
		
		if($res){
			header("Location: edit_content.php?id=$lastId");
		}else{
			$output = "Informationen sparades EJ.<br><pre>" .print_r($this->db->ErrorInfo(), 1) ."</pre>";
		}
	}
	
	public function Edit($title,$slug,$url,$data,$type,$filter,$published,$user,$id){
		$sql = "
		UPDATE Content SET
			title = ?, slug = ?,
			url = ?, data = ?,
			type = ?, filter = ?,
			published = ?, updated = now(), user = ?
		WHERE id = ?
		";
		
		$url = empty($url) ? null : $url;
		$params = array($title, $slug, $url, $data, $type, $filter, $published, $user, $id);
		$res = $this->db->ExecuteQuery($sql, $params);      
		
		if($res){
			$output = "Informationen sparades.";
		}else{
			$output = "Informationen sparades EJ.<br><pre>" .print_r($this->db->ErrorInfo(), 1) ."</pre>";
		}

		return $output;
	}
	

	
	function GetUrlToContent($contObj){
		switch($contObj->type){
			case 'page': 
				return "page.php?url={$contObj->url}";
				break;
			case 'post':
				return "blog.php?slug={$contObj->slug}";
				break;
			default:
				return null;
				break;
		}
	}
	
	public function GetContent($id = null){
		if($id){
			$sqlMod = "WHERE id=?";
		}else{
			$sqlMod = "";
		}
		$sql = "SELECT *,(published <= NOW()) AS available FROM Content $sqlMod;";
		$res = $this->db->ExecuteSelectAndFetchAll($sql,array($id));
		
		return $res;
	}
	
	
	public function GetContentAsList(){
		$res = $this->GetContent();
		$contentList = "<ul>";

		foreach($res as $contObj){
			$contentList .= "<li>{$contObj->type}(".(!$contObj->available ? 'ej ' : null)."publicerad):{$contObj->title} (<a href='".$this->GetUrlToContent($contObj)."'>Visa</a> <a href='edit_content.php?id={$contObj->id}'>Edit</a> <a href='delete_content.php?id={$contObj->id}&title={$contObj->title}&type={$contObj->type}'>Inställningar/Radera</a>)";

		}

		$contentList .="</ul>";
		
		return $contentList;
	
	}
	
	public function Unpublish($id){
		
		$sql = "UPDATE Content SET published = ?, deleted = NOW() WHERE id = ?";
		$res = $this->db->ExecuteQuery($sql, array(null,$id));
		
		if($res){
			header("Location: content.php");
		}else{
			$output = "Informationen raderades EJ.<br><pre>" .print_r($this->db->ErrorInfo(), 1) ."</pre>";
		}
	}
	
	public function Publish($id){
		$sql = "UPDATE Content SET published = NOW(), deleted = ? WHERE id = ?";
		$res = $this->db->ExecuteQuery($sql, array(null,$id));
		
		if($res){
			header("Location: content.php");
		}else{
			$output = "Informationen raderades EJ.<br><pre>" .print_r($this->db->ErrorInfo(), 1) ."</pre>";
		}
	}
	
	public function Remove($id){
		$sql = "DELETE FROM Content WHERE id = ?;";
		$res = $this->db->ExecuteQuery($sql, array($id));
		
		if($res){
			header("Location: content.php");
		}else{
			$output = "Informationen raderades EJ.<br><pre>" .print_r($this->db->ErrorInfo(), 1) ."</pre>";
		}
	}
	
	
	
	

	
}