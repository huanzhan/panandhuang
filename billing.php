<!-- Copyright (c) 2017, Huan Zhan All rights reserved. -->

<html>
<head> 
  <?php include("./templates/head.php") ?>
	<script type="text/javascript" src="./js/billing.js?2"></script>
</head>

<body>
	<div id="wrapper">
		<?php include("./templates/header.php") ?>
		<?php include("./templates/nav.php") ?>

		<div id="utility" class="hz-horizontal hz-container">
			<div id="select-nurse" class="hz-horizontal hz-widget">
        <select id="select-nurse-entry" class="hz-horizontal">
          <option value="all">All nurses</option>
        </select>
      </div>
      <div id="make-payment" class="hz-horizontal hz-widget">
        <button id="make-payment-btn" class="hz-horizontal btn-red">Make Payment</button>
      </div>
		</div>

		<div id="content">
			<table id="billing-table">
				<thead><tr>
					<th>Id</th>
					<th>Date</th>
					<th>Credit</th>
					<th>Debit</th>
					<th>Balance</th>
					<th>Note</th>
					<th>Attachment</th>
				</tr></thead>
				<tbody></tbody>
			</table>
		</div>

		<?php include("./templates/footer.php") ?>
	</div>

  <?php include("./templates/generic.php") ?>
  
  <div id="make-payment-popup" class="hz-popup" title="Make Payment">
    <div class="hz-vertical hz-popup-row1">
      <label class="hz-horizontal">Amount</label>
      <input id="make-payment-amount-entry" class="hz-horizontal" type="number" placeholder="Amount to pay" />
    </div>
    <div class="hz-vertical hz-popup-row1">
      <label class="hz-horizontal">Note</label>
      <input id="make-payment-note-entry" class="hz-horizontal" placeholder="Note" />
    </div>
    <div class="hz-vertical hz-popup-row1">
      <label class="hz-horizontal">Attachment</label>
      <input id="make-payment-attachment-entry" id="make-payment-popup-attachment" class="hz-horizontal" type="file" placeholder="e.g. photo of check" />
    </div>
  </div>
</body>
</html>
