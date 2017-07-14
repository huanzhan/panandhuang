<?php
	$link = mysqli_connect( "127.0.0.1", "root", "porter98" )
	        or die( "Cannot connect to MySQL: " . mysql_error() );

	$select = mysqli_select_db( $link, "trackdb" )
	          or die( "Cannot connect to the database: " . mysql_error() );

  // $sDate = date( "Y-m-d", strtotime("2016-04-12") );
  // $eDate = date( "Y-m-d", strtotime("2016-04-16") );

	// $sDate = $_GET["sDate"];
	// $eDate = $_GET["eDate"];

	$query = "SELECT orderId, orderDate, tracking, shipDate, cancelled, cancelDate, buyPrice, sellPrice, cashBack, item FROM orders";

	$result = mysqli_query( $link, "$query" );
	if ( !$result ) {
		return;
	}

	// echo "<table border=1><tr>";
	// echo "<td><b>Order Id</b></td>";
	// echo "<td><b>Order Date</b></td>";
	// echo "<td><b>Tracking #</b></td>";
	// echo "<td><b>Ship Date</b></td>";
	// echo "<td><b>Canceled</b></td>";
	// echo "<td><b>Cancel Date</b></td>";
	// echo "<td><b>Item</b></td>";
	// echo "</tr>";

	$response = array();
	while ( $row = mysqli_fetch_assoc($result) )
	{
		// echo "<tr>";
	  // echo "<td>" . $row["orderId"] . "</td>";
	  // echo "<td>" . $row["orderDate"] . "</td>";
	  // echo "<td>" . $row["tracking"] . "</td>";
	  // echo "<td>" . $row["shipDate"] . "</td>";
	  // echo "<td>" . $row["cancelled"] . "</td>";
	  // echo "<td>" . $row["cancelDate"] . "</td>";
	  // echo "<td>" . $row["item"] . "</td>";
		// echo "</tr>";
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

		array_push( $response, $order );
	}
	// echo "</table>";
	//
	header( "Content-type: application/json" );
	echo json_encode( $response );	
	
	if ( $link ) {
		mysqli_close( $link );
	}

	if ( $result ) {
		mysqli_free_result( $result );
	}
?>
