/**
  * Copyright (c) 2016, Huan Zhan
  * All rights reserved.
  */

var DEAL_COL = {
  DEALID     : 0,
  OPTIONID   : 1,
  DOCTORID   : 2,
  NURSEID    : 3,
  TRACK      : 4,
  NURSE      : 5,
  DOCTOR     : 6,
  PO         : 7,
  ITEM       : 8,
  PRICE      : 9,
  QTY        : 10,
  CLAIMED    : 11,
  VERIFIED   : 12,
  PAID       : 13,
  CK_CLAIMED : 14,
  CK_PAID    : 15,
  DATE       : 16,
  EDIT       : 17,
};

var DEAL_DATA = {
  DEALID   : 0,
  OPTIONID : 1,
  DOCTORID : 2,
  NURSEID  : 3,
  TRACK    : 4,
  NURSE    : 5,
  DOCTOR   : 6,
  PO       : 7,
  ITEM     : 8,
  PRICE    : 9,
  QTY      : 10,
  CLAIMED  : 11,
  VERIFIED : 12,
  PAID     : 13,
  DATE     : 14,
  NOTE     : 15,
};

$(function(){this.dealsPage = new DealPage()});

DealPage.prototype = new GenericPage();

/**
 * Deal page widget.
 */
function DealPage() {
  this.logoutAlertWidget = new LogoutAlertWidget();
  this.systemAlertWidget = new SystemAlertWidget();
  this.spinnerWidget = new SpinnerWidget();
  this.menuWidget = new MenuWidget();

  this.dealTableWidget = new DealTableWidget(this.userProfile);
  this.dealTable = this.dealTableWidget.dealTable;

  this.updateDealwidget = new UpdateDealWidget(this.userProfile, this.dealTable);
  this.uploadDealwidget = new UploadDealWidget(this.userProfile, this.dealTable);
  this.claimDealwidget = new ClaimDealWidget(this.userProfile, this.dealTable);
  this.verifyDealwidget = new VerifyDealWidget(this.userProfile, this.dealTable);
  this.bulkModeWidget = new BulkModeWidget(this.userProfile, this.dealTable);
  this.selectDealTypeWidget = new SelectDealTypeWidget(this.userProfile, this.dealTable);
}

/**
 * Deal table widget.
 */
function DealTableWidget(userProfile) {
  this.userProfile = userProfile;
  this.init();
}

DealTableWidget.prototype.init = function() {
  var self = this;
  
  function truncateTracking(data, type, full) {
    const MAX_TRACKING_LEN = 16;
    var tracking = full[DEAL_DATA.TRACK];
    if (tracking.length > MAX_TRACKING_LEN) {
      tracking = "***" + tracking.substr(tracking.length - MAX_TRACKING_LEN);
    }
    return tracking;
  }

  function adjustItemColumnWidth() {
    if (self.userProfile.privilege === PRIVILEGE_TYPE.NURSE)
      return "36%";
    else if (self.userProfile.privilege === PRIVILEGE_TYPE.DOCTOR)
      return "31%";
    else
      return "26%";
  }

  self.dealTable = $("#deal-table").DataTable({
    "sAjaxSource"     : "php/requestprocessor.php?requestId=10001",
    "bProcessing"     : true,
    "bServerSide"     : true,
    "iDisplayLength"  : 50,
    "bPaginate"       : true,
    "bLengthChange"   : true,
    "bFilter"         : true,
    "bSort"           : true,
    "bInfo"           : false,
    "bAutoWidth"      : false,
    "aaSorting"       : [[DEAL_DATA.DATE, "desc"], [DEAL_DATA.TRACK, "desc"]],
    "aoColumnDefs": [
      {
        "aTargets"  : [DEAL_COL.DEALID],
        "mRender"   : function(data, type, full) { return full[DEAL_DATA.DEALID]; },
        "bVisible"  : false,
      },
      {
        "aTargets"  : [DEAL_COL.OPTIONID],
        "mRender"   : function(data, type, full) { return full[DEAL_DATA.OPTIONID]; },
        "bVisible"  : false,
      },
      {
        "aTargets"  : [DEAL_COL.DOCTORID],
        "mRender"   : function(data, type, full) { return full[DEAL_DATA.DOCTORID]; },
        "bVisible"  : false,
      },
      {
        "aTargets"  : [DEAL_COL.NURSEID],
        "mRender"   : function(data, type, full) { return full[DEAL_DATA.NURSEID]; },
        "bVisible"  : false,
      },
      {
        "aTargets"  : [DEAL_COL.TRACK],
        "mRender"   : function(data, type, full) { return truncateTracking(data, type, full) },
        "bVisible"  : true,
        "sWidth"    : "18%",
      },
      {
        "aTargets"  : [DEAL_COL.NURSE],
        "mRender"   : function(data, type, full) { return full[DEAL_DATA.NURSE]; },
        "bVisible"  : !(self.userProfile.privilege==PRIVILEGE_TYPE.NURSE),
        "sWidth"    : "8%",
      },
      {
        "aTargets"  : [DEAL_COL.DOCTOR],
        "mRender"   : function(data, type, full) { return full[DEAL_DATA.DOCTOR]; },
        "bVisible"  : !(self.userProfile.privilege==PRIVILEGE_TYPE.DOCTOR),
        "sWidth"    : "8%",
      },
      {
        "aTargets"  : [DEAL_COL.PO],
        "mRender"   : function(data, type, full) { return full[DEAL_DATA.PO]; },
        "sWidth"    : "8%",
      },
      {
        "aTargets"  : [DEAL_COL.ITEM],
        "mRender"   : function(data, type, full) { return full[DEAL_DATA.ITEM]; },
        "sWidth"    : adjustItemColumnWidth(),
      },
      {
        "aTargets"  : [DEAL_COL.PRICE],
        "mRender"   : function(data, type, full) { return "$"+Number(full[DEAL_DATA.PRICE]).toFixed(2); },
        "sWidth"    : "8%",
      },
      {
        "aTargets"  : [DEAL_COL.QTY],
        "mRender"   : function(data, type, full) { return full[DEAL_DATA.QTY]; },
        "sWidth"    : "5%",
      },
      {
        "aTargets"  : [DEAL_COL.CLAIMED],
        "mRender"   : function(data, type, full) {
                        var tick = "<span value='t'>&#10004;</span>";
                        var cross = "<span value='c'>&#10006;</span>";
                        return (full[DEAL_DATA.CLAIMED]==1)?tick:cross;
                      },
        "bVisible"  : !(self.userProfile.privilege===PRIVILEGE_TYPE.NURSE),
        "sType"     : "cust-txt",
      },
      {
        "aTargets"  : [DEAL_COL.CK_CLAIMED],
        "mRender"   : function(data, type, full) {
                        var checked = "<input type='checkbox' name='claimed' checked />";
                        var unchecked = "<input type='checkbox' name='claimed' />";
                        return (full[DEAL_DATA.CLAIMED]==1)?checked:unchecked;
                      },
        "bVisible"  : false,
        "sType"     : "cust-txt",
      },
      {
        "aTargets"  : [DEAL_COL.VERIFIED],
        "mRender"   : function(data, type, full) { return full[DEAL_DATA.VERIFIED]; },
        "bSortable" : true,
        "bVisible"  : false,
      },
      {
        "aTargets"  : [DEAL_COL.PAID],
        "mRender"   : function(data, type, full) {
                        var tick = "<span value='t'>&#10004;</span>";
                        var cross = "<span value='c'>&#10006;</span>";
                        return (full[DEAL_DATA.CLAIMED]==1)?tick:cross;
                      },
        "sType"     : "cust-txt",
      },
      {
        "aTargets"  : [DEAL_COL.CK_PAID],
        "mRender"   : function(data, type, full) {
                        var checked = "<input type='checkbox' name='paid' checked />";
                        var unchecked = "<input type='checkbox' name='paid' />";
                        return (full[DEAL_DATA.PAID]==1)?checked:unchecked;
                      },
        "sType"     : "cust-txt",
        "bVisible"  : false,
      },
      {
        "aTargets"  : [DEAL_COL.DATE],
        "mRender"   : function(data, type, full) { return full[DEAL_DATA.DATE]; },
        "bVisible"  : true,
        "bSortable" : false,
        "sWidth"    : "10%",
      },
      {
        "aTargets"  : [DEAL_COL.EDIT],
        "mRender"   : function(data, type, full) { 
                        return "<img src='images/edit.png' name='update-deal' title='Update deal'>"; 
                      },
        "bSortable" : false,
        "sWidth"    : "4%",
      },
    ],

    // Mark the unverified rows in red
    // "fnRowCallback" : function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
    //    if (aData[DEAL_DATA.VERIFIED] == 0)
    //      $(nRow).css("color", "#cc0000");
    // },

    // Customize font/color of "claimed" and "paid" columns
    "createdRow" : function (row, data, index) {
      $("td > span[value='t']", row).css("color", "#00a300");
      $("td > span[value='t']", row).css("font-size", "18px");
      $("td > span[value='c']", row).css("color", "#ff0000");
      $("td > span[value='c']", row).css("font-size", "18px");
    }
  });
}

function getValue(x) {
  if (x.indexOf('input') >= 0) {
    return $(x).val();
  }         
  return x;
}

jQuery.fn.dataTableExt.oSort['cust-txt-asc'] = function (a, b) {
  var x = getValue(a);
  var y = getValue(b);
  return ((x < y) ? -1 : ((x > y) ? 1 : 0));
};

jQuery.fn.dataTableExt.oSort['cust-txt-desc'] = function (a, b) {
  var x = getValue(a);
  var y = getValue(b);
  return ((x < y) ? 1 : ((x > y) ? -1 : 0));
};

$.fn.dataTable.ext.search.push(
  function(settings, data, dataIndex) {
    var dealType = $('#deal-type-list').val();
    var isClaimed = data[12].charCodeAt(0);
    if ((dealType == 'claimed' && isClaimed == 10004)
        || (dealType == 'unclaimed' && isClaimed == 10006)
        || dealType == 'all' ) {
      return true;
    }
    return false;
  }
);

/**
 * Update deal widget.
 */
function UpdateDealWidget(userProfile, dealTable) {
  this.userProfile = userProfile;
  this.dealTable = dealTable;
  this.init();
  this.onClickUpdate();
}
  
UpdateDealWidget.prototype.initNurseList = function() {
  function onSuccess(nurses) {
    nurses.forEach (function(nurse) {
      var option = "<option value='" + nurse.id + "'>" + nurse.name + "</option>";
      $("#update-deal-popup-nurse > select").append(option);
    });
  }
  $.ajax({
    url: "php/requestprocessor.php?requestId=10010",
    type: "POST",
    dataType: "json",
    success: function(response) { onSuccess(response) },
  });
}

UpdateDealWidget.prototype.init = function() {
  var self = this;

  function onClickUpdate(obj) {
    if (self.validate()) {
      self.send();
      $(obj).dialog("close");
    }
  }
  
  self.updateDealPopup = $("#update-deal-popup").dialog({
    modal          : true,
    draggable      : false,
    resizable      : false,
    position       : {my: "center", at: "top", of: window},
    closeOnEscape  : true,
    width          : 480,
    dialogClass    : "alert",
    autoOpen       : false,
    show           : "clip",
    hide           : "clip",
    buttons        : [
                       {
                         text: "Update",
                         class: "btn-red",
                         click: function() { onClickUpdate(this) },
                       }, 
                       {
                         text: "Cancel",
                         class: "btn-red",
                         click: function() { $(this).dialog("close"); }
                       }
                     ]
  });

  self.initNurseList();
}

UpdateDealWidget.prototype.onClickUpdate = function() {
  var self = this;
  $("#deal-table tbody").on("click", "tr > td > img[name='update-deal']", function() {
    self.paint($(this).parent().parent());
  });
}  

UpdateDealWidget.prototype.resetBorder = function() {
  $("#update-deal-popup input").each(function(){
    $(this).removeClass("invalidInput");
  });
}

UpdateDealWidget.prototype.validate = function() {
  this.resetBorder();
  var isValidated = true;

  var tracking = $("#update-deal-popup-track > input").val();
  var patternTracking = /^[a-z0-9A-Z]+$/;
  if (!patternTracking.test(tracking)) {
    $("#update-deal-popup-track > input").addClass("invalidInput");
    isValidated = false;
  }

  var qty = $("#update-deal-popup-qty > input").val();
  var patternQty = /^[1-9][0-9]*$/;
  if (!patternQty.test(qty)) {
    $("#update-deal-popup-qty > input").addClass("invalidInput");
    isValidated = false;
  }

  return isValidated;
}

UpdateDealWidget.prototype.send = function() {
  var self = this;

  var data = {
    dealId   : $("#update-deal-popup-dealId > input").val(),
    optionId : $("#update-deal-popup-optionId > input").val(),
    po       : $("#update-deal-popup-po > input").val(),
    track    : $("#update-deal-popup-track > input").val(),
    qty      : $("#update-deal-popup-qty > input").val(),
    paid     : $("#update-deal-popup-paid > select").val(),
    date1    : $("#update-deal-popup-date > input").val(),
    price    : Number($("#update-deal-popup-price > input").val().replace(/[^0-9\.]+/g, "")),
    note     : $("#update-deal-popup-note > input").val(),
    claimed  : $("#update-deal-popup-claim > select").val(),
    nurseId  : $("#update-deal-popup-nurse > select").val(),
  };

  function onSuccess(response) {
    if (response.header.errcode != 0) {
      $("#generic-alert-popup").html(response.header.errmsg).dialog("open");
      return;
    }
    self.dealTable.ajax.reload();
  }
  
  $.ajax({
    url      : "php/requestprocessor.php?requestId=30001",
    type     : "POST",
    dataType : "json",
    data     : data, 
    success  : function(response){ onSuccess(response) },
    error    : function(){ $("#generic-alert-popup").html("Server error").dialog("open") } 
  });
}

// We should reset the popup every time re-using it.
UpdateDealWidget.prototype.reset = function() {
  var self= this;

  $("#update-deal-popup").find("input, select").each(function(){
    $(this).prop("disabled", true).removeClass("invalidInput").val("");
  });

  switch (self.userProfile.privilege) {
  case PRIVILEGE_TYPE.ADMIN:
    $("#update-deal-popup-track > input").prop("disabled", false);
    $("#update-deal-popup-po > input").prop("disabled", false);
    $("#update-deal-popup-qty > input").prop("disabled", false);
    $("#update-deal-popup-date > input").prop("disabled", false);
    $("#update-deal-popup-note > input").prop("disabled", false);
    $("#update-deal-popup-nurse > select").prop("disabled", false);
    $("#update-deal-popup-claim > select").prop("disabled", false);
    break;
  case PRIVILEGE_TYPE.DOCTOR:
    $("#update-deal-popup-doctor").hide();
    break;
  case PRIVILEGE_TYPE.NURSE:
    $("#update-deal-popup-nurse").hide();
    break;
  default:
    break;
  }
}

UpdateDealWidget.prototype.paint = function(tr) {
  var self = this;
  self.reset();
  var data = {
    dealId   : self.dealTable.cell(tr, DEAL_DATA.DEALID).data(),
    optionId : self.dealTable.cell(tr, DEAL_DATA.OPTIONID).data(),
    doctorId : self.dealTable.cell(tr, DEAL_DATA.DOCTORID).data(),
    nurseId  : self.dealTable.cell(tr, DEAL_DATA.NURSEID).data(),
    track    : self.dealTable.cell(tr, DEAL_DATA.TRACK).data(),
    doctor   : self.dealTable.cell(tr, DEAL_DATA.DOCTOR).data(),
    nurse    : self.dealTable.cell(tr, DEAL_DATA.NURSE).data(),
    po       : self.dealTable.cell(tr, DEAL_DATA.PO).data(),
    item     : self.dealTable.cell(tr, DEAL_DATA.ITEM).data(), 
    price    : '$'+Number(self.dealTable.cell(tr, DEAL_DATA.PRICE).data()).toFixed(2),
    qty      : self.dealTable.cell(tr, DEAL_DATA.QTY).data(),
    paid     : self.dealTable.cell(tr, DEAL_DATA.PAID).data(),
    date     : self.dealTable.cell(tr, DEAL_DATA.DATE).data(),
    note     : self.dealTable.cell(tr, DEAL_DATA.NOTE).data(),
    claimed  : self.dealTable.cell(tr, DEAL_DATA.CLAIMED).data(),
  }
  $("#update-deal-popup-dealId > input").val(data.dealId);
  $("#update-deal-popup-optionId > input").val(data.optionId);
  $("#update-deal-popup-track > input").val(data.track);
  $("#update-deal-popup-doctor > input").val(data.doctor);
  $("#update-deal-popup-nurse > select").val(data.nurseId);
  $("#update-deal-popup-po > input").val(data.po);
  $("#update-deal-popup-item > input").val(data.item);
  $("#update-deal-popup-price > input").val(data.price);
  $("#update-deal-popup-qty > input").val(data.qty);
  $("#update-deal-popup-paid > select").val(data.paid);
  $("#update-deal-popup-date > input").val(data.date);
  $("#update-deal-popup-note > input").val(data.note);
  $("#update-deal-popup-claim > select").val(data.claimed);
  $("#update-deal-popup").dialog("open");
}

/**
 * Upload deal widget.
 * User can upload multiple deals using a file. Support UPC and
 * PO# formats.
 */
function UploadDealWidget(userProfile, dealTable) {
  this.userProfile = userProfile;
  this.dealTable = dealTable;
  this.onClickUpload();
  this.initUploadDealFormatPopup();
  if (userProfile.privilege !== PRIVILEGE_TYPE.ADMIN) {
    $("#upload-deal").hide();
  }
}

UploadDealWidget.prototype.onClickUpload = function() {
  $("#upload-deal-btn").on("click", function() {
    $("#upload-deal-format-popup").dialog("open");
  });
}

UploadDealWidget.prototype.initUploadDealFormatPopup = function() {
  var self = this;

  function successCallback(obj) {
    self.dealTable.ajax.reload();
    if (obj.header.errno != 0) {
      var auxMsg = ". See the report at www.panandhuang.com/logs/upload.log";
      $("#generic-alert-popup").text(obj.header.errmsg + auxMsg);
    } else {
      $("#generic-alert-popup").html(obj.uploadInfo);
    }
    $("#generic-alert-popup").dialog("open");
  }

  function confirmBtnHandler(obj) {
    var file = $("#upload-deal-entry")[0].files[0];
    var data = new FormData();
    data.append("deals", file);
    data.append("format", $("input[name='upload-deal-format']:checked").val());
    $.ajax({
      url         : "php/requestprocessor.php?requestId=20003",
      type        : "POST",
      data        : data,
      processData : false,
      contentType : false,
      success     : function (response) { successCallback(response) },
      error       : function() { $("#generic-alert-popup").html("Server error.").dialog("open") },
      beforeSend  : function() { $(obj).dialog("close"); $("#spinner-popup").dialog("open"); },
      complete    : function() { $("#spinner-popup").dialog("close"); }
    });
  }
  
  var uploadDealFormatPopup = $("#upload-deal-format-popup").dialog({
    modal          : true,
    draggable      : false,
    resizable      : false,
    position       : {my: "center", at: "top", of: window},
    closeOnEscape  : true,
    width          : 400,
    dialogClass    : "alert",
    autoOpen       : false,
    show           : "clip",
    hide           : "clip",
    buttons        : [
                       {
                         text: "Confirm",
                         class: "btn-red",
                         click: function() { confirmBtnHandler(this) },
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
 * Claim deal widget.
 */
function ClaimDealWidget(userProfile, dealTable) {
  this.userProfile = userProfile;
  this.dealTable = dealTable;
  this.init();
  this.claimDealBtnClickHandler();

  if (userProfile.privilege === PRIVILEGE_TYPE.ADMIN) {
    $("#claim-deal").hide();
  }
}

ClaimDealWidget.prototype.init = function() {
  var self = this;

  function onUpdate(obj) {
    if ($("#claim-deal-popup-dealId > input").val() != "") {
      self.sendClaimDealRequest();
      $(obj).dialog("close");
    }
  }

  self.claimDealPopup = $("#claim-deal-popup").dialog({
    modal          : true,
    draggable      : false,
    resizable      : false,
    position       : {my: "center", at: "top", of: window},
    closeOnEscape  : true,
    width          : 480,
    dialogClass    : "alert",
    autoOpen       : false,
    show           : "clip",
    hide           : "clip",
    buttons        : [
                       {
                         text: "Update",
                         class: "btn-red",
                         click: function() { onUpdate(this) },
                       }, 
                       {
                         text: "Cancel",
                         class: "btn-red",
                         click: function() { $(this).dialog("close"); }
                       }
                     ]
  });
}

ClaimDealWidget.prototype.claimDealBtnClickHandler = function() {
  var self = this;
  $("#claim-deal-btn").on("click", function() {
    $("#claim-deal-popup").dialog("open");
    $("#claim-deal-popup-track input").val("");
    self.resetClaimDealPopup();
    $("#claim-deal-popup-track button").on("click", function() {
      $.ajax({
        url      : "./php/requestprocessor.php?requestId=10003",
        type     : "POST",
        dataType : "json",
        data     : { track: $("#claim-deal-popup-track input").val() },
        success  : function(response) { self.paint(response); },
        error    : function() { $("#generic-alert-popup").html("Server error.").dialog("open") }
      });
    });
  });
}

ClaimDealWidget.prototype.resetClaimDealPopup = function() {
  $("#claim-deal-popup-alert > span").html("&nbsp;");
  $("#claim-deal-popup-nurse > input").prop("disabled", true).val("");
  $("#claim-deal-popup-doctor > input").prop("disabled", true).val("");
  $("#claim-deal-popup-po > input").prop("disabled", true).val("");
  $("#claim-deal-popup-item > input").prop("disabled", true).val("");
  $("#claim-deal-popup-qty > input").prop("disabled", true).val("");
  $("#claim-deal-popup-price > input").prop("disabled", true).val("");
  $("#claim-deal-popup-date > input").prop("disabled", true).val("");
  $("#claim-deal-popup-note > input").prop("disabled", true).val("");
  $('#claim-deal-popup-claim > select').prop("disabled", true).val("");
}

ClaimDealWidget.prototype.paint = function(response) {
  var self = this;
  this.resetClaimDealPopup();
  if (response.header.errcode != 0) {
    $('#claim-deal-popup-alert > span').html("* This deal doesn't exist. Please try again later.");
  } else {
    $('#claim-deal-popup-track > input').val(response.track);
    $('#claim-deal-popup-dealId > input').val(response.dealId);
    $('#claim-deal-popup-nurse > input').val(response.nurseName);
    $('#claim-deal-popup-doctor > input').val(response.doctorName);
    $('#claim-deal-popup-doctor > input').val(response.doctorName);
    $('#claim-deal-popup-po > input').val(response.po);
    $('#claim-deal-popup-item > input').val(response.item);
    $('#claim-deal-popup-qty > input').val(response.qty);
    $('#claim-deal-popup-price > input').val(response.price);
    $('#claim-deal-popup-date > input').val(response.date);
    $('#claim-deal-popup-note > input').val(response.note);
    $('#claim-deal-popup-claim > select').val(response.claimed);
    if (response.claimed == 0) {
      $('#claim-deal-popup-alert > span').html("* This deal hasn't been claimed.");
      $('#claim-deal-popup-claim select').prop('disabled', false);
    } else {
      $('#claim-deal-popup-alert > span').html("* This deal has been claimed.");
      $('#claim-deal-popup-claim select').prop('disabled', true);
    }
  }
}

ClaimDealWidget.prototype.sendClaimDealRequest = function() {
  var self = this;

  var url = "php/requestprocessor.php?requestId=30003";
  var data = {
    dealId : $("#claim-deal-popup-dealId input").val(),
    claimed : $("#claim-deal-popup-claim select").val(),
  };

  function successCallback(obj) {
    if (obj.header.errcode != 0) {
      $("#generic-alert-popup").html(obj.header.errmsg).dialog("open");
    } else {
      self.dealTable.ajax.reload();
    }
  }
  
  $.ajax({
    url      : url,
    type     : "POST",
    dataType : "json",
    data     : data, 
    success  : function(response){ successCallback(response) },
    error    : function(){ $("#generic-alert-popup").html("Server error.").dialog("open") } 
  });
}

/**
 * Verify deal widget.
 */
function VerifyDealWidget(userProfile, dealTable) {
  this.userProfile = userProfile;

  if (this.userProfile.privilege !== PRIVILEGE_TYPE.ADMIN) {
    $("#verify-deal-btn").text("Claim Multiple");
  }

  this.dealTable = dealTable;
  this.verifyDealBtnClickHandler();
}

VerifyDealWidget.prototype.verifyDealBtnClickHandler = function() {
  var self = this;
  
  function successCallback(obj) {
    url ="php/requestprocessor.php?requestId=10001&" + obj.dealList; 
    self.dealTable.ajax.url(url).load();
    $("#generic-alert-popup").html(obj.header.errmsg);
    $("#generic-alert-popup").dialog("open");
  }

  $("#verify-deal-btn").on("click", function() {
    var file = $("#verify-deal-entry")[0].files[0];
    var data = new FormData();
    data.append("verify-deal", file);
    $.ajax({
      url         : "php/requestprocessor.php?requestId=30005",
      type        : "POST",
      data        : data,
      processData : false,
      contentType : false,
      success     : function(response) { successCallback(response) },
      error       : function() { $("#generic-alert-popup").html("Server error.").dialog("open") },
      beforeSend  : function() { $("#spinner-popup").dialog("open"); },
      complete    : function() { $("#spinner-popup").dialog("close"); }
    });
  });
}

/**
 * Bulk mode widget.
 */
function BulkModeWidget(userProfile, dealTable) {
  this.userProfile = userProfile;

  if (this.userProfile.privilege === PRIVILEGE_TYPE.NURSE) {
    $("#bulk-mode").hide();
  }

  this.claimDealList = [];
  this.dealTable = dealTable;
  this.bulkModeCheckboxHandler();
  this.commitBtnHandler();
  this.dealTableCheckboxHandler();
}

BulkModeWidget.prototype.bulkModeCheckboxHandler = function() {
  var self = this;
  $("#bulk-mode-entry").on("click", function() {
    // Flip the visibility.
    // Can't use toggle() here because it is not JQuery obj
    var column;
    column = self.dealTable.column(DEAL_COL.CLAIMED);
    column.visible(!column.visible());
    column = self.dealTable.column(DEAL_COL.CK_CLAIMED);
    column.visible(!column.visible());
    column = self.dealTable.column(DEAL_COL.PAID);
    column.visible(!column.visible());
    column = self.dealTable.column(DEAL_COL.CK_PAID);
    column.visible(!column.visible());

    // Set button color based on status.
    if ($("#bulk-mode-commit-btn").hasClass("btn-red")) {
      $("#bulk-mode-commit-btn").removeClass("btn-red");
      $("#bulk-mode-commit-btn").addClass("btn-disabled");
      // Set tick/cross color.
      self.dealTable.rows().iterator("row", function(context, index) {
        $(this.row(index).node()).find("span").css("font-size", "18px");
        $(this.row(index).node()).find("span[value='t']").css("color", "#00a300");
        $(this.row(index).node()).find("span[value='c']").css("color", "#ff0000");
      });
    } else if ($("#bulk-mode-commit-btn").hasClass("btn-disabled")) {
      $("#bulk-mode-commit-btn").removeClass("btn-disabled");
      $("#bulk-mode-commit-btn").addClass("btn-red");
    }
  });
}

BulkModeWidget.prototype.commitBtnHandler = function() {
  var self = this;
  $("#bulk-mode-commit-btn").on("click", function() {
    if ($("#bulk-mode-commit-btn").hasClass("btn-red")) {
      var j_itemList = JSON.stringify(self.claimDealList);
      $.ajax({
        url      : "php/requestprocessor.php?requestId=30004",
        type     : "POST",
        dataType : "json",
        data     : { itemList : j_itemList },
        success  : function(response) { location.reload() },
        error    : function(){ $("#generic-alert-popup").html("Server error.").dialog("open") } 
      });
    }
  });
}

BulkModeWidget.prototype.dealTableCheckboxHandler = function() {
  var self = this;
  $("#deal-table tbody").on("click", "tr td input", function() {
    // Get database status
    var tr = $(this).parent().parent();
    var dealId = self.dealTable.cell(tr, DEAL_DATA.DEALID).data(); 
    var claimed = self.dealTable.cell(tr, DEAL_DATA.CLAIMED).data(); 
    var paid = self.dealTable.cell(tr, DEAL_DATA.PAID).data(); 

    // Get status on current page
    var isClaimed = (tr.find("td > input[name='claimed']").is(":checked")) ? 1 : 0;
    var isPaid = (tr.find("td > input[name='paid']").is(":checked")) ? 1 : 0;

    // Check if item already exists
    var i;
    for (i=0; i<self.claimDealList.length; ++i) {
      if (self.claimDealList[i].dealId == dealId) {
        break;
      }
    }  

    // Compare status and update
    if (i != self.claimDealList.length) {
      if (isClaimed != claimed || isPaid != paid) {
        self.claimDealList[i].claimed = isClaimed;
        self.claimDealList[i].paid = isPaid;
      } else {
        self.claimDealList.splice(i, 1);
      }
    } else {
      if (isClaimed != claimed || isPaid != paid) {
        var item = new Object();
        item.dealId = dealId;
        item.claimed = isClaimed;
        item.paid = isPaid;
        self.claimDealList.push(item);
      }
    }
  });
}

/**
 * Select deal type widget.
 */
function SelectDealTypeWidget(userProfile, dealTable) {
  this.userProfile = userProfile;
  this.dealTable = dealTable;
  this.dealTypeComboboxHandler();
  if (this.userProfile.privilege === PRIVILEGE_TYPE.NURSE) {
    $("#select-deal-type").hide();
  }
  $.cookie("dealType", "all", {expires : 7, path : "/"});
}

SelectDealTypeWidget.prototype.dealTypeComboboxHandler = function() {
  var self = this;
  $("#deal-type-list").on("change", function() {
    $.cookie("dealType", $("#deal-type-list").val(), {expires:7, path:"/"});
    self.dealTable.ajax.reload();
  });
}
