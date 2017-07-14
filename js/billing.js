/**
  * Copyright (c) 2016, Huan Zhan
  * All rights reserved.
  */

var BILLING_COL = {
  ID         : 0,
  DATE       : 1,
  CREDIT     : 2,
  DEBIT      : 3,
  BALANCE    : 4,
  NOTE       : 5,
  ATTACHMENT : 6
};

var BILLING_DATA = {
  ID         : 0,
  DATE       : 1,
  CREDIT     : 2,
  DEBIT      : 3,
  BALANCE    : 4,
  NOTE       : 5,
  ATTACHMENT : 6
};

$(function() {var billingPage = new BillingPage()});

BillingPage.prototype = new GenericPage();

function BillingPage() {
  // Shared widgets.
  this.logoutAlertWidget = new LogoutAlertWidget();
  this.systemAlertWidget = new SystemAlertWidget();
  this.spinnerWidget = new SpinnerWidget;
  this.menuWidget = new MenuWidget();

  // Core option table widget.
  this.billingTableWidget = new BillingTableWidget(this.userProfile);
  this.billingTable = this.billingTableWidget.billingTable;
  
  this.makePaymentWidget = new MakePaymentWidget(this.userProfile, this.billingTable);
  this.selectNurseWidget = new SelectNurseWidget(this.userProfile, this.billingTable);
}

/**
 * Billing table widget.
 */
function BillingTableWidget(userProfile) {
  this.userProfile = userProfile;
  this.initBillingTable();
}

BillingTableWidget.prototype.initBillingTable = function() {
  var self = this;
  self.billingTable = $("#billing-table").DataTable({
    "sAjaxSource"     : "./php/requestprocessor.php?requestId=10009",
    "bProcessing"     : true,
    "bServerSide"     : true,
    "iDisplayLength"  : 50,
    "bPaginate"       : true,
    "bLengthChange"   : true,
    "bFilter"         : true,
    "bSort"           : true,
    "bInfo"           : false,
    "bAutoWidth"      : false,
    "aaSorting"       : [[BILLING_DATA.DATE, "desc"], [BILLING_DATA.ID, "desc"]],
    "aoColumnDefs": [
      {
        "aTargets"  : [BILLING_COL.ID],
        "mRender"   : function(data, type, full) { return full[BILLING_DATA.ID]; },
        "bVisible"  : false,
      },
      {
        "aTargets"  : [BILLING_COL.DATE],
        "mRender"   : function(data, type, full) { return full[BILLING_DATA.DATE]; },
        "bVisible"  : true,
        "sWidth"    : "12%",
      },
      {
        "aTargets"  : [BILLING_COL.CREDIT],
        "mRender"   : function(data, type, full) { return Number(full[BILLING_DATA.CREDIT]).toFixed(2); },
        "bVisible"  : true,
        "sWidth"    : "12%",
      },
      {
        "aTargets"  : [BILLING_COL.DEBIT],
        "mRender"   : function(data, type, full) { return Number(full[BILLING_DATA.DEBIT]).toFixed(2); },
        "bVisible"  : true,
        "sWidth"    : "12%",
      },
      {
        "aTargets"  : [BILLING_COL.BALANCE],
        "mRender"   : function(data, type, full) { return Number(full[BILLING_DATA.BALANCE]).toFixed(2); },
        "bVisible"  : true,
        "sWidth"    : "12%",
      },
      {
        "aTargets"  : [BILLING_COL.NOTE],
        "mRender"   : function(data, type, full) { return full[BILLING_DATA.NOTE]; },
        "bVisible"  : true,
        "sWidth"    : "40%",
      },
      {
        "aTargets"  : [BILLING_COL.ATTACHMENT],
        "mRender"   : function(data, type, full) { return full[BILLING_DATA.ATTACHMENT]; },
        "bVisible"  : true,
        "sWidth"    : "12%",
      },
    ],

    "fnRowCallback" : function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
      $("td:eq(1), td:eq(2), td:eq(3)", nRow).css("text-align", "right");
      $("td:eq(4)", nRow).css("padding-left", "40px");
    },
  });
}

/**
 * Make payment widget.
 */
function MakePaymentWidget(userProfile, billingTable) {
  this.userProfile = userProfile;

  switch (this.userProfile.privilege) {
    case PRIVILEGE_TYPE.ADMIN:
      break;
    case PRIVILEGE_TYPE.DOCTOR:
      $("#make-payment").hide();
      break;
    case PRIVILEGE_TYPE.NURSE:
      $("#make-payment").hide();
      break;
    default:
      break;
  }

  this.billingTable = billingTable;
  this.initMakePaymentPopup();
  this.makePaymentBtnHandler();
}

MakePaymentWidget.prototype.makePaymentBtnHandler = function() {
  var self = this;
  $("#make-payment-btn").on("click", function() {
    if ($("#select-nurse-entry").val() != "all") {
      $("#make-payment-popup").dialog("open");
    } else {
      $("#generic-alert-popup").html("<span>Please select a nurse.</span>");
      $("#generic-alert-popup").dialog("open");
    }
  });
}

MakePaymentWidget.prototype.validateScreenInput = function() {
  var isValidated = true;
  return isValidated; 
}

MakePaymentWidget.prototype.sendServerRequest = function() {
  var self = this;

  var url = "./php/requestprocessor.php?requestId=60001";
  var data = {
    nurseId: $("#select-nurse-entry").val(),  
    amount: $("#make-payment-amount-entry").val(),
    note: $("#make-payment-note-entry").val(),
    // attachment: $("#make-payment-attachment-entry").val()
  };

  $.ajax({
    url      : url,
    type     : "POST",
    dataType : "json",
    data     : data, 
    success  : function(response) { self.billingTable.ajax.reload() },
    error    : function(){ $("#generic-alert-popup").html("Server error.").dialog("open") } 
  });
}

MakePaymentWidget.prototype.initMakePaymentPopup = function() {
  var self = this;

  function payBtnHandler(obj) {
    if (self.validateScreenInput()) {
      self.sendServerRequest();
    }
    $(obj).dialog("close");
  }

  self.makePaymentPopup = $("#make-payment-popup").dialog({
    modal       : true,
    draggable   : false,
    resizable   : false,
    position    : { my: "center", at: "top", of: window },
    width       : 480,
    dialogClass : "alert",
    autoOpen    : false,
    show        : "clip",
    hide        : "clip",
    buttons     : [
                    { 
                      text: "Pay",
                      class: "btn-red",
                      click: function() { payBtnHandler(this) },
                    }, 
                    { 
                      text: "Cancel",
                      class: "btn-red",
                      click: function() { $(this).dialog("close"); }
                    }
                  ]
  });
}

/**
 * Select nurse widget.
 */
function SelectNurseWidget(userProfile, billingTable) {
  this.userProfile = userProfile;

  switch (this.userProfile.privilege) {
    case PRIVILEGE_TYPE.ADMIN:
      break;
    case PRIVILEGE_TYPE.DOCTOR:
      $("#select-nurse").hide();
      break;
    case PRIVILEGE_TYPE.NURSE:
      $("#select-nurse").hide();
      break;
    default:
      break;
  }

  this.billingTable = billingTable;
  this.changeNurseComboboxHandler();  
  this.initSelectNurseCombobox();
}

SelectNurseWidget.prototype.initSelectNurseCombobox = function() {
  function successCallback(obj) {
    if (obj.header) {
      $("#generic-alert-popup").html(obj.header.errmsg).dialog("open");
    } else {
     for (var i=0; i<obj.length; i++) {
       var option = "<option value='" + obj[i].doctorId + "'>" + obj[i].doctorName + "</option>"
       $("#select-nurse-entry").append(option);
     }
    }
  }

  // Retrieve doctor list
  $.ajax({
    url      : "php/requestprocessor.php?requestId=10004&mode=billing",
    type     : "POST",
    dataType : "json",
    success  : function(response) { successCallback(response) },
    error    : function(){ $("#generic-alert-popup").html("Server error.").dialog("open") } 
  });
}

SelectNurseWidget.prototype.changeNurseComboboxHandler = function() {
  var self = this;
  $("#select-nurse-entry").on("change", function() {
    doctorId = $("#select-nurse-entry").val();
    url = "php/requestprocessor.php?requestId=10009";
    url += "&doctorId="+doctorId;
    self.billingTable.ajax.url(url).load();
  });
}





