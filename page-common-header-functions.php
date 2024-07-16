<?php



function defineSidConstants(){

	global $dbm;

	$genCid = 1; $entCid = 2; $sciCid = 3;
	$sql = "SELECT ACCESS_LEVEL, GROUP_CONCAT(ID) IDS, GROUP_CONCAT(SECTION_NAME) SCAT_NAMES FROM sections WHERE ACCESS_LEVEL !='' GROUP BY ACCESS_LEVEL";
	$valArr = array();
	$stmt = $dbm->doSecuredQuery($sql, $valArr);
			
	while($row = $dbm->fetchRow($stmt)){

		$accessLevel = strtolower($row["ACCESS_LEVEL"]);
		$ids = $row["IDS"];
		$scatNames = $row["SCAT_NAMES"];
		$sidsArr = explode(',', $ids);
		$scatNamesArr = explode(',', $scatNames);

		switch($accessLevel){

			case 'staff': $staffsOnlySids = $ids; $staffsOnlySidArr = $sidsArr; $staffsOnlySectionNamesArr = $scatNamesArr; break;

			case 'virtual_sid': $virtualSids = $ids; $virtualSidArr = $sidsArr; $virtualSectionNamesArr = $scatNamesArr; break;

			case 'virtual_csid': $cids = $genCid.','.$entCid.','.$sciCid; $cidsArr = explode(',', $cids); 
				$virtualCSid = $ids; $virtualCSidArr = $sidsArr; $virtualCategNamesArr = $scatNamesArr; break;

			case 'adult': $adultsOnlySids = $ids; $adultsOnlySidArr = $sidsArr; $adultsOnlySectionNameArr = $scatNamesArr; break;

		}

	}

	
	define("SIDS_STAFF_ONLY", $staffsOnlySids);
	define("SIDS_STAFF_ONLY_ARR", $staffsOnlySidArr);
	define("VIRTUAL_SIDS", $virtualSids);
	define("VIRTUAL_SIDS_ARR", $virtualSidArr);
	define("VIRTUAL_CSID", $virtualCSid);
	define("VIRTUAL_CSID_ARR", $virtualCSidArr);
	define("CIDS", $cids);
	define("CIDS_ARR", $cidsArr);
	define("SIDS_ADULT_ONLY", $adultsOnlySids);
	define("SIDS_ADULT_ONLY_ARR", $adultsOnlySidArr);
	define("HOMEPAGE_SID", $virtualSidArr[array_search('homepage', array_flip(array_change_key_case(array_flip($virtualSectionNamesArr))))]);
	define("ELITE_SID", $virtualSidArr[array_search('elite', array_flip(array_change_key_case(array_flip($virtualSectionNamesArr))))]);
	define("MODS_SID", $staffsOnlySidArr[array_search('moderators', array_flip(array_change_key_case(array_flip($staffsOnlySectionNamesArr))))]);
	define("RECYCLEBIN_SID", $staffsOnlySidArr[array_search('recycle bin', array_flip(array_change_key_case(array_flip($staffsOnlySectionNamesArr))))]);
	define("ADULT_SID", $adultsOnlySidArr[array_search('adults', array_flip(array_change_key_case(array_flip($adultsOnlySectionNameArr))))]);
	define("GEN_SID", $virtualCSidArr[array_search('general', array_flip(array_change_key_case(array_flip($virtualCategNamesArr))))]);
	define("ENT_SID", $virtualCSidArr[array_search('entertainment', array_flip(array_change_key_case(array_flip($virtualCategNamesArr))))]);
	define("SCI_TECH_SID", $virtualCSidArr[array_search('science & technology', array_flip(array_change_key_case(array_flip($virtualCategNamesArr))))]);
	define("GEN_CID", $genCid);
	define("ENT_CID", $entCid);
	define("SCI_TECH_CID", $sciCid);
	
}

//GET EXCEPTION PARAMS//
function getExceptionParams($type, $retArr=true){
	global $GLOBAL_isStaff;

	$virtualSidArr = VIRTUAL_SIDS_ARR;
	$virtualCSidArr = VIRTUAL_CSID_ARR;
	$staffsOnlySidArr = SIDS_STAFF_ONLY_ARR;
	$allVirtualsArr = array_merge($virtualSidArr, $virtualCSidArr);
	$allVirtualsNstafssArr = array_merge($allVirtualsArr, $staffsOnlySidArr);
	$virtualSidArr = array_merge($virtualSidArr, $staffsOnlySidArr);

	switch(strtolower($type)){

		case 'sidsnofollow': $param = $allVirtualsArr; break;	
	
		case 'sidsnosearch': $param = (!$GLOBAL_isStaff)? $allVirtualsNstafssArr : $allVirtualsArr; break;

		case 'sidsstaffsonly': $param = $staffsOnlySidArr; break;

		case 'virtualsids': $param = $virtualSidArr; break;

		case 'virtualcsid': $param = $virtualCSidArr; break;

		case 'allvirtuals': $param = $allVirtualsArr; break;

		case 'sidsnothread': $param = array_merge($allVirtualsArr, array(RECYCLEBIN_SID)); break;

	}
	
	if($retArr) return $param;

	return implode(",", $param);

}


///FUNCTION TO GET STATES OF AUTO PILOT/////
function getAutoPilotState($pilotNameArr, $retValArr=""){
	
	global $dbm;

	$ret=$colsFoundArr=array();
	$pilotNameArr = (array)$pilotNameArr;
	$retValArr = (array)$retValArr;
	$paramLen = count($pilotNameArr);
	$pilotNameArr = array_flip(array_change_key_case(array_flip($pilotNameArr), CASE_LOWER));
	$paramPH = rtrim(str_repeat('?,', $paramLen), ',');

	if(!empty($pilotNameArr)){
		
		$sql = "SELECT ID, PILOT_NAME, STATE, STATE_NAME FROM auto_pilots WHERE PILOT_NAME IN(".$paramPH.") LIMIT ".$dbm->getMaxRowPerSelect();
		$valArr = $pilotNameArr;
		$stmt = $dbm->doSecuredQuery($sql, $valArr);
		
		while($row = $dbm->fetchRow($stmt)){

			$id = $found = $row["ID"];
			$pilotName = strtolower($row["PILOT_NAME"]);
			$state = $row["STATE"];
			$stateName = $row["STATE_NAME"];
			$rvInd = array_search($pilotName, $pilotNameArr);
			$retVal = isset($retValArr[$rvInd])? $retValArr[$rvInd] : '';
			$retVal = strtolower($retVal);
			$verifyItExists = in_array($retVal, array('true', 1));

			if($retVal == "sn")
				$ret[$pilotName] = $stateName;

			else
				$ret[$pilotName] =  ($verifyItExists? $found : $state);
			
		}
		
		if(isset($found)){
			
			##REARRANGE TO MATCH INDEX OF COLS PASSED AND TO CONVERT $ret TO NUMERIC ARRAY
			foreach($pilotNameArr as $pilotName)
				$tmp[] = isset($ret[$pilotName])? $ret[$pilotName] : 0;
			
			$ret = $tmp;
			
		}else
			$ret = array_fill(0, $paramLen, 0);
		
	}
			
	if($paramLen <= 1)
		$ret = implode('', $ret);
			
	return $ret;
			
}







?>