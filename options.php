<!-- Copyright (c) 2016, Huan Zhan All rights reserved. -->

<html>
<head> 
  <?php include("./templates/head.php") ?>
	<script type="text/javascript" src="./js/options.js?4"></script>
</head>

<body>
	<div id="wrapper">
		<?php include("./templates/header.php") ?>
		<?php include("./templates/nav.php") ?>

		<div id="utility" class="hz-container">
			<div id="create-option-widget" class="hz-horizontal hz-widget">
        <button id="create-option" class="hz-horizontal btn-red">Create Option</button>
      </div>

			<div id="select-doctor-group" class="hz-horizontal hz-widget">
        <select id="select-doctor" class="hz-horizontal">
          <option value="all">All doctors</option>
        </select>
      </div>

			<div id="report-doctor-group" class="hz-horizontal hz-widget">
        <input id="report-doctor-sdate" class="hz-horizontal" type="date" />
        <span class="hz-horizontal hz-no-gutter">-</span>
        <input id="report-doctor-edate" class="hz-horizontal" type="date" />
        <button id="doctor-report" class="hz-horizontal btn-red" title="Click to see the qty of items for all the doctor's PO# within the selected date range. Note those within zero qty will not be displayed.">Report</button>
      </div>
		</div>

		<div id="content">
			<table id="option-table">
				<thead><tr>
					<th>Doctor</th>
					<th>PO #</th>
					<th>Item</th>
					<th>UPC</th>
					<th>Price</th>
					<th>Qty</th>
					<th title="Labeled">L</th>
					<th title="Shipped">S</th>
					<th title="In stock">I</th>
					<th>Fee</th>
					<th>Date</th>
					<th title="Update Option"></th>
					<th title="Create Deal"></th>
					<th title="Download Tracking"></th>
				</tr></thead>
				<tbody></tbody>
			</table>

		</div>

		<?php include("./templates/footer.php") ?>
	</div>

  <?php include("./templates/generic.php") ?>

	<!-- Create option popup -->
	<div id="create-option-popup" class="hz-popup" title="Create Option">
		<div id="create-option-popup-doctor" class="hz-popup-row1">
		  <label class="hz-horizontal">Doctor</label>
		  <select class="hz-horizontal"><option value=0>Select a doctor</option></select>
		</div>
		<div id="create-option-popup-po" class="hz-popup-row1">
		  <label class="hz-horizontal">PO #</label>
		  <input class="hz-horizontal" type="text" placeholder="PO #" />
		</div>
		<div id="create-option-popup-item" class="hz-popup-row1">
		  <label class="hz-horizontal">Item</label>
		  <input class="hz-horizontal" type="text" placeholder="Item" />
		</div>
		<div id="create-option-popup-upc" class="hz-popup-row1">
		  <label class="hz-horizontal">UPC</label>
		  <input class="hz-horizontal" type="text" placeholder="UPC, must be a 12-digit number" />
		</div>
		<div id="create-option-popup-price" class="hz-popup-row1">
		  <label class="hz-horizontal">Price</label>
		  <input class="hz-horizontal" type="text" value="$0.00" placeholder="Price" />
		</div>
		<div id="create-option-popup-note" class="hz-popup-row1">
		  <label class="hz-horizontal">Note</label>
		  <input class="hz-horizontal" type="text" placeholder="Note" />
		</div>
	</div>

	<!-- Update option popup -->
	<div id="update-option-popup" class="hz-popup" title="Update Option">
		<div id="update-option-popup-optionId" class="hz-popup-row1">
		  <label class="hz-horizontal">OptionId</label>
		  <input class="hz-horizontal" type="text" />
		</div>
		<div id="update-option-popup-po" class="hz-popup-row1">
		  <label class="hz-horizontal">PO #</label>
		  <input class="hz-horizontal" type="text" />
		</div>
		<div id="update-option-popup-item" class="hz-popup-row1">
		  <label class="hz-horizontal">Item</label>
		  <input class="hz-horizontal" type="text" />
		</div>
		<div id="update-option-popup-upc" class="hz-popup-row1">
		  <label class="hz-horizontal">UPC</label>
		  <input class="hz-horizontal" type="text" />
		</div>
		<div id="update-option-popup-price" class="hz-popup-row1">
		  <label class="hz-horizontal">Price</label>
		  <input class="hz-horizontal" type="text" />
		</div>
		<div id="update-option-popup-qty" class="hz-popup-row1">
		  <label class="hz-horizontal">Qty</label>
		  <input class="hz-horizontal" type="number" min="0" step="1" />
		</div>
		<div id="update-option-popup-total-labeled" class="hz-popup-row1">
		  <label class="hz-horizontal">Total Labeled</label>
		  <input class="hz-horizontal" type="number" min="0" step="1" />
		</div>
		<div id="update-option-popup-total-shipped" class="hz-popup-row1">
		  <label class="hz-horizontal">Total Shipped</label>
		  <input class="hz-horizontal" type="number" min="0" step="1" />
		</div>
		<div id="update-option-popup-labeled" class="hz-popup-row1">
		  <label class="hz-horizontal">Labeled</label>
		  <input class="hz-horizontal" type="number" min="0" step="1" />
		</div>
		<div id="update-option-popup-shipped" class="hz-popup-row1">
		  <label class="hz-horizontal">Shipped</label>
		  <input class="hz-horizontal" type="number" min="0" step="1" />
		</div>
		<div id="update-option-popup-active" class="hz-popup-row1">
		  <label class="hz-horizontal">Active</label>
		  <select class="hz-horizontal">
		    <option value=1>Yes</option>
		    <option value=0>No</option>
		  </select>
		</div>
		<div id="update-option-popup-note" class="hz-popup-row1">
		  <label class="hz-horizontal">Note</label>
		  <input class="hz-horizontal" type="text" />
		</div>
		<div id="update-option-popup-shipLog" class="hz-popup-row1">
		  <label class="hz-horizontal">Ship Log</label>
		  <textarea class="hz-horizontal"></textarea>
		</div>
	</div>

	<!-- Create deal popup -->
	<div id="create-deal-popup" class="hz-popup" title="Create Deal">
		<div id="create-deal-popup-optionId" class="hz-popup-row1">
		  <label class="hz-horizontal">optionId</label>
		  <input class="hz-horizontal" type="text" />
		</div>
		<div id="create-deal-popup-track" class="hz-popup-row1">
		  <label class="hz-horizontal">Tracking #</label>
		  <input class="hz-horizontal" type="text" />
		</div>
		<div id="create-deal-popup-doctor" class="hz-popup-row1">
		  <label class="hz-horizontal">Doctor</label>
		  <input class="hz-horizontal" type="text" />
		</div>
		<div id="create-deal-popup-nurse" class="hz-popup-row1">
		  <label class="hz-horizontal">Nurse</label>
		  <input class="hz-horizontal" type="text" />
		</div>
		<div id="create-deal-popup-po" class="hz-popup-row1">
		  <label class="hz-horizontal">PO #</label>
		  <input class="hz-horizontal" type="text" />
		</div>
		<div id="create-deal-popup-item" class="hz-popup-row1">
		  <label class="hz-horizontal">Item</label>
		  <input class="hz-horizontal" type="text" />
		</div>
		<div id="create-deal-popup-price" class="hz-popup-row1">
		  <label class="hz-horizontal">Price</label>
		  <input class="hz-horizontal" type="text" />
		</div>
		<div id="create-deal-popup-qty" class="hz-popup-row1">
		  <label class="hz-horizontal">Qty</label>
		  <input class="hz-horizontal" type="number" min="1" step="1" />
		</div>
	</div>
</body>
</html>
