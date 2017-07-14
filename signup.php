<!--
  Copyright (c) 2016, Huan Zhan
	All rights reserved.

  Version: 1.0
  Author: Huan Zhan
  Date: March 2016
-->

<html>

<head> 
	<title>Pan & Huang Inventory Services</title> 
	<link rel="stylesheet" type="text/css" href="./thirdParty/jquery/jquery.dataTables.min.css">
	<link rel="stylesheet" type="text/css" href="./thirdParty/jquery/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="./css/generic.css">
	<link rel="stylesheet" type="text/css" href="./css/signup.css">
	<script type="text/javascript" src="./thirdParty/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="./thirdParty/jquery/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="./thirdParty/jquery/jquery-ui.js"></script>
	<script type="text/javascript" src="./thirdParty/jquery/jquery.cookie.js"></script>
	<script type="text/javascript" src="./thirdParty/json2.js"></script>
	<script type="text/javascript" src="./js/generic.js"></script>
</head>

<body>

  <!-- main wrapper -->
	<div id="wrapper">

		<!-- header -->
		<?php include("./templates/header.php") ?>

    <!-- main body -->
		<div id="content">

			<div id="left-pane">
				<ul id="form">
				
					<li id="name">
						<div>First Name:</div>
						<div><input type="text" /></div>
						<div>Last Name:</div>
						<div><input type="text" /></div>
					</li>
				
					<li id="username">
						<div>Username:</div>
						<div><input type="text" /></div>
					</li>
				
					<li id="password">
						<div>Password:</div>
						<div><input type="text" /></div>
					</li>
				
					<li id="address1">
						<div>Address:</div>
						<div><input type="text" /></div>
					</li>
				
					<li id="address2">
						<div>State :</div>
						<div>
							<select>
								<option>NJ</option>
								<option>NY</option>
								<option>AZ</option>
								<option>CA</option>
								<option>MN</option>
							</select>
						</div>
						<div>Zip Code:</div>
						<div><input type="text" /></div>
					</li>
				
					<li id="phone">
						<div>Phone:</div>
						<div><input type="text" /></div>
					</li>
				
					<li id="email">
						<div>Email:</div>
						<div><input type="text" /></div>
					</li>
				
					<li id="dob">
						<div title="Date of Birth">DOB:</div>
						<div><input type="date" /></div>
					</li>
				
					<li id="gender">
						<div>Gender:</div>
						<div><input type="radio" /></div>
						<div>Male</div>
						<div><input type="radio" /></div>
						<div>Female</div>
					</li>
				
					<li id="comment">
						<div>Comment:</div>
						<div><textarea></textarea></div>
					</li>
				
					<li id="agreement">
						<div><input type="checkbox" /></div>
						<div>I agree to the <a href="">Term of Use</a> and <a href="">Privacy Policy</a>.</div>
					</li>
				
					<li id="submit">
						<button>SUBMIT</button>
					</li>
				</ul>
			</div>
			<div id="right-pane"></div>
		</div>

    <!-- footer -->
		<?php include("./templates/footer.php") ?>

	</div>

</body>

</html>
