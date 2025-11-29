<?php

require_once 'config.php';
require_once 'db.php';
require_once 'core-val.php';
require_once 'global_values.php';
?>

<style>
  #insTable td {
    padding: 2px 4px;
  }
   #insTable th {
    padding: 5px 4px;
  }
</style>

<?php
if($is_admin == 5){
  $q = $conn->query("SELECT sccode, scname, sccategory, ps, dist FROM scinfo  ORDER BY scname ASC");
} else {
  $q = $conn->query("SELECT sccode, scname, sccategory, ps, dist FROM scinfo where display=1 ORDER BY scname ASC");

}

if ($q->num_rows > 0) {
  echo '<table class="table table-responsive   table-sm" id="insTable">';
  echo '<thead>
            <tr>
              <th>EIIN</th>
              <th>Name of Institute</th>
              <th>Category</th>
              <th>PS</th>
              <th>District</th>
              <th>Action</th>
            </tr>
          </thead><tbody>';
  while ($row = $q->fetch_assoc()) {
    echo '<tr>
                <td>' . htmlspecialchars($row['sccode']) . '</td>
                <td>' . htmlspecialchars($row['scname']) . '</td>
                <td>' . htmlspecialchars($row['sccategory']) . '</td>
                <td>' . htmlspecialchars($row['ps']) . '</td>
                <td>' . htmlspecialchars($row['dist']) . '</td>
                <td>
                  <div class="btn-group" role="group" aria-label="Basic example">
                      <button class="btn btn-sm btn-success m-0 p-1 ps-2 pe-2" onclick="loginSccode(\'' . $row['sccode'] . '\')"><i class="bi bi-box-arrow-in-right"></i></button>
                      <button class="btn btn-sm btn-info m-0 p-1 ps-2 pe-2" onclick="viewDetails(\'' . $row['sccode'] . '\')"><i class="bi bi-file-text-fill"></i></button>
                      <a href="view-ins-profile.php?sccode=' . $row['sccode'] . '" class="btn btn-sm btn-primary m-0 p-1 ps-2 pe-2" target="_blank"><i class="bi bi-journal"></i></a>
                  </div>
                </td>
              </tr>';
  }
  echo '</tbody></table>';
} else {
  echo "<div class='alert alert-warning'>No institutions found.</div>";
}
?>
