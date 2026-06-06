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
                            WHERE sccode='$sccode'
                            ORDER BY areaname ASC
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
                            WHERE sccode='$sccode'
                            ORDER BY subarea ASC
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
                    <button type="submit" class="btn btn-primary w-100">
                        Filter
                    </button>
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
            ";

            $result = mysqli_query($conn, $sql);

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
                                    <th>Student Name</th>
                                    <th>Parents Name</th>
                                    <th>Class</th>
                                    <th>Guardian Mobile</th>
                                    <th width="120">DOB</th>
                                    <th width="100">Student ID</th>
                                </tr>
                            </thead>

                            <tbody>
                    ';

                    // $sl = 1;
                }

                echo '
                    <tr>
                        <td>' . $sl++ . '</td>
                        <td>' . $row['rollno'] . '</td>
                        <td>' . $row['stnameeng'] . '</td>
                        <td>' . $row['fname'] . '<br>'.  $row['mname'] .'</td>
                        <td>' .  '</td>
                        <td>' . $row['classname'] . '<br>' . $row['sectionname'] .'</td>
                        <td>' . $row['guarmobile'] . '</td>
                        <td>' . $row['dob'] . '</td>
                        <td>' . $row['stid'] . '</td>
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

            ?>

        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script>

</script>
<!-- ----------------------------------- -->
</body>
</html>