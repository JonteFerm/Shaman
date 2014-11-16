<?php
class CBlog{
	private $slug;
	private $slugarr;
	private $filter;
	private $db;
	private $data;
	private $title;
	private $editLink;
	private $user;
	private $id;
	
	public function __construct($slug, $filter, $db){
		$this->slug = $slug;
		$this->filter = $filter;
		$this->db = $db;
		$this->data = array();
		$this->title = array();
		$this->editLink = array();
		$this->slugarr = array();
		$this->user = array();
		$this->id = array();
		
		$this->InitPageVariables();
	
	}
	
	private function InitPageVariables(){
		$slugSql = $this->slug ? 'slug=?' : '1';

		$sql = "
		SELECT * FROM Content WHERE
			type = 'post' AND
			$slugSql AND published <= NOW()
			ORDER BY updated DESC;
		";

		$res = $this->db->ExecuteSelectAndFetchAll($sql,array($this->slug));
		$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
		foreach($res as $post){
			$this->title[] = htmlentities($post->title, null, 'UTF-8');
			$this->data[] = $this->filter->doFilter(htmlentities($post->data, null, 'UTF-8'), $post->filter);
			$this->slugarr[] = htmlentities($post->slug, null, 'UTF-8');
			$this->id[] = htmlentities($post->id,null,'UTF-8');
			if($post->user){
				$this->user[] = htmlentities($post->user, null, 'UTF-8');
			}else{
				$this->user[] = htmlentities("Okänd", null, 'UTF-8');
			}
			
			$this->editLink[] = $acronym ? "<p><a href='edit_content.php?id={$post->id}'>Uppdatera sidan</a></p>" : null;
		}
	}
	
	
	public function GetPageTitle(){
		return $this->title;
	}
	
	public function GetPageEditLink(){
		if($this->editLink){
			return $this->editLink;
		}else{
			return null;
		}
	}
	
	public function GetBlogPosts(){
		$blogPosts = null;
		$ids = $this->id;
		$titles = $this->title;
		$datas = $this->data;
		$slugs = $this->slugarr;
		$editLinks = $this->GetPageEditLink();
		$users = $this->user;
		
		for($i=0; $i < count($ids); $i++){
			$title = $titles[$i];
			$data = $datas[$i];
			$user =  $users[$i];
			$editLink = $editLinks[$i];

			$blogPosts .= <<<EOD
			<header>
			<h1><a href='blog.php?slug={$slugs[$i]}'>{$title}</a></h1>
			</header>

			{$data}

			<footer>
			{$editLink}
			<p>Publicerad av: {$user}</p>
			</footer>
		
EOD;
		}
		
		if($blogPosts){
			return $blogPosts;
		}else{
			if($this->slug){
				return "<p>Det finns inga poster med detta id.</p>";
			}else{
			
				return "<p>Det finns inga poster.</p>";
			}
			
		
		}
	
	}
	
}