<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

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

  function parseTables($sql)
  {
    $tables = [];
    preg_match_all('/CREATE TABLE `([^`]+)` \((.*?)\)\s*ENGINE=/s', $sql, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
      $tableName = $match[1];
      $body = $match[2];
      $lines = preg_split('/\r\n|\r|\n/', $body);
      $columns = [];
      foreach ($lines as $line) {
        $line = trim($line);
        if (substr($line, -1) === ',')
          $line = substr($line, 0, -1);
        if (preg_match('/^(PRIMARY|UNIQUE|KEY|CONSTRAINT|INDEX|FOREIGN|CHECK)/i', $line))
          continue;
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

  <button class="btn btn-primary float-end" onclick="reve();"> <i class="bi bi-arrow-repeat"></i> &nbsp;Switch</button>
  <h4 class="mb-4"> Schema Comparison <span class="text-warning fw-bold"><?php echo $ttl; ?></span></h4>

  <!-- ✅ Counter Summary -->
  <div class="card bg-light mb-4 border">
    <div class="card-body d-flex flex-wrap gap-3">
      <div><strong>Total Tables:</strong> <span id="count-total-tables">0</span></div>
      <div><strong>Found in Remote:</strong> <span id="count-found-tables" class="text-success">0</span></div>
      <div><strong>Missing in Remote:</strong> <span id="count-missing-tables" class="text-danger">0</span></div>
      <div class="border-start ps-3"><strong>Total Columns:</strong> <span id="count-total-cols">0</span></div>
      <div><strong>Matched:</strong> <span id="count-match-cols" class="text-success">0</span></div>
      <div><strong>Different:</strong> <span id="count-diff-cols" class="text-warning">0</span></div>
      <div><strong>Missing:</strong> <span id="count-missing-cols" class="text-danger">0</span></div>
    </div>
  </div>

  <?php
  $index = 0;
  foreach ($localTables as $tableName => $localCols) {
    $index++;
    $collapseId = "collapse_$index";
    $remoteCols = $remoteTables[$tableName] ?? [];
    $existsRemote = !empty($remoteCols);

    if ($existsRemote)
      $found_table++;
    else
      $missing_table++;

    echo "<div class='card mb-3'>";


    echo "<div class='card-header d-flex align-items-center justify-content-between p-2'>";

    // 🔹 Left section: Table name
    echo "<div class='flex-fill d-flex align-items-center'>";
    echo "<h6 class='mb-0 ms-2'>$tableName</h6>";
    echo "</div>";

    // 🔹 Middle section: Fixed width badge area (সবগুলো সমান সারিতে থাকবে)
    echo "<div class='d-flex justify-content-center align-items-center' style='min-width:180px;'>";
    echo ($existsRemote
      ? "<span class='badge bg-success'>Found in Remote</span>"
      : "<span class='badge bg-danger'>Missing in Remote</span>");
    echo "</div>";

    // 🔹 Right section: Buttons
    echo "<div class='d-flex align-items-center gap-2' >";
    if (!$existsRemote) {
      preg_match('/CREATE TABLE `' . preg_quote($tableName, '/') . '` \((.*?)\)[^\;]*;/s', $localContent, $m);
      $createStmt = $m[0] ?? '';
      echo "<button class='btn btn-sm btn-primary sync-table p-1'  style='min-width: 100px;' 
              data-table='$tableName' 
              data-create='" . htmlspecialchars($createStmt, ENT_QUOTES) . "'>➕ Sync Table</button>";
    } else {
      echo "<span style='min-width: 100px;'></span>";
    }

    echo "<button class='btn btn-sm' data-bs-toggle='collapse' data-bs-target='#$collapseId'>
          <i class='bi bi-chevron-down'></i>
        </button>";
    echo "</div>";

    echo "</div>"; // end card-header
  



    echo "<div id='$collapseId' class='collapse'><div class='card-body '>";
    echo "<table class='table table-sm table-bordered align-middle'>";
    echo "<thead><tr>
        <th>Column / Definition</th>
        <th>Status</th>
        <th>Action</th>
        <th class='text-center'><button class='btn btn-sm btn-outline-secondary toggle-check p-1' data-table='$tableName'>Toggle</button></th>
      </tr></thead><tbody>";

    foreach ($localCols as $colDef) {
      $total_column++;
      preg_match('/^`([^`]+)`/', $colDef, $cm);
      $colName = $cm[1] ?? '';
      $remoteColNames = array_map(fn($def) => preg_match('/^`([^`]+)`/', $def, $m) ? $m[1] : '', $remoteCols);

      $existsByName = in_array($colName, $remoteColNames);
      $isExactMatch = in_array($colDef, $remoteCols);

      if ($isExactMatch)
        $match_column++;
      elseif ($existsByName)
        $different_column++;
      else
        $missing_column++;

      $status = $isExactMatch ? "<span class='badge bg-success'>Matched</span>" :
        ($existsByName ? "<span class='badge bg-warning'>Different</span>" :
          "<span class='badge bg-danger'>Missing</span>");

      $actionBtn = $isExactMatch ? '' :
        "<button style='min-width: 90px;' class='btn btn-sm btn-outline-primary sync-column' data-table='$tableName' data-column='" . htmlspecialchars($colDef, ENT_QUOTES) . "'>⚙️ Apply</button>";

      $disabled = $isExactMatch ? 'disabled' : '';

      echo "<tr>
              <td><code>$colDef</code></td>
              <td>$status</td>
              <td>$actionBtn</td>
              <td class='text-center'><input type='checkbox' class='check-column' data-table='$tableName' data-column='" . htmlspecialchars($colDef, ENT_QUOTES) . "' $disabled></td>
            </tr>";
    }

    echo "</tbody></table>";
    if (!$existsRemote) {
      preg_match('/CREATE TABLE `' . preg_quote($tableName, '/') . '` \((.*?)\)[^\;]*;/s', $localContent, $m);
      $createStmt = $m[0] ?? '';
      echo "<button class='btn btn-sm btn-primary sync-table' data-table='$tableName' data-create='" . htmlspecialchars($createStmt, ENT_QUOTES) . "'>➕ Sync Table</button>";
    }
    echo "</div></div></div>";
  }
  ?>

  <button class="btn btn-success mt-3" id="syncSelected">Sync Selected Columns/Tables</button>
</div>

<?php require_once 'footer.php'; ?>



<script>
  // Sync Table button
  document.querySelectorAll('.sync-table').forEach(btn => {
    btn.addEventListener('click', () => {
      const table = btn.dataset.table;
      const createSQL = btn.dataset.create;

      const payload = `table=${encodeURIComponent(table)}&create=${encodeURIComponent(createSQL)}`;

      fetch('apply-schema.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: payload
      })
        .then(res => res.text())
        .then(text => {
          try {
            const data = JSON.parse(text);
            if (data.status === 'ok') {
              const r = data.results[0];
              if (r.status === 'table_created') {
                alert(`✅ Table "${r.table}" created successfully`);
                location.reload();
              } else if (r.status === 'error') {
                alert(`❌ Error creating "${r.table}": ${r.error}`);
              } else {
                alert(`ℹ️ ${r.status} for "${r.table}"`);
              }
            } else {
              alert(data.msg || '❌ Unknown server error');
            }
          } catch (e) {
            alert('❌ JSON parse error: ' + e.message + '\nRaw response:\n' + text);
          }
        })
        .catch(err => alert('❌ AJAX error: ' + err.message));
    });
  });


  // Apply Column button
  document.querySelectorAll('.sync-column').forEach(btn => {
    btn.addEventListener('click', () => {
      const table = btn.dataset.table;
      const column = btn.dataset.column;

      const payload = JSON.stringify([{ table, column }]);

      fetch('apply-schema.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'items=' + encodeURIComponent(payload)
      })
        .then(res => res.text()) // ✅ raw response first
        .then(text => {
          try {
            const data = JSON.parse(text);
            if (data.status === 'ok') {
              const r = data.results[0];
              if (r.status === 'applied') {
                alert(`✅ Column applied to "${r.table}": ${r.column}`);
                location.reload();
              } else if (r.status === 'skipped') {
                alert(`⚠️ Skipped: ${r.table} → ${r.column}\nReason: ${r.error}`);
              } else {
                alert(`❌ Error in "${r.table}": ${r.error}`);
              }
            } else {
              alert('❌ Server error: ' + data.msg);
            }
          } catch (e) {
            alert('❌ JSON parse error: ' + e.message + '\nRaw response:\n' + text);
          }
        })
        .catch(err => {
          alert('❌ AJAX error: ' + err.message);
        });
    });
  });


  document.querySelectorAll('.toggle-check').forEach(btn => {
    btn.addEventListener('click', () => {
      const table = btn.dataset.table;
      document.querySelectorAll(`.check-column[data-table='${table}']`).forEach(c => c.checked = !c.checked);
    });
  });


  function showMessage(text, type = 'info') {
    const container = document.createElement('div');
    container.className = `alert alert-${type}`;
    container.innerText = text;

    // পজিশন নির্ধারণ
    container.style.position = 'fixed';
    container.style.top = '20px';
    container.style.left = '50%';
    container.style.transform = 'translateX(-50%)';
    container.style.zIndex = '9999';
    container.style.minWidth = '300px';
    container.style.textAlign = 'center';
    container.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';

    document.body.appendChild(container);

    // স্বয়ংক্রিয়ভাবে ৫ সেকেন্ড পর লুকাবে
    setTimeout(() => container.remove(), 5000);
  }




  document.getElementById('syncSelected').addEventListener('click', () => {
    const selected = [];
    document.querySelectorAll('.check-column:checked').forEach(chk => {
      selected.push({
        table: chk.dataset.table,
        column: chk.dataset.column
      });
    });

    if (selected.length === 0) {
      showMessage('❌ No columns selected!', 'danger');
      return;
    }



    fetch('apply-schema.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'items=' + encodeURIComponent(JSON.stringify(selected))
    })
      .then(res => res.text()) // ✅ raw text first
      .then(text => {
        try {
          const data = JSON.parse(text);
          if (data.status === 'ok') {
            let msg = '✅ Sync completed:\n';
            data.results.forEach(r => {
              msg += `${r.table} → ${r.column} : ${r.status}\n`;
              if (r.error) msg += '⚠️ Error: ' + r.error + '\n';
            });
            showMessage(msg, 'success');
          } else {
            showMessage('❌ Server error: ' + data.msg, 'danger');
          }
        } catch (e) {
          showMessage('❌ JSON parse error: ' + e.message + '\nRaw response:\n' + text, 'danger');
        }
      })
      .catch(err => {
        showMessage('❌ AJAX error: ' + err.message, 'danger');
      });




  });



</script>



<script>


  // ✅ Set summary counts dynamically
  document.getElementById('count-total-tables').textContent = <?php echo $total_table; ?>;
  document.getElementById('count-found-tables').textContent = <?php echo $found_table; ?>;
  document.getElementById('count-missing-tables').textContent = <?php echo $missing_table; ?>;
  document.getElementById('count-total-cols').textContent = <?php echo $total_column; ?>;
  document.getElementById('count-match-cols').textContent = <?php echo $match_column; ?>;
  document.getElementById('count-diff-cols').textContent = <?php echo $different_column; ?>;
  document.getElementById('count-missing-cols').textContent = <?php echo $missing_column; ?>;
</script>

<script>
  function reve() {
    const url = new URL(window.location);
    if (url.searchParams.get('reverse') === '1') {
      url.searchParams.delete('reverse');
    } else {
      url.searchParams.set('reverse', '1');
    }
    window.location = url.toString();
  }
</script>

</body>

</html>