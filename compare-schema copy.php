<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">


  <?php

  $localFile = __DIR__ . '/schema/localhost.sql';
  $remoteFile = __DIR__ . '/schema/remote.sql';
  $ttl = 'Local VS Remote';
  if (isset($_GET['reverse']) && $_GET['reverse'] == '1') {
    $temp = $localFile;
    $localFile = $remoteFile;
    $remoteFile = $temp;
    $ttl = 'Remote VS Local';
  }

  if (!file_exists($localFile)) {
    echo "<div class='alert alert-danger'>Local schema file not found!</div>";
    exit;
  }

  $localContent = file_get_contents($localFile);
  $remoteContent = file_exists($remoteFile) ? file_get_contents($remoteFile) : '';


  $found_table = $missing_table = $missing_column = $different_column = $match_column = 0;
  ?>

  <h4 class="mb-4">🔍 Database Schema Comparison <?php echo $ttl; ?></h4>
  <?php
  function parseTables($sql)
  {
    $tables = [];

    // Match CREATE TABLE blocks including multiline content
    preg_match_all('/CREATE TABLE `([^`]+)` \((.*?)\)\s*ENGINE=/s', $sql, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
      $tableName = $match[1];
      $body = $match[2];

      // Break into lines safely
      $lines = preg_split('/\r\n|\r|\n/', $body);
      $columns = [];

      foreach ($lines as $line) {
        $line = trim($line);
        if (substr($line, -1) === ',')
          $line = substr($line, 0, -1);

        // Skip constraints, keys, indexes
        if (preg_match('/^(PRIMARY|UNIQUE|KEY|CONSTRAINT|INDEX|FOREIGN|CHECK)/i', $line))
          continue;

        // Only keep actual column definitions
        if (preg_match('/^`[^`]+`/', $line)) {
          $columns[] = $line;
        }
      }

      $tables[$tableName] = $columns;
    }

    return $tables;
  }


  $localTables = parseTables($localContent);
  $remoteTables = parseTables($remoteContent);

  $index = 0;
  foreach ($localTables as $tableName => $localCols) {
    $index++;
    $collapseId = "collapse_$index";
    $remoteCols = $remoteTables[$tableName] ?? [];
    $existsRemote = !empty($remoteCols);

    echo "<div class='card mb-3'>";
    echo "<div class='card-header d-flex justify-content-between align-items-center p-2' >";

    echo "<div class='d-flex align-items-center'>";
    echo "<h6 class='mb-0'>$tableName</h6>";
    echo ($existsRemote
      ? "<span class='badge bg-success ms-2'>Found in Remote</span>"
      : "<span class='badge bg-danger ms-2'>Missing in Remote</span>");
    echo "</div>";

    echo "<div class='d-flex align-items-center gap-2'>";

    if (!$existsRemote) {
      preg_match('/CREATE TABLE `' . preg_quote($tableName, '/') . '` \((.*?)\)[^\;]*;/s', $localContent, $m);
      $createStmt = $m[0] ?? '';
      echo "<button class='btn btn-sm btn-primary sync-table p-1' data-table='$tableName' data-create='" . htmlspecialchars($createStmt, ENT_QUOTES) . "'>➕ Sync Table</button>";
    }
    echo "<button class='btn btn-sm ' data-bs-toggle='collapse' data-bs-target='#$collapseId'><i class='bi bi-chevron-down'></i></button>";



    echo "</div>";
    echo "</div>";


    echo "<div id='$collapseId' class='collapse'><div class='card-body'>";
    echo "<table class='table table-sm table-bordered align-middle'>";
    echo "<thead><tr>
        <th>Column / Definition</th>
        <th>Status</th>
        <th>Action</th>
        <th class='text-center'>
          <button class='btn btn-sm btn-outline-secondary toggle-check p-1 ' data-table='$tableName'>Toggle</button>
        </th>
      </tr></thead><tbody>";


    foreach ($localCols as $colDef) {
      preg_match('/^`([^`]+)`/', $colDef, $cm);
      $colName = $cm[1] ?? '';
      $remoteColNames = array_map(fn($def) => preg_match('/^`([^`]+)`/', $def, $m) ? $m[1] : '', $remoteCols);

      $existsByName = in_array($colName, $remoteColNames);
      $isExactMatch = in_array($colDef, $remoteCols);

      $disabled_check = '';
      if ($isExactMatch || $existsByName) {
        $disabled_check = 'disabled';
      }

      $status = $isExactMatch ? "<span class='badge bg-success'>Matched</span>" :
        ($existsByName ? "<span class='badge bg-warning'>Different</span>" :
          "<span class='badge bg-danger'>Missing</span>");

      $actionBtn = $isExactMatch ? '' :
        "<button class='btn btn-sm btn-outline-primary sync-column' data-table='$tableName' data-column='" . htmlspecialchars($colDef, ENT_QUOTES) . "'>⚙️ Apply</button>";

      echo "<tr>
                <td><code>$colDef</code></td>
                <td>$status</td>
                <td>$actionBtn</td>
                <td class='text-center'><input type='checkbox' class='check-column' data-table='$tableName' data-column='" . htmlspecialchars($colDef, ENT_QUOTES) . " ' . $disabled_check . ' ></td>
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
    container.className = `alert alert-${type} mt-3`;
    container.innerText = text;
    document.querySelector('.container-xxl').prepend(container);

    setTimeout(() => container.remove(), 5000); // Auto-hide after 8s
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
</body>

</html>