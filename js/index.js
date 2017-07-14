/**
  * Copyright (c) 2016, Huan Zhan
  * All rights reserved.
  */

$(function() {
  var indexPage = new IndexPage();
  indexPage.initPage();
  indexPage.setEventHandlers();
});

function IndexPage() {}

IndexPage.prototype.initPage = function()
{
  $("#login-error").hide();
  if ($.cookie("userProfile") === undefined) {
    $("#username input").focus();
  } else {
    window.open("deals.php", "_self");
  }
}

IndexPage.prototype.setEventHandlers = function()
{
  $("#login-buttons button").on("click", function() {
    
    function successHandler(obj) {
      if (obj.header.errcode != 0) {
        $("#login-error").show();
      } else {
        window.open("deals.php", "_self");
      }
    }
    
    var data = { 
      username: $("#username input").val(),
      password: $("#password input").val()
    };

    $.ajax({
      url      : "php/requestprocessor.php?requestId=10006",
      type     : "POST",
      dataType : "json",
      data     : data,
      success  : function (response) { successHandler(response) },
      error    : function(){ $("#generic-alert-popup").html("Server error.").dialog("open") } 
    });
  });

  // Bind key "Enter" to "Logon" button
  $(document).keyup(function(e) {
    var code = e.keyCode ? e.keyCode : e.which;
    if (code === 13) {
      $("#login-buttons button").click();
    }
  });
}

