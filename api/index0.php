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
    $sql = "SELECT * from `admin` WHERE `username`='" . $post["username"] . "' AND `password`='" . $post["password"] . "'";
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
      $row = mysqli_fetch_assoc($result);
      $row["user_type"] = "1";
      $row["status"] = "ok";
      return $row;
    }
    $sql = "SELECT * from `users` WHERE `site`='" . $post["username"] . "' AND `password`='" . $post["password"] . "' and `active`='1'";
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
      $row = mysqli_fetch_assoc($result);
      $row["user_type"] = "0";
      $row["status"] = "ok";
      return $row;
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
    $sql = "insert into `site_pumps` (`siteid`, `label`) Values ('" . $post["siteid"] . "','" . $post["label"] . "')";
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
    $sql = "update `nozzles` set `active`=0 where `pumpid`='" . $post["pumpid"] . "'";
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
    var_dump($post);
    exit;
    $res = [];
    $res["fuels"] = [];
    $sql = "select * from `fuel`";
    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["fuels"][count($res["fuels"])] = $row;
    }

    $res["pumps"] = [];
    $sql = "select * from `site_pumps` where `siteid`='$siteid' AND `active`=1";
    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["pumps"][count($res["pumps"])] = $row;
    }

    $res["nozzles"] = [];
    $sql = "SELECT `nozzles`.*, `fuel`.`grade` FROM `nozzles`
            left join `fuel` on `nozzles`.`fuelid`=`fuel`.`id`
            WHERE `nozzles`.`siteid`='$siteid' and `nozzles`.`active`=1";
    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["nozzles"][count($res["nozzles"])] = $row;
    }

    $res["lastsale"] = [];
    $sql = "SELECT *
                FROM `fuel_sales` t1
                WHERE t1.date = (SELECT MAX(t2.date)
               FROM fuel.sales t2
               WHERE t2.fuelid = t1.fuelid and t1.`siteid`='$siteid' and t1.`date`<'" . $post["date"] . "')";

    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["lastsale"][count($res["lastsale"])] = $row;
    }


    $res["fuelprices"] = [];
    $sql = "SELECT *
                FROM `pricelist` t1
                WHERE t1.date = (SELECT MAX(t2.date)
               FROM pricelist t2
               WHERE t2.fuelid = t1.fuelid and t1.`siteid`='$siteid' and t1.`date`<='" . $post["date"] . "')";

    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      if ($row["siteid"] = $siteid) {
        $res["fuelprices"][count($res["fuelprices"])] = $row;
      }
    }

    $res["fuelsales"] = [];
    $sql = "select * from `fuel_sales` where `siteid`='$siteid' AND `date`='$date'";
    $r = $mysqli->query($sql);
    while ($row = mysqli_fetch_assoc($r)) {
      $res["fuelsales"][count($res["fuelsales"])] = $row;
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
?>
