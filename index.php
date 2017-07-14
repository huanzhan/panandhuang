<!-- Copyright (c) 2016, Huan Zhan All rights reserved. -->

<html>
<head> 
  <title>Pan & Huang Trading Services</title> 
  <link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
  <link rel="stylesheet" type="text/css" href="./css/generic.css?2">
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/json2/20160511/json2.min.js"></script>
  <link rel="stylesheet" type="text/css" href="./css/index.css">
	<script type="text/javascript" src="./js/index.js?3"></script>
</head>

<body>
	<div id="wrapper">
		<?php include("./templates/header.php") ?>

		<div id="content">
			<div id="left-pane">
				<div id="username"><div>Username</div><div><input type="text" /></div></div>
				<div id="password"><div>Password</div><div><input type="password" /></div></div>
				<div id="login-error">Username doesn't match password</div>
				<div id="login-buttons"><button class="btn-red">Login</button></div>
				<div id="signup">Don't have an account? Sign up <a href="signup.php">here</a>.</div>
			</div>

			<div id="right-pane"></div>
		</div>

		<?php include("./templates/footer.php") ?>
	</div>
</body>
</html>
