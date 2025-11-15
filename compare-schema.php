<?php
require_once 'header.php';
if (isLocalhost()) {
  echo "🖥️ Running on Localhost";
} else {
  echo "🌐 Running on Remote Server";
}


// echo '<pre>' . print_r($_SESSION) . '</pre>';
?>

<div class="container-xxl flex-grow-1 container-p-y">

<div id="syncResult">.........</div>

  <?php
  $localFile = __DIR__ . '/schema/localhost.sql';
  $remoteFile = __DIR__ . '/schema/remote.sql';
  $ttl = 'Local <i class="bi bi-heart-arrow"></i> Remote';
  $_SESSION['reverse'] = 0;

  if (isset($_GET['reverse']) && $_GET['reverse'] == '1') {
    $temp = $localFile;
    $localFile = $remoteFile;
    $remoteFile = $temp;
    $ttl = 'Remote <i class="bi bi-heart-arrow"></i> Local';
    $_SESSION['reverse'] = 1;
  }

  if (!file_exists($localFile)) {
    echo "<div class='alert alert-danger'>Local schema file not found!</div>";
    exit;
  }

  $localContent = file_get_contents($localFile);
  $remoteContent = file_exists($remoteFile) ? file_get_contents($remoteFile) : '';

  // Counters
  $found_table = $missing_table = $total_table = 0;
  $match_column = $different_column = $missing_column = $total_column = 0;

  // ✅ Parse tables
  function parseTables($sql)
  {
    $tables = [];
    // Multiline-compatible regex
    preg_match_all('/CREATE TABLE `([^`]+)` \((.*?)\)\s*ENGINE=/si', $sql, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
      $tableName = $match[1];
      $body = $match[2];
      $lines = preg_split('/\r\n|\r|\n/', $body);
      $columns = [];
      foreach ($lines as $line) {
        $line = trim($line);
        if (substr($line, -1) === ',')
          $line = substr($line, 0, -1);
        // Skip keys/constraints
        if (preg_match('/^(PRIMARY|UNIQUE|KEY|CONSTRAINT|INDEX|FOREIGN|CHECK)/i', $line))
          continue;
        // Column definition
        if (preg_match('/^`[^`]+`/', $line))
          $columns[] = $line;
      }
      $tables[$tableName] = $columns;
    }
    return $tables;
  }

  $localTables = parseTables($localContent);
  $remoteTables = parseTables($remoteContent);
  $total_table = count($localTables);
  ?>

  <script>
    function updatediffmiss(diff, miss, block) {
      // alert(diff + miss + block);
      if (diff + miss > 0) {
        let blk = document.getElementById('diffmiss-' + block);
        let a = "<span class='badge bg-warning'>" + diff + "</span>";
        let b = "<span class='badge bg-danger'>" + miss + "</span>";
        blk.innerHTML = a + " " + b;
      }
    }
  </script>

  <button class="btn btn-primary float-end" onclick="reve();"> <i class="bi bi-arrow-repeat"></i> &nbsp;Switch</button>
  <h4 class="mb-4"> Schema Comparison <span class="text-warning fw-bold"><?php echo $ttl; ?></span></h4>
  একশন অ্যাপ্লাই করার পর ‍এসকিউএল ফাইল থেকে টেক্স মুছে ফেলার চেষ্টা করতে হবে।
  <div class="card bg-light mb-4 border">
    <div class="card-body d-flex flex-wrap gap-3">
      <div><strong>Total Tables:</strong> <span id="count-total-tables"><?php echo $total_table; ?></span></div>
      <div><strong>Found in Remote:</strong> <span id="count-found-tables"
          class="text-success"><?php echo $found_table; ?></span></div>
      <div><strong>Missing in Remote:</strong> <span id="count-missing-tables"
          class="text-danger"><?php echo $missing_table; ?></span></div>
      <div class="border-start ps-3"><strong>Total Columns:</strong> <span
          id="count-total-cols"><?php echo $total_column; ?></span></div>
      <div><strong>Matched:</strong> <span id="count-match-cols"
          class="text-success"><?php echo $match_column; ?></span></div>
      <div><strong>Different:</strong> <span id="count-diff-cols"
          class="text-warning"><?php echo $different_column; ?></span></div>
      <div><strong>Missing:</strong> <span id="count-missing-cols"
          class="text-danger"><?php echo $missing_column; ?></span></div>
    </div>
  </div>


  <?php
  $index = 0;
  foreach ($localTables as $tableName => $localCols) {
    $index++;
    $collapseId = "collapse_$index";
    $remoteCols = $remoteTables[$tableName] ?? [];
    $existsRemote = !empty($remoteCols);
    $existsRemote ? $found_table++ : $missing_table++;

    echo "<div class='card mb-3'>";
    echo "<div class='card-header d-flex align-items-center justify-content-between p-2'>";
    echo "<div class='flex-fill d-flex align-items-center'><h6 class='mb-0 ms-2'>$tableName</h6></div>";
    echo "<div class='d-flex justify-content-center align-items-center' style='min-width:180px;'>";
    echo $existsRemote ? "<span class='badge bg-success'>Found in Remote</span>" : "<span class='badge bg-danger'>Missing in Remote</span>";
    echo "</div>";
    echo "<div class='d-flex align-items-center gap-2'>";
    if (!$existsRemote) {
      preg_match('/CREATE TABLE `' . preg_quote($tableName, '/') . '` \((.*?)\)[^\;]*;/si', $localContent, $m);
      $createStmt = $m[0] ?? '';
      echo "<button class='btn btn-sm btn-primary sync-table p-1' style='min-width: 100px;' 
                data-table='$tableName' 
                data-create='" . htmlspecialchars($createStmt, ENT_QUOTES) . "'>➕ Sync Table</button>";
    } else {
      echo "<span style='min-width:100px;' id='diffmiss-$tableName'></span>";
    }

    // echo htmlspecialchars($createStmt, ENT_QUOTES);
    echo "<button class='btn btn-sm' data-bs-toggle='collapse' data-bs-target='#$collapseId'><i class='bi bi-chevron-down'></i></button>";
    echo "</div></div>";

    echo "<div id='$collapseId' class='collapse'><div class='card-body'>";
    echo "<table class='table table-sm table-bordered align-middle'>";
    echo "<thead><tr><th>Column / Definition</th><th>Status</th><th>Action</th><th class='text-center'><button class='btn btn-sm btn-outline-secondary toggle-check p-1' data-table='$tableName'>Toggle</button></th></tr></thead><tbody>";
    $curdiff = $curmiss = 0;
    foreach ($localCols as $colDef) {
      $total_column++;
      preg_match('/^`([^`]+)`/', $colDef, $cm);
      $colName = $cm[1] ?? '';
      $remoteColNames = array_map(fn($def) => preg_match('/^`([^`]+)`/', $def, $m) ? $m[1] : '', $remoteCols);
      $existsByName = in_array($colName, $remoteColNames);
      $isExactMatch = in_array($colDef, $remoteCols);

      if ($isExactMatch) {
        $match_column++;
      } elseif ($existsByName) {
        $different_column++;
        $curdiff++;
      } else {
        $missing_column++;
        $curmiss++;
      }


      $status = $isExactMatch ? "<span class='badge bg-success'>Matched</span>" :
        ($existsByName ? "<span class='badge bg-warning'>Different</span>" : "<span class='badge bg-danger'>Missing</span>");
      $actionBtn = $isExactMatch ? '' : "<button class='btn btn-sm btn-outline-primary sync-column' style='min-width:90px;' data-table='$tableName' data-column='" . htmlspecialchars($colDef, ENT_QUOTES) . "'>⚙️ Apply</button>";
      $disabled = $isExactMatch ? 'disabled' : '';
      echo '<script>updatediffmiss(' . $curdiff . ', ' . $curmiss . ', "' . $tableName . '");;</script>';

      echo "<tr>
                <td><code>$colDef</code></td>
                <td>$status</td>
                <td>$actionBtn</td>
                <td class='text-center'><input type='checkbox' class='check-column' data-table='$tableName' data-column='" . htmlspecialchars($colDef, ENT_QUOTES) . "' $disabled></td>
              </tr>";
    }

    echo "</tbody></table>";
    echo "</div></div></div>";
  }
  ?>

  <button class="btn btn-success mt-3" id="syncSelected">Sync Selected Columns/Tables</button>
</div>

<?php require_once 'footer.php'; ?>

<script src="compare-schema.js"></script> <!-- JS logic এখানে আলাদা ফাইলেও রাখতে পারো -->

<script>
  // Function to update summary counts dynamically
  function updateSummaryCounts() {
    const rows = document.querySelectorAll('table tbody tr');

    let totalCols = 0, matchCols = 0, diffCols = 0, missingCols = 0;
    rows.forEach(row => {
      totalCols++;
      const statusBadge = row.querySelector('td:nth-child(2) span');
      if (!statusBadge) return;
      const status = statusBadge.textContent.trim().toLowerCase();
      if (status.includes('matched')) matchCols++;
      else if (status.includes('different')) diffCols++;
      else if (status.includes('missing')) missingCols++;
    });

    document.getElementById('count-total-cols').textContent = totalCols;
    document.getElementById('count-match-cols').textContent = matchCols;
    document.getElementById('count-diff-cols').textContent = diffCols;
    document.getElementById('count-missing-cols').textContent = missingCols;

    // Update tables counts
    const tableCards = document.querySelectorAll('.card.mb-3');
    let foundTables = 0, missingTables = 0;
    tableCards.forEach(card => {
      const badge = card.querySelector('.card-header .badge');
      if (!badge) return;
      if (badge.textContent.toLowerCase().includes('found')) foundTables++;
      else if (badge.textContent.toLowerCase().includes('missing')) missingTables++;
    });
    document.getElementById('count-found-tables').textContent = foundTables;
    document.getElementById('count-missing-tables').textContent = missingTables;
  }


  // Call initially
  updateSummaryCounts();

  // Update counts after Sync Table / Apply Column

  /*
  document.addEventListener('click', async (e) => {
    if (e.target.matches('.sync-table, .sync-column')) {
      const btn = e.target;
      const table = btn.dataset.table;
      const column = btn.dataset.column || null;
      const action = column ? 'apply-column' : 'apply-table';
      const sql = column || btn.dataset.create;

      btn.disabled = true;

      try {
        const res = await postData('apply-schema.php', { action, table, sql });
        if (res.status === 'ok') {
          // Update row badge if column applied
          if (column) {
            const row = btn.closest('tr');
            row.querySelector('td:nth-child(2)').innerHTML = "<span class='badge bg-success'>Matched</span>";
            btn.remove();
          } else {
            // Table synced → reload page or mark found
            btn.closest('.card.mb-3').querySelector('.card-header .badge').textContent = "Found in Remote";
            btn.remove();
          }
          updateSummaryCounts();
        } else {
          alert("Error: " + res.msg);
          btn.disabled = false;
        }
      } catch (err) {
        console.error(err);
        btn.disabled = false;
      }
    }
  });
  */

</script>


</body>

</html>