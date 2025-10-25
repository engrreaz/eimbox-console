<?php require_once 'header.php';

// ডেটা লোড
$sql = "SELECT se.*, sat.category
FROM suspicious_events se
LEFT JOIN suspicious_activity_types sat ON se.matched_rule_id = sat.id
ORDER BY se.created_at DESC";
$res = $conn->query($sql);
?>
<style>
    .badge.log_only {
        background: #6c757d;
    }

    .badge.alert {
        background: #ffc107;
        color: #000;
    }

    .badge.review {
        background: #17a2b8;
    }

    .badge.block {
        background: #dc3545;
    }

    .risk-low {
        color: green;
        font-weight: 600;
    }

    .risk-mid {
        color: #e67e22;
        font-weight: 600;
    }

    .risk-high {
        color: #c0392b;
        font-weight: 600;
    }
</style>

<div class="container">
    <h2>🔍 Suspicious Activity Dashboard</h2>
    <div class="table-responsive">
        <table id="activityTable" class="display table table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Category</th>
                    <th>Event</th>
                    <th>Risk</th>
                    <th>Action</th>
                    <th>IP</th>
                    <th>Time</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                while ($row = $res->fetch_assoc()):
                    $riskClass = ($row['risk_score'] >= 30 ? 'risk-high' : ($row['risk_score'] >= 15 ? 'risk-mid' : 'risk-low'));
                    ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= htmlspecialchars($row['user_id'] ?? 0); ?></td>
                        <td><?= htmlspecialchars($row['email'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($row['category'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($row['event_type'] ?? ''); ?></td>
                        <td class="<?= $riskClass; ?>"><?= $row['risk_score'] ?? 0; ?></td>
                        <td><span
                                class="badge <?= $row['recommended_action'] ?? ''; ?>"><?= strtoupper($row['recommended_action']); ?></span>
                        </td>
                        <td><?= htmlspecialchars($row['ip_address'] ?? ''); ?></td>
                        <td><?= date("Y-m-d H:i", strtotime($row['created_at'])); ?></td>
                        <td><?= htmlspecialchars($row['description'] ?? ''); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script></script>

<script>
    $(document).ready(function () {
        $('#activityTable').DataTable({
            "pageLength": 25,
            "order": [[0, "desc"]],
            "language": {
                "search": "🔎 অনুসন্ধান:",
                "lengthMenu": "প্রতি পৃষ্ঠায় _MENU_ রেকর্ড দেখাও",
                "zeroRecords": "কোন তথ্য পাওয়া যায়নি",
                "info": "মোট _TOTAL_ রেকর্ডের মধ্যে _START_ থেকে _END_ পর্যন্ত দেখানো হচ্ছে",
                "infoEmpty": "কোন তথ্য নেই",
                "infoFiltered": "(মোট _MAX_ রেকর্ড থেকে ফিল্টার করা হয়েছে)"
            }
        });
    });
</script>
<!-- ----------------------------------- -->
</body>

</html>