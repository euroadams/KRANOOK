<?php


trait Category{
	
	
	
	/************************************************************************************/
	/************************************************************************************
										METHODS
	/************************************************************************************
	/************************************************************************************/
		
		
	/*** Method for fetching category description text ***/
	public function getCategoryDescription($param){

		//////PDO QUERY//
	
		$sql = "SELECT CATEG_DESC FROM categories WHERE ID = ? OR CATEG_NAME = ? LIMIT 1";
		$valArr = array($param, $param);
		$desc = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		 
		return $desc;
		
	}

	

	


	
		
	/*** Method for toggling category id to category name and vice versa ***/
	public function categoryIdToggle($param){
	
		$return = '';
		
		/////PDO QUERY//////////
		
		$sql = 'SELECT ID, CATEG_NAME FROM categories WHERE (ID = ? OR CATEG_NAME = ? OR CATEG_SLUG = ?) LIMIT 1 ';
		$valArr = array($param, $param, $param);
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		$row = $this->DBM->fetchRow($stmt);
		
		if(!empty($row)){
			
			$return = is_numeric($param)? $row["CATEG_NAME"] : $row["ID"];
			
		}
		
		return $return;

	}


		

	


	


	
		
				
	 
	 
	
	
	


}


?>