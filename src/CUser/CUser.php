<?php
class CUser{
	private $acronym;
	private $name;
	private $db;
	
	public function __construct($db){
		$this->acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
		$this->name = isset($_SESSION['user']) ? $_SESSION['user']->name : null;
		$this->db = $db;	
	}

	public function Login($acronym, $password){
		$sql = "SELECT acronym, name FROM User WHERE acronym = ? AND password = md5(concat(?,salt))";
		$res = $this->db->ExecuteSelectAndFetchAll($sql,array($acronym, $password));
		
		if(isset($res[0])){
			$_SESSION['user'] = $res[0];
		}
		header('Location: login.php');			
	}
	
	public function Logout(){
		unset($_SESSION['user']);
		header('Location: login.php');
	}
	
	
	public function GetAcronym(){
		return $this->acronym;
	}
	
	public function GetName(){
		return $this->name;
	}	
	
	public function Output(){
		if($this->acronym){
			$output = "<p>Du är inloggad som: {$this->acronym}({$this->name})</p>";
			$output .= "<form method='post'><fieldset><legend>Logga ut</legend><p><input type='submit' name='doLogout' value='Logga ut'></p></fieldset></form>";
			
		}else{
			$output = "<p>Du är INTE inloggad.</p>";
			$output .= "<form method='post'><fieldset><legend>Logga in</legend><label for='user-input'>Användarnamn</label><p><input id='user-input' type='text' name='acronym'></p>";
			$output .= "<label for='pw-input'>Lösenord</label><p><input id='pw-input' type='text' name='password'></p><p><input type='submit' name='doLogin' value='Logga in'></p></fieldset></form>";
		
		}
		
		return $output;
	
	}


}