<!--
  Copyright (c) 2016, Huan Zhan
	All rights reserved.

  Author: Huan Zhan
  Date: March 2016
-->

<html>

<head> 
	<title>Pan & Huang Inventory Services</title> 
	<link rel="stylesheet" type="text/css" href="./thirdParty/jquery/jquery.dataTables.min.css">
	<link rel="stylesheet" type="text/css" href="./thirdParty/jquery/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="./css/generic.css">
	<link rel="stylesheet" type="text/css" href="./css/orders.css">
	<script type="text/javascript" src="./thirdParty/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="./thirdParty/jquery/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="./thirdParty/jquery/jquery-ui.js"></script>
	<script type="text/javascript" src="./thirdParty/jquery/jquery.cookie.js"></script>
	<script type="text/javascript" src="./thirdParty/json2.js"></script>
	<script type="text/javascript" src="./thirdParty/spin.js"></script>
	<script type="text/javascript" src="./js/generic.js"></script>
	<script type="text/javascript" src="./js/orders.js"></script>
</head>

<body>

  <!-- main wrapper -->
	<div id="wrapper">

		<?php include("./templates/header.php") ?>
		<?php include("./templates/nav.php") ?>

		<div id="utilities" >
			<input type="text" id="email" placeholder="Email" />
			<input type="password" id="password" placeholder="Password" />
			<!--<input type="text" id="back-date" placeholder="days" />-->
			<button class="button-red" id="scan-button">Scan</button>
		</div>

		<!--
		<div id="filters">
			<input id="sDate" name="sDate" type="date" /> - <input id="eDate" name="eDate" type="date" />
			<select id="order-status">
				<option value=0>All</option>
				<option value=1>Shipped</option>
			</select>
			<button class="button-red" id="download-button">Download</button>
		</div>
		-->

    <!-- main body -->
		<div id="content">

			<table id="order-table">
				<thead><tr>
					<th>Order #</th>
					<th>Order Date</th>
					<th>Tracking #</th>
					<th>Ship Date</th>
					<th>Canceled</th>
					<th>Cancel Date</th>
					<th>Bid</th>
					<th>Ask</th>
					<th title="Cash back">CB</th>
					<th>Item</th>
					<th></th>
				</tr></thead>
				<tbody></tbody>
			</table>

		</div>

    <!-- footer -->
		<?php include("./templates/footer.php") ?>

	</div>

  <?php include("./templates/generic.php") ?>

</body>

</html>
