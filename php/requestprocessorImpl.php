<?php

/**
  * Copyright (c) 2016, Huan Zhan
  * All rights reserved.
  */

abstract class USER_PRIVILEGE
{
	const ADMIN  = 1;
	const DOCTOR = 2;
	const NURSE  = 3;
}

class RequestProcessor
{
	// For identity and privilege checking
	private $userId;
	private $userPrivilege;
	private $log;
	
  // Response sent to client side
  // Sending is done in destructor
  // Must contain an error obj to indicating status
	private $response = array();

	// Define so the resoures can be released in destructor
	private $con;
	private $result;

	function __construct()
	{
		// Open log file
		$this->log = fopen($_SERVER["DOCUMENT_ROOT"] . "/logs/act.log", "a");
		
		// Read cookie
	  if (isset($_COOKIE["sUserProfile"])) {
			$userProfile = json_decode(base64_decode($_COOKIE["sUserProfile"]));
			$this->userId = $userProfile->{ "userId" };
			$this->userPrivilege = $userProfile->{ "privilege" };
		}

		// Connect MySQL
		$this->con = mysqli_connect("127.0.0.1", "xxx", "xxx");
		if (!$this->con) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Can't connect MySQL.")
			);
			return;
		}

		// Select database
		$select = mysqli_select_db($this->con, "trackdb");
		if (!$select) {
			$this->response = array(
				"header" => array("errcode" => 1, "Can't connect database.")
			);
			$this->con = false;
			return;
		}
	}

	function __destruct()
	{
		if ($this->con) {
			mysqli_close($this->con);
		}

		if ($this->result) {
			mysqli_free_result($this->result);
		}

		// Return response to frontend.
		if ($_GET["requestId"] != 50002 && $_GET["requestId"] != 10007) {
			header("Content-type: application/json");
			echo json_encode($this->response);
		}

		fclose($this->log);
	}

	function con()
	{
		return $this->con;
	}

	// Client side paging
	function getDealListClient()
	{
		$query = "SELECT SQL_CACHE
										 d.dealId, d.optionId, o.doctorId, d.nurseId,
										 d.track, u1.name AS nurseName, u2.name AS doctorName,
										 o.po, o.item, d.qty, o.price, d.paid, d.note,
										 d.date AS date, d.claimed, d.verified
								FROM deals d
								LEFT JOIN options o ON d.optionId=o.optionId
								LEFT JOIN users u1 ON d.nurseId=u1.userId
								LEFT JOIN users u2 ON o.doctorId=u2.userId";

		$where = "";

		// Filter by deal type
		if (isset($_GET["dealType"])) {
			$where .= " WHERE d.claimed=" . ($_GET["dealType"] == "claimed")?1:0;
		}

    // Doctor and nurse can only see their own deals
		if ($this->userPrivilege != USER_PRIVILEGE::ADMIN) {
			$sWhere .= ($sWhere == "") ? " WHERE " : " AND ";

			if ($this->userPrivilege == USER_PRIVILEGE::DOCTOR) {
				$where .= " o.doctorId=$this->userId";
			} else if ($this->userPrivilege == USER_PRIVILEGE::NURSE) {
				$where .= " d.nurseId=$this->userId";
			}
		}

		$query .= $where;

		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		while ($row = mysqli_fetch_assoc($this->result)) {
			// Try to use name-value pair instead
			$deal = array(
				$row["dealId"],
				$row["optionId"],
				$row["doctorId"],
				$row["nurseId"],
				$row["track"],
				$row["nurseName"],
				$row["doctorName"],
				$row["po"],
				$row["item"],
				$row["qty"],
				$row["price"],
				$row["paid"],
				$row["claimed"],
				$row["verified"],
				$row["note"],
				$row["date"],
			);
			array_push($this->response, $deal);
		}
	}

	// Server-side paging
	function getDealListServer()
	{
		// Fields for query
		$aColumns = array(
			"d.dealId", "d.optionId", "o.doctorId", "d.nurseId", "d.track",
			"u1.name AS nurseName", "u2.name AS doctorName", "o.po", "o.item",
			"o.price", "d.qty", "d.claimed", "d.verified", "d.paid", "d.date", "d.note"
		);

		// Fields for fetching query result
		$aColumnsFetch = array(
			"dealId", "optionId", "doctorId", "nurseId", "track", "nurseName",
			"doctorName", "po", "item", "price", "qty", "claimed", "verified",
			"paid", "date", "note"
		);

		// Fields for ordering, because order may be based on calculation
		$aColumnsOrder = array(
			"d.dealId", "d.optionId", "o.doctorId", "d.nurseId", "d.track",
			"u1.name", "u2.name", "o.po", "o.item", "o.price", "d.qty", "d.claimed",
			"d.verified", "d.paid", "d.date", "d.note"
		);
     
		// Fields for ordering, because order may be based on calculation
		$aColumnsSearch = array(
			"d.track", "u1.name", "u2.name", "o.po", "o.item" 
		);

    $sIndexColumn = "dealId";
    $sTable = "deals";
     
		// Paging
    $sLimit = "";
    if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
      $sLimit = "LIMIT " . intval($_GET['iDisplayStart']) . ", " . intval($_GET['iDisplayLength']);
    }
     
		// Ordering
    $sOrder = "";
    if (isset($_GET['iSortCol_0'])) {
			$sOrder = "ORDER BY  ";
			for ($i=0 ; $i<intval($_GET['iSortingCols']) ; $i++) {
				if ($_GET['bSortable_'.intval($_GET['iSortCol_'.$i])] == "true") {
				  $sOrder .= $aColumnsOrder[intval($_GET['iSortCol_'.$i])]."
					  ".($_GET['sSortDir_'.$i]==='asc'?'asc':'desc').", ";
				}
			}
			 
			$sOrder = substr_replace($sOrder, "", -2);
			if ($sOrder == "ORDER BY") {
				$sOrder = "";
			}
    }
     
    // Filtering
    // NOTE this does not match the built-in DataTables filtering which does it
    // word by word on any field. It's possible to do here, but concerned about efficiency
    // on very large tables, and MySQL's regex functionality is very limited
    $sWhere = "";
    if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
			$sWhere = "WHERE (";
			for ($i=0; $i<count($aColumnsSearch); $i++) {
				if (isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true") {
					$sWhere .= $aColumnsSearch[$i]." LIKE '%".mysqli_real_escape_string($this->con, $_GET['sSearch'])."%' OR ";
				}
			}
			$sWhere = substr_replace($sWhere, "", -3);
			$sWhere .= ')';
    }
     
    // Individual column filtering
    for ($i=0; $i<count($aColumns); $i++) {
			if (isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '') {
				$sWhere .= ($sWhere == "")?"WHERE ":" AND ";
				$sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($this->con, $_GET['sSearch_'.$i])."%' ";
			}
    }
     
		// Filter by dealType
		if ($_COOKIE['dealType'] != 'all') {
			if ($_COOKIE['dealType'] == 'claimed') {
        $sWhere .= ($sWhere == "")?"WHERE ":" AND ";
				$sWhere .= $aColumns[11]."=1";
			} else if ($_COOKIE['dealType'] == 'unclaimed') {
        $sWhere .= ($sWhere == "")?"WHERE ":" AND ";
				$sWhere .= $aColumns[11]."=0";
			}
		}
     
		// Filter by privilege
		if ($this->userPrivilege != USER_PRIVILEGE::ADMIN) {
			$sWhere .= ($sWhere == "")?"WHERE ":" AND ";

			if ($this->userPrivilege == USER_PRIVILEGE::DOCTOR) {
				$sWhere .= "o.doctorId=$this->userId";
			} else if ($this->userPrivilege == USER_PRIVILEGE::NURSE) {
				$sWhere .= "d.nurseId=$this->userId";
			}
		}

		if (isset($_GET["dealList"])) {
			$deals = preg_split('/-+/', $_GET["dealList"]);

			if ($sWhere == "") {
				$sWhere = " WHERE dealId IN (";
			} else {
				$sWhere .= " AND dealId IN (";
			}

			foreach ($deals AS $deal) {
				$sWhere .= "'" . $deal . "', ";
			}

			$sWhere = substr_replace($sWhere, "", -2);
			$sWhere .= ")";
		}

    $sQuery = "
			SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
			FROM $sTable d     
			JOIN options o ON d.optionId=o.optionId    
			JOIN users u1 ON d.nurseId=u1.userId  
			JOIN users u2 ON o.doctorId=u2.userId  
			$sWhere
			$sOrder
			$sLimit
    ";

    $rResult = mysqli_query($this->con, $sQuery);
		if (!$rResult) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}
     
    // Data set length after filtering
    $sQuery = " SELECT FOUND_ROWS() ";
    $rResultFilterTotal = mysqli_query($this->con, $sQuery);
		if (!$rResultFilterTotal) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}
    $aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
    $iFilteredTotal = $aResultFilterTotal[0];
     
    // Total data set length
    $sQuery = "
			SELECT COUNT(".$sIndexColumn.")
			FROM $sTable
    ";
    $rResultTotal = mysqli_query($this->con, $sQuery);
		if (!$rResultTotal) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}
    $aResultTotal = mysqli_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];
     
    $this->response = array(
			"sEcho"                => intval($_GET['sEcho']),
			"iTotalRecords"        => $iTotal,
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData"               => array()
    );
     
    while ($aRow = mysqli_fetch_array($rResult)) {
			$row = array();
			for ($i=0 ; $i<count($aColumnsFetch) ; $i++) {
				if ($aColumnsFetch[$i] == "version") {
					// Special this->response formatting for 'version' column
					$row[] = ($aRow[ $aColumnsFetch[$i] ]=="0")?'-':$aRow[$aColumnsFetch[$i]];
				} else if ($aColumns[$i] != ' ') {
					// General this->response
					$row[] = $aRow[$aColumnsFetch[$i]];
				}
			}
			$this->response['aaData'][] = $row;
    }
	}

	function getOptionListClient()
	{
		$doctorId = $_GET["doctorId"];
		$query = "SELECT SQL_CACHE
										 o.optionId, o.po, u.name, o.item, o.price, o.date,
										 o.qty, o.labeled, o.shipped, o.active, o.note, o.shipLog
								FROM options o
								LEFT JOIN users u ON o.doctorId=u.userId";

		if ($this->userPrivilege == USER_PRIVILEGE::DOCTOR) {
			// doctors can only see their own
			$query .= " WHERE o.doctorId=$this->userId";
		} else {
			// 0 means selecting all
			if ($doctorId != NULL && $doctorId != 0) {
				$query .= " WHERE o.doctorId=$doctorId";
			}
		}

		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		while ($row = mysqli_fetch_assoc($this->result)) {
			if ($this->userPrivilege != USER_PRIVILEGE::NURSE) {
				$option = array(
					$row["optionId"],
					$row["name"],
					$row["po"],
					$row["item"],
					$row["price"],
					$row["date"],
					$row["qty"],
					$row["labeled"],
					$row["shipped"],
					$row["active"],
					$row["note"],
					$row["shipLog"],
				);
			} else {
				$option = array(
					$row["optionId"],
					$row["name"],
					$row["po"],
					$row["item"],
					$row["price"],
					$row["date"],
					0,
					0,
					0,
					$row["active"],
					0,
					0
				);
			}
			array_push($this->response, $option);
		}
	}

  function getNurseOptionListServer()
  {
	  $aColumns = array(
		  'o.optionId', 'u.name', 'o.po', 'o.item', 'o.upc', 'o.price', 'o.date',
		  'sum(d.qty) as qty', 'o.labeled', 'o.shipped', 'o.active', 'o.note', 'o.shipLog'
		);

		// Fields for fetching query result. No prefix "o.".
		$aColumnsFetch = array(
		  'optionId', 'name', 'po', 'item', 'upc', 'price', 'date',
		  'qty', 'labeled', 'shipped', 'active', 'note', 'shipLog'
		);

		// Fields for sorting. The index must be the same as column index on the page.
		$aColumnsOrder = array(
			'u.name', 'o.po', 'o.item', 'upc', 'o.price', 'o.qty', 'o.labeled',
			'o.shipped', '(o.qty-o.shipped)', '(o.price*o.qty)', 'o.date'
		);

    // From
    $sFrom = "FROM deals d 
              JOIN options o ON d.optionId=o.optionId
			        JOIN users u ON o.doctorId=u.userId";

    // Filter, don't change
    $sWhere = "";
    if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
			$sWhere = "WHERE (";
			for ($i=0; $i<count($aColumns); $i++) {
				if (isset($_GET['bSearchable_'.$i]) 
            && $_GET['bSearchable_'.$i] == "true") {
					$sWhere .= $aColumns[$i] 
                     . " LIKE '%" 
                     . mysqli_real_escape_string($this->con, $_GET['sSearch']) 
                     . "%' OR ";
				}
			}
			$sWhere = substr_replace($sWhere, "", -3);
			$sWhere .= ')';
    }

    $sWhere .= ($sWhere == "") ? " WHERE " : " AND ";
    $sWhere .= "d.nurseId=$this->userId";

    // Group
    $sGroupBy = "GROUP BY o.optionId";

    // Order, don't change
    $sOrder = "";
    if (isset($_GET['iSortCol_0'])) {
			$sOrder = "ORDER BY  ";
			for ($i=0; $i<intval($_GET['iSortingCols']); $i++) {
				if ($_GET['bSortable_' . intval($_GET['iSortCol_'.$i])] == "true") {
				  $sOrder .= $aColumnsOrder[intval($_GET['iSortCol_'.$i])] 
                  . " " 
                  . ($_GET['sSortDir_'.$i]==='asc'?'asc':'desc') 
                  . ", ";
				}
			}
			$sOrder = substr_replace($sOrder, "", -2);
			if ($sOrder == "ORDER BY") {
				$sOrder = "";
			}
    }

    // Limit, don't change
    $sLimit = "";
    if (isset($_GET['iDisplayStart']) 
        && $_GET['iDisplayLength'] != '-1') {
      $sLimit = "LIMIT " 
                . intval($_GET['iDisplayStart']) 
                . ", " 
                . intval($_GET['iDisplayLength']);
    }

    // Execute query.
    $sQuery = "SELECT SQL_CALC_FOUND_ROWS " 
               . str_replace(" , ", " ", implode(", ", $aColumns)) . "
			         $sFrom $sWhere $sGroupBy $sOrder $sLimit";
    $rResult = mysqli_query($this->con, $sQuery);
		if (!$rResult) {
			$this->response = array(
				"header" => array("errcode" => 1, 
                          "errmsg" => "Database query failed.")
			);
			return;
		}

    // Response data.
    $this->response = array(
			"sEcho" => intval($_GET['sEcho']),
			"iTotalRecords" => 0,
			"iTotalDisplayRecords" => 0,
			"aaData" => array()
    );
    while ($aRow = mysqli_fetch_array($rResult)) {
			$row = array();
			for ($i=0; $i<count($aColumnsFetch); $i++) {
        $row[] = $aRow[$aColumnsFetch[$i]];
			}
			$this->response['aaData'][] = $row;
    }
  }

	// Server-side paging
	function getOptionListServer()
	{
		if ($this->userPrivilege == USER_PRIVILEGE::NURSE) {
      $this->getNurseOptionListServer();
      return;
    }

		// Fields for SQL query
		$aColumns = array(
			 'o.optionId', 'u.name', 'o.po', 'o.item', 'o.upc', 'o.price', 'o.date',
			 'o.qty', 'o.labeled', 'o.shipped', 'o.active', 'o.note', 'o.shipLog'
		 );

		// Fields for fetching query result. No prefix "o.".
		$aColumnsFetch = array(
			 'optionId', 'name', 'po', 'item', 'upc', 'price', 'date',
			 'qty', 'labeled', 'shipped', 'active', 'note', 'shipLog'
		);

		// Fields for sorting. The index must be the same as column index on the page.
		$aColumnsOrder = array(
			'u.name', 'o.po', 'o.item', 'upc', 'o.price', 'o.qty', 'o.labeled',
			'o.shipped', '(o.qty-o.shipped)', '(o.price*o.qty)', 'o.date'
		);
     
    $sIndexColumn = "optionId";
    $sTable = "options";
     
		// Paging
    $sLimit = "";
    if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
      $sLimit = "LIMIT " . intval($_GET['iDisplayStart']) . ", " . intval($_GET['iDisplayLength']);
    }
     
		// Ordering.
		// $_GET['iSortCol_'.$i] is the column indices from the page, which
		// must be in the same order as $aColumnsOrder defined above.
    $sOrder = "";
    if (isset($_GET['iSortCol_0'])) {
			$sOrder = "ORDER BY  ";
			for ($i=0; $i<intval($_GET['iSortingCols']); $i++) {
				if ($_GET['bSortable_' . intval($_GET['iSortCol_'.$i])] == "true") {
				  $sOrder .= $aColumnsOrder[intval($_GET['iSortCol_'.$i])] . " " . ($_GET['sSortDir_'.$i]==='asc'?'asc':'desc') . ", ";
				}
			}
			 
			// Remove the ending ", ".
			$sOrder = substr_replace($sOrder, "", -2);
			if ($sOrder == "ORDER BY") {
				$sOrder = "";
			}
    }
     
	  // Searching
		// $_GET['sSearch'] is from the search entry on the page. Must use aColumns here.
    $sWhere = "";
    if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
			$sWhere = "WHERE (";
			for ($i=0; $i<count($aColumns); $i++) {
				if (isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true") {
					$sWhere .= $aColumns[$i] . " LIKE '%" . mysqli_real_escape_string($this->con, $_GET['sSearch']) . "%' OR ";
				}
			}
			$sWhere = substr_replace($sWhere, "", -3);
			$sWhere .= ')';
    }
     
    // Individual column filtering
    for ($i=0; $i<count($aColumns); $i++) {
			if (isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '') {
				if ($sWhere == "") {
					$sWhere = "WHERE ";
				} else {
					$sWhere .= " AND ";
				}
				$sWhere .= $aColumns[$i] . " LIKE '%" . mysqli_real_escape_string($this->con, $_GET['sSearch_'.$i]) . "%' ";
			}
    }
     
		// Filter by doctor.
		$doctorId = isset($_GET["doctorId"])?$_GET["doctorId"]:NULL;
		// Doctors only see their own.
		if ($this->userPrivilege == USER_PRIVILEGE::DOCTOR) {
			$sWhere .= ($sWhere == "") ? " WHERE " : " AND ";
			$sWhere .= "o.doctorId=$this->userId";
		} else {
			if ($doctorId != NULL && $doctorId != 0) {
				$sWhere .= ($sWhere == "")?" WHERE ":" AND ";
				$sWhere .= "o.doctorId=$doctorId";
			}
		}

		//if (isset($_GET["startDate"])) {
		//	$startDate = date("Y-m-d", strtotime($_GET["startDate"]));
		//} else {
		//	$startDate = date("Y-m-d", strtotime("1970-01-01"));
		//}

		//if (isset($_GET["endDate"])) {
		//	$endDate = date("Y-m-d", strtotime($_GET["endDate"]));
		//	$latestDate = date("Y-m-d", strtotime("1970-01-01"));
		//	if ($endDate == $latestDate)	{
		//		$endDate = date("Y-m-d", strtotime("2030-01-01"));
		//	}
		//} else {
		//	$endDate = date("Y-m-d", strtotime("2030-01-01"));
		//}

		//if ($sWhere == "") {
		//	$sWhere = " WHERE ";
		//} else {
		//	$sWhere .= " AND ";
		//}

		//$sWhere .= "o.date BETWEEN \"$startDate\" AND \"$endDate\"";

		// Execute the query.
    $sQuery = "
			SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
			FROM $sTable o
			JOIN users u ON o.doctorId=u.userId
			$sWhere
			$sOrder
			$sLimit
    ";
    $rResult = mysqli_query($this->con, $sQuery);
		if (!$rResult) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}
     
    // Get number of records after filtering.
    $sQuery = " SELECT FOUND_ROWS() ";
    $rResultFilterTotal = mysqli_query($this->con, $sQuery);
		if (!$rResultFilterTotal) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}
    $aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
    $iFilteredTotal = $aResultFilterTotal[0];
     
    // Get total number of records by counting $sIndexColumn.
    $sQuery = " SELECT COUNT(" . $sIndexColumn . ") FROM $sTable ";
    $rResultTotal = mysqli_query($this->con, $sQuery);
		if (!$rResultTotal) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}
    $aResultTotal = mysqli_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];
     
    $this->response = array(
			"sEcho"                => intval($_GET['sEcho']),
			"iTotalRecords"        => $iTotal,
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData"               => array()
    );
     
		// Get main data for the table.
    while ($aRow = mysqli_fetch_array($rResult)) {
			$row = array();
			for ($i=0; $i<count($aColumnsFetch); $i++) {
				if ($aColumnsFetch[$i] == "version") {
					// Special format for 'version' column.
					$row[] = ($aRow[ $aColumnsFetch[$i] ]=="0")?'-':$aRow[$aColumnsFetch[$i]];
				} else if ($aColumns[$i] != ' ') {
					// General format.
					$row[] = $aRow[$aColumnsFetch[$i]];
				}
			}
			// Append to the response array.
			$this->response['aaData'][] = $row;
    }
	}
	
	function getStatementsServer()
	{
		$aColumns = array(
      'billingId', 'date', 'credit', 'debit', 'balance', 'note', 'attachment'
		);

		$aColumnsFetch = array(
      'billingId', 'date', 'credit', 'debit', 'balance', 'note', 'attachment'
		);

		$aColumnsOrder = array(
      'billingId', 'date', 'credit', 'debit', 'balance', 'note', 'attachment'
		);

    $sIndexColumn = "billingId";
    $sTable = "statements";

		// Paging
    $sLimit = "";
    if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
      $sLimit = "LIMIT " . intval($_GET['iDisplayStart']) . ", " . intval($_GET['iDisplayLength']);
    }
     
		// Ordering.
    $sOrder = "";
    if (isset($_GET['iSortCol_0'])) {
			$sOrder = "ORDER BY  ";
			for ($i=0; $i<intval($_GET['iSortingCols']); $i++) {
				if ($_GET['bSortable_' . intval($_GET['iSortCol_'.$i])] == "true") {
				  $sOrder .= $aColumnsOrder[intval($_GET['iSortCol_'.$i])] . " " . ($_GET['sSortDir_'.$i]==='asc'?'asc':'desc') . ", ";
				}
			}
			 
			$sOrder = substr_replace($sOrder, "", -2);
			if ($sOrder == "ORDER BY") {
				$sOrder = "";
			}
    }
     
	  // Searching
    $sWhere = "";
    if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
			$sWhere = "WHERE (";
			for ($i=0; $i<count($aColumns); $i++) {
				if (isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true") {
					$sWhere .= $aColumns[$i] . " LIKE '%" . mysqli_real_escape_string($this->con, $_GET['sSearch']) . "%' OR ";
				}
			}
			$sWhere = substr_replace($sWhere, "", -3);
			$sWhere .= ')';
    }
     
    // Individual column filtering
    for ($i=0; $i<count($aColumns); $i++) {
			if (isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '') {
				if ($sWhere == "") {
					$sWhere = "WHERE ";
				} else {
					$sWhere .= " AND ";
				}
				$sWhere .= $aColumns[$i] . " LIKE '%" . mysqli_real_escape_string($this->con, $_GET['sSearch_'.$i]) . "%' ";
			}
    }

		// Filter by nurse.
    if ($this->userPrivilege == USER_PRIVILEGE::ADMIN) {
      if (isset($_GET["doctorId"]) && $_GET["doctorId"] != 0) {
        $nurseId = $_GET["doctorId"];
        $sWhere .= ($sWhere == "")?" WHERE ":" AND ";
        $sWhere .= "userId=$nurseId";
      }
    } else {
      $nurseId = $this->userId;
      if ($nurseId != NULL && $nurseId != 0) {
        $sWhere .= ($sWhere == "")?" WHERE ":" AND ";
        $sWhere .= "userId=$nurseId";
      }
    }

		// Execute the query.
    $sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
			         FROM $sTable $sWhere $sOrder $sLimit";
    $rResult = mysqli_query($this->con, $sQuery);
		if (!$rResult) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}
	  
    // Get number of records after filtering.
    $sQuery = " SELECT FOUND_ROWS() ";
    $rResultFilterTotal = mysqli_query($this->con, $sQuery);
		if (!$rResultFilterTotal) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}
    $aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
    $iFilteredTotal = $aResultFilterTotal[0];
     
    // Get total number of records by counting $sIndexColumn.
    $sQuery = " SELECT COUNT(" . $sIndexColumn . ") FROM $sTable ";
    $rResultTotal = mysqli_query($this->con, $sQuery);
		if (!$rResultTotal) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}
    $aResultTotal = mysqli_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];
     
    $this->response = array(
			"sEcho"                => intval($_GET['sEcho']),
			"iTotalRecords"        => $iTotal,
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData"               => array()
    );
     
		// Get main data for the table.
    while ($aRow = mysqli_fetch_array($rResult)) {
			$row = array();
			for ($i=0; $i<count($aColumnsFetch); $i++) {
				if ($aColumnsFetch[$i] == "version") {
					// Special format for 'version' column.
					$row[] = ($aRow[ $aColumnsFetch[$i] ]=="0")?'-':$aRow[$aColumnsFetch[$i]];
				} else if ($aColumns[$i] != ' ') {
					// General format.
					$row[] = $aRow[$aColumnsFetch[$i]];
				}
			}
			// Append to the response array.
			$this->response['aaData'][] = $row;
    }
	}

	/**
	 *  Check database to see if a deal exists based on the tracking number.  
	 */
	function getClaimedDeal()
	{
		$track = $_POST["track"];
		$query = "SELECT d.dealId, d.optionId, o.doctorId, d.nurseId,
										 d.track, u1.name AS nurseName, u2.name AS doctorName,
										 o.po, o.item, d.qty, o.price, d.paid, d.note,
										 d.date, d.claimed, d.verified
								FROM deals d
								LEFT JOIN options o ON d.optionId=o.optionId
								LEFT JOIN users u1 ON d.nurseId=u1.userId
								LEFT JOIN users u2 ON o.doctorId=u2.userId
							 WHERE d.track LIKE '%$track%'";

		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		if ($row = mysqli_fetch_assoc($this->result)) {
			$this->response = array(
				"header"	    => array("errcode" => 0),
				"dealId" 			=> $row["dealId"],
				"optionId" 		=> $row["optionId"],
				"doctorId" 		=> $row["doctorId"],
				"nurseId" 		=> $row["nurseId"],
				"track" 			=> $row["track"],
				"nurseName" 	=> $row["nurseName"],
				"doctorName" 	=> $row["doctorName"],
				"po" 					=> $row["po"],
				"item" 				=> $row["item"],
				"qty" 				=> $row["qty"],
				"price" 			=> $row["price"],
				"paid" 				=> $row["paid"],
				"note" 				=> $row["note"],
				"date" 				=> $row["date"],
				"claimed" 		=> $row["claimed"],
				"verified" 		=> $row["verified"],
			);
		} else {
			$this->response = array(
				"header" => array(
					"errcode" => 1,
					"errmsg" => "Deal doesn't exist."
				)
			);
		}
	}

	function getDoctorList()
	{
		$query = "SELECT SQL_CACHE userId, name FROM users WHERE privilege=2";
		if (isset($_GET["mode"]) && $_GET["mode"] == "billing") {
		  $query .= " or privilege=3";
		}
		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		while ($row = mysqli_fetch_assoc($this->result)) {
			$doctor = array(
				"doctorId" => $row["userId"],
				"doctorName" => $row["name"],
			);
			array_push($this->response, $doctor);
		}
	}

  function getUserList()
  {
		$query = "SELECT SQL_CACHE userId, name FROM users";

		if (isset($_GET["mode"])) {
      $query .= " WHERE";
      if (strpos($_GET["mode"], 'd')) {
        $query .= " privilege=2 OR";
      }
      if (strpos($_GET["mode"], 'n')) {
        $query .= " privilege=3 OR";
      }
      if (strpos($_GET["mode"], 'a')) {
        $query .= " privilege=1 OR";
      }
			$query = substr_replace($query, "", -3);
    }

		$result = mysqli_query($this->con, "$query");
		if (!$result) {
			return;
		}

		while ($row = mysqli_fetch_assoc($result)) {
			$user = array(
				"id" => $row["userId"],
				"name" => $row["name"],
			);
			array_push($this->response, $user);
		}
  }

	function reportDoctor()
	{
		$doctorId	= $_POST["doctorId"];

		$query = "SELECT SUM(labeled) AS labeled, SUM(shipped) AS shipped,
										 SUM(qty) AS qty, SUM(0.03*price*qty) AS fee 
								FROM options";

		if ($doctorId != 0) {
			$query .= " WHERE doctorId=$doctorId";
		}

		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		if ($row = mysqli_fetch_assoc($this->result)) {
			// Only admin can see this
			if ($this->userPrivilege == USER_PRIVILEGE::ADMIN) {
				$this->response = array(
					"qty" 		=> $row["qty"],
					"labeled"	=> $row["labeled"],
					"shipped"	=> $row["shipped"],
					"instock"	=> $row["qty"] - $row["shipped"],
					"fee"			=> $row["fee"],
				);
			} else {
				$this->response = array(
					"qty" 		=> 0,
					"labeled"	=> 0,
					"shipped"	=> 0,
					"instock"	=> 0,
					"fee"			=> 0,
				);
			}
		}
	}

	function reportDoctorV2()
	{
		$doctorId   = $_GET["doctorId"];
		$startDate  = date("Y-m-d", strtotime($_GET["startDate"]));
		$endDate    = date("Y-m-d", strtotime($_GET["endDate"]));
		$latestDate = date("Y-m-d", strtotime("1970-01-01"));
		if ($endDate <= $latestDate) {
			$endDate = date("Y-m-d", strtotime("2030-01-01"));
		}

		$query = "SELECT o.po, o.item, o.price, SUM(d.qty) AS qty 
			        FROM deals d LEFT JOIN options o ON d.optionId=o.optionId 
							WHERE d.date BETWEEN '$startDate' AND '$endDate'";
		if ($doctorId != 0) {
			$query .= " AND o.doctorId=$doctorId";
		}
		$query .= " GROUP BY o.po";
		fwrite($this->log, $query . "\r\n");

		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		$output = "<table>";
		$output .= "<tr><td>PO#</td><td>Item</td><td>Price</td><td>Qty</td></tr>";
		$totalQty = 0;
		while ($row = mysqli_fetch_assoc($this->result)) {
			$po    = $row["po"];
			$item  = $row["item"];
			$price = $row["price"];
			$qty   = $row["qty"];

			//$output .= $po . " \"" . $item . "\" " . $price . " " . $qty . "<br/>";
			$output .= "<tr>";
			$output .= "<td>" . $po . "</td>";
			$output .= "<td>" . $item . "</td>";
		  $output .= "<td>" . $price . "</td>";
		  $output .= "<td>"	. $qty . "</td>";
			$output .= "</tr>";

			$totalQty += $qty;
		}
		$output .= "</table>";

		// header("Content-Type: application/vnd.ms-execl");
		// header("Content-Type: application/vnd.ms-excel; charset=utf-8");
		// header("Content-Disposition:filename=" . $startDate . "-" . $endDate . ".xls");
		// header("Pragma: no-cache");
		// header("Expires: 0");

		echo "A total of " . $totalQty . " items are found between " . $startDate . " and " . $endDate . "<br/><br/>";
		echo $output;
	}

	function login()
	{
		$username = $_POST["username"];
		$password = $_POST["password"];

		$query = "SELECT userId, name, privilege, permission FROM users
							WHERE username='$username' AND password='$password'";

		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		if (0 == mysqli_num_rows($this->result)) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		$row = mysqli_fetch_assoc($this->result);
		$this->response = array(
			"header"	   => array("errcode" => 0),
			"userId" 		 => $row["userId"],
			"name" 			 => $row["name"],
			"privilege"  => $row["privilege"],
			"permission" => $row["permission"],
		);

		// Cookie for server
		$sProfile = new stdClass();
		$sProfile->userId 		= $row["userId"];
		$sProfile->name 			= $row["name"];
		$sProfile->privilege  = $row["privilege"];
		$sProfile->permission = $row["permission"];
		$sProfileJson         = base64_encode(json_encode($sProfile));
		setcookie("sUserProfile", $sProfileJson, time()+(86400*7), "/");

		// Cookie for client
		$cProfile = new stdClass();
		$cProfile->userId 		= intval($row["userId"]);
		$cProfile->name 			= $row["name"];
		$cProfile->privilege  = intval($row["privilege"]);
		$cProfile->permission = intval($row["permission"]);
		$cProfileJson         = json_encode($cProfile);
		setcookie("userProfile", $cProfileJson, time()+(86400*7), "/");
	}

	function addDeal()
	{
		// Only admin can add deals
		if ($this->userPrivilege != USER_PRIVILEGE::ADMIN) {
			$this->response = array(
				"header" => array(
					"errcode"  => 1,
					"errmsg" => "You don't have privilege to do this."
				)
			);
			return;
		}

		$optionId = $_POST["optionId"];

		// Validate option
		$query = "SELECT active FROM options WHERE optionId='$optionId'";
		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result || 0 == mysqli_num_rows($this->result)) {
			$this->response = array(
				"header" => array(
					"errcode"  => 1,
					"errmsg" => "Option doesn't exist."
				)
			);
			return;
		}

		$row = mysqli_fetch_assoc($this->result); 

		// Block closed option
		if ($row['active'] == 0) {
			$this->response = array(
				"header" => array(
					"errcode" => 1,
					"errmsg" => "Option has been closed."
				)
			);
			return;
		}

		$track 	= $_POST["track"];
		$qty 		= $_POST["qty"];
		$date		= date("Y-m-d");
		$dealId = uniqid("DL", true);
		// $dealId = md5(uniqid("DL"));
		$query = "INSERT INTO deals (dealId, optionId, nurseId, track, qty, date) 
							VALUES ('$dealId', '$optionId', $this->userId, '$track', $qty, '$date')";

		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		$this->response = array(
			"header" => array(
				"errcode" => 0,
			)
		);
	}

	function addOption()
	{
		if ($this->userPrivilege == USER_PRIVILEGE::NURSE) {
			$this->response = array(
				"header" => array(
					"errcode" => 1,
					"errmsg" => "You don't have privilege to do this."
				)
			);
			return;
		}

		$doctorId = $_POST["doctorId"];
		$po 		  = $_POST["po"];
		$item 	  = $_POST["item"];
		$upc  	  = $_POST["upc"];
		$price 	  = $_POST["price"];
		$date 	  = date("Y-m-d");
		$optionId = uniqid("OP", true);
		$note	    = addslashes($_POST["note"]);

		if ($doctorId == 0) {
			$query = "INSERT INTO options (optionId, doctorId, po, item, upc, price, date, note) 
								VALUES ('$optionId', $this->userId, '$po', '$item', '$upc', $price, '$date', '$note')";
		} else {
			$query = "INSERT INTO options (optionId, doctorId, po, item, upc, price, date, note) 
								VALUES ('$optionId', $doctorId, '$po', '$item', '$upc', $price, '$date', '$note')";
		}
		fwrite($this->log, $query . "\r\n");

		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		$this->response = array(
			"header" => array(
				"errcode" => 0,
			)
		);
	}
	
	function convertUpcToOptionId($upc, &$optionId)
	{
	  // Search in options owned by "pan"
    $query = "select optionId, active from options 
              where upc='$upc' and doctorId=8 
              order by date desc limit 1";
    $this->result = mysqli_query($this->con, "$query");
    if (!$this->result || 0 == mysqli_num_rows($this->result)) {
      return 1;
    }
    $row = mysqli_fetch_assoc($this->result); 
    if ($row["active"] == 0) {
      return 1; 
    }
    $optionId = $row["optionId"];
    return 0;
	}
	
	function convertPoToOptionId($po, &$optionId)
	{
    $query = "SELECT optionId, active FROM options WHERE po='$po'";
    $this->result = mysqli_query($this->con, "$query");
    if (!$this->result || 0 == mysqli_num_rows($this->result)) {
      return 1;
    }
    $row = mysqli_fetch_assoc($this->result); 
    if ($row['active'] == 0) {
      return 1;
    }
    $optionId = $row['optionId'];
    return 0;
	}

	function uploadDeals()
	{
		// Only admin is allowed to call this API
		if ($this->userPrivilege != USER_PRIVILEGE::ADMIN) {
			$this->response = array(
				"header" => array(
					"errcode" => 1,
					"errmsg" => "You are not privileged to do this."
				)
			);
			return;
		}

		$logDir = $_SERVER["DOCUMENT_ROOT"] . "/logs";
		if (!file_exists($logDir)) {
			mkdir($logDir);
		}    

		// Fail to open uploaded file
		$file = $logDir . "/" . $_FILES["deals"]["name"];
		copy($_FILES["deals"]["tmp_name"], $file);
		if (!($fp = fopen($file, "r"))) {
			$this->response = array(
				"header" => array(
					"errcode" => 1,
					"errmsg" => "Unable to open the uploaded file."
				)
			);
			return;
		}

		// Fail to open report file
		$report = $logDir . "/upload.log";
		if (!($rp = fopen($report, "w"))) {
			$this->response = array(
				"header" => array(
					"errcode" => 1,
					"errmsg" => "Unable to open the report file."
				)
			);
			fclose($fp);
			return;
		}

		// $uploadInfo = array();
		$uploadInfo = "";

		$nurseId = $this->userId;
		$date 	 = date("Y-m-d");
		$count   = 0;
		$total   = 0;
		fwrite($rp, date("Y-m-d H:i:s") . "\r\n");
		while ($line = fgets($fp)) {
			$total++;
			//$fields = explode("\t", $line);
			$fields = preg_split('/( |\t|,)+/', $line);

			if (count($fields) != 3) {
				$errMsg = $line;
				fwrite($rp, $errMsg);
				$uploadInfo .= $errMsg . "<br/>";
				//array_push($uploadInfo, $errMsg . "\r\n");
				continue;
			}

			// get option Id.
			$optionIdentifier = $fields[1]; // po or upc
			$optionId = "";
			if ($_POST["format"] == "upc") {
        $rcode = $this->convertUpcToOptionId($optionIdentifier, $optionId);
			} else {
        $rcode = $this->convertPoToOptionId($optionIdentifier, $optionId);
			}
			if ($rcode) {
				$errMsg = $line;
				fwrite($rp, $errMsg);
				$uploadInfo .= $errMsg . "<br/>";
				continue;
			}

			// Add to deal table
			$track = $fields[0];
			$qty = $fields[2];
			$dealId = uniqid("DL", true);
			$query = "INSERT INTO deals (dealId, optionId, nurseId, track, qty, date) 
								VALUES ('$dealId', '$optionId', $nurseId, '$track', $qty, '$date')";
			$this->result = mysqli_query($this->con, "$query");
			if (!$this->result) {
				$errMsg = $line;
				fwrite($rp, $errMsg);
				$uploadInfo .= $errMsg . "<br/>";
				// array_push($uploadInfo, $errMsg . "\r\n");
				continue;
			}

			fwrite($rp, "Valid, deal uploaded - " . $line);
			$count++;
		}

		$msg = "<br/>" . $count . " out of " . $total . " deals have been uploaded.";
		fwrite($rp, $msg);
		$uploadInfo .= $msg;
		// array_push($uploadInfo, $msg);

		// $errno = ($count==$total) ? 0 : 999;
		$this->response = array(
			"header" => array(
				"errcode" => 0,
			),
			"uploadInfo" => $uploadInfo,
		);

		fclose($fp);
		fclose($rp);
	}

	function updateDeal()
	{
		$dealId		= $_POST["dealId"];
		$optionId	= $_POST["optionId"];
		$po      	= $_POST["po"];
		$track 		= $_POST["track"];
		$price 		= $_POST["price"];
		$qty 			= $_POST["qty"];
		$paid 		= $_POST["paid"];
		$note 		= addslashes($_POST["note"]);
		$date 		= $_POST["date1"];
		$claimed 	= $_POST["claimed"];
		$nurseId  = $_POST["nurseId"];
		// $verified = $_POST["verified"];

		// Convert to MySQL date format
		// $date = mysql_real_escape_string($date);
		// $date = date("Y-m-d", strtotime(str_replace("-", "/", $date)));

		// This part is for updating the PO#.
		$query = "SELECT optionId FROM options WHERE po='$po'";

		$this->result	= mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		// No change will be made if the PO# is invalid.
		if (0 == mysqli_num_rows($this->result)) {
			$this->response = array(
				"header" => array(
					"errcode" => 1,
					"errmsg" => "Invalid PO number." 
				)
			);
			return;
		}

		if ($row = mysqli_fetch_assoc($this->result)) {
			$newOptionId = $row["optionId"];
		}

		if (!$date) {
			$date = date("Y-m-d");
		}

		$query = "UPDATE deals SET track='$track', qty=$qty, paid=$paid, note='$note',
										 date='$date', claimed=$claimed, optionId='$newOptionId', nurseId=$nurseId 
							 WHERE dealId='$dealId'";

		//fwrite($this->log, $query . "\r\n");

		$this->result	= mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		$this->response = array(
			"header" => array(
				"errcode" => 0,
			)
		);
	}

	function updateOption()
	{
		$optionId = $_POST["optionId"];
		$po 			= $_POST["po"];
		$item 		= $_POST["item"];
		$upc  		= $_POST["upc"];
		$price 		= $_POST["price"];
		$labeled 	= $_POST["labeled"];
		$shipped 	= $_POST["shipped"];
		$active 	= $_POST["active"];
		$note 		= addslashes($_POST["note"]);


		// $query = "UPDATE options SET po='$po', item='$item', price=$price, labeled=labeled+$labeled, 
		// shipped=shipped+$shipped, active=$active, note='$note',
		// shipLog=CONCAT(IFNULL(shipLog, ''), '$shipLog')";

		$query = "UPDATE options SET po='$po', item='$item', upc='$upc', price=$price, active=$active, note='$note'";

		if ($shipped != 0) {
			$shipLog = date("Y-m-d")." : " . $shipped . " shipped.\r\n";
			$query .= ", shipped=shipped+$shipped,  shipLog=CONCAT(IFNULL(shipLog, ''), '$shipLog')";
		}

		if ($labeled != 0) {
			$shipLog = date("Y-m-d")." : " . $labeled . " labeled.\r\n";
			$query .= ", labeled=labeled+$labeled, shipLog=CONCAT(IFNULL(shipLog, ''), '$shipLog')";
		}

		$query .= " WHERE optionId='$optionId'";

		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		$this->response = array(
			"header" => array(
				"errcode" => 0,
			)
		);
	}

	function updateClaimedDeal()
	{
		$dealId	= $_POST["dealId"];
		$claimed = $_POST["claimed"];
		$date = date("Y-m-d");

		$query = "update deals set nurseId=$this->userId, claimed=1, claimDate='$date' 
		          where dealId='$dealId' and claimed=0";
		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		$this->response = array(
			"header" => array("errcode" => 0)
		);
	}

	function updateBulkMode()
	{
		$itemList	= json_decode($_POST['itemList']);

		for ($i=0; $i<count($itemList); $i++) {
			$dealId = $itemList[$i]->dealId;
			$claimed = $itemList[$i]->claimed;
			$paid = $itemList[$i]->paid;

			$query = "UPDATE deals SET claimed=$claimed, paid=$paid WHERE dealId='$dealId'";

			$this->result = mysqli_query($this->con, "$query");
			if (!$this->result) {
				$this->response = array(
					"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
				);
				return;
			}
		}

		$this->response = array(
			"header" => array(
				"errcode" => 0,
			)
		);
	}

	function deleteDeal()
	{
		// Only admin can do this
		if ($this->userPrivilege != USER_PRIVILEGE::ADMIN) {
			$this->response = array(
				"header" => array(
					"errcode" => 0,
				)
			);
			return;
		}

		$dealId	= $_POST["dealId"];
		$query = "DELETE FROM deals WHERE dealId='$dealId'";

		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		$this->response = array(
			"header" => array(
				"errcode" => 0,
			)
		);
	}

	function deleteOption()
	{
		$optionId	= $_POST["optionId"];

		$query = "DELETE FROM options WHERE optionId='$optionId'";

		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		$this->response = array(
			"header" => array(
				"errcode" => 0,
			)
		);
	}

	function logout()
	{
		setcookie("userProfile", "", time()-3600, "/");
		setcookie("sUserProfile", "", time()-3600, "/");
		$this->response = array(
			"header" => array(
				"errcode" => 0,
			)
		);
	}

	function createTrackIdPdf()
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/thirdParty/tcpdf/tcpdf.php");

		$po = $_GET["po"];

		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
		
		// Document info
		$pdf->SetCreator('PANANDHUANG');
		$pdf->SetAuthor('PANANDHUANG');
		$pdf->SetTitle('PANANDHUANG TRADING SERVICES');
		$pdf->SetSubject('TCPDF Tutorial');
		$pdf->SetKeywords('TCPDF, PDF, PHP');
		
		// Header and footer info
		$currDate = date("Y-m-d H:i:s");
		$pdf->SetHeaderData('', 0, 'PANANDHUANG TRADING SERVICES', 'Deal Tracking List of ' . $po . '      ' . $currDate, array(0, 0, 0), array(0, 0, 0));
		$pdf->setFooterData(array(0, 0, 0), array(0, 0, 0));
		
		// Header and footer font
		$pdf->setHeaderFont(Array('helvetica', '', '14'));
		$pdf->setFooterFont(Array('helvetica', '', '10'));
		
		$pdf->SetDefaultMonospacedFont('courier');
		
		// Spacing
		$pdf->SetMargins(10, 30, 10);
		$pdf->SetHeaderMargin(10);
		$pdf->SetFooterMargin(10);
		
		// Paging
		$pdf->SetAutoPageBreak(TRUE, 25);
		
		// Image scale factor
		$pdf->setImageScale(1.25);
		
		// Default font subsetting mode
		$pdf->setFontSubsetting(true);
		
		// Font
		$pdf->SetFont('helvetica', '', 12);
		
		$pdf->AddPage();
		
		// Generate PDF body
		$optionId = $_GET["optionId"];
		$query = "SELECT track, qty FROM deals WHERE optionId='$optionId'";
		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		$pdfBody = "";
		$count = 0;
		while ($row = mysqli_fetch_assoc($this->result)) {
			$pdfBody .= $row["track"] . "\t" . $row["qty"] . "\r\n";
			++$count;
		}
		$pdfBody .= "\nA total of " . $count . " trackings are found.";

		$pdf->Write(0, $pdfBody,'', 0, 'L', true, 0, false, false, 0);
		
		// Output PDF
		$pdf->Output('t.pdf', 'I');
	}

	function createTrackIdHtml()
	{
		$optionId = $_GET["optionId"];
		$query = "SELECT track, qty FROM deals WHERE optionId='$optionId'";
		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		// header("Content-Type: application/vnd.ms-execl");
		// header("Content-Type: application/vnd.ms-excel; charset=utf-8");
		// header("Content-Disposition:filename=" . $_GET["po"] . ".xls");
		// header("Pragma: no-cache");
		// header("Expires: 0");
		
		echo "<b>" . mysqli_num_rows($this->result) . "</b> trackings found for <b>" . 
			   $_GET["po"] . "</b> (" . date("Y-m-d H:i:s") . ")<br/><br/>";

    echo "<table>";
		while ($row = mysqli_fetch_assoc($this->result)) {
			echo "<tr><td>" . $row["track"] . "</td><td>" . $row["qty"] . "</td></tr>";
		}
    echo "</table>";
	}

	/**
	 *  Claim deals in batches.
	 */
	function verifyDeals()
	{
		require_once("../thirdParty/PHPMailer-master/class.phpmailer.php");
		require_once("../thirdParty/PHPMailer-master/class.smtp.php");

		// check log dir
		$logDir = $_SERVER["DOCUMENT_ROOT"] . "/logs";
		if (!file_exists($logDir)) {
			mkdir($logDir);
		}    

		// copy and open the uploaded file
		$file = $logDir . "/" . $_FILES["verify-deal"]["name"];
		copy($_FILES["verify-deal"]["tmp_name"], $file);
		if (!($fp = fopen($file, "r"))) {
			$this->response = array(
				"header" => array(
					"errcode" => 1,
					"errmsg" => "Unable to open the uploaded file."
				)
			);
			return;
		}

		$numTotal = 0;
		$numVerified = 0;
		$dealList = "dealList="; // for displaying claimed deals when claim is done
		$verifiedList = "";
		$notVerifiedList = "";

		// read the uploaded file
		while ($line = fgets($fp)) {
			++$numTotal;

			$fields = preg_split('/( |\t|,)+/', $line);
			$tracking = $fields[0];
			$po = $fields[1];
			$qty = intval($fields[2]);

			// For Admin, there must be 4 fields on each line. For doctor/nurse, 
			// there should be 3 fields. The nurse will be read from user settings.
      if ($this->userPrivilege == USER_PRIVILEGE::ADMIN) {
        if (4 != count($fields)) {
          $notVerifiedList .= $line . ": wrong format.<br/>";
          continue;
        }
        // Load nurseId based on the nurse name.
        $nurse = preg_replace("/\r\n/", "", $fields[3]);
        $query = "SELECT userId FROM users WHERE name='$nurse'";
        $this->result = mysqli_query($this->con, "$query");
        if (!$this->result) {
          $notVerifiedList .= $tracking . "<br/>";
          continue;
        }
        $row = mysqli_fetch_assoc($this->result); 
        $userId = $row["userId"];
      } else {
        if (3 != count($fields)) {
          $notVerifiedList .= $line . ": wrong format.<br/>";
          continue;
        }
        $userId = $this->userId;
      }

			// tracking from nurses may be shorter. use wild-type here
			$query = "SELECT d.dealId, o.po, d.nurseId, d.qty, d.claimed 
				        FROM deals d LEFT JOIN options o ON d.optionId=o.optionId 
				        WHERE d.track LIKE '%$tracking%' AND o.po='$po' AND d.qty=$qty";
			$this->result = mysqli_query($this->con, "$query");

			// Deal not found
			if (!$this->result) {
				$notVerifiedList .= $tracking . "<br/>";
				continue;
			}

			// Multiple deals may be found due to vague search on tracking.
			if (1 != mysqli_num_rows($this->result)) {
				$notVerifiedList .= $tracking . "<br/>";
				continue;
			}

			$row = mysqli_fetch_assoc($this->result); 
			$dealId = $row["dealId"];
			$claimed = $row["claimed"];
			$nurseId = $row["nurseId"];

			// Claimed deal is not allowed to be claimed again.
			if ($claimed) {
				$verifiedList .= $tracking . "<br/>";
				continue;
			}

			++$numVerified;
			$dealList .= $dealId . "-";

			// Update claim status.
			$date = date("Y-m-d");
			$query = "UPDATE deals SET nurseId=$userId, claimed=1, claimDate='$date' WHERE dealId='$dealId'";
			$this->result = mysqli_query($this->con, "$query");
			if (!$this->result) {
				$notVerifiedList .= $tracking . "<br/>";
				continue;
			}
		}

		$mailBody  = $numVerified . " out of " . $numTotal . " deals verified.<br/><br/>";
		$mailBody .= "The following deals have already been claimed and can't be claimed again:<br/>";
		$mailBody .= $verifiedList . "<br/>";
		$mailBody .= "The following deals can't be claimed due to errors:<br/>";
		$mailBody .= $notVerifiedList . "<br/><br/>";

		$fromAddr = "hzhan_3@yahoo.com";
		$toAddr = "Panhm.orders@gmail.com";
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = 'ssl'; // or tls
		$mail->Host = "smtp.mail.yahoo.com";
		$mail->Port = 465; // or 587
		$mail->Username = $fromAddr;
		$mail->Password = "xxx";
		$mail->SetFrom($fromAddr, "Murloc");
		$mail->AddReplyTo($fromAddr, "Murloc");
		$mail->Subject = "[SERVICE] Deal verfication report";
		$mail->MsgHTML($mailBody);
		$mail->ClearAllRecipients();
		$mail->AddAddress($toAddr, "Huan");
		$mail->Send();

		$this->response = array(
			"header" => array(
				"errcode" => 1,
				"errmsg" => $numVerified . " out of " . $numTotal . " deals verified (as shown on the screen). An email has been sent to " . $toAddr,
			),
			"dealList" => $dealList,
		);

		fclose($fp);
		fclose($rp);
	}

	function plot()
	{
		// $query = "SELECT d.date, SUM(d.qty) AS qty FROM deals d left JOIN options o ON d.optionId=o.optionId GROUP BY WEEK(d.date) ORDER BY d.date";
		if ($this->userPrivilege == USER_PRIVILEGE::ADMIN) {
      $query = "select date_add('1900-01-01',interval floor(datediff(d.date,'1900-01-01')/7)*7 day) as date, sum(d.qty) as qty 
                  from deals d left JOIN options o ON d.optionId=o.optionId 
                  group by floor(datediff(d.date,'1900-01-01')/7)";
		} else if ($this->userPrivilege == USER_PRIVILEGE::DOCTOR) {
      $query = "select date_add('1900-01-01',interval floor(datediff(d.date,'1900-01-01')/7)*7 day) as date, sum(d.qty) as qty 
                  from deals d left JOIN options o ON d.optionId=o.optionId 
                  where o.doctorId=$this->userId group by floor(datediff(d.date,'1900-01-01')/7)";
		} else {
      $query = "select date_add('1900-01-01',interval floor(datediff(d.date,'1900-01-01')/7)*7 day) as date, sum(d.qty) as qty 
                  from deals d left JOIN options o ON d.optionId=o.optionId 
                  where d.nurseId=$this->userId group by floor(datediff(d.date,'1900-01-01')/7)";
		}
		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			return;
		}

		while ($row = mysqli_fetch_assoc($this->result)) {
			$dateArr = explode("-", $row["date"]);
			$year    = intval($dateArr[0]);
			$month   = intval($dateArr[1]);
			$day     = intval($dateArr[2]);
			$qty     = intval($row["qty"]);
			$this->response[] = array(
				"year"  => $year,
				"month" => $month,
				"day"   => $day,
				"qty"   => $qty,
			);
		}
	}

	function getOrders()
	{
		$query = "SELECT orderId, orderDate, tracking, shipDate, cancelled, cancelDate, buyPrice, sellPrice, cashBack, item 
			        FROM orders WHERE nurseId=" . $this->userId;

		$this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			return;
		}

		while ($row = mysqli_fetch_assoc($this->result)) {
			$order = array(
				$row["orderId"],
				$row["orderDate"],
				$row["tracking"],
				$row["shipDate"],
				$row["cancelled"],
				$row["cancelDate"],
				$row["buyPrice"],
				$row["sellPrice"],
				$row["cashBack"],
				$row["item"],
			);

			array_push($this->response, $order);
		}
	}

	function scanOrders()
	{
		$hostname = "{imap.gmail.com:993/imap/ssl}INBOX";
		$username = $_POST["email"];
		$password = $_POST["password"];

		$inbox = imap_open($hostname, $username, $password) 
						 or die("Cannot connect to Gmail: " . imap_last_error());

		// $emails = imap_search($inbox, "ALL");
		// $emails = imap_search($inbox, 'UID 5:7', SE_UID);
		// $yesterday = date("Y-m-d", strtotime("-2 days"));
		// $emails = imap_search($inbox, 'SINCE "' . $yesterday . '"');
		$emails = imap_search($inbox, "UNFLAGGED");

		if ($emails) {
			rsort($emails);
			
			foreach ($emails AS $email) {
				$status = imap_setflag_full($inbox, $email, "\\SEEN \\FLAGGED");

				$overview = imap_fetch_overview($inbox, $email, 0);
				$message  = imap_fetchbody($inbox, $email, 2);
				$message  = quoted_printable_decode($message);

				$subject = $overview[0]->subject;
				$date    = $overview[0]->date;
				$date    = date("Y-m-d", strtotime($date));
				// $sender  = $overview[0]->from;
				// $uid     = $overview[0]->uid;
				// $seen    = $overview[0]->seen;

				// Confirmation
				$pattern = "/^(?=.*(BBY[-0-9]+))(?=.*received).*/i";
				$rcode = preg_match_all($pattern, $subject, $matches);
				if ($rcode > 0) {
					$orderId = $matches[1][0];
					$orderId = str_replace(" ", "", $orderId);

					// Scrape item
					$patternItem = '/>([^<]+)<\/a><br>.*?<strong>Model/s';
					$rcode = preg_match_all($patternItem, $message, $matchesItem);
					$item = $matchesItem[1][0];

					$query = "SELECT orderId FROM orders WHERE orderId='$orderId'";
					$this->result = mysqli_query($this->con, "$query");

					// If already exists, update orderDate/
					if (mysqli_num_rows($this->result) > 0) {
						$query = "UPDATE orders SET orderDate='$date', item='$item' WHERE orderId='$orderId'";
						mysqli_query($this->con, "$query");
						continue;
					}

					// Otherwise inserts the order.
					$query = "INSERT INTO orders (orderId, orderDate, item, nurseId) VALUES ('$orderId', '$date', '$item', $this->userId)";
					mysqli_query($this->con, "$query");

					continue;
				}

				// Confirmation
				$pattern = "/^(?=.*(#\s?[-0-9]+))(?=.*received).*/i";
				$rcode = preg_match_all($pattern, $subject, $matches);
				if ($rcode > 0) {
					$orderId = $matches[1][0];
					$orderId = str_replace(" ", "", $orderId);

					// Scrape item
					$patternItem = '/>([^<]+)<\/a><br>.*?<strong>Model/s';
					$rcode = preg_match_all($patternItem, $message, $matchesItem);
					$item = $matchesItem[1][0];

					$query = "SELECT orderId FROM orders WHERE orderId='$orderId'";
					$this->result = mysqli_query($this->con, "$query");

					// If already exists, update orderDate/
					if (mysqli_num_rows($this->result) > 0) {
						$query = "UPDATE orders SET orderDate='$date', item='$item' WHERE orderId='$orderId'";
						mysqli_query($this->con, "$query");
						continue;
					}

					// Otherwise inserts the order.
					$query = "INSERT INTO orders (orderId, orderDate, item, nurseId) VALUES ('$orderId', '$date', '$item', $this->userId)";
					mysqli_query($this->con, "$query");

					continue;
				}

				// Shipping
				$pattern = "/^(?=.*(BBY[-0-9]+))(?=.*shipped).*/i";
				$rcode = preg_match_all($pattern, $subject, $matches);
				if ($rcode > 0) {
					$orderId = $matches[1][0];
					$orderId = str_replace(" ", "", $orderId);

					// Scrape tracking
					// $patternTracking = '/004691;?">(1ZV[^<]+)/';
					$patternTracking = '/TRACKING #.*?004691[^>]+>([^<]+)</s';
					$rcode = preg_match_all($patternTracking, $message, $matchesTracking);
					$tracking = $matchesTracking[1][0];
					
					$query = "SELECT orderId FROM orders WHERE orderId='$orderId'";
					$this->result = mysqli_query($this->con, "$query");

					// If not exists, insert the order with tracking info.
					if (mysqli_num_rows($this->result) == 0) {
						$query = "INSERT INTO orders (orderId, orderDate, tracking, shipDate, nurseId) 
											VALUES ('$orderId', '$date', '$tracking', '$date', $this->userId)";
						mysqli_query($this->con, "$query");
						continue;
					}

					// Otherwise updates the tracking info.
					$query = "UPDATE orders SET tracking='$tracking', shipDate='$date' WHERE orderId='$orderId'";
					mysqli_query($this->con, "$query");

					continue;
				}

				// Shipping OMS
				$pattern = "/^(?=.*(#\s?[-0-9]+))(?=.*shipped).*/i";
				$rcode = preg_match_all($pattern, $subject, $matches);
				if ($rcode > 0) {
					$orderId = $matches[1][0];
					$orderId = str_replace(" ", "", $orderId);

					// Scrape tracking
					// $patternTracking = '/004691;?">(1ZV[^<]+)/';
					$patternTracking = '/TRACKING #.*?004691[^>]+>([^<]+)</s';
					$rcode = preg_match_all($patternTracking, $message, $matchesTracking);
					$tracking = $matchesTracking[1][0];
					
					$query = "SELECT orderId FROM orders WHERE orderId='$orderId'";
					$this->result = mysqli_query($this->con, "$query");

					// If not exists, insert the order with tracking info.
					if (mysqli_num_rows($this->result) == 0) {
						$query = "INSERT INTO orders (orderId, orderDate, tracking, shipDate, nurseId) 
											VALUES ('$orderId', '$date', '$tracking', '$date', $this->userId)";
						mysqli_query($this->con, "$query");
						continue;
					}

					// Otherwise updates the tracking info.
					$query = "UPDATE orders SET tracking='$tracking', shipDate='$date' WHERE orderId='$orderId'";
					mysqli_query($this->con, "$query");

					continue;
				}

				// Cancel
				$pattern = "/^(?=.*(BBY[-0-9]+))(?=.*canceled).*/i";
				$rcode = preg_match_all($pattern, $subject, $matches);
				if ($rcode > 0) {
					$orderId = $matches[1][0];
					$orderId = str_replace(" ", "", $orderId);
					
					$query = "SELECT orderId FROM orders WHERE orderId='$orderId'";
					$this->result = mysqli_query($this->con, "$query");

					// If not exists, insert the order with the cancel info.
					if (mysqli_num_rows($this->result) == 0) {
						$query = "INSERT INTO orders (orderId, orderDate, cancelled, cancelDate, nurseId) 
											VALUES ('$orderId', '$date', '1', '$date', $this->userId)";
						mysqli_query($this->con, "$query");
						continue;
					}

					// Otherwise updates the cancel info.
					$query = "UPDATE orders SET cancelled='1', cancelDate='$date' WHERE orderId='$orderId'";
					mysqli_query($this->con, "$query");

					continue;
				}
			}
		} 

		imap_close($inbox);

		$this->response = "";
	}
	
	function makePayment()
	{
		$nurseId = $_POST["nurseId"];
		$amount = $_POST["amount"];
		$note = $_POST["note"];
		$date	= date("Y-m-d");

		// get balance
    $query = "select balance from statements where userId=$nurseId order by billingId desc limit 1";
    $this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		$balance = 0;
		if ($row = mysqli_fetch_assoc($this->result)) {
		  $balance = $row["balance"];
		}
		$balance -= $amount; 

    $query = "insert into statements (userId, date, debit, balance, note) values ($nurseId, '$date', $amount, $balance, '$note')";
    $this->result = mysqli_query($this->con, "$query");
		if (!$this->result) {
			$this->response = array(
				"header" => array("errcode" => 1, "errmsg" => "Database query failed.")
			);
			return;
		}

		$this->response = array(
			"header" => array("errcode" => 0)
		);
	}

} //Close class RequestProcessor

?>
