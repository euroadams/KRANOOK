<?php


trait Section{
	
	
	
	/************************************************************************************/
	/************************************************************************************
										METHODS
	/************************************************************************************
	/************************************************************************************/
				
	
	/*** Method for fetching a field from database section table ***/
	public function getSectionField($param, $col="CATEG_ID"){
		
		/////PDO QUERY//////////
		
		$sql = 'SELECT '.$col.' FROM sections WHERE (ID = ? OR SECTION_NAME = ?) LIMIT 1 ';
		$valArr = array($param, $param);
		$return = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
		return $return;

	}


	

	

	
		
	/*** Method for toggling section id to section name and vice versa ***/
	public function sectionIdToggle($param){
		
		$return = '';
		
		/////PDO QUERY//////////
		
		$sql = 'SELECT ID, SECTION_NAME FROM sections WHERE (ID = ? OR SECTION_NAME = ? OR SECTION_SLUG = ?) LIMIT 1 ';
		$valArr = array($param, $param, $param);
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		$row = $this->DBM->fetchRow($stmt);
	
		if(!empty($row)){
			
			$return = is_numeric($param)? $row["SECTION_NAME"] : $row["ID"];
			
		}
		
		return $return;

	}


	

	


	
		
	/*** Method for fetching section description text ***/
	public function getSectionDescription($param){

		$desc="";
	   
	   //////PDO QUERY//
   
	   $sql = "SELECT SECTION_DESC, ID FROM sections WHERE ID = ? OR SECTION_NAME = ? LIMIT 1";
	   $valArr = array($param, $param);
	   $stmt = $this->DBM->doSecuredQuery($sql, $valArr);
	   $row = $this->DBM->fetchRow($stmt);
	   
	   if(!empty($row)){
		   
		   $desc = $row["SECTION_DESC"];
		   $id = $row["ID"];
		
		   if($id == HOMEPAGE_SID)
			   $desc = $this->SITE->getSiteName().' '.$desc;
		
	   }
		   
	   
	   return $desc;
	   
   }					
	



   
	   

   
	   
   /*** Method for counting topics in a section ***/
   public function countSectionTopics($sid, $html=false){
			
	   ///PDO QUERY//////
	   
	   $sql = "SELECT COUNT(*) FROM topics WHERE SECTION_ID=?";
	   $valArr = array($sid);
				   
	   $totalTopics = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
	   $boardTopics = $this->ENGINE->format_number($totalTopics);

	   $boardTopics = $html? '<span class="cyan">'.$boardTopics.'</span>' : $boardTopics.' topic'.(($totalTopics == 1)? '' : 's');
	   
	   return $boardTopics;


   }
	
	

   



	
	
		
	/*** Method for loading section ***/
	public function loadSections($sid){
		
		$sectionDesc = $this->getSectionDescription($sid);
		$sectionName = $this->SITE->sectionIdToggle($sid);			
		
		/////GET THE TOTAL TOPICS IN SECTION///////			
		$totalTopicCountView = "(".$this->countSectionTopics($sid).", ";

		///////////GET THE TOTAL POST OF ALL TOPICS IN SECTION///////										
		$sidTidsSubQry = "SELECT ID FROM topics WHERE SECTION_ID=?";
		///////////PDO QUERY//////////
			
		$sql = "SELECT COUNT(*) FROM posts WHERE TOPIC_ID IN(".$sidTidsSubQry.")";
		$valArr = array($sid);
		$totalPosts = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();

		$totalPostCountView = $this->ENGINE->format_number($totalPosts).' post'.(($totalPosts == 1)? '' : 's').' & ';
		
		
		///////GET TOTAL TOPIC VIEWS IN SECTION//////
		
		///////////PDO QUERY/////////
			
		$sql = "SELECT  COUNT(*) AS TOTAL_RECS FROM topic_views WHERE TOPIC_ID IN(".$sidTidsSubQry.")";
		$valArr = array($sid);
		$topicViewCountView = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();

		$topicViewCountView = $this->ENGINE->format_number($topicViewCountView).' view'.(($topicViewCountView == 1)? '' : 's').')';

		$sectionSlug = /*(strtolower($sectionName) == "adults")? 'plus18' :*/ $this->ENGINE->sanitize_slug($sectionName);
		
		
		////GENERATE THE SECTION LOOKS//////
			
		$sections = '<li>
						<a title="'.$sectionName.': '.$sid.'" class="ra-la-base sc1 block" href="/'.$sectionSlug.'">
							<div class="base-pad" >
								<span class="ra-la" >'.$sectionName.'</span>
								<div class="ra-la-follower">
									<div class="sc-desc">'.$sectionDesc.'</div>
									<small class="sc-footer">'.$totalTopicCountView.$totalPostCountView.$topicViewCountView.'</small>
								</div>
							</div>
						</a>
					</li>';

		return $sections;	
		
	}
	 
	 
	 
	 
	 
	
	
	
	




	
	


}


?>