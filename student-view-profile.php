<?php 
require_once 'header.php'; 

// প্যারামিটার গ্রহণ
$stid = $_GET['stid'] ?? null;
$cls = $_GET['cls'] ?? null;
$sec = $_GET['sec'] ?? null;
$year = $_GET['year'] ?? date('Y');

// ১. ডাটা ফেচিং লজিক
$students_data = [];

if ($stid) {
    // সিঙ্গেল স্টুডেন্ট কুয়েরি
    $sql = "SELECT s.*, si.* FROM students s 
            INNER JOIN sessioninfo si ON s.stid = si.stid 
            WHERE s.stid = '$stid' AND si.sccode = '$sccode' AND si.sessionyear = '$year'";
} elseif ($cls) {
    // পুরো ক্লাসের স্টুডেন্ট কুয়েরি
    $sec_cond = $sec ? "AND si.sectionname = '$sec'" : "";
    $sql = "SELECT s.*, si.* FROM students s 
            INNER JOIN sessioninfo si ON s.stid = si.stid 
            WHERE si.classname = '$cls' $sec_cond AND si.sccode = '$sccode' AND si.sessionyear = '$year'
            ORDER BY si.rollno ASC";
}

if (isset($sql)) {
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $students_data[] = $row;
    }
}
?>

<style>
    /* সাধারণ ডিসপ্লে স্টাইল */
    .profile-card {
        background: #fff;
        border: 1px solid #ddd;
        padding: 30px;
        margin-bottom: 30px;
        position: relative;
    }
    
    .student-photo {
        width: 120px;
        height: 135px;
        border: 1px solid #ccc;
        object-fit: cover;
        padding: 2px;
    }

    .info-table th {
        width: 180px;
        background-color: #f9f9f9;
        font-weight: 600;
        color: #555;
    }

    /* প্রিন্ট সেটিংস (A4) */
    @media print {
        body { background: #fff !important; }
        nav, .no-print, .layout-navbar, .layout-menu, .footer { display: none !important; }
        .container-xxl { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .content-wrapper { margin: 0 !important; padding: 0 !important; }
        
        .profile-card {
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            page-break-after: always; /* প্রতি শিক্ষার্থীর পর নতুন পেজ */
            height: 290mm; /* A4 Height */
        }

        .table { border-color: #333 !important; }
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="fw-bold m-0">Student Profile View</h4>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Profile
        </button>
    </div>

    <?php if (empty($students_data)): ?>
        <div class="alert alert-warning text-center">কোন শিক্ষার্থীর তথ্য পাওয়া যায়নি।</div>
    <?php else: ?>
        <?php foreach ($students_data as $s): ?>
            <div class="profile-card">
                
                <div class="text-center mb-4">
                    <h2 class="fw-bold mb-0">EIMBox Model School & College</h2>
                    <p class="mb-0 text-muted">Student Identification Record</p>
                    <hr style="border-top: 2px solid #333;">
                </div>

                <div class="row">
                    <div class="col-9">
                        <h4 class="text-primary fw-bold mb-3"><?= $s['sname'] ?></h4>
                        <table class="table table-bordered table-sm info-table">
                            <tr>
                                <th>Student ID</th> <td><?= $s['stid'] ?></td>
                                <th>Session</th> <td><?= $s['sessionyear'] ?></td>
                            </tr>
                            <tr>
                                <th>Class</th> <td><?= $s['classname'] ?></td>
                                <th>Section</th> <td><?= $s['sectionname'] ?></td>
                            </tr>
                            <tr>
                                <th>Roll No</th> <td><?= $s['rollno'] ?></td>
                                <th>Gender</th> <td><?= $s['gender'] ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-3 text-end">
                        <img src="path_to_photos/<?= $s['stid'] ?>.jpg" 
                             onerror="this.src='https://via.placeholder.com/120x135?text=No+Photo'" 
                             class="student-photo shadow-sm" alt="Student Photo">
                    </div>
                </div>

                <div class="mt-4">
                    <h6 class="fw-bold text-uppercase border-bottom pb-1 mb-3">Personal Information</h6>
                    <table class="table table-bordered table-sm info-table">
                        <tr>
                            <th>Father's Name</th> <td colspan="3"><?= $s['fname'] ?></td>
                        </tr>
                        <tr>
                            <th>Mother's Name</th> <td colspan="3"><?= $s['mname'] ?></td>
                        </tr>
                        <tr>
                            <th>Date of Birth</th> <td><?= date('d M, Y', strtotime($s['dob'])) ?></td>
                            <th>Blood Group</th> <td class="text-danger fw-bold"><?= $s['bloodgroup'] ?></td>
                        </tr>
                        <tr>
                            <th>Religion</th> <td><?= $s['religion'] ?></td>
                            <th>Nationality</th> <td><?= $s['nationality'] ?></td>
                        </tr>
                        <tr>
                            <th>Mobile No</th> <td><?= $s['mobile'] ?></td>
                            <th>Status</th> <td><?= $s['status'] ?></td>
                        </tr>
                        <tr>
                            <th style="height: 60px;">Present Address</th>
                            <td colspan="3"><?= $s['pre_addr'] ?></td>
                        </tr>
                        <tr>
                            <th style="height: 60px;">Permanent Address</th>
                            <td colspan="3"><?= $s['per_addr'] ?></td>
                        </tr>
                    </table>
                </div>

                <div class="mt-5 pt-5 d-none d-print-block">
                    <div class="row text-center">
                        <div class="col-4">
                            <p class="border-top border-dark d-inline-block px-4">Class Teacher</p>
                        </div>
                        <div class="col-4">
                            <p class="border-top border-dark d-inline-block px-4">Guardian</p>
                        </div>
                        <div class="col-4">
                            <p class="border-top border-dark d-inline-block px-4">Principal Signature</p>
                        </div>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>

<script>
    // প্রোফাইল পেজে কোনো বিশেষ জাভাস্ক্রিপ্ট দরকার হলে এখানে দিন।
</script>
</body>
</html>