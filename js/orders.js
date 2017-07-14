// Copyright (c) 2016, Huan Zhan
// All rights reserved.
//
// Author: Huan Zhan
// Date: March 2016

var ORDER_COL = {
	ORDER_ID    : 0,
	ORDER_DATE  : 1,
	TRACKING	  : 2,
	SHIP_DATE	  : 3,
	CANCELED		: 4,
	CANCEL_DATE : 5,
	BUY_PRICE   : 6,
	SELL_PRICE  : 7,
	CASH_BACK   : 8,
	ITEM        : 9,
	EDIT        : 10,
};

var ORDER_DATA = {
	ORDER_ID    : 0,
	ORDER_DATE  : 1,
	TRACKING	  : 2,
	SHIP_DATE	  : 3,
	CANCELED		: 4,
	CANCEL_DATE : 5,
	BUY_PRICE   : 6,
	SELL_PRICE  : 7,
	CASH_BACK   : 8,
	ITEM        : 9,
};

$( function() {

	var ordersPage = new OrdersPage();

  ordersPage.checkPrivilege();
	ordersPage.initPage();
	ordersPage.setWidgets();
	ordersPage.setEventHandlers();
});

function OrdersPage()
{
	var orderTable;
}

OrdersPage.prototype = new GenericPage();

OrdersPage.prototype.initPage = function()
{
	var self = this;

	self.userProfile = JSON.parse( $.cookie("userProfile") );

	// Set current page tab color
	$( "#menu > li[name='orders']" ).css( "background-color", "#E0E0E0" );
}

OrdersPage.prototype.setWidgets = function()
{
	this.setOrderTable();
	this.setGenericAlertPopup();
	this.setLogOutAlertPopup();
	this.setSpinner();
}

OrdersPage.prototype.setEventHandlers = function()
{
	this.menuEventHandler();
	this.updateButtonHandler();
}

OrdersPage.prototype.updateButtonHandler = function()
{
	$( "#scan-button" ).on( "click", function() {

		var jData = {
			email : $( "#email" ).val(),
			password : $( "#password" ).val(),
		};

		$.ajax({
			url				  : "php/requestprocessor.php?requestId=50004",
			type			  : "POST",
			dataType    : "json",
			data			  : jData,
			success     : function( response )
		                {
											$("#spinner-popup").dialog("close");
										  this.orderTable.ajax.reload();	
								    },
			error	      : function() {
											$("#spinner-popup").dialog("close");
											alert("Error");
										},
	    beforeSend  : function() { $("#spinner-popup").dialog("open"); },
			complete    : function() {}
		});
	});
}

OrdersPage.prototype.setOrderTable = function()
{
	var self = this;

	self.orderTable = $("#order-table").DataTable({
		"sAjaxSource"			:	"php/requestprocessor.php?requestId=10008",
		"sAjaxDataProp"		:	"",
		"bProcessing"			: true,
		// "bServerSide"     : true,
		"iDisplayLength"	: 50,
		"bPaginate"				: true,
		"bLengthChange"		: true,
		"bFilter"					: true,
		"bSort"						: true,
		"bInfo"						: false,
		"bAutoWidth"			: false,
		"aaSorting"				: [[3, "asc"], [1, "desc"]],

		"aoColumnDefs"		: [
		  {
				"aTargets"	: [ORDER_COL.ORDER_ID],
			  "mRender"		: function(data, type, full) { return full[ORDER_DATA.ORDER_ID]; },
			},
		  {
				"aTargets"	: [ORDER_COL.ORDER_DATE],
			  "mRender"		: function(data, type, full) { return full[ORDER_DATA.ORDER_DATE]; },
			},
		  {
				"aTargets"	: [ORDER_COL.TRACKING],
			  "mRender"		: function(data, type, full) { return full[ORDER_DATA.TRACKING]===null?"N/A":full[ORDER_DATA.TRACKING]; },
			},
		  {
				"aTargets"	: [ORDER_COL.SHIP_DATE],
			  "mRender"		: function(data, type, full) { return full[ORDER_DATA.SHIP_DATE]===null?"N/A":full[ORDER_DATA.SHIP_DATE]; },
			},
		  {
				"aTargets"	: [ORDER_COL.CANCELED],
			  "mRender"		: function(data, type, full) { return full[ORDER_DATA.CANCELED]; },
				"bVisible"  : false,
			},
		  {
				"aTargets"	: [ORDER_COL.CANCEL_DATE],
			  "mRender"		: function(data, type, full) { return full[ORDER_DATA.CANCEL_DATE]===null?"N/A":full[ORDER_DATA.CANCEL_DATE]; },
			},
		  {
				"aTargets"	: [ORDER_COL.BUY_PRICE],
			  "mRender"		: function(data, type, full) { return full[ORDER_DATA.BUY_PRICE]; },
			},
		  {
				"aTargets"	: [ORDER_COL.SELL_PRICE],
			  "mRender"		: function(data, type, full) { return full[ORDER_DATA.SELL_PRICE]; },
			},
		  {
				"aTargets"	: [ORDER_COL.CASH_BACK],
			  "mRender"		: function(data, type, full) { return full[ORDER_DATA.CASH_BACK]; },
			},
		  {
				"aTargets"	: [ORDER_COL.ITEM],
			  "mRender"		: function(data, type, full) { return full[ORDER_DATA.ITEM]===null?"N/A":full[ORDER_DATA.ITEM]; },
				"bVisible"  : false,
			},
		  {
				"aTargets"	: [ORDER_COL.EDIT],
			  "mRender"		: function(data, type, full) { return "<img src='images/edit.png' name='update-order' title='Update order'>"; },
			  "bSortable" : false
			},
    ],
  });
}

