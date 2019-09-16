<?php
session_start();
include "../../config/database.php";
foreach ($_SESSION["reports"] as $k => $v) {
  $$k = $v;
}
$table = [];
$sql = "select group_concat(distinct id) as ids from payments order by id";
$r = $mysqli->query($sql);
$methods = explode(",", mysqli_fetch_assoc($r)["ids"]);
sort($methods);
$mnames = [];
foreach ($methods as $m) {
  $sql = "select name from payment_methods where id=$m";
  $mnames[$m] = mysqli_fetch_assoc($mysqli->query($sql))["name"];
}

$sit = implode(",", $sites);
$sql = "select distinct siteid from payments where siteid IN($sit) order by id";
$r = $mysqli->query($sql);
if ($timeline == "sum") {
  if ($grouping == "sites") {
      while ($row = mysqli_fetch_assoc($r)) {
        $table[$row["siteid"]] = $row["id"];
        $site = $row["siteid"];
        foreach ($methods as $m) {
          $sum = "select IFNULL(sum(value),0) as s from payments where siteid=$site and id=$m AND (date >= '$datefrom' AND date <= '$dateto')";
          $rsum = $mysqli->query($sum);
          $table[$site][$m] = floatval(mysqli_fetch_assoc($rsum)["s"]);
        }
      }
  } else {
    foreach ($methods as $m) {
      $sum = "select IFNULL(sum(value),0) as s from payments where siteid IN ($sit) AND id=$m AND (date >= '$datefrom' AND date <= '$dateto')";
      $rsum = $mysqli->query($sum);
      $table[$site][$m] = floatval(mysqli_fetch_assoc($rsum)["s"]);
    }
  }
}
if ($timeline != "sum") {
  if ($grouping == "sites") {
      while ($row = mysqli_fetch_assoc($r)) {
        $site =$row["siteid"];
        foreach ($methods as $m) {
          if ($timeline == "day") {
            $sum = "select date,siteid,IFNULL(sum(value),0) as s from payments where siteid=$site and id=$m AND (date >= '$datefrom' AND date <= '$dateto') group by date order by date";
          }
          if ($timeline == "week") {
            $sum = "select CONCAT('Week ',week(date)) as date,siteid,IFNULL(sum(value),0) as s from payments where siteid=$site and id=$m AND (date >= '$datefrom' AND date <= '$dateto') group by week(date) order by date";
          }
          if ($timeline == "month") {
            $sum = "select CONCAT('Month ',month(date)) as date,siteid,IFNULL(sum(value),0) as s from payments where siteid=$site and id=$m AND (date >= '$datefrom' AND date <= '$dateto') group by month(date) order by date";
          }
          $rsum = $mysqli->query($sum);
          if (mysqli_num_rows($rsum) == 0) {
            $table[$row["date"]][$row["siteid"]][$m] = 0;
          } else {
            while ($row = mysqli_fetch_assoc($rsum)) {
              $table[$row["date"]][$row["siteid"]][$m] =  $row["s"];
            }
          }
        }
      }
////////////////////////////////////////////izmene OVDE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
  } else {
    foreach ($methods as $m) {
      if ($timeline == "day") {
        $sum = "select date,IFNULL(sum(value),0) as s from payments where siteid IN ($sit) AND id=$m AND (date >= '$datefrom' AND date <= '$dateto') Group By date";
      }
      if ($timeline == "month") {
        $sum = "select CONCAT('Week ',week(date)) as date,IFNULL(sum(value),0) as s from payments where siteid IN ($sit) AND id=$m AND (date >= '$datefrom' AND date <= '$dateto') Group By Week(date)";
      }
      if ($timeline == "month") {
        $sum = "select CONCAT('Month ',month(date)) as date,IFNULL(sum(value),0) as s from payments where siteid IN ($sit) AND id=$m AND (date >= '$datefrom' AND date <= '$dateto') Group By month(date)";
      }
      $rsum = $mysqli->query($sum);
      while ($row = mysqli_fetch_assoc($rsum)) {
          $table[$row["date"]][$m] = $row["s"];
      }

    }
  }
}
$dt = json_encode($table);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>Admin | Manage Users</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

    <link href="/assets/css/bootstrap.css" rel="stylesheet">
    <link href="/assets/css/jquery-ui.css" rel="stylesheet">
    <link href="/assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/style-responsive.css" rel="stylesheet">
    <link href="/assets/css/sweetalert.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-select.css" rel="stylesheet">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.6/b-flash-1.5.6/b-html5-1.5.6/b-print-1.5.6/datatables.min.css"/>

    <style>
    body,html {
      overflow: auto;
      height: 4000px;
    }
      table {
       table-layout: auto;
      }
      tfoot, thead {
        background:black;
        color:white;
        font-weight: bold,
      }
      tfoot th {
        padding-right: 0;
      }
      td {
        padding-left: 15px;
        padding-right: 15px;

      }
      th:not(:first-of-type), td:not(:first-of-type) {
        text-align:right;
      }

    </style>
    <script src="/assets/js/jquery.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
    <script class="include" type="text/javascript" src="/assets/js/jquery.dcjqaccordion.2.7.js"></script>
    <script src="/assets/js/jquery.scrollTo.min.js"></script>
    <script src="/assets/js/jquery.nicescroll.js" type="text/javascript"></script>
    <script src="/assets/js/common-scripts.js"></script>
    <script type="text/javascript" src="/assets/js/api.js"></script>
    <script type="text/javascript" src="/assets/js/sweetalert2.js"></script>
    <script type="text/javascript" src="/assets/js/jquery.validate.js"></script>
    <script type="text/javascript" src="/assets/js/underscore.js"></script>
    <link rel="stylesheet" href="/assets/css/bootstrap-select.css" type="text/css"/>
    <script type="text/javascript" src="/assets/js/bootstrap-select.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
   <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
   <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
   <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.6/b-flash-1.5.6/b-html5-1.5.6/b-print-1.5.6/datatables.min.js"></script>
   <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.colVis.min.js"></script>

  </head>

  <body>
    <div id="reportDiv" style="padding:10px;">
      <div style="text-align:center;width:750px;max-width:500px;margin: 0 auto;padding-bottom:10px;">
        <select class="form-control" id="chartType">

        </select>
        <img id="graphImage" style="display:none" src="" />
        <img id="graphImageSmall"  style="width:350px;height:auto" src="" />
        <canvas id="graphCanvas" style="display:none"></canvas>
        <canvas id="graphCanvasSmall" style="display:none"></canvas>
        </div>
        <div id="chart_div" style="margin-top:10px;text-align:center;width:750px;max-width:750px;margin: 0 auto;"></div>
      <table class="table table-striped table-advanced table-hover" style="margin:0 auto;" id="reportTable">
        <thead>
            <tr>
              <th>Period</th>
              <th>Site</th>
              <?php
                foreach ($methods as $m) {
                  $sql = "select name from payment_methods where id=$m";
                  $r = $mysqli->query($sql);
                  echo "<th>" . mysqli_fetch_assoc($r)["name"] . "</th>";
                }
                echo "<th>Total</th>";
               ?>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($timeline == "sum") {

              setlocale(LC_MONETARY, 'en_GB');
                  foreach($table as $k =>$v) {
                    echo "<tr><td></td>";
                    $sql = "select site from users where id=$k";
                    $r = $mysqli->query($sql);
                    echo "<td>" . mysqli_fetch_assoc($r)["site"] . "</td>";
                    $sum = 0;
                    foreach ($v as $value) {
                      $sum = $sum + $value;
                    ?>
                      <td><?=utf8_encode(money_format('%n', $value))?></td>
                    <?php } ?>
                    <td><?=utf8_encode(money_format('%n', $sum))?></td>
                    </tr>
              <?php   } ?>
            <?php } ?>
            <?php
            if ($timeline != "sum" && $grouping != "sum") {

                setlocale(LC_MONETARY, 'en_GB');
                  foreach($table as $km =>$vm) {
                    $total = [];
                    echo "<tr>";
                    foreach ($vm as $k => $v) {
                      $sql = "select site from users where id=$k";
                      $r = $mysqli->query($sql);
                      echo "<td>" . $km . "</td>";
                      echo "<td>" . mysqli_fetch_assoc($r)["site"] . "</td>";
                      $sum = 0;
                      foreach ($methods as $m) {
                        $value = $v[$m];
                        $total[$m] = $total[$m] + $value;
                        $sum = $sum + $value;
                      ?>
                        <td><?=utf8_encode(money_format('%n', $value))?></td>
                      <?php } ?>
                      <td><?=utf8_encode(money_format('%n', $sum))?></td>
                      </tr>
                  <?php } ?>
                  <tr class="total"><td>Period total</td><td></td>
                    <?php
                    $t1 = 0;
                      foreach($total as $t) {
                        $t1 = $t1 + $t;
                         ?>
                        <td><strong><?=utf8_encode(money_format('%n', $t))?></strong></td>
                    <?php }  ?>
                    <td><strong><?=utf8_encode(money_format('%n', $t1))?></strong></td>
                  </tr>
              <?php   } ?>
            <?php } ?>

            <?php if ($grouping == "sites") { ?>
                <tfoot>
                  <tr>
                    <th></th>
                    <th>Total</th>
                    <?php
                      foreach ($methods as $m) {
                        $sql = "select name from payment_methods where id=$m";
                        $r = $mysqli->query($sql);
                        echo "<th style='padding-right:7px;font-wight:bold;'>0.00</th>";
                      }
                     ?>
                     <th style='padding-right:7px;font-wight:bold;'>0.00</th>
                  </tr>
                </tfoot>
              <?php } ?>
              <?php
            if ($timeline != "sum" && $grouping == "sum") {

                setlocale(LC_MONETARY, 'en_GB');
                    foreach($table as $k =>$v) {
                      echo "<tr><td>" . $k . "</td><td></td>";

                      $sum = 0;
                      foreach ($v as $value) {
                        $sum = $sum + $value;
                      ?>
                        <td><?=utf8_encode(money_format('%n', $value))?></td>
                      <?php } ?>
                      <td><?=utf8_encode(money_format('%n', $sum))?></td>
                      </tr>
                <?php   } ?>
                <tfoot>
                  <tr>
                    <th></th>
                    <th>Total</th>
                    <?php
                      foreach ($methods as $m) {
                        $sql = "select name from payment_methods where id=$m";
                        $r = $mysqli->query($sql);
                        echo "<th style='padding-right:7px;font-wight:bold;'>0.00</th>";
                      }
                     ?>
                     <th style='padding-right:7px;font-weight:bold;'>0.00</th>
                  </tr>
                </tfoot>
              <?php } ?>


        </tbody>

      </table>
    </div>

  </body>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script type="text/javascript">
  var data = <?php echo json_encode($table) ?>;
  var methods = <?php echo json_encode($mnames) ?>;
  var gData = null;
  var graphData = null
  var colorArray = ['#FF6633', '#FFB399', '#FF33FF', '#FFFF99', '#00B3E6',
		  '#E6B333', '#3366E6', '#999966', '#99FF99', '#B34D4D',
		  '#80B300', '#809900', '#E6B3B3', '#6680B3', '#66991A',
		  '#FF99E6', '#CCFF1A', '#FF1A66', '#E6331A', '#33FFCC',
		  '#66994D', '#B366CC', '#4D8000', '#B33300', '#CC80CC',
		  '#66664D', '#991AFF', '#E666FF', '#4DB3FF', '#1AB399',
		  '#E666B3', '#33991A', '#CC9999', '#B3B31A', '#00E680',
		  '#4D8066', '#809980', '#E6FF80', '#1AFF33', '#999933',
		  '#FF3380', '#CCCC00', '#66E64D', '#4D80CC', '#9900B3',
		  '#E64D66', '#4DB380', '#FF4D4D', '#99E6E6', '#6666FF'];
  google.charts.load('current', {'packages':['corechart']});
  google.charts.setOnLoadCallback(drawChartSumSum(null));
  var g = "<?=$grouping?>";
  var t = "<?=$timeline?>";
  if (g == "sum" && t == "sum") {
    var gData = data[""];
    graphData = {};
    for (var prop in gData) {
      graphData[methods[prop]] =  gData[prop];
    }

    $("<option value='PieChart'>Pie</option>").appendTo($("#chartType"));
    $("<option value='PieChart'>Donut</option>").appendTo($("#chartType"));
    $("<option value='ColumnChart'>Column Chart</option>").appendTo($("#chartType"));
    $("#chartType").unbind("change");
    $("#chartType").bind("change", function() {
      drawChartSumSum(graphData);
    });
    var ww = setInterval(function () {
      if (google.visualization !== undefined) {
        clearInterval(ww);
        setTimeout(function() {
          drawChartSumSum(graphData);
        }, 1000);
      }
    }, 100);
  }

    jQuery.fn.dataTable.Api.register( 'sum()', function ( ) {
        return this.flatten().reduce( function ( a, b ) {
            if ( typeof a === 'string' ) {
                a = a.replace(/[^\d.-]/g, '') * 1;
            }
            if ( typeof b === 'string' ) {
                b = b.replace(/[^\d.-]/g, '') * 1;
            }

            return a + b;
        }, 0 );
    } );
    setTimeout(function() {
        var rt = $("#reportTable").DataTable({
          "paging": false,
          "sorting": false,
          dom: 'Bfrtip',
           buttons: [
             'colvis',
              { extend: 'print', footer: true, exportOptions: {
                    columns: ':visible'
              },
              title: "Some title",
              customize: function ( win ) {

                    $(win.document.body).append($("svg").eq(0).clone());

                    $(win.document.body)
                        .css( 'font-size', '10pt' )


                    $(win.document.body).find( 'table' )
                        .addClass( 'compact' )
                        .css( 'font-size', 'inherit' );
                }
              },
              { extend: 'excelHtml5', footer: true, exportOptions: {
                    columns: ':visible'
              }},
              { extend: 'csvHtml5', footer: true, exportOptions: {
                    columns: ':visible'
              }},
              { extend: 'pdfHtml5', footer: true, exportOptions: {
                    columns: ':visible'
              },
              title: "Some title",
              customize: function ( doc ) {
                      doc.content.push( {
                          image: $("#graphImageSmall").attr("src"),
                          alignment: 'center'

                      });
                  }

            },
           ],
          drawCallback: function () {
            var api = this.api();
              $.each($("tfoot").find("tr").find("th"), function (ind) {
                if (ind > ((t == "sum") ? 0 : 1)) {
                  var s = 0;
                  api.rows(":not('.total')").every(function() {
                      s +=  parseFloat(this.data()[ind].replace(/[^\d.-]/g, '') * 1);
                  });
                  var fmt = new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(parseFloat(s));
                  $(this).html(fmt);
                }
              });
          }
        });


        if (g == "sum" && t == "sum") {
          rt.column( 1 ).visible( false );
          rt.column( 0 ).visible( false );
        }
        if (g == "sum" && t != "sum") {
          rt.column( 1 ).visible( false );
        }
        if (g != "sum" && t == "sum") {
          rt.column( 0 ).visible( false );
        }

    }, 300);
    function drawChartSumSum(d) {
      if (d == null) {
        return;
      }
    //  alert(JSON.stringify(data));
    var data = new google.visualization.DataTable();
      data.addColumn('string', 'Method');
      data.addColumn('number', 'Amount');
      if ($("#chartType").find("option:selected").text() == "Column Chart") {
        data.addColumn({ type: "string", role: "style" } );
      }
      var dt = [];
      var i = 0;
      for (var k in d) {
        if ($("#chartType").find("option:selected").text() == "Column Chart") {
          dt.push([k, d[k], "color: " + colorArray[i]]);
        } else {
          dt.push([k, d[k]]);
        }
        i++;
      }

      data.addRows( dt );
      var options = {'title':'Payments by methods',
                      'width': 750,
                      'height': 500};

      if ($("#chartType").find("option:selected").text() == "Donut") {
        options["pieHole"] = 0.4;
      }
       // Instantiate and draw our chart, passing in some options.
       var chart = new google.visualization[$("#chartType").val()](document.getElementById('chart_div'));
       chart.draw(data, options);

       var s = new XMLSerializer().serializeToString($("svg")[0]);
       var encodedData = window.btoa(s);
       $("#graphImage").attr("src", "data:image/svg+xml;base64," + encodedData);
       var svgString = s;
        var canvas = document.getElementById("graphCanvas");
        var canvasSmall = document.getElementById("graphCanvasSmall");
        var ctx = canvas.getContext("2d");

        var ctxSmall = canvasSmall.getContext("2d");

        var DOMURL = self.URL || self.webkitURL || self;
        var img = new Image();
        var svg = new Blob([svgString], {type: "image/svg+xml;charset=utf-8"});
        var url = DOMURL.createObjectURL(svg);
        img.onload = function() {
            ctx.drawImage(img, 0, 0);

            ctxSmall.width = img.width * 3;
            ctxSmall.height = img.height / 3;
              $("#graphImageSmall").css({
                width: img.width / 3,
                height: img.height / 3,
                display: "none"
              })
            ctxSmall.drawImage(img, 0, 0,img.width / 3,img.height / 3);
            var png = canvas.toDataURL("image/png");
            var pngSmall = canvasSmall.toDataURL("image/png");

            $("#graphImage").attr("src", png);
            $("#graphImageSmall").attr("src", pngSmall);
        };
        img.src = url;

    }
  </script>
</html>
