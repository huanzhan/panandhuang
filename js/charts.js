/**
  * Copyright (c) 2016, Huan Zhan
  * All rights reserved.
  */

$(function() {this.chartPage = new ChartPage()});

ChartPage.prototype = new GenericPage();

function ChartPage() {
  this.logoutAlertWidget = new LogoutAlertWidget();
  this.systemAlertWidget = new SystemAlertWidget();
  this.spinnerWidget = new SpinnerWidget;
  this.menuWidget = new MenuWidget();
  this.chartWidget = new ChartWidget();
}

/**
 * Chart widget.
 */
function ChartWidget() {
  this.initChart();
}

ChartWidget.prototype.initChart = function() {
  var self = this;
  $.ajax({
    url      : "php/requestprocessor.php?requestId=50003",
    type     : "POST",
    dataType : "json",
    data     : "",
    success  : function(response) { self.plot(response) },
    error    : function(){ $("#generic-alert-popup").html("Server error.").dialog("open") } 
  });
}

ChartWidget.prototype.plot = function(response) {
  // data is in the format of array[][]. array[i] represents each point
  // on the curve. array[0] is x coordinate, and array[1] is y coordinate
  var dataSet = [];
  var yData = [];
  for (var i=0; i<response.length; ++i) {
    // convert date to flot date type
    xDate = (new Date(response[i]["year"], response[i]["month"], response[i]["day"])).getTime();
    dataSet.push([xDate, response[i]["qty"]]);
    yData.push(response[i]["qty"]);
  }
  var maxYData = Math.max.apply(null, yData);
  var yOrder = Math.floor(Math.log(maxYData)/Math.LN10+0.000000001);
  var yTickerSize = Math.pow(10, yOrder)/2;

  // generate data for plot
  var data = [];
  data.push({
    "label" : "Weekly Trading Volume",
    "data" : dataSet
  });

  // options is for controlling the format of plot
  var options = {
    lines : {
      show : true,
      fill : true,
      lineWidth : 1,
      // fillColor is the color of area between the curve and X axis.
      // it can be set as a single color or a color gradient.
      fillColor : {colors : [{opacity : 0.2}, {opacity : 0.05}]}
    },

    points : {
      show : false,
      radius : 3,
    },

    series : {},

    bars : {
      show : false,
      // for time mode, base unit of barWidth is 1 ms
      barWidth : 1000*60*60*24*6,
    },

    // colors controls the color of curves. Each element of the array
    // corresponds to one curve (series)
    colors : ["#FF9900"],
    // colors : ["#FF9900", "#FFFFFF"],

    // time mode requires jquery.flot.time.js
    // axis setting requires jquery.flot.axislabels.js
    xaxis : {
      mode : "time",
      min : dataSet[0][0],
      max : dataSet[dataSet.length-1][0],
      timeformat : "%Y/%m", // %Y-%m-%d
      // tickDecimals : 0,
      tickSize : [3, "month"], // day/month/year
      tickLength : 0,
      color : "#000",
      axisLabel : "Date",
      axisLabelUseCanvas: true,
      axisLabelFontSizePixels: 14,
      axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
      axisLabelPadding: 15,
      // tickColor : "#C2C2A3",
    },

    yaxis : {
      color : "black",
      tickDecimals : 0, // number of decimals, not for time mode
      tickSize : yTickerSize, // distance between ticks
      axisLabel : "Trading Volume",
      axisLabelUseCanvas : true,
      axisLabelFontSizePixels : 14,
      axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
      axisLabelPadding : 5,
      tickColor : "#C2C2A3", // color of tick line
    },

    grid : {
      color : "#000", // color of legend?
      borderColor : "#000", // border color
      backgroundColor : "#FFFFFF", // background color except the filling area
      borderWidth : 2, // border width
      autoHighlight : true,
      clickable : true,
      hoverable : true,
      aboveData : false,
    },

    tick : {
      color : "#fff",
    },  
  };

  // set the placeholder of  plot
  var placeholder = $("#chart-holder");
  $.plot(placeholder, data, options);

  // Add tooltip to curve points when hovering over.
  $("#chart-holder").bind("plothover", function (event, pos, item) {
    if (item) {
      var x = new Date(item.datapoint[0]);
      var d = x.getFullYear() + "/" + (x.getMonth()+1) + "/" + x.getDay();
      var y = item.datapoint[1].toFixed(0);
      $("#tooltip").html("Date: "+d+"<br/>"+"Qty: "+y)
        .css({top: item.pageY+5, left: item.pageX+5})
        .fadeIn(200);
    } else {
      $("#tooltip").hide();
    }
  });

  // Define style of tooltip.
  $("<div id='tooltip'></div>").css({
    fontSize: "12px",
    position : "absolute",
    display : "none",
    border : "2px solid gray",
    padding : "2px",
    "background-color" : "#fee",
    opacity : 0.80
  }).appendTo("body");
}
