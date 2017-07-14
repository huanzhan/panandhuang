<?php

/**
  * Copyright (c) 2016, Huan Zhan
  * All rights reserved.
  */

session_start();
require("requestprocessorImpl.php");
$request = new RequestProcessor();

// Return if can't connect to database.
if (!$request->con()) {
  return;
}

// Router.
switch ($_GET["requestId"])
{
  case 10001:
    $request->getDealListServer();
    break;
  case 10002:
    $request->getOptionListServer();
    break;
  case 10003:
    $request->getClaimedDeal();
    break;
  case 10004:
    $request->getDoctorList();
    break;
  case 10005:
    $request->reportDoctor();
    break;
  case 10006:
    $request->login();
    break;
  case 10007:
    $request->reportDoctorV2();
    break;
  case 10008:
    $request->getOrders();
    break;
  case 10009:
    $request->getStatementsServer();
    break;
  case 10010:
    $request->getUserList();
    break;
  case 20001:
    $request->addDeal();
    break;
  case 20002:
    $request->addOption();
    break;
  case 20003:
    $request->uploadDeals();
    break;
  case 30001:
    $request->updateDeal();
    break;
  case 30002:
    $request->updateOption();
    break;
  case 30003:
    $request->updateClaimedDeal();
    break;
  case 30004:
    $request->updateBulkMode();
    break;
  case 30005:
    $request->verifyDeals();
    break;
  case 40001:
    $request->deleteDeal();
    break;
  case 40002:
    $request->deleteOption();
    break;
  case 50001:
    $request->logout();
    break;
  case 50002:
    $request->createTrackIdHtml();
    break;
  case 50003:
    $request->plot();
    break;
  case 50004:
    $request->scanOrders();
    break;
  case 60001:
    $request->makePayment();
    break;
  default:
    break;
}

?>
