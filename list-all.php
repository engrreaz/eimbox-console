<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Student List</h4>
        </div>

        <div class="card-body">

            <!-- Filter Form -->
            <form method="GET" class="row g-3 mb-4">

                <div class="col-md-3">
                    <label class="form-label">Class</label>
                    <select name="classname" class="form-select">
                        <option value="">All Class</option>

                        <?php
                        $classQuery = mysqli_query($conn, "
                            SELECT DISTINCT areaname
                            FROM areas
                            WHERE sccode='$sccode' and sessionyear = '$sessionyear'
                            ORDER BY idno ASC
                        ");

                        while ($classRow = mysqli_fetch_assoc($classQuery)) {
                            $selected = (@$_GET['classname'] == $classRow['areaname']) ? 'selected' : '';
                            echo '<option value="' . $classRow['areaname'] . '" ' . $selected . '>' . $classRow['areaname'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Section</label>
                    <select name="sectionname" class="form-select">
                        <option value="">All Section</option>

                        <?php
                        $sectionQuery = mysqli_query($conn, "
                            SELECT DISTINCT subarea
                            FROM areas
                            WHERE sccode='$sccode' AND sessionyear = '$sessionyear'
                            ORDER BY idno ASC
                        ");

                        while ($sectionRow = mysqli_fetch_assoc($sectionQuery)) {
                            $selected = (@$_GET['sectionname'] == $sectionRow['subarea']) ? 'selected' : '';
                            echo '<option value="' . $sectionRow['subarea'] . '" ' . $selected . '>' . $sectionRow['subarea'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Session</label>
                    <select name="sessionyear" class="form-select">
                        <option value="">All Session</option>

                        <?php
                        $sessionQuery = mysqli_query($conn, "
                            SELECT DISTINCT sessionyear
                            FROM areas
                            WHERE sccode='$sccode'
                            ORDER BY sessionyear DESC
                        ");

                        while ($sessionRow = mysqli_fetch_assoc($sessionQuery)) {
                            $selected = (@$_GET['sessionyear'] == $sessionRow['sessionyear']) ? 'selected' : '';
                            echo '<option value="' . $sessionRow['sessionyear'] . '" ' . $selected . '>' . $sessionRow['sessionyear'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <div class="d-flex w-100 gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            Filter
                        </button>
                        <button type="button" class="btn btn-success flex-fill" onclick="window.print()">
                            Print
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary px-2" onclick="changeFontSize(1)" title="Increase Font">A+</button>
                            <button type="button" class="btn btn-secondary px-2" onclick="changeFontSize(-1)" title="Decrease Font">A-</button>
                        </div>
                    </div>
                </div>

            </form>

            <?php

            $where = "";

            if (!empty($_GET['classname'])) {
                $classname = mysqli_real_escape_string($conn, $_GET['classname']);
                $where .= " AND si.classname='$classname'";
            }

            if (!empty($_GET['sectionname'])) {
                $sectionname = mysqli_real_escape_string($conn, $_GET['sectionname']);
                $where .= " AND si.sectionname='$sectionname'";
            }

            if (!empty($_GET['sessionyear'])) {
                $sessionyear = mysqli_real_escape_string($conn, $_GET['sessionyear']);
                $where .= " AND si.sessionyear='$sessionyear'";
            }

            $sql = "
                SELECT 
                    si.sccode,
                    si.sessionyear,
                    si.classname,
                    si.sectionname,
                    si.rollno,
                    si.stid,

                    st.stnameeng,
                    st.fname,
                    st.mname,
                    st.previll, st.prepo, st.preps, st.predist,
                    st.guarmobile,
                    st.dob

                FROM sessioninfo si

                LEFT JOIN students st 
                    ON si.sccode = st.sccode
                    AND si.stid = st.stid

                WHERE si.sccode='$sccode'
                $where

                ORDER BY 
                    si.sessionyear DESC,
                    si.classname ASC,
                    si.sectionname ASC,
                    CAST(si.rollno AS UNSIGNED) ASC
                    LIMIT 50
            ";

            $result = mysqli_query($conn, $sql);

            echo '<div id="print-block-list">';

            // --- Print Only Summary Section ---
            echo '<div class="d-none d-print-block mb-4">';
            include 'templete/letter-head-01.php';
            echo '<h4 class="text-center mt-4 mb-3" style="text-decoration: underline;">Student Summary</h4>';
            
            $summarySql = "
                SELECT 
                    si.classname,
                    si.sectionname,
                    COUNT(si.stid) as total_students
                FROM sessioninfo si
                WHERE si.sccode='$sccode' $where
                GROUP BY si.classname, si.sectionname
                ORDER BY si.classname ASC, si.sectionname ASC
            ";
            $summaryResult = mysqli_query($conn, $summarySql);
            $total_students = 0;
            
            echo '<table class="table table-bordered table-sm text-center" style="width: 80%; margin: 0 auto;">
                    <thead class="table-light">
                        <tr>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Total Students</th>
                        </tr>
                    </thead>
                    <tbody>';
                    
            if($summaryResult && mysqli_num_rows($summaryResult) > 0) {
                while($sRow = mysqli_fetch_assoc($summaryResult)) {
                    echo '<tr>
                        <td>'.$sRow['classname'].'</td>
                        <td>'.$sRow['sectionname'].'</td>
                        <td>'.$sRow['total_students'].'</td>
                    </tr>';
                    $total_students += $sRow['total_students'];
                }
            } else {
                echo '<tr><td colspan="3">No data found.</td></tr>';
            }
            echo '      <tr>
                            <th colspan="2" class="text-end">Grand Total</th>
                            <th>'.$total_students.'</th>
                        </tr>
                    </tbody>
                  </table>';
            echo '<div style="page-break-before: always;"></div>';
            echo '</div>'; // End d-print-block summary
            // ----------------------------------

            $currentGroup = "";

            while ($row = mysqli_fetch_assoc($result)) {

                $groupName = $row['sessionyear'] . ' | ' . $row['classname'] . ' | ' . $row['sectionname'];

                if ($currentGroup != $groupName) {

                    // Previous Table Close
                    if ($currentGroup != "") {
                        echo '
                                </tbody>
                            </table>
                        </div>
                        <br>';
                    }

                    // New Group Start
                    $currentGroup = $groupName;

                    echo '
                    <div class="table-responsive mb-4">

                        <h5 class="mb-3 text-primary">
                            Session: ' . $row['sessionyear'] . ' |
                            Class: ' . $row['classname'] . ' |
                            Section: ' . $row['sectionname'] . '
                        </h5>

                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-dark">
                                <tr>
                                    <th width="60">SL</th>
                                    <th width="80">Roll</th>
                                    <th>Class (Section)</th>
                                    <th>Student Name</th>
                                    <th>Parents Name</th>
                                    <th>Address</th>
                                    <th>Mobile & DOB</th>
                                </tr>
                            </thead>

                            <tbody>
                    ';

                    $sl = 1;
                }

                echo '
                    <tr>
                        <td>' . $sl++ . '</td>
                        <td>' . $row['rollno'] . '</td>
                         <td>' . $row['classname'] . '<br>(' . $row['sectionname'] . ')</td>
                        <td>' . $row['stnameeng'] . '</td>
                        <td>' . $row['fname'] . '<br>'.  $row['mname'] .'</td>
                        <td>' . $row['previll']  . '<br>' . $row['preps'] . '<br>' . $row['predist'] .'</td>
                        <td>' . $row['guarmobile']  . '<br>' . $row['dob'] . '</td>
                    </tr>
                ';
            }

            // Final Table Close
            if ($currentGroup != "") {
                echo '
                        </tbody>
                    </table>
                </div>
                ';
            } else {

                echo '
                <div class="alert alert-warning">
                    No student found.
                </div>
                ';
            }

            echo '</div>'; // End print-block

            ?>

        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<style>
#print-block-list {
    --print-font-size: 14px;
    font-size: var(--print-font-size);
}
#print-block-list table {
    font-size: inherit;
}

@media print {
    /* Hide specific layout elements */
    .layout-menu,
    .layout-navbar,
    .footer,
    .content-footer,
    .card-header,
    form,
    .btn,
    .buy-now,
    .layout-overlay,
    .toast-container,
    #mainFooter,
    #extend-footer,
    #feedbackModal,
    .app-brand {
        display: none !important;
    }

    /* Hide any direct siblings of print-block-list within card-body */
    .card-body > *:not(#print-block-list) {
        display: none !important;
    }
    
    /* Hide extra scripts/divs injected directly into body */
    body > *:not(.layout-wrapper):not(script) {
        display: none !important;
    }
    
    /* Reset layout container padding/margins */
    .layout-page,
    .content-wrapper,
    .container-xxl,
    .card,
    .card-body {
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>

<script>
let currentFontSize = 14;
function changeFontSize(step) {
    currentFontSize += step;
    if(currentFontSize < 8) currentFontSize = 8;
    if(currentFontSize > 24) currentFontSize = 24;
    document.getElementById('print-block-list').style.setProperty('--print-font-size', currentFontSize + 'px');
}
</script>
<!-- ----------------------------------- -->
</body>
</html>