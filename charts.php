<!-- Copyright (c) 2016, Huan Zhan All rights reserved. -->

<html>
<head> 
  <?php include("./templates/head.php") ?>
	<script type="text/javascript" src="./thirdParty/flot/jquery.flot.js"></script>
	<script type="text/javascript" src="./thirdParty/flot/jquery.flot.time.js"></script>
	<script type="text/javascript" src="./thirdParty/flot/jquery.flot.axislabels.js"></script>
	<script type="text/javascript" src="./js/charts.js?3"></script>
</head>

<body>
	<div id="wrapper">
		<?php include("./templates/header.php") ?>
		<?php include("./templates/nav.php") ?>

		<div id="content">
      <div id="chart-holder"></div>
		</div>

		<?php include("./templates/footer.php") ?>
	</div>

  <?php include("./templates/generic.php") ?>
</body>
</html>
