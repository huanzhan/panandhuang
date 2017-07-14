/**
  * Copyright (c) 2016, Huan Zhan
  * All rights reserved.
  */

var OPTION_COL = {
  DOCTOR   : 0,
  PO       : 1,
  ITEM     : 2,
  UPC      : 3,
  PRICE    : 4,
  QTY      : 5,
  LABELED  : 6,
  SHIPPED  : 7,
  INSTOCK  : 8,
  FEE      : 9,
  DATE     : 10,
  EDIT     : 11,
  ADD      : 12,
  DOWNLOAD : 13,
};

var OPTION_DATA = {
  OPTIONID : 0,
  DOCTOR   : 1,
  PO       : 2,
  ITEM     : 3,
  UPC      : 4,
  PRICE    : 5,
  DATE     : 6,
  QTY      : 7,
  LABELED  : 8,
  SHIPPED  : 9,
  ACTIVE   : 10,
  NOTE     : 11,
  SHIPLOG  : 12,
};

$(function() {this.optionsPage = new OptionPage()});

/**
 * Option page widget.
 */
OptionPage.prototype = new GenericPage();

function OptionPage() {
  // Shared widgets.
  this.logoutAlertWidget = new LogoutAlertWidget();
  this.systemAlertWidget = new SystemAlertWidget();
  this.spinnerWidget = new SpinnerWidget;
  this.menuWidget = new MenuWidget();

  // Core option table widget.
  this.optionTableWidget = new OptionTableWidget(this.userProfile);
  this.optionTable = this.optionTableWidget.optionTable;
  
  // Widgets depending on option table.
  this.createOptionWidget = new CreateOptionWidget(this.userProfile, this.optionTable);
  this.updateOptionWidget = new UpdateOptionWidget(this.userProfile, this.optionTable);
  this.createDealWidget = new CreateDealWidget(this.userProfile, this.optionTable);
  this.selectDoctorWidget = new SelectDoctorWidget(this.userProfile, this.optionTable);
  this.reportDoctorWidget = new ReportDoctorWidget(this.userProfile);
  this.downloadTrackingWidget = new DownloadTrackingWidget(this.userProfile, this.optionTable);
}

/**
 * Report doctor widget.
 */
function ReportDoctorWidget(userProfile) {
  this.userProfile = userProfile;

  switch (this.userProfile.privilege) {
    case PRIVILEGE_TYPE.NURSE:
      $("#report-doctor-group").hide();
      break;
    default:
      break;
  }

  this.reportDoctorBtnHandler();
}

ReportDoctorWidget.prototype.reportDoctorBtnHandler = function() {
  var self = this;
  $("#doctor-report").on("click", function() {
    var doctorId = (self.userProfile.privilege==PRIVILEGE_TYPE.ADMIN)
        ?$("#select-doctor").val():self.userProfile.userId;
    if (doctorId == "all") {
      $("#generic-alert-popup").text("Please select a doctor.").dialog("open");
      return;
    }
    sdate = $("#report-doctor-sdate").val();
    edate = $("#report-doctor-edate").val();
    url = "./php/requestprocessor.php?requestId=10007&doctorId=";
    url += doctorId + "&startDate=" + sdate + "&endDate=" + edate;
    window.open(url , "_self");
  });
}

/**
 * Download tracking widget.
 */
function DownloadTrackingWidget(userProfile, optionTable) {
  this.userProfile = userProfile;
  this.optionTable = optionTable;
  this.downloadTrackingBtnHandler();
}

DownloadTrackingWidget.prototype.downloadTrackingBtnHandler = function() {
  var self = this;
  $("#option-table tbody").on("click", "tr td img[name='download-tracking']", function() {
    var tr = $(this).parent("td").parent("tr");
    var po = self.optionTable.cell(tr, OPTION_DATA.PO).data(); 
    var optionId = self.optionTable.cell(tr, OPTION_DATA.OPTIONID).data(); 
    window.open("php/requestprocessor.php?requestId=50002&&po="+po+"&optionId="+optionId, "_self");
  });
}

/**
 * Select doctor widget.
 */
function SelectDoctorWidget(userProfile, optionTable) {
  this.userProfile = userProfile;

  switch (this.userProfile.privilege) {
    case PRIVILEGE_TYPE.DOCTOR:
    case PRIVILEGE_TYPE.NURSE:
      $("#select-doctor-group").hide();
      break;
    default:
      break;
  }

  this.optionTable = optionTable;
  this.initSelectDoctorCombobox();
  this.changeDoctorComboboxHandler();
}

SelectDoctorWidget.prototype.initSelectDoctorCombobox = function() {
  var self = this;
  
  function successHandler(obj) {
    if (obj.header) {
      $("#generic-alert-popup").html(obj.header.errmsg).dialog("open");
    } else {
     for (var i=0; i<obj.length; i++) {
       var option = "<option value='" + obj[i].doctorId + "'>" + obj[i].doctorName + "</option>"
       $("#select-doctor").append(option);
       $("#create-option-popup-doctor > select").append(option);
     }
    }
  }

  $.ajax({
    url      : "php/requestprocessor.php?requestId=10004",
    type     : "POST",
    dataType : "json",
    success  : function(response) { successHandler(response) },
    error    : function(){ $("#generic-alert-popup").html("Server error.").dialog("open") } 
  });
}

SelectDoctorWidget.prototype.changeDoctorComboboxHandler = function() {
  var self = this;
  $("#select-doctor").on("change", function() {
    var doctorId = $("#select-doctor").val();
    var url = "php/requestprocessor.php?requestId=10002";
    url += "&doctorId="+doctorId;
    self.optionTable.ajax.url(url).load();
  });
}

/**
 * Option table widget.
 */
function OptionTableWidget(userProfile) {
  this.userProfile = userProfile;
  this.initOptionTable();
}

OptionTableWidget.prototype.initOptionTable = function() {
  var self = this;
  self.optionTable = $("#option-table").DataTable({
    "sAjaxSource"     :  "php/requestprocessor.php?requestId=10002",
    //"sAjaxDataProp" :  "",
    "bProcessing"     : true,
    "bServerSide"     : true,
    "iDisplayLength"  : 50,
    "bPaginate"       : true,
    "bLengthChange"   : true,
    "bFilter"         : true,
    "bSort"           : true,
    "bInfo"           : false,
    "bAutoWidth"      : false,
    "aaSorting"       : [[10, "desc"], [0, "desc"]],
    "aoColumnDefs"    : [
      {
        "aTargets"  : [OPTION_COL.DOCTOR],
        "mRender"   : function(data, type, full) { return full[OPTION_DATA.DOCTOR]; },
        "bVisible"  : (self.userProfile.privilege===PRIVILEGE_TYPE.DOCTOR)?false:true,
        "sWidth"    : "10%",
      },
      {
        "aTargets"  : [OPTION_COL.PO],
        "mRender"   : function(data, type, full) { return full[OPTION_DATA.PO]; },
        "sWidth"    : "8%",
      },
      {
        "aTargets"  : [OPTION_COL.ITEM],
        "mRender"   : function(data, type, full) { return full[OPTION_DATA.ITEM]; },
        "sWidth"    : (self.userProfile.privilege===PRIVILEGE_TYPE.NURSE)?"58%":((self.userProfile.privilege===PRIVILEGE_TYPE.DOCTOR)?"32%":"22%"),
      },
      {
        "aTargets"  : [OPTION_COL.UPC],
        "mRender"   : function(data, type, full) { return full[OPTION_DATA.UPC]; },
        "bVisible"  : false,
        "sWidth"    : "8%",
      },
      {
        "aTargets"  : [OPTION_COL.PRICE],
        "mRender"   : function(data, type, full) { return "$"+Number(full[OPTION_DATA.PRICE]).toFixed(2); },
        "sWidth"    : "8%",
      },
      {
        "aTargets"  : [OPTION_COL.QTY],
        "mRender"   : function(data, type, full) { return full[OPTION_DATA.QTY]; },
        "bSortable" : true,
        "bVisible"  : true,
        "sWidth"    : "5%",
      },
      {
        "aTargets"  : [OPTION_COL.LABELED],
        "mRender"   : function(data, type, full) { return full[OPTION_DATA.LABELED]; },
        "bSortable" : true,
        "bVisible"  : (self.userProfile.privilege===PRIVILEGE_TYPE.NURSE)?false:true,
        "sWidth"    : "5%",
      },
      {
        "aTargets"  : [OPTION_COL.SHIPPED],
        "mRender"   : function(data, type, full) { return full[OPTION_DATA.SHIPPED]; },
        "bSortable" : true,
        "bVisible"  : (self.userProfile.privilege===PRIVILEGE_TYPE.NURSE)?false:true,
        "sWidth"    : "5%",
      },
      {
        "aTargets"  : [OPTION_COL.INSTOCK],
        "mRender"   : function(data, type, full) { return full[OPTION_DATA.QTY]-full[OPTION_DATA.SHIPPED]; },
        "bSortable" : true,
        "bVisible"  : (self.userProfile.privilege===PRIVILEGE_TYPE.NURSE)?false:true,
        "sWidth"    : "5%",
      },
      {
        "aTargets"  : [OPTION_COL.FEE],
        "mRender"   : function(data, type, full) { return "$"+(0.03*full[OPTION_DATA.PRICE]*full[OPTION_DATA.QTY]).toFixed(2); },
        "bSortable" : true,
        "bVisible"  : (self.userProfile.privilege===PRIVILEGE_TYPE.NURSE)?false:true,
        "sWidth"    : "8%",
      },
      {
        "aTargets"  : [OPTION_COL.DATE],
        "mRender"   : function(data, type, full) { return full[OPTION_DATA.DATE]; },
        "bVisible"  : true
      },
      {
        "aTargets"  : [OPTION_COL.EDIT],
        "mRender"   : function(data, type, full) { return "<img src='images/edit.png' name='edit-option' title='Update Option'>"; },
        "bSortable" : false,
        "bVisible"  : (self.userProfile.privilege===PRIVILEGE_TYPE.NURSE)?false:true,
      },
      {
        "aTargets"  : [OPTION_COL.ADD],
        "mRender"   : function(data, type, full) { return "<img src='images/add.png' name='add-deal' title='Create Deal'>"; },
        "bSortable" : false,
        "bVisible"  : (self.userProfile.privilege!==PRIVILEGE_TYPE.ADMIN)?false:true,
      },
      {
        "aTargets"  : [OPTION_COL.DOWNLOAD],
        "mRender"   : function(data, type, full) { return "<img src='images/download.png' name='download-tracking' title='Download Tracking'>"; },
        "bSortable" : false,
        "bVisible"  : (self.userProfile.privilege===PRIVILEGE_TYPE.NURSE)?false:true,
      },
    ],

    // Hide inactive options from nurses, and highlight them for others
    "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
      if (aData[OPTION_DATA.ACTIVE] == 0) {
        if (self.userProfile.privilege === PRIVILEGE_TYPE.NURSE) {
          $(nRow).css("display", "none");
        } else {
          $(nRow).css("color", "#cc0000");
        }
      }
    }
  });
}

/**
 * Create option widget.
 */
function CreateOptionWidget(userProfile, optionTable) {
  this.userProfile = userProfile;
  this.optionTable = optionTable;
  this.initCreateOptionPopup();
  this.createOptionBtnHandler();

  switch (this.userProfile.privilege) {
    case PRIVILEGE_TYPE.NURSE:
      $("#create-option-widget").hide();
      return;
      break;
    default:
      break;
  }
}

CreateOptionWidget.prototype.createOptionBtnHandler = function() {
  var self = this;
  $("#create-option").on("click", function() {
    self.resetCreateOptionPopup();
    $("#create-option-popup").dialog("open");
    // Only admin can choose doctor when creating option.
    if (self.userProfile.privilege !== PRIVILEGE_TYPE.ADMIN) {
      $("#create-option-popup-doctor").hide();
    }
  });
}

CreateOptionWidget.prototype.resetCreateOptionPopupValue = function() {
  $("#create-option-popup-doctor > select").val(0);
  $("#create-option-popup-po > input").val("");
  $("#create-option-popup-item > input").val("");
  $("#create-option-popup-upc > input").val("");
  $("#create-option-popup-price > input").val("");
  $("#create-option-popup-note > input").val("");
}

CreateOptionWidget.prototype.resetCreateOptionPopupBorder = function() {
  $("#create-option-popup-po > input").removeClass("invalidInput");
  $("#create-option-popup-item > input").removeClass("invalidInput");
  $("#create-option-popup-price > input").removeClass("invalidInput");
  $("#create-option-popup-upc > input").removeClass("invalidInput");
  $("#create-option-popup-note > input").removeClass("invalidInput");
}

CreateOptionWidget.prototype.resetCreateOptionPopup = function() {
  $("#create-option-popup-doctor > select").val(0);
  $("#create-option-popup-po > input").removeClass("invalidInput").val("");
  $("#create-option-popup-item > input").removeClass("invalidInput").val("");
  $("#create-option-popup-price > input").removeClass("invalidInput").val("");
  $("#create-option-popup-upc > input").removeClass("invalidInput").val("");
  $("#create-option-popup-note > input").removeClass("invalidInput").val("");
}

CreateOptionWidget.prototype.validateScreenInput = function() {
  this.resetCreateOptionPopupBorder();
  var isValidated = true;

  var po = $("#create-option-popup-po > input").val();
  var patternPo = /^[a-z0-9A-Z]+$/;
  if (!patternPo.test(po)) {
    $("#create-option-popup-po input").addClass("invalidInput");
    isValidated = false;
  }

  var item = $("#create-option-popup-item > input").val();
  var patternItem = /^[a-z0-9A-Z\s\-'"]+$/;
  if (!patternItem.test(item)) {
    $("#create-option-popup-item input").addClass("invalidInput");
    isValidated = false;
  }

  var upc = $("#create-option-popup-upc > input").val();
  var patternUpc = /^\d{12}$/;
  if (!patternUpc.test(upc)) {
    $("#create-option-popup-upc input").addClass("invalidInput");
    isValidated = false;
  }

  return isValidated;
}

CreateOptionWidget.prototype.initCreateOptionPopup = function() {
  var self = this;

  function createBtnHandler(obj) {
    if (self.validateScreenInput()) {
      self.sendServerRequest();
      $(obj).dialog("close");
    }
  }

  self.createOptionPopup = $("#create-option-popup").dialog({
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
                      text: "Create",
                      class: "btn-red",
                      click: function() { createBtnHandler(this) },
                    }, 
                    { 
                      text: "Cancel",
                      class: "btn-red",
                      click: function() { $(this).dialog("close"); }
                    }
                  ]
  });
}

CreateOptionWidget.prototype.sendServerRequest = function() {
  var self = this;
  var url = "php/requestprocessor.php?requestId=20002";
  var data = {
    doctorId : $("#create-option-popup-doctor > select").val(),
    po       : $("#create-option-popup-po > input").val(),
    item     : $("#create-option-popup-item > input").val(),
    upc      : $("#create-option-popup-upc > input").val(),
    price    : Number($("#create-option-popup-price > input").val().replace(/[^0-9\.]+/g, "")),
    note     : $("#create-option-popup-note > input").val()
  };

  function successCallback(obj) {
    if (obj.header.errcode != 0) {
      $("#generic-alert-popup").html(obj.header.errmsg).dialog("open");
    } else {
      self.optionTable.ajax.reload();
    }
  }

  $.ajax({
    url      : url,
    type     : "POST",
    dataType : "json",
    data     : data, 
    success  : function(response) { successCallback(response) },
    error    : function(){ $("#generic-alert-popup").html("Server error.").dialog("open") } 
  });
}

/**
 * Update option widget.
 */
function UpdateOptionWidget(userProfile, optionTable) {
  this.userProfile = userProfile;
  this.optionTable = optionTable;
  this.initUpdateOptionPopup();
  this.updateOptionBtnHandler();
}

UpdateOptionWidget.prototype.updateOptionBtnHandler = function() {
  var self = this;
  $("#option-table tbody").on("click", "tr > td > img[name='edit-option']", function() {
    self.paintUpdateOptionPopup($(this).parent("td").parent("tr"));
  });
}

UpdateOptionWidget.prototype.resetUpdateOptionPopupValue = function() {
  $("#update-option-popup-optionId > input").val("");
  $("#update-option-popup-po > input").val("");
  $("#update-option-popup-item > input").val("");
  $("#update-option-popup-upc > input").val("");
  $("#update-option-popup-price > input").val("$"+Number(0).toFixed(2));
  $("#update-option-popup-qty > input").val("");
  $("#update-option-popup-total-labeled > input").val("");
  $("#update-option-popup-total-shipped > input").val("");
  $("#update-option-popup-labeled > input").val(0);
  $("#update-option-popup-shipped > input").val(0);
  $("#update-option-popup-active > select").val("");
  $("#update-option-popup-note > input").val("");
  $("#update-option-popup-shiplog > textarea").val("");
}

UpdateOptionWidget.prototype.resetUpdateOptionPopupBorder = function() {
  $("#update-option-popup-shipped > input").removeClass("invalidInput");
  $("#update-option-popup-po > input").removeClass("invalidInput");
  $("#update-option-popup-item > input").removeClass("invalidInput");
  $("#update-option-popup-price > input").removeClass("invalidInput");
  $("#update-option-popup-labeled > input").removeClass("invalidInput");
}

UpdateOptionWidget.prototype.resetUpdateOptionPopupProperty = function() {
  $("#update-option-popup-optionId").hide();
  if (this.userProfile.privilege === PRIVILEGE_TYPE.ADMIN) {
    $("#update-option-popup-shiplog > textarea").prop("disabled", true);
  } else if (this.userProfile.privilege === PRIVILEGE_TYPE.DOCTOR) {
    $("#update-option-popup-shipped > input").prop("disabled", true);
    $("#update-option-popup-shiplog > textarea").prop("disabled", true);
  }
}

UpdateOptionWidget.prototype.resetUpdateOptionPopup = function() {
  this.resetUpdateOptionPopupValue();
  this.resetUpdateOptionPopupBorder();
  this.resetUpdateOptionPopupProperty();
}

UpdateOptionWidget.prototype.paintUpdateOptionPopup = function(tr) {
  var self = this;
  self.resetUpdateOptionPopup();
  var data = {
    optionId : self.optionTable.cell(tr, OPTION_DATA.OPTIONID).data(),
    doctor   : self.optionTable.cell(tr, OPTION_DATA.DOCTOR).data(),
    po       : self.optionTable.cell(tr, OPTION_DATA.PO).data(),
    item     : self.optionTable.cell(tr, OPTION_DATA.ITEM).data(), 
    upc      : self.optionTable.cell(tr, OPTION_DATA.UPC).data(),
    price    : self.optionTable.cell(tr, OPTION_DATA.PRICE).data(), 
    qty      : self.optionTable.cell(tr, OPTION_DATA.QTY).data(),
    labeled  : self.optionTable.cell(tr, OPTION_DATA.LABELED).data(), 
    shipped  : self.optionTable.cell(tr, OPTION_DATA.SHIPPED).data(), 
    active   : self.optionTable.cell(tr, OPTION_DATA.ACTIVE).data(),
    note     : self.optionTable.cell(tr, OPTION_DATA.NOTE).data(),
    shipLog  : self.optionTable.cell(tr, OPTION_DATA.SHIPLOG).data() 
  }
  $("#update-option-popup-optionId > input").val(data.optionId);
  $("#update-option-popup-po > input").val(data.po);
  $("#update-option-popup-item > input").val(data.item);
  $("#update-option-popup-upc > input").val(data.upc);
  $("#update-option-popup-price > input").val("$"+Number(data.price).toFixed(2));
  $("#update-option-popup-qty > input").val(data.qty);
  $("#update-option-popup-total-labeled > input").val(data.labeled);
  $("#update-option-popup-total-shipped > input").val(data.shipped);
  $("#update-option-popup-labeled > input").val(0);
  $("#update-option-popup-shipped > input").val(0);
  $("#update-option-popup-active > select").val(data.active);
  $("#update-option-popup-note > input").val(data.note);
  $("#update-option-popup-shiplog > textarea").val(data.shipLog);
  $("#update-option-popup").dialog("open");
}

UpdateOptionWidget.prototype.validateScreenInput = function() {
  this.resetUpdateOptionPopupBorder();
  var isValidated = true;

  var shipped = $("#update-option-popup-shipped > input").val();
  var patternShipped = /^-?[1-9]\d*|0$/;
  if (!patternShipped.test(shipped)) {
    $("#update-option-popup-shipped > input").addClass("invalidInput");
    isValidated = false;
  }

  var po = $("#update-option-popup-po > input").val();
  var patternPo = /^[a-z0-9A-Z]+$/;
  if (!patternPo.test(po)) {
    $("#update-option-popup-po > input").addClass("invalidInput");
    isValidated = false;
  }

  var item = $("#update-option-popup-item > input").val();
  var patternItem = /^[a-z0-9A-Z\s\-'"]+$/;
  if (!patternItem.test(item)) {
    $("#update-option-popup-item > input").addClass("invalidInput");
    isValidated = false;
  }

  var price = $("#update-option-popup-price > input").val();
  var patternPrice = /^\$[0-9]+\.[0-9]{2}$/;
  if (!patternPrice.test(price)) {
    $("#update-option-popup-price > input").addClass("invalidInput");
    isValidated = false;
  }

  var labeled = $("#update-option-popup-labeled > input").val();
  var patternLabeled = /^-?[1-9]\d*|0$/;
  if (!patternLabeled.test(labeled)) {
    $("#update-option-popup-labeled > input").addClass("invalidInput");
    isValidated = false;
  }

  // Total number of labeled must be smaller than qty
  // var totalLabeled = $("#update-option-popup-total-labeled input").val();
  // if ( parseInt(labeled) + parseInt(totalLabeled) > parseInt(qty) ) {
  //   $("#update-option-popup-labeled input").css("background-color", "#FF9999" );
  //   isValidated = false;
  // }
  return isValidated;
}

UpdateOptionWidget.prototype.initUpdateOptionPopup = function() {
  var self = this;

  function updateBtnHandler(obj) {
    if (self.validateScreenInput()) {
      self.sendServerRequest();
      $(obj).dialog("close");
    }
  }

  self.updateOptionPopup = $("#update-option-popup").dialog({
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
                      text: "Update",
                      class: "btn-red",
                      click: function() { updateBtnHandler(this) },
                    }, 
                    { 
                      text: "Cancel",
                      class: "btn-red",
                      click: function() { $(this).dialog("close"); }
                    }
                  ]
  });
}

UpdateOptionWidget.prototype.sendServerRequest = function() {
  var self = this;
  var url  = "php/requestprocessor.php?requestId=30002";
  var data = {
    optionId : $("#update-option-popup-optionId > input").val(),
    po       : $("#update-option-popup-po > input").val(),
    item     : $("#update-option-popup-item > input").val(),
    upc      : $("#update-option-popup-upc > input").val(),
    price    : Number($("#update-option-popup-price > input").val().replace(/[^0-9\.]+/g, "")),
    labeled  : $("#update-option-popup-labeled > input").val(),
    shipped  : $("#update-option-popup-shipped > input").val(),
    active   : $("#update-option-popup-active > select").val(),
    note     : $("#update-option-popup-note > input").val(),
    shipLog  : $("#update-option-popup-shiplog > textarea").val(),
  };

  function successCallback(obj) {
    if (obj.header.errcode != 0) {
      $("#generic-alert-popup").html(obj.header.errmsg).dialog("open");
    } else {
      self.optionTable.ajax.reload();
    }
  }

  $.ajax({
    url      : url,
    type     : "POST",
    dataType : "json",
    data     : data, 
    success  : function(response) { successCallback(response) },
    error    : function(){ $("#generic-alert-popup").html("Server error.").dialog("open") } 
  });
}

/**
 * Create deal widget.
 */
function CreateDealWidget(userProfile, optionTable) {
  this.userProfile = userProfile;
  this.optionTable = optionTable;
  this.initCreateDealPopup();
  this.createDealBtnHandler();
}

CreateDealWidget.prototype.createDealBtnHandler = function() {
  var self = this;
  $("#option-table tbody").on("click", "tr > td > img[name='add-deal']", function() {
    self.paintCreateDealPopup($(this).parent("td").parent("tr")); 
  });
}

CreateDealWidget.prototype.paintCreateDealPopup = function(tr) {
  var self = this;
  if (self.optionTable.cell(tr, OPTION_DATA.ACTIVE).data() == 0 ) { 
    $("#generic-alert-popup").html("This option has been closed.").dialog("open");
    return;
  }
  self.resetCreateDealPopup();
  var data = { 
    optionId : self.optionTable.cell(tr, OPTION_DATA.OPTIONID).data(),
    doctor   : self.optionTable.cell(tr, OPTION_DATA.DOCTOR).data(),
    po       : self.optionTable.cell(tr, OPTION_DATA.PO).data(),
    item     : self.optionTable.cell(tr, OPTION_DATA.ITEM).data(), 
    price    : self.optionTable.cell(tr, OPTION_DATA.PRICE).data() 
  }
  $("#create-deal-popup-optionId > input").val(data.optionId);
  $("#create-deal-popup-doctor > input").val(data.doctor);
  $("#create-deal-popup-nurse > input").val(self.userProfile.name);
  $("#create-deal-popup-po > input").val(data.po);
  $("#create-deal-popup-item > input").val(data.item);
  $("#create-deal-popup-price > input").val("$"+Number(data.price).toFixed(2));
  $("#create-deal-popup").dialog("open");
}

CreateDealWidget.prototype.resetCreateDealPopupValue = function() {
  $("#create-deal-popup-optionId > input").val();
  $("#create-deal-popup-doctor > input").val();
  $("#create-deal-popup-nurse > input").val();
  $("#create-deal-popup-po > input").val();
  $("#create-deal-popup-item > input").val();
  $("#create-deal-popup-price > input").val("$"+Number(0).toFixed(2));
}

CreateDealWidget.prototype.resetCreateDealPopupBorder = function() {
  $("#create-deal-popup-track > input").removeClass("invalidInput");
  $("#create-deal-popup-qty > input").removeClass("invalidInput");
}

CreateDealWidget.prototype.resetCreateDealPopupProperty = function() {
  $("#create-deal-popup-doctor > input").prop("disabled", true);
  $("#create-deal-popup-nurse > input").prop("disabled", true);
  $("#create-deal-popup-po > input").prop("disabled", true);
  $("#create-deal-popup-item > input").prop("disabled", true);
  $("#create-deal-popup-price > input").prop("disabled", true);
  $("#create-deal-popup-optionId").hide();
}

CreateDealWidget.prototype.resetCreateDealPopup = function() {
  this.resetCreateDealPopupValue();
  this.resetCreateDealPopupBorder();
  this.resetCreateDealPopupProperty();
}

CreateDealWidget.prototype.validateScreenInput = function() {
  this.resetCreateDealPopupBorder();
  var isValidated = true;

  var track = $("#create-deal-popup-track > input").val();
  var patternTrack = /^[a-z0-9A-Z]+$/;
  if (!patternTrack.test(track)) {
    $("#create-deal-popup-track > input").addClass("invalidInput");
    isValidated = false;
  }

  var qty = $("#create-deal-popup-qty > input").val();
  var patternQty = /^[1-9][0-9]*$/;
  if (!patternQty.test(qty)) {
    $("#create-deal-popup-qty > input").addClass("invalidInput");
    isValidated = false;
  }
  return isValidated;
}

CreateDealWidget.prototype.sendServerRequest = function() {
  var self = this;

  var url = "php/requestprocessor.php?requestId=20001";
  var data = {
    optionId : $("#create-deal-popup-optionId > input").val(),
    track    : $("#create-deal-popup-track > input").val(),
    qty      : $("#create-deal-popup-qty > input").val(),
  };

  function successCallback(obj) {
    if (obj.header.errcode != 0) {
      $("#generic-alert-popup").html(obj.header.errmsg).dialog("open");
    } else {
      self.optionTable.ajax.reload();
    }
  }

  $.ajax({
    url      : url,
    type     : "POST",
    dataType : "json",
    data     : data, 
    success  : function(response) { successCallback(response) },
    error    : function(){ $("#generic-alert-popup").html("Server error.").dialog("open") } 
  });
}

CreateDealWidget.prototype.initCreateDealPopup = function() {
  var self = this;
  function createBtnHandler(obj) {
    if (self.validateScreenInput()) {
      self.sendServerRequest();
      $(obj).dialog("close");
    }
  }
  self.createDealPopup = $("#create-deal-popup").dialog({
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
                      text: "Create",
                      class: "btn-red",
                      click: function() { createBtnHandler(this) },
                    }, 
                    { 
                      text: "Cancel",
                      class: "btn-red",
                      click: function() { $(this).dialog("close"); }
                    }
                  ]
  });
}

$.fn.dataTable.ext.search.push(
  function(settings, data, dataIndex) {
    var selectedDoctor = $("#select-doctor").val();
    var currDoctor = data[0];
    if (selectedDoctor == currDoctor || selectedDoctor == 'all') {
      return true;
    }
    return false;
  }
);



