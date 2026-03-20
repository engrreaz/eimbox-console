<?php require_once 'header.php'; ?>

<?php
// ================= FILTER =================
$range = $_GET['range'] ?? 'today';

$dateWhere = "DATE(ua.timestamp)=CURDATE()";

if ($range == 'month') {
    $dateWhere = "YEAR(ua.timestamp)=YEAR(CURDATE()) 
                  AND MONTH(ua.timestamp)=MONTH(CURDATE())";
} elseif ($range == 'year') {
    $dateWhere = "YEAR(ua.timestamp)=YEAR(CURDATE())";
}

// ================= MAIN QUERY =================
$sql = "
SELECT 
    ua.sccode,
    MAX(ua.timestamp) AS last_active,
    COUNT(*) AS total_actions,
    COUNT(DISTINCT ua.email) AS unique_emails,  -- আলাদা ইমেইল সংখ্যা

    ANY_VALUE(sc.scname) AS scname,
    ANY_VALUE(sc.sccategory) AS sccategory,
    ANY_VALUE(sc.scadd1) AS scadd1,
    ANY_VALUE(sc.scadd2) AS scadd2,
    ANY_VALUE(sc.ps) AS ps,
    ANY_VALUE(sc.dist) AS dist,
    ANY_VALUE(sc.mobile) AS mobile,

    IFNULL(ANY_VALUE(inv.unpaid_total),0) AS unpaid_total

FROM user_actions ua
LEFT JOIN scinfo sc ON sc.sccode = ua.sccode
LEFT JOIN (
    SELECT sccode, SUM(due_amount) AS unpaid_total
    FROM billing_invoices
    WHERE due_amount > 0
    GROUP BY sccode
) inv ON inv.sccode = ua.sccode

WHERE ua.sccode IS NOT NULL
AND $dateWhere
GROUP BY ua.sccode
ORDER BY last_active DESC;
";


$q = mysqli_query($conn, $sql);
?>

<style>
    .kebab-btn {
        border: none;
        background: transparent;
        font-size: 20px;
        line-height: 1;
        
        padding: 6px;
        border-radius: 50%;
    }

    .kebab-btn:hover {
        background: #e5d5e9;
    }

    .kebab-dropdown .dropdown-menu {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .kebab-dropdown .dropdown-item {
        font-size: 14px;
    }

    .kebab-dropdown .dropdown-item:hover {
        background: #f5f7fa;
    }
</style>

<div class="container-fluid">

    <!-- ================= HEADER ================= -->
    <div class="row mb-3">
        <div class="col-md-6">
            <h4 class="fw-bold">Institution Activity Monitor</h4>
        </div>
        <div class="col-md-6 text-end">

            <a href="?range=today"
                class="btn btn-sm <?= ($range == 'today' ? 'btn-primary' : 'btn-outline-primary') ?>">Today</a>

            <a href="?range=month"
                class="btn btn-sm <?= ($range == 'month' ? 'btn-primary' : 'btn-outline-primary') ?>">This
                Month</a>

            <a href="?range=year"
                class="btn btn-sm <?= ($range == 'year' ? 'btn-primary' : 'btn-outline-primary') ?>">This
                Year</a>
        </div>
    </div>

    <!-- ================= SEARCH ================= -->
    <div class="row mb-3">
        <div class="col-md-4 ms-auto">
            <input type="text" id="searchInput" class="form-control form-control-sm"
                placeholder="Search institution...">
        </div>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="card">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0" id="monitorTable">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Institution</th>
                            <th>Contact</th>
                            <th>User</th>
                            <th>Actions</th>
                            <th>Unpaid</th>
                            <th>Last Active</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        $i = 1;
                        while ($r = mysqli_fetch_assoc($q)):
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>

                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($r['school_name']) ?></div>
                                    <div class="text-muted small">Code: <?= $r['sccode'] ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($r['address']) ?></div>
                                </td>

                                <td>
                                    📞 <?= htmlspecialchars($r['phone']) ?>
                                </td>

                                <td>
                                    <span class="badge bg-info">
                                        <?= $r['unique_emails'] ?> User
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-info">
                                        <?= $r['total_actions'] ?> actions
                                    </span>
                                </td>

                                <td>
                                    <?php if ($r['unpaid_total'] > 0): ?>
                                        <span class="badge bg-danger">
                                            ৳ <?= number_format($r['unpaid_total']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class=" small"><?= date('d M Y h:i A', strtotime($r['last_active'])) ?></div>
                                    
                                    <div class="text-muted small">
                                        
                                        <?= timeAgo($r['last_active']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown kebab-dropdown">
                                        <button class="btn kebab-btn" type="button" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li><a class="dropdown-item" href="#">View Log</a></li>
                                            <li><a class="dropdown-item" href="#">View Profile</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    // ================= SEARCH FILTER =================
    document.getElementById('searchInput').addEventListener('keyup', function () {
        let val = this.value.toLowerCase();
        document.querySelectorAll('#monitorTable tbody tr').forEach(tr => {
            tr.style.display = tr.innerText.toLowerCase().includes(val) ? '' : 'none';
        });
    });
</script>