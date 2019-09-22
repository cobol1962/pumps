<?php
  session_start();
  if (!isset($_SERVER["HTTP_REFERER"])) {
    header('HTTP/1.0 403 Forbidden', true, 403);
    die;
  }
  if (!strpos($_SERVER["HTTP_REFERER"], $_SERVER['HTTP_HOST'])) {
    header('HTTP/1.0 403 Forbidden', true, 403);
    die;
  }
  include "../config/database.php";
  header('Content-Type: application/json');
  $action = $_GET["request"];

  $r = $action($_POST, $mysqli);
  echo json_encode($r);
  exit;
  function login($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    if ($user_type == "1") {
      $sql = "SELECT * from `admin` WHERE `username`='" . $post["username"] . "' AND `password`='" . $post["password"] . "'";
      $result = $mysqli->query($sql);
      if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        $row["siteid"] = "0";
        $row["user_type"] = "1";
        $row["status"] = "ok";
        return $row;
      }
    }
    if ($user_type == "0") {
      $sql = "SELECT * from `users` WHERE `site`='" . $post["username"] . "' AND `password`='" . $post["password"] . "' and `active`='1'";
      $result = $mysqli->query($sql);
      if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        $row["siteid"] = $row["id"];
        $row["user_type"] = "0";
        $row["status"] = "ok";
        return $row;
      }
    }
    $row = [];

    $row["status"] = "fail";
    $row["error"] = "Invalid username/password.";
    return $row;
  }
  function createUser($post, $mysqli) {
    $nms = [];
    $vls = [];

    foreach($post as $k => $v) {
      $nms[count($nms)] = "`" . $k . "`";
      $vls[count($vls)] = "'" . $v . "'";
    }

    $names = "(" . implode(",", $nms) . ")";
    $values = "("  . implode(",", $vls) . ")";
    $sql = "insert into `users` " . $names . " VALUES " . $values;
    $result = $mysqli->query($sql);

    $row = [];
    if (mysqli_insert_id($mysqli) != 0) {
      $row["status"] = "ok";
      $row["siteid"] = $mysqli->insert_id;
    } else {
      $row["status"] = "fail";
      $row["error"] = mysqli_error($mysqli);
    }
    return $row;
  }
  function createOil($post, $mysqli) {
    $nms = [];
    $vls = [];

    foreach($post as $k => $v) {
      $nms[count($nms)] = "`" . $k . "`";
      $vls[count($vls)] = "'" . $v . "'";
    }

    $names = "(" . implode(",", $nms) . ")";
    $values = "("  . implode(",", $vls) . ")";
    $sql = "insert into `oil_lottery` " . $names . " VALUES " . $values;
    $result = $mysqli->query($sql);

    $row = [];
    if (mysqli_insert_id($mysqli) != 0) {
      $row["status"] = "ok";
      $row["siteid"] = $mysqli->insert_id;
    } else {
      $row["status"] = "fail";
      $row["error"] = mysqli_error($mysqli);
    }
    return $row;
  }
  function editUser($post, $mysqli) {
    $nms = [];
    foreach($post as $k => $v) {
      if ($k != "id") {
        $nms[count($nms)] = "`" . $k . "`='" . $v . "'";
      }
    }
    $names =  "SET " . implode(",", $nms);
    $sql = "update `users` " . $names . " Where `id`='" . $post["id"] . "'";
    $result = $mysqli->query($sql);
    $row = [];
    if (!mysqli_query($mysqli,$sql)) {
      $row["status"] = "fail";
      $row["error"] = mysqli_error($mysqli);
    } else {
      $row["status"] = "ok";
    }
    return $row;
  }
  function editOil($post, $mysqli) {
    $nms = [];
    foreach($post as $k => $v) {
      if ($k != "oilid") {
        $nms[count($nms)] = "`" . $k . "`='" . $v . "'";
      }
    }
    $names =  "SET " . implode(",", $nms);
    $sql = "update `oil_lottery` " . $names . " Where `oilid`='" . $post["oilid"] . "'";
    $result = $mysqli->query($sql);
    $row = [];
    if (!mysqli_query($mysqli,$sql)) {
      $row["status"] = "fail";
      $row["error"] = mysqli_error($mysqli);
    } else {
      $row["status"] = "ok";
    }
    return $row;
  }
  function deactivateUser($post, $mysqli) {

    $sql = "update `users` set `active`='0' Where `id`='" . $post["id"] . "'";
    $result = $mysqli->query($sql);
    $row = [];
    if (!mysqli_query($mysqli,$sql)) {
      $row["status"] = "fail";
      $row["error"] = mysqli_error($mysqli);
    } else {
      $row["status"] = "ok";
    }
    return $row;
  }
  function deactivateOil($post, $mysqli) {
    $sql = "update `oil_lottery` set `active`='0' Where `oilid`='" . $post["oilid"] . "'";
    echo $sql;
    $result = $mysqli->query($sql);
    $row = [];
    if (!mysqli_query($mysqli,$sql)) {
      $row["status"] = "fail";
      $row["error"] = mysqli_error($mysqli);
    } else {
      $row["status"] = "ok";
    }
    return $row;
  }
  function getFuel($post, $mysqli) {
    $rows = [];
    $sql = "select * from `fuel`";
    $result = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($result)) {
      $rows[count($rows)] = $row;
    }
    return $rows;
  }
  function addFuel($post, $mysqli) {
    $sql = "insert into `fuel` (`grade`,`initialprice`) Values ('" . $post["grade"] . "','" . $post["initialprice"] . "')";
    $row = [];
    if (!mysqli_query($mysqli,$sql)) {
      $row["status"] = "fail";
      $row["error"] = mysqli_error($mysqli);
    } else {
      $row["status"] = "ok";
      $row["id"] = $mysqli->insert_id;
      $row["price"] = $post["initialprice"];
    }
    return $row;
  }
  function updateFuel($post, $mysqli) {
    $sql = "update `fuel` SET `grade`='" . $post["grade"] . "', `initialprice`='" . $post["initialprice"] . "' WHERE `id`='" . $post["id"] . "'";
    $row = [];
    if (!mysqli_query($mysqli,$sql)) {
      $row["status"] = "fail";
      $row["error"] = mysqli_error($mysqli);
    } else {
      $row["status"] = "ok";
    }
    return $row;
  }
  function deleteFuel($post, $mysqli) {
    $sql = "delete from `fuel`  WHERE `id`='" . $post["id"] . "'";
    $row = [];
    if (!mysqli_query($mysqli,$sql)) {
      $row["status"] = "fail";
      $row["error"] = mysqli_error($mysqli);
    } else {
      $row["status"] = "ok";
    }
    return $row;
  }
  function createPricetable($post, $mysqli) {
    $resp = [];
    $sql = "select *  from `pricelist`  WHERE `date`='" . $post["date"] . "'";
    $result = $mysqli->query($sql);
    if ($result->num_rows == 0) {
      $f_sql = "select *  from `fuel`";
      $s_sql = "select *  from `users` where `active`=1";
      $s_res = $mysqli->query($s_sql);
      while ($r_site = mysqli_fetch_assoc($s_res)) {
        $r_fuel = $mysqli->query($f_sql);
        while ($row_fuel = mysqli_fetch_assoc($r_fuel)) {
            $i_sql = "insert into `pricelist` (`date`,`siteid`,`fuelid`, `price`) values ";
            $i_sql .= "('" . $post["date"] . "','" . $r_site["id"] . "','" . $row_fuel["id"] . "','" . $row_fuel["initialprice"] . "')";
            $mysqli->query($i_sql);
        }
      }
    #  $max_sql = "SELECT max(date) as d, `siteid`,`fuelid`,`price` from `pricelist` where `date`<'" . $post["date"] . "' group by `siteid`,`fuelid`";
      $max_sql = "SELECT *
                  FROM `pricelist` t1
                  WHERE t1.date = (SELECT MAX(t2.date)
                 FROM pricelist t2
                 WHERE t2.fuelid = t1.fuelid and date<'" . $post["date"] . "')";


      $max_res = $mysqli->query($max_sql);
      while ($max_row = mysqli_fetch_assoc($max_res)) {
        $up_price_sql = "update pricelist set `price`=" . $max_row["price"] . " where `date`='" . $post["date"] . "' and `siteid`='" . $max_row["siteid"] . "' AND `fuelid`='" . $max_row["fuelid"] . "'";
        $mysqli->query($up_price_sql);

      }

    }
    $i_str = "";
    if (isset($post["sites"])) {
      $i_str = " AND `siteid` IN (" . $post["sites"]  . ") ";
    }
    $sql = "SELECT `date`,`fuelid`,`siteid`,`price`, `fuel`.`grade`, `users`.`site` FROM `pricelist`
            left join `fuel` on `pricelist`.`fuelid`=`fuel`.`id`
            left join `users` on `pricelist`.`siteid`=`users`.`id`
            WHERE `date`='" . $post["date"] . "'" . $i_str . " order by `fuelid`,`siteid`";

    $result = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($result)) {
      $resp["records"][count($resp["records"])] = $row;
    }
    $f_sql = "select *  from `fuel`";
    $r_fuel = $mysqli->query($f_sql);
    while ($row = mysqli_fetch_assoc($r_fuel)) {
      $resp["fuel"][count($resp["fuel"])] = $row;
    }
    $f_sql = "select *  from `users` where `active`=1";
    $r_fuel = $mysqli->query($f_sql);
    while ($row = mysqli_fetch_assoc($r_fuel)) {
      $resp["sites"][count($resp["sites"])] = $row;
    }
    return $resp;
  }
  function updateFuelPrice($post, $mysqli) {
    $sql = "update `pricelist` set `price`='" . $post["price"] . "' WHERE `date`='" . $post["date"] . "' AND `fuelid`='" . $post["fuelid"] . "' AND `siteid`='" . $post["siteid"] . "'";
    $row = [];
    if (!mysqli_query($mysqli,$sql)) {
      $row["status"] = "fail";
      $row["error"] = mysqli_error($mysqli);
    } else {
      $row["status"] = "ok";
    }
    return $row;
  }
  function generateNewFuelPrices($post, $mysqli) {
    if ($post["siteid"] == "all") {
      $s_s = "select `id` from `users` where `active`=1";
    } else {
      $s_s = "select `id` from `users` where `active`=1 and `id`='" . $post["siteid"] . "'";
    }
    $result = $mysqli->query($s_s);
    while ($row = mysqli_fetch_assoc($result)) {
      $i_q = "insert into `pricelist` (`date`, `siteid`, `fuelid`, `price`) VALUES ('" . $post["date"] . "','" . $row["id"] . "','" . $post["fuelid"] . "','" . $post["price"] . "')";
      $mysqli->query($i_q);
    }
    $row = [];
    $row["status"] = "ok";
    return $row;
  }
  function generateNewSitePrices($post, $mysqli) {
    $f_sql = "select *  from `fuel`";
    $f_res = $mysqli->query($f_sql);
    while ($r_f = mysqli_fetch_assoc($f_res)) {
        $i_sql = "insert into `pricelist` (`date`,`siteid`,`fuelid`, `price`) values ";
        $i_sql .= "('" . $post["date"] . "','" . $post["siteid"] . "','" . $r_f["id"] . "','" . $r_f["initialprice"] . "')";
        $mysqli->query($i_sql);
    }
    $row = [];
    $row["status"] = "ok";
    return $row;
  }
  function getSitePumps($post, $mysqli) {
    $res = [];
    $sql = "select * from `site_pumps` where siteid='" . $post["siteid"] . "' AND `active`=1 order by `pumpid`";

    $result = $mysqli->query($sql);
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
      $res["records"] = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $res["records"][count($res["records"])] = $row;
      }
    }
    return $res;
  }
  function insertSitePump($post, $mysqli) {
    $res = [];
    $sql = "insert into `site_pumps` (`siteid`, `label`,`date_added`) Values ('" . $post["siteid"] . "','" . $post["label"] . "','" . $post["date_added"] . "')";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function deletePump($post, $mysqli) {
    $sql = "update `site_pumps` set `active`=0 where `pumpid`='" . $post["pumpid"] . "'";
    $mysqli->query($sql);

    $res = ["status" => "ok"];
    return $res;
  }
  function getNozzles($post, $mysqli) {
    $res = [];
    $sql = "select * from `nozzles` where `pumpid`='" . $post["pumpid"] . "' AND `active`=1 order by `nozzleid`";

    $result = $mysqli->query($sql);
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
      $res["records"] = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $res["records"][count($res["records"])] = $row;
      }
    }
    return $res;
  }
  function insertNozzle($post, $mysqli) {
    $res = [];
    $sql = "insert into `nozzles` (`siteid`, `pumpid`, `fuelid`, `start`) Values ('" . $post["siteid"] . "','" . $post["pumpid"] . "','" . $post["fuelid"] . "','" . $post["start"] . "')";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
      $res["nozzleid"] = $mysqli->insert_id;
    }
    return $res;
  }
  function updateNozzle($post, $mysqli) {
    $res = [];
    $sql = "update `nozzles` set `fuelid`='". $post["fuelid"] . "',`start`='" . $post["start"] . "' WHERE `nozzleid`='". $post["nozzleid"] . "'";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function deleteNozzle($post, $mysqli) {
    $res = [];
    $sql = "update `nozzles` set `active`=0 WHERE `nozzleid`='". $post["nozzleid"] . "'";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function changePassword($post, $mysqli) {
    $res = [];
    $sql = "update `users` set `password`='" . $post["password"] . "' WHERE `id`='". $post["id"] . "'";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function getSiteConfiguration($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $res["fuels"] = [];
    $sql = "select * from `fuel`";
    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["fuels"][count($res["fuels"])] = $row;
    }

    $res["pumps"] = [];
    $sql = "select * from `site_pumps` where `siteid`='$siteid'";
    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["pumps"][count($res["pumps"])] = $row;
    }

    $res["nozzles"] = [];
    $sql = "SELECT `nozzles`.*, `fuel`.`grade` FROM `nozzles`
            left join `fuel` on `nozzles`.`fuelid`=`fuel`.`id`
            WHERE `nozzles`.`siteid`='$siteid'";
    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["nozzles"][count($res["nozzles"])] = $row;
    }

    $res["fuelprices"] = [];
    $sql = "SELECT *
                FROM `pricelist` t1
                WHERE t1.date = (SELECT MAX(t2.date)
               FROM pricelist t2
               WHERE t2.fuelid = t1.fuelid and t1.`siteid`='$siteid' and t2.`date`<='" . $post["date"] . "')";
   $res["sql"] = $sql;
    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      if ($row["siteid"] = $siteid) {
        $res["fuelprices"][count($res["fuelprices"])] = $row;
      }
    }

    $res["lastsale"] = [];
    $sql = "SELECT *
                FROM `fuel_sales` t1
                WHERE t1.date = (SELECT MAX(t2.date)
               FROM fuel_sales t2
               WHERE t2.fuelid = t1.fuelid and t1.`siteid`='$siteid' and t1.`date`<'" . $post["date"] . "')";


    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["lastsale"][count($res["lastsale"])] = $row;
    }

    $res["lastservicesale"] = [];
    $sql = "SELECT *
                FROM `services_sales` t1
                WHERE t1.date = (SELECT MAX(t2.date)
               FROM `services_sales` t2
               WHERE t2.counterid = t1.counterid and t1.`siteid`='$siteid' and t1.`date`<'" . $post["date"] . "')";


    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["lastservicesale"][] = $row;
    }


    $res["services"] = [];
    $sql = "select * from `site_services` where `siteid`='$siteid' AND `active`=1";

    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["services"][count($res["services"])] = $row;
    }

    $res["counters"] = [];
    $sql = "SELECT * FROM `site_services_counters`
            WHERE `siteid`='$siteid'";
            $res["sql1"] = $sql;
    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["counters"][] = $row;
    }

    $res["fuelsales"] = [];
    $sql = "select * from `fuel_sales` where `siteid`='$siteid' AND `date`='$date'";

    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["fuelsales"][count($res["fuelsales"])] = $row;
    }

    $res["servicesales"] = [];
    $sql = "select * from `services_sales` where `siteid`='$siteid' AND `date`='$date'";

    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["servicesales"][] = $row;
    }

    $res["status"] = "ok";
    return $res;
  }
  function insertOrUpdateSale($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $sql = "insert into fuel_sales (`date`,`siteid`,`fuelid`,`nozzleid`,`start`,`counter`,`end`,`price`,`value`)
              VALUES ('$date','$siteid','$fuelid','$nozzleid','$start','$counter','$end','$price','$value')
              ON DUPLICATE KEY UPDATE
              `fuelid`='$fuelid', `start`='$start', `counter`='$counter',`end`='$end',`price`=$price,`value`='$value'";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function oilSpecialPrices($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $sit = implode(",", $sites);

    $res = [];
    $res["error"] = [];
    $res["sql"] = [];
    foreach ($sites as $s) {
      $sql = "insert into `sites_oil_lottery` values ('$s', '$oilid','$price','$discount','$active','$special_offer','$special_offer_text')
      ON DUPLICATE KEY UPDATE  `price`='$price', `discount`='$discount', `active`='$active'";
      $res["sql"][count($res["sql"])] = $sql;
      if (!mysqli_query($mysqli,$sql)) {
        $res["error"][count($res["error"])] = mysqli_error($mysqli);
      }
    }
    if (count($res["error"]) == 0) {
      $res["status"] = "ok";
    } else {
      $res["status"] = "fail";
    }
    return $res;
  }
  function deleteOilSettings($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $sql = "delete from `sites_oil_lottery` where `oilid`='$oilid'";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function getOils($post, $mysqli) {
    $res = [];
    $sql = "select * from `oil_lottery`";

    $result = $mysqli->query($sql);
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
      $res["records"] = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $res["records"][count($res["records"])] = $row;
      }
    }
    return $res;
  }
  function deleteOilActions($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $sit = implode(",", $sites);
    $ols = implode(",", $oils);
    $ins = [];
    if (strlen($sit) > 0) {
      $ins[] = "`siteid` IN ($sit)";
    }
    if (strlen($ols) > 0) {
      $ins[] = "`oilid` IN ($ols)";
    }
    $w = implode(" AND ", $ins);
    $res = [];
    $sql = "delete from `sites_oil_lottery` where $w";
    $res["sql"] = $sql;
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function createOilActions($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $res["sql"] = [];
    foreach($sites as $s) {
      foreach($oils as $o) {
        $sql = "insert into `sites_oil_lottery` (`siteid`,`oilid`,`discount`,`special_offer_text`) values ('$s', '$o','$discount','$special_offer_text')
        ON DUPLICATE KEY UPDATE   `discount`='$discount'";
        $mysqli->query($sql);
      }
    }
    $sql = "UPDATE sites_oil_lottery s
                       INNER JOIN oil_lottery AS o
                       ON s.oilid = o.oilid
                       SET s.price = o.price
                       WHERE s.price = 0";
   $mysqli->query($sql);
    $res["status"] = "ok";
    return $res;
  }
  function getSiteOil($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $res["records"] = [];
    $res["sales"] = [];
    $sql = "select * from `oil_lottery` where `date_added`<='$date' order by `type`";
    $res["sql"] = $sql;
    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["records"][$row["oilid"]] = $row;
    }

    $sql = "select * from `sites_oil_lottery` where `siteid`='$siteid'";
    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      if (isset($res["records"][$row["oilid"]])) {
        if ($row["price"] != 0) {
          $res["records"][$row["oilid"]]["price"] = $row["price"];
        }
        $res["records"][$row["oilid"]]["discount"] = $row["discount"];
        $res["records"][$row["oilid"]]["active"] = $row["active"];
      }
    }
    $sql = "SELECT `sales_oil_lottery`.*, `oil_lottery`.`name`,`oil_lottery`.`type`
        FROM `sales_oil_lottery`
        LEFT JOIN `oil_lottery`
        ON `sales_oil_lottery`.`oilid` = `oil_lottery`.`oilid`
        where `sales_oil_lottery`.`siteid`=$siteid and `sales_oil_lottery`.`date`='$date'";
    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["sales"][$row["oilid"]] = $row;
    }

    $res["lastsales"] = [];
    $sql = "SELECT *
                FROM `sales_oil_lottery` t1
                WHERE t1.date = (SELECT MAX(t2.date)
               FROM `sales_oil_lottery` t2
               WHERE t2.oilid = t1.oilid and t1.`siteid`='$siteid' and t1.`date`<'" . $post["date"] . "')";


    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["lastsales"][$row["oilid"]] = $row;
    }

    $res["status"] = "ok";
    return $res;
  }
  function insertUpdateOilSale($post, $mysqli) {
    $res = [];
    $insert= [];
    $update = [];
    foreach ($post as $k => $v) {
      $insert[] = "'" . $v . "'";
      $update[] = "`" . $k . "`='" . $v . "'";
    }
    $i = implode(",", $insert);
    $u = implode(",", $update);
    $sql = "insert into `sales_oil_lottery` values($i) ON DUPLICATE KEY UPDATE $u";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
      $res["sql"] = $sql;
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function getSiteServices($post, $mysqli) {
    $res = [];
    $sql = "select * from `site_services` where siteid='" . $post["siteid"] . "' and `active`=1 order by `serviceid`";

    $result = $mysqli->query($sql);
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
      $res["records"] = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $res["records"][count($res["records"])] = $row;
      }
    }
    return $res;
  }
  function insertSiteService($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $sql = "insert into `site_services` (`siteid`, `label`,`counters`,`date_added`) Values ('" . $post["siteid"] . "','" . $post["label"] . "','$counters','" . $post["date_added"] . "')";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function getServiceCounters($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $sql = "select * from `site_services_counters` where `serviceid`='$serviceid' order by `counterid`";

    $result = $mysqli->query($sql);
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
      $res["records"] = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $res["records"][count($res["records"])] = $row;
      }
    }
    return $res;
  }
  function insertUpdateCounter($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    if ($counterid == "-1") {
      $sql = "insert into `site_services_counters` (`siteid`,`serviceid`, `price`,`label`,`start`) Values ('$siteid','$serviceid','$price','$label','$start')";
      if (!mysqli_query($mysqli,$sql)) {
        $res["status"] = "fail";
        $res["error"] = mysqli_error($mysqli);
      } else {
        $res["status"] = "ok";
        $res["counterid"] = $mysqli->insert_id;
      }
      return $res;
    } else {
      $sql = "update `site_services_counters` set `price`='$price', `label`='$label', `start`='$start' where `counterid`='$counterid'";
      if (!mysqli_query($mysqli,$sql)) {
        $res["status"] = "fail";
        $res["error"] = mysqli_error($mysqli);
      } else {
        $res["status"] = "ok";
        $res["counterid"] = $counterid;
      }
      return $res;
    }
  }
  function deleteService($post, $mysqli) {
    $sql = "update `site_services` set `active`=0 where `serviceid`='" . $post["serviceid"] . "'";
    $mysqli->query($sql);
    $res = ["status" => "ok"];
    return $res;
  }
  function insertOrUpdateServiceSale($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $sql = "insert into `services_sales` (`date`,`siteid`,`counterid`,`start`,`counter`,`end`,`price`,`value`)
              VALUES ('$date','$siteid','$counterid','$start','$counter','$end','$price','$value')
              ON DUPLICATE KEY UPDATE
              `start`='$start', `counter`='$counter',`end`='$end',`price`=$price,`value`='$value'";
    $res["sql"] = $sql;
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function insertProduct($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $sql = "insert into `products` (`category`,`name`) VALUES ('$category','$name')";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function updateProduct($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $sql = "update  `products` set `name`='$name' where `id`='$id'";
    $res = [];
    $res["sql"] = $sql;
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function getProductsFromCategory($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $sql = "select count(`id`) as 'count' from `products` where `category`='$category'";
    $r = $mysqli->query($sql);
    $cnt = mysqli_fetch_assoc($r);
    $res = [];
    $res["count"] = $cnt["count"];
    $res["sql"] = $sql;
    return $res;
  }
  function deleteProduct($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $sql = "delete from  `products`  where `id`='$id'";
    $res = [];
    $res["sql"] = $sql;
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function getProductsSales($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $sql = "select * from `products_sales` where siteid='" . $post["siteid"] . "' and `date`='$date'";
    $res["sql"] = $sql;
    $result = $mysqli->query($sql);
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
      $res["records"] = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $res["records"][$row["productid"]] = $row;
      }
    }
    return $res;
  }
  function insertUpdateProductSales($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $res["error"] = [];
    $res["sql"] = [];
    $sql = "insert into `products_sales` (`date`,`siteid`,`productid`,`sales`) values ('$date', '$siteid','$id','$sales')
      ON DUPLICATE KEY UPDATE  `sales`='$sales'";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function getSiteFuels($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $sql = "SELECT distinct fuelid, `nozzles`.*, `fuel`.`grade` FROM `nozzles`
      left join `fuel` on `nozzles`.`fuelid`=`fuel`.`id`
      WHERE `siteid`=$siteid";
    $result = $mysqli->query($sql);
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
      $res["records"] = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $res["records"][$row["fuelid"]] = $row;
      }
    }
    return $res;
  }
  function getFuelDelivery($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $sql = "select `fuel_delivery`.*, `fuel`.`grade` from `fuel_delivery`
            left join `fuel` on `fuel_delivery`.`fuelid`=`fuel`.`id`
            where siteid='" . $post["siteid"] . "' and `date`='$date' order by `grade`";
    $res["sql"] = $sql;
    $result = $mysqli->query($sql);
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
      $res["records"] = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $res["records"][$row["fuelid"]] = $row;
      }
    }
    return $res;
  }
  function insertUpdateFuelDelivery($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $res["error"] = [];
    $res["sql"] = [];
    $sql = "insert into `fuel_delivery` (`date`,`siteid`,`fuelid`,`volume`) values ('$date', '$siteid','$fuelid','$volume')
      ON DUPLICATE KEY UPDATE  `volume`='$volume'";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function getPayments($post, $mysqli) {
    $rows = [];
    $sql = "select * from `payment_methods`";
    $result = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($result)) {
      $rows[$row["id"]] = $row;
    }
    return $rows;
  }
  function addPayment($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $sql = "insert into `payment_methods` (`name`) Values ('$name')";
    $row = [];
    if (!mysqli_query($mysqli,$sql)) {
      $row["status"] = "fail";
      $row["error"] = mysqli_error($mysqli);
    } else {
      $row["status"] = "ok";
      $row["id"] = $mysqli->insert_id;
    }
    return $row;
  }
  function updatePayment($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $sql = "update `payment_methods` SET `name`='$name' WHERE `id`='$id'";
    $row = [];
    if (!mysqli_query($mysqli,$sql)) {
      $row["status"] = "fail";
      $row["error"] = mysqli_error($mysqli);
    } else {
      $row["status"] = "ok";
    }
    return $row;
  }
  function deletePayment($post, $mysqli) {
    $sql = "delete from `payment_methods`  WHERE `id`='" . $post["id"] . "'";
    $row = [];
    if (!mysqli_query($mysqli,$sql)) {
      $row["status"] = "fail";
      $row["error"] = mysqli_error($mysqli);
    } else {
      $row["status"] = "ok";
    }
    return $row;
  }
  function getPaymentsDone($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $sql = "select `payments`.*, `payment_methods`.`name` from `payments`
            left join `payment_methods` on `payment_methods`.`id`=`payments`.`id`
            where siteid='" . $post["siteid"] . "' and `date`='$date' order by `payment_methods`.`name`";
    $res["sql"] = $sql;
    $result = $mysqli->query($sql);
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
      $res["records"] = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $res["records"][$row["id"]] = $row;
      }
    }
    return $res;
  }
  function insertUpdatePayment($post, $mysqli) {
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $res = [];
    $res["error"] = [];
    $res["sql"] = [];
    $sql = "insert into `payments` (`date`,`siteid`,`id`,`value`) values ('$date', '$siteid','$id','$value')
      ON DUPLICATE KEY UPDATE  `value`='$value'";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function listSites($post, $mysqli) {
    $res = [];

    $s_sql = "select *  from `users`";
    $s_res = $mysqli->query($s_sql);
    if (!mysqli_query($mysqli,$s_sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      while ($row = mysqli_fetch_assoc($s_res)) {
        $res["records"][] = $row;
      }
      $res["status"] = "ok";
    }
    return $res;
  }
  function listMethods($post, $mysqli) {
    $res = [];

    $s_sql = "select *  from `payment_methods`";
    $s_res = $mysqli->query($s_sql);
    if (!mysqli_query($mysqli,$s_sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      while ($row = mysqli_fetch_assoc($s_res)) {
        $res["records"][] = $row;
      }
      $res["status"] = "ok";
    }
    return $res;
  }
  function listFuel($post, $mysqli) {
    $res = [];

    $s_sql = "select *  from `fuel`";
    $s_res = $mysqli->query($s_sql);
    if (!mysqli_query($mysqli,$s_sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      while ($row = mysqli_fetch_assoc($s_res)) {
        $res["records"][] = $row;
      }
      $res["status"] = "ok";
    }
    return $res;
  }
  function listTypes($post, $mysqli) {
    $res = [];

    $s_sql = "select *  from `sale_types`";
    $s_res = $mysqli->query($s_sql);
    if (!mysqli_query($mysqli,$s_sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      while ($row = mysqli_fetch_assoc($s_res)) {
        $res["records"][] = $row;
      }
      $res["status"] = "ok";
    }
    return $res;
  }
  function listProductCategories($post, $mysqli) {
    $res = [];

    $s_sql = "select *  from  `product_categories`  where parent_id<>''";
    $s_res = $mysqli->query($s_sql);
    if (!mysqli_query($mysqli,$s_sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      while ($row = mysqli_fetch_assoc($s_res)) {
        $res["records"][] = $row;
      }
      $res["status"] = "ok";
    }
    return $res;
  }
  function submitFuelFales($post, $mysqli) {
    $res = [];
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $sql = "update fuel_sales set submitted=1 where date='$date' and siteid='$siteid'";

    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function submitSales($post, $mysqli) {
    $res = [];
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $sql = "update `sales_oil_lottery` set submitted=1 where date='$date' and siteid='$siteid'";
    $mysqli->query($sql);
    $sql = "update fuel_sales set submitted=1 where date='$date' and siteid='$siteid'";
    $mysqli->query($sql);
    $sql = "update `products_sales` set submitted=1 where date='$date' and siteid='$siteid'";
    $mysqli->query($sql);
    $sql = "update `services_sales` set submitted=1 where date='$date' and siteid='$siteid'";
    $mysqli->query($sql);
    $sql = "update `payments` set submitted=1 where date='$date' and siteid='$siteid'";
    $mysqli->query($sql);
    $sql = "insert into sites_submitted (date,siteid) values('$date','$siteid')";
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";
    }
    return $res;
  }
  function isSubmitted($post, $mysqli) {
    $res = [];
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $sql = "select * from `sites_submitted` where date='$date' and siteid='$siteid'";
    $r = $mysqli->query($sql);
    $res["submitted"] = mysqli_num_rows($r);
    if (!mysqli_query($mysqli,$sql)) {
      $res["status"] = "fail";
      $res["error"] = mysqli_error($mysqli);
    } else {
      $res["status"] = "ok";

    }
    return $res;
  }
  function getDailyReport($post, $mysqli) {
    $res = [];
    foreach ($post as $k => $v) {
      $$k = $v;
    }
    $sql = "select IFNULL(sum(value),0) as s from fuel_sales where submitted=1 AND date = '$date' and siteid='$siteid'";
    $r = $mysqli->query($sql);
    $res["Fuel"] = mysqli_fetch_assoc($r)["s"];

    $sql = "select IFNULL(sum(value),0) as s,type from `sales_oil_lottery`
    left join oil_lottery on oil_lottery.oilid=`sales_oil_lottery`.oilid
    where submitted=1 and type=1 AND date='$date' and siteid='$siteid'";
    $r = $mysqli->query($sql);
    $res["Oil"] = mysqli_fetch_assoc($r)["s"];

    $sql = "select IFNULL(sum(value),0) as s,type from `sales_oil_lottery`
    left join oil_lottery on oil_lottery.oilid=`sales_oil_lottery`.oilid
    where submitted=1 and type=2 AND date='$date' and siteid='$siteid'";
    $r = $mysqli->query($sql);
    $res["Lottery"] = mysqli_fetch_assoc($r)["s"];

    $sql = "select IFNULL(sum(value),0) as s from `services_sales` where submitted=1  AND date= '$date' and siteid='$siteid'";
    $r = $mysqli->query($sql);
    $res["Services"] = mysqli_fetch_assoc($r)["s"];

    $sql = "select IFNULL(sum(sales),0) as s from `products_sales` where submitted=1  AND date= '$date' and siteid='$siteid'";
    $r = $mysqli->query($sql);
    $res["Products"] = mysqli_fetch_assoc($r)["s"];

    $sql = "select IFNULL(sum(value), 0) as s from `payments` where submitted=1  AND date= '$date' and siteid='$siteid'";
    $r = $mysqli->query($sql);
    $res["Payments"] = mysqli_fetch_assoc($r)["s"];
    return $res;
  }
?>
