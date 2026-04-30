<?php
include "Layouts/Header.php";
include "Layouts/Sidebar.php"; 



$result = $mysqli->query("SELECT id, name, parent_id FROM users");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[$row['id']] = $row;
    $users[$row['id']]['children'] = [];
}



$tree = [];
foreach ($users as $id => &$user) {
    if ($user['parent_id']) {
        $users[$user['parent_id']]['children'][] = &$user;
    } else {
        $tree[] = &$user;
    }
}


?>


<div class="content-body">
    <div class="container-fluid">
      
<div id="chart_div"></div>
    </div>
</div>


<?php include "Layouts/Footer.php"  ?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
  google.charts.load('current', {packages:["orgchart"]});
  google.charts.setOnLoadCallback(drawChart);

  function drawChart() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Name');
    data.addColumn('string', 'Manager');

    data.addRows([
      <?php
      function printNodes($nodes, $parent = '') {
          foreach ($nodes as $node) {
              echo "['".$node['name']."', ".($parent ? "'$parent'" : "null")."],\n";
              if (!empty($node['children'])) {
                  printNodes($node['children'], $node['name']);
              }
          }
      }
      printNodes($tree);
      ?>
    ]);

    var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
    chart.draw(data, {allowHtml:true});
  }
</script>



