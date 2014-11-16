<?php
class CDBView{
	private $db;

	public function __construct($db){
		$this->db = $db;
	}
	
	private function GetQueryString($options=array(), $prepend='?') {

	  $query = array();
	  parse_str($_SERVER['QUERY_STRING'], $query);


	  $query = array_merge($query, $options);


	  return $prepend . htmlentities(http_build_query($query));
	}

	private function GetHitsPerPage($hits, $current=null) {
	  $nav = "Träffar per sida: ";
	  
	  foreach($hits as $val) {
		if($current == $val) {
		  $nav .= "$val ";
		}
		else {
		  $nav .= "<a href='" . $this->GetQueryString(array('hits' => $val)) . "'>$val</a> ";
		}
	  }  
	  return $nav;
	}

	private function GetPageNavigation($hits, $page, $max, $min=1) {
	  $nav  = ($page != $min) ? "<a href='" . $this->GetQueryString(array('page' => $min)) . "'>&lt;&lt;</a> " : '&lt;&lt; ';
	  $nav .= ($page > $min) ? "<a href='" . $this->GetQueryString(array('page' => ($page > $min ? $page - 1 : $min) )) . "'>&lt;</a> " : '&lt; ';

	  for($i=$min; $i<=$max; $i++) {
		if($page == $i) {
		  $nav .= "$i ";
		}
		else {
		  $nav .= "<a href='" . $this->GetQueryString(array('page' => $i)) . "'>$i</a> ";
		}
	  }

	  $nav .= ($page < $max) ? "<a href='" . $this->GetQueryString(array('page' => ($page < $max ? $page + 1 : $max) )) . "'>&gt;</a> " : '&gt; ';
	  $nav .= ($page != $max) ? "<a href='" . $this->GetQueryString(array('page' => $max)) . "'>&gt;&gt;</a> " : '&gt;&gt; ';
	  return $nav;
	}

	private function Orderby($column) {
	  $nav  = "<a href='" . $this->GetQueryString(array('orderby'=>$column, 'order'=>'asc')) . "'>&darr;</a>";
	  $nav .= "<a href='" . $this->GetQueryString(array('orderby'=>$column, 'order'=>'desc')) . "'>&uarr;</a>";
	  return "<span class='orderby'>" . $nav . "</span>";
	}
	
	
	private function CreateForm($year1, $year2){
		$form = "<form method='get'><fieldset><legend>Sök</legend>";
		$form .= "<p><label for='title-input'>Titel (delsträng, använd % som *):</label><input type='text' name='title' id='title-input'/></p>";
		$form .= "<p><label>Skapad mellan åren:<input type='text' name='year1' value='{$year1}' style='background-color:white;'/>
					- <input type='text' name='year2' value='{$year2}' style='background-color:white;'/>
				</label>
				</p>";
		$form .= "<p><input type='submit' name='doSearch' value='Sök'/></p>";
		$form .= "</fieldset></form>";
		
		return $form;
	}
	
	private function CreateTable($orderby, $order, $hits, $title, $year1, $year2, $page){
		$sqlOrig = 'SELECT M.* FROM Movie AS M';
		$where    = null;
		$groupby  = ' GROUP BY id';
		$limit    = null;
		$sort     = " ORDER BY $orderby $order";
		$params   = array();

		if($title) {
		  $where .= ' AND title LIKE ?';
		  $params[] = $title;
		} 

		if($year1) {
		  $where .= ' AND year >= ?';
		  $params[] = $year1;
		} 
		if($year2) {
		  $where .= ' AND year <= ?';
		  $params[] = $year2;
		} 

		if($hits && $page) {
		  $limit = " LIMIT $hits OFFSET " . (($page - 1) * $hits);
		}
		
		$where = $where ? " WHERE 1 {$where}" : null;
		$sql = $sqlOrig . $where . $groupby . $sort . $limit;
		$res = $this->db->ExecuteSelectAndFetchAll($sql, $params);
		$table = "<table><tr><th>Rad</th><th>Id " . $this->Orderby('id') . "</th><th>Bild</th><th>Titel " . $this->Orderby('title') . "</th><th>År " . $this->Orderby('year') . "</th></tr>";
		foreach($res AS $key => $val) {
			$table .= "<tr><td>{$key}</td><td>{$val->id}</td><td><img src='{$val->image}' alt='image' style='width:100px; height:70px;'/></td><td>{$val->title}</td><td>{$val->YEAR}</td></tr>";
		}
		$table .= "</table>";
		
		$sql = "SELECT COUNT(id) AS rows FROM ($sqlOrig $where $groupby) AS Movie";
		$res = $this->db->ExecuteSelectAndFetchAll($sql, $params);
		$rows = $res[0]->rows;
		
		$max = ceil($rows / $hits);
		
		$table .= $this->GetPageNavigation($hits,$page,$max);
		
		return $table;
	}
	
	public function GenerateContent(){
		$title    = isset($_GET['title']) ? $_GET['title'] : null;
		$hits     = isset($_GET['hits'])  ? $_GET['hits']  : 8;
		$page     = isset($_GET['page'])  ? $_GET['page']  : 1;
		$year1    = isset($_GET['year1']) && !empty($_GET['year1']) ? $_GET['year1'] : null;
		$year2    = isset($_GET['year2']) && !empty($_GET['year2']) ? $_GET['year2'] : null;
		$orderby  = isset($_GET['orderby']) ? strtolower($_GET['orderby']) : 'id';
		$order    = isset($_GET['order'])   ? strtolower($_GET['order'])   : 'asc';		
		
		is_numeric($hits) or die('Check: Hits must be numeric.');
		is_numeric($page) or die('Check: Page must be numeric.');
		is_numeric($year1) || !isset($year1)  or die('Check: Year must be numeric or not set.');
		is_numeric($year2) || !isset($year2)  or die('Check: Year must be numeric or not set.');
		in_array($orderby, array('id', 'title', 'year')) or die('Check: Not valid column');
        in_array($order, array('asc', 'desc')) or die('Check: Not valid sort order');
		
		$html = $this->GetHitsPerPage(array(2, 4, 8),$hits);
		$html .= $this->CreateForm($year1, $year2);
		$html .= $this->CreateTable($orderby, $order, $hits, $title, $year1, $year2, $page);
		
		return $html;
	}

}