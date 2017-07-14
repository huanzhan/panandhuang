<!-- Copyright (c) 2017, Huan Zhan All rights reserved.  -->

<html>
<head> 
  <?php include("./templates/head.php") ?>
  <script type="text/javascript" src="./js/deals.js?7"></script>
</head>

<body>
  <div id="wrapper">
    <?php include("./templates/header.php") ?>
    <?php include("./templates/nav.php") ?>

    <div id="utility" class="hz-horizontal hz-container">
      <div id="select-deal-type" class="hz-horizontal hz-widget">
        <select id="deal-type-list" class="hz-horizontal">
          <option value="all">All deals</option>
          <option value="claimed">Claimed</option>
          <option value="unclaimed">Unclaimed</option>
        </select>
      </div>

      <div id="claim-deal" class="hz-horizontal hz-widget">
        <button id="claim-deal-btn" class="hz-horizontal btn-red" title="Claim a deal to be the owner of deal. You must know the tracking # to do this operation. The claimed deal will be added to your deal list.">Claim Single</button>
      </div>

      <div id="upload-deal" class="hz-horizontal hz-widget">
        <input id="upload-deal-entry" class="hz-horizontal" type="file" />
        <button id="upload-deal-btn" class="hz-horizontal btn-red">Upload</button>
      </div>

      <div id="verify-deal" class="hz-horizontal hz-widget">
        <input id="verify-deal-entry" class="hz-horizontal" type="file" />
        <button id="verify-deal-btn" class="hz-horizontal btn-red">Verify</button>
      </div>

      <div id="bulk-mode" class="hz-horizontal hz-widget">
        <div class="hz-horizontal hz-checkboxradio-wrapper" title="Use 'Bulk Mode' to update multiple deals altogether. Click the 'Commit' button to make the change.">
          <input id="bulk-mode-entry" class="hz-horizontal" type="checkbox" />
          <span class="hz-horizontal">Bulk Mode</span>
        </div>
        <button id="bulk-mode-commit-btn" class="hz-horizontal btn-disabled">Commit</button>
      </div>
    </div>

    <div id="content" class="hz-container">
      <table id="deal-table">
        <thead><tr>
          <th>Deal ID</th>
          <th>Option ID</th>
          <th>Doctor ID</th>
          <th>Nurse ID</th>
          <th>Tracking #</th>
          <th>Nurse</th>
          <th>Doctor</th>
          <th>PO #</th>
          <th>Item</th>
          <th>Price</th>
          <th>Qty</th>
          <th title="Claimed">C</th>
          <th title="Verified">V</th>
          <th title="Paid">P</th>
          <th title="Claimed">C</th>
          <th title="Paid">P</th>
          <th title="Receiving date">Date</th>
          <th></th>
        </tr></thead>
        <tbody></tbody>
      </table>
    </div>

    <?php include("./templates/footer.php") ?>
  </div>

  <?php include("./templates/generic.php") ?>

  <!-- Update deal popup -->
  <div id="update-deal-popup" class="hz-popup" title="Update Deal">
    <div id="update-deal-popup-dealId" class="hz-popup-row1">
      <input class="hz-horizontal" type="hidden" />
    </div>
    <div id="update-deal-popup-optionId" class="hz-popup-row1">
      <input class="hz-horizontal" type="hidden" />
    </div>
    <div id="update-deal-popup-track" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Tracking #</strong></label>
      <input class="hz-horizontal" type="text" />
    </div>
    <div id="update-deal-popup-doctor" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Doctor</strong></label>
      <input class="hz-horizontal" type="text" disabled />
    </div>
    <div id="update-deal-popup-nurse" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Nurse</strong></label>
      <!--<input class="hz-horizontal" type="text" />-->
      <select class="hz-horizontal"></select>
    </div>
    <div id="update-deal-popup-po" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>PO #</strong></label>
      <input class="hz-horizontal" type="text" />
    </div>
    <div id="update-deal-popup-item" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Item</strong></label>
      <input class="hz-horizontal" type="text" disabled />
    </div>
    <div id="update-deal-popup-price" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Price</strong></label>
      <input class="hz-horizontal" type="text" disabled />
    </div>
    <div id="update-deal-popup-qty" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Qty</strong></label>
      <input class="hz-horizontal" type="number" min="1" step="1" />
    </div>
    <div id="update-deal-popup-paid" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Paid</strong></label>
      <select class="hz-horizontal">
        <option value=1>Yes</option>
        <option value=0>No</option>
      </select>
    </div>
    <div id="update-deal-popup-date" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Date</strong></label>
      <input class="hz-horizontal" type="date">
    </div>
    <div id="update-deal-popup-note" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Note</strong></label>
      <input class="hz-horizontal" type="text">
    </div>
    <div id="update-deal-popup-claim" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Claimed</strong></label>
      <select class="hz-horizontal">
        <option value=1>Yes</option>
        <option value=0>No</option>
      </select>
    </div>
  </div>

  <!-- Claim deal popup -->
  <div id="claim-deal-popup" class="hz-popup" title="Claim Deal">
    <div id="claim-deal-popup-track" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Tracking #</strong></label>
      <div class="hz-horizontal search-box">
        <input type="text" placeholder="Type the tracking number and search." /><button class="btn-red">GO</button>
      </div>
    </div>
    <div id="claim-deal-popup-dealId" class="hz-popup-row1">
      <input class="hz-horizontal" type="hidden" />
    </div>
    <div id="claim-deal-popup-optionId" class="hz-popup-row1">
      <input class="hz-horizontal" type="hidden" />
    </div>
    <div id="claim-deal-popup-alert" class="hz-popup-row1">
      <span class="hz-horizontal">* This deal doesn't exist. Please try again later.</span>
    </div>
    <div id="claim-deal-popup-nurse" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Nurse</strong></label>
      <input class="hz-horizontal" type="text" />
    </div>
    <div id="claim-deal-popup-doctor" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Doctor</strong></label>
      <input class="hz-horizontal" type="text" />
    </div>
    <div id="claim-deal-popup-po" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>PO #</strong></label>
      <input class="hz-horizontal" type="text" />
    </div>
    <div id="claim-deal-popup-item" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Item</strong></label>
      <input class="hz-horizontal" type="text" />
    </div>
    <div id="claim-deal-popup-qty" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Qty</strong></label>
      <input class="hz-horizontal" type="number" min="1" step="1" />
    </div>
    <div id="claim-deal-popup-price" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Price</strong></label>
      <input class="hz-horizontal" type="text" />
    </div>
    <div id="claim-deal-popup-date" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Date</strong></label>
      <input class="hz-horizontal" type="date" />
    </div>
    <div id="claim-deal-popup-note" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Note</strong></label>
      <input class="hz-horizontal" type="text" />
    </div>
    <div id="claim-deal-popup-claim" class="hz-popup-row1">
      <label class="hz-horizontal"><strong>Claimed</strong></label>
      <select class="hz-horizontal">
        <option value=1>Yes</option>
        <option value=0>No</option>
      </select>
    </div>
  </div>
  
  <!-- Upload deal format popup -->
  <div id="upload-deal-format-popup" class="hz-popup" title="Please choose the upload format">
    <div class="hz-popup-row2">
      <div class="hz-horizontal hz-checkboxradio-wrapper">
        <label class="hz-horizontal">UPC</label>
        <input class="hz-horizontal" type="radio" name="upload-deal-format" value="upc" checked/>
      </div>
      <span class="hz-horizontal">Format: Tracking UPC Qty</span>
    </div>
    <div class="hz-popup-row2">
      <div class="hz-horizontal hz-checkboxradio-wrapper">
        <label class="hz-horizontal">PO#</label>
        <input class="hz-horizontal" type="radio" name="upload-deal-format" value="po" />
      </div>
      <span class="hz-horizontal">Format: Tracking PO# Qty</span>
    </div>
  </div>
</body>
</html>

