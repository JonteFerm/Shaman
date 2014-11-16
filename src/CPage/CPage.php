<?php
class CPage{
	private $url;
	private $filter;
	private $db;
	private $data;
	private $title;
	private $editLink;
	private $unpublished;
	private $user;
	
	public function __construct($url, $filter, $db){
		$this->url = $url;
		$this->filter = $filter;
		$this->db = $db;
		$this->data = null;
		$this->title = null;
		$this->editLink = null;
		$this->user = null;
		$this->InitPageVariables();
	}
	
	private function InitPageVariables(){
		$sql = "
		SELECT * FROM Content WHERE
			type = 'page' AND
			url = ? AND published <= NOW();
		";
		
		$res = $this->db->ExecuteSelectAndFetchAll($sql,array($this->url));
		$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
		if(isset($res[0])){
			$this->title = htmlentities($res[0]->title, null, 'UTF-8');
			$this->data = $this->filter->doFilter(htmlentities($res[0]->data, null, 'UTF-8'), $res[0]->filter);
			if($res[0]->user){
				$this->user = htmlentities($res[0]->user, null, 'UTF-8');
			}else{
				$this->user = htmlentities("Okänd", null, 'UTF-8');
			}
			$this->editLink = $acronym ? "<p><a href='edit_content.php?id={$res[0]->id}'>Uppdatera sidan</a></p>" : null;
		}

	}
	
	public function GetPageEditLink(){
		if($this->editLink){
			return $this->editLink;
		}else{
			return null;
		}
	}

	
	public function GetPage(){
		if($this->title == null && $this->data == null){
			$pageData = "Det finns ingen publicerad sida med denna url.";
		
		}else{
			$pageData = <<<EOD
			<header>
			<h1>{$this->title}</h1>
			</header>

			{$this->data}

			<footer>
			{$this->GetPageEditLink()}
			<p>Publicerad av: {$this->user}</p>
			</footer>
EOD;
			
		}		
		
		return $pageData;
	}

}