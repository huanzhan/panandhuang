/**
  * Copyright (c) 2016, Huan Zhan
  * All rights reserved.
  */

PRIVILEGE_TYPE = {
  ADMIN  : 1,
  DOCTOR : 2,
  NURSE  : 3
};

PERMISSIONS = {
  SHOW_DEAL_PAGE    : 0x00000001,
  SHOW_OPTION_PAGE  : 0x00000002,
  SHOW_BILLING_PAGE : 0x00000004,
  SHOW_CHART_PAGE   : 0x00000008,
};

GenericPage.prototype = {};

function GenericPage() {
  $(document).tooltip();
  $(".ui-dialog :button").blur();
  this.privilegeWidget = new PrivilegeWidget();
  this.userProfile = this.privilegeWidget.userProfile;
}

/**
 * Privilege widget.
 */
function PrivilegeWidget() {
  if ($.cookie("userProfile") === undefined) {
    window.open("index.php", "_self");
  }
  this.userProfile = JSON.parse($.cookie("userProfile"));
}

/**
 * Menu widget.
 */
function MenuWidget() {
  this.userProfile = JSON.parse($.cookie("userProfile"));
  switch (this.userProfile.privilege) {
    case PRIVILEGE_TYPE.ADMIN:
      if (!(this.userProfile.permission & PERMISSIONS.SHOW_BILLING_PAGE)) {
        $("#menu > li[name='billing']").hide();
      }
      break;
    case PRIVILEGE_TYPE.DOCTOR:
      break;
    case PRIVILEGE_TYPE.NURSE:
      $("#menu > li[name='options']").hide();
      break;
    default:
      break;
  }

  this.menuClickHandler();
}

MenuWidget.prototype.menuClickHandler = function() {
  $("#logout-btn").on("click", function() {
    $("#logout-alert-popup").dialog("open");
  });

  $("[href]").each(function(){
    if (this.href == window.location.href) {
      // $(this).parent().addClass("active");
      $(this).parent().css("background-color", "#e0e0e0");
    }
  });
}

function SpinnerWidget() {
  var opts = {
    lines     : 13, // The number of lines to draw
    length    : 20, // The length of each line
    width     : 10, // The line thickness
    radius    : 20, // The radius of the inner circle
    scale     : 0.4, // Scales overall size of the spinner
    corners   : 1, // Corner roundness (0..1)
    color     : "#000", // #rgb or #rrggbb or array of colors
    opacity   : 0.25, // Opacity of the lines
    rotate    : 0, // The rotation offset
    direction : 1, // 1: clockwise, -1: counterclockwise
    speed     : 1, // Rounds per second
    trail     : 60, // Afterglow percentage
    fps       : 20, // Frames per second when using setTimeout() as a fallback for CSS
    zIndex    : 2e9, // The z-index (defaults to 2000000000)
    className : "spinner", // The CSS class to assign to the spinner
    top       : "50%", // Top position relative to parent
    left      : "50%", // Left position relative to parent
    shadow    : false, // Whether to render a shadow
    hwaccel   : false, // Whether to use hardware acceleration
    position  : "absolute", // Element positioning
  };

  var target = document.getElementById("spinner");
  var spinner = new Spinner(opts).spin(target);

  var spinnerPopup = $("#spinner-popup").dialog({
    modal         : true,
    draggable     : false,
    resizable     : false,
    position      : { my: "center", at: "top", of: window },
    closeOnEscape : true,
    width         : 300,
    dialogClass   : "alert",
    autoOpen      : false,
  }).dialog("widget").find(".ui-dialog-titlebar").hide();
}

function SystemAlertWidget() {
  systemAlertPopup = $("#generic-alert-popup").dialog({
    modal       : true,
    draggable   : false,
    resizable   : false,
    position    : { my: "center", at: "top", of: window },
    width       : 400,
    dialogClass : "alert",
    autoOpen    : false,
    show        : "clip",
    hide        : "clip",
    buttons     : [
                    {
                      text: "Close",
                      class: "btn-red",
                      click: function() { $(this).dialog("close") },
                    }
                  ]
  });
}

function LogoutAlertWidget() {
  function yesHandler() {
    $.ajax({
      url      : "php/requestprocessor.php?requestId=50001",
      type     : "POST",
      dataType : "json",
      data     : "",
      success  : function() { window.open("index.php", "_self"); },
      error    : function() { alert("Error"); }
    });
  }

  var logoutAlertPopup = $("#logout-alert-popup").dialog({
    modal       : true,
    draggable   : false,
    resizable   : false,
    position    : { my: "center", at: "top", of: window },
    width       : 400,
    dialogClass : "alert",
    autoOpen    : false,
    show        : "clip",
    hide        : "clip",
    buttons     : [
                    {
                      text: "Yes",
                      class: "btn-red",
                      click: function() { yesHandler(this) },
                    }, 
                    {
                      text: "No",
                      class: "btn-red",
                      click: function() { $(this).dialog("close"); }
                    }
                  ]
    
  });
}



