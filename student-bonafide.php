<?php
require_once 'header.php';

$stid = $_GET['stid'] ?? '';
$year = $_GET['year'] ?? date('Y');

$student = null;
if (!empty($stid)) {
    $stmt = $conn->prepare("SELECT s.*, si.*, si.rollno AS current_roll
                            FROM students s
                            INNER JOIN sessioninfo si ON s.stid = si.stid AND s.sccode = si.sccode
                            WHERE s.stid = ? AND s.sccode = ? AND si.sessionyear = ?
                            LIMIT 1");
    $stmt->bind_param("sis", $stid, $sccode, $year);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$student) {
        // Fallback to latest session
        $stmt = $conn->prepare("SELECT s.*, si.*, si.rollno AS current_roll
                                FROM students s
                                INNER JOIN sessioninfo si ON s.stid = si.stid AND s.sccode = si.sccode
                                WHERE s.stid = ? AND s.sccode = ?
                                ORDER BY si.sessionyear DESC LIMIT 1");
        $stmt->bind_param("si", $stid, $sccode);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}
?>

<style>
    .certificate-card {
        background: #fff;
        border: 2px solid #334155;
        border-radius: 8px;
        padding: 40px;
        margin: 20px auto;
        max-width: 900px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        position: relative;
    }

    .certificate-inner {
        border: 2px dashed #94a3b8;
        padding: 30px;
        border-radius: 6px;
    }

    .certificate-title-box {
        text-align: center;
        margin: 25px 0 35px 0;
    }

    .certificate-title {
        font-family: 'Times New Roman', Times, serif;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        display: inline-block;
        border-bottom: 2px solid #1e293b;
        padding-bottom: 6px;
        color: #0f172a;
    }

    .cert-body {
        font-family: 'Georgia', serif;
        font-size: 15.5px;
        line-height: 2.2;
        color: #1e293b;
        text-align: justify;
    }

    .cert-highlight {
        font-weight: 700;
        color: #0f172a;
        text-decoration: underline;
        text-underline-offset: 4px;
    }

    .meta-line {
        font-size: 13px;
        color: #475569;
        font-weight: 600;
    }

    @media print {
        body {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        nav, .no-print, .layout-navbar, .layout-menu, .footer, .content-footer {
            display: none !important;
        }
        .container-xxl {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
        }
        .certificate-card {
            border: 2px solid #000 !important;
            box-shadow: none !important;
            padding: 20px !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        .certificate-inner {
            border: 2px dashed #000 !important;
            padding: 20px !important;
        }
        .cert-body {
            font-size: 14.5px !important;
            line-height: 2.1 !important;
        }
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print" style="max-width: 900px; margin: 0 auto;">
        <div class="d-flex align-items-center gap-2">
            <a href="students-list.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <h4 class="fw-bold m-0 ms-2">Bonafide Certificate</h4>
        </div>
        <button class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Certificate
        </button>
    </div>

    <?php if (!$student): ?>
        <div class="alert alert-warning text-center border-0 shadow-sm py-4" style="max-width: 900px; margin: 0 auto;">
            <i class="bi bi-exclamation-triangle fs-3 d-block mb-2 text-warning"></i>
            <strong>শিক্ষার্থীর তথ্য পাওয়া যায়নি।</strong>
            <p class="text-muted small mt-1">অনুগ্রহ করে সঠিক Student ID উল্লেখ করুন।</p>
        </div>
    <?php else: ?>
        <?php
        $pronoun_he_she = (in_array(strtolower($student['gender'] ?? ''), ['male', 'boy'])) ? 'He' : 'She';
        $pronoun_his_her = (in_array(strtolower($student['gender'] ?? ''), ['male', 'boy'])) ? 'His' : 'Her';
        $pronoun_him_her = (in_array(strtolower($student['gender'] ?? ''), ['male', 'boy'])) ? 'him' : 'her';
        $child_of = (in_array(strtolower($student['gender'] ?? ''), ['male', 'boy'])) ? 'Son' : 'Daughter';
        $dob_formatted = (!empty($student['dob']) && $student['dob'] != '0000-00-00') ? date('F d, Y', strtotime($student['dob'])) : '—';
        $ref_no = 'EIMB/BC/' . ($student['sessionyear'] ?? date('Y')) . '/' . $student['stid'];
        ?>

        <div class="certificate-card">
            <div class="certificate-inner">
                
                <!-- Institutional Letterhead -->
                <div class="letterhead-wrapper pb-2 border-bottom">
                    <?php include __DIR__ . '/templete/letter-head-01.php'; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 meta-line">
                    <div>
                        <span>Ref No:</span> <strong class="text-dark"><?= htmlspecialchars($ref_no) ?></strong>
                    </div>
                    <div>
                        <span>Date:</span> <strong class="text-dark"><?= date('d F, Y') ?></strong>
                    </div>
                </div>

                <div class="certificate-title-box">
                    <span class="certificate-title">BONAFIDE CERTIFICATE</span>
                    <div class="text-muted small mt-1 fw-bold">প্রত্যয়ন পত্র</div>
                </div>

                <div class="cert-body">
                    <p>
                        This is to certify that <span class="cert-highlight"><?= htmlspecialchars($student['stnameeng'] ?? '') ?></span>
                        <?php if (!empty($student['stnameben'])): ?>
                            (<span class="fw-bold"><?= htmlspecialchars($student['stnameben']) ?></span>),
                        <?php endif; ?>
                        <?= $child_of ?> of <span class="cert-highlight"><?= htmlspecialchars($student['fname'] ?? '—') ?></span>
                        and <span class="cert-highlight"><?= htmlspecialchars($student['mname'] ?? '—') ?></span>,
                        bearing <strong>Student ID: <span class="text-primary"><?= htmlspecialchars($student['stid'] ?? '') ?></span></strong>, 
                        is a bonafide and regular student of this institution in 
                        <strong>Class:</strong> <span class="cert-highlight"><?= htmlspecialchars($student['classname'] ?? '') ?></span>, 
                        <strong>Section:</strong> <span class="cert-highlight"><?= htmlspecialchars($student['sectionname'] ?? '') ?></span>, 
                        <strong>Roll No:</strong> <span class="cert-highlight"><?= htmlspecialchars($student['current_roll'] ?? $student['rollno'] ?? '') ?></span>, 
                        <strong>Academic Session:</strong> <span class="cert-highlight"><?= htmlspecialchars($student['sessionyear'] ?? '') ?></span>.
                    </p>

                    <p class="mt-3">
                        According to our institutional admission records, <?= strtolower($pronoun_his_her) ?> date of birth is 
                        <span class="cert-highlight"><?= $dob_formatted ?></span>. 
                        <?= $pronoun_his_her ?> residential address on record is 
                        <strong><?= htmlspecialchars(implode(', ', array_filter([$student['previll'] ?? '', $student['prepo'] ?? '', $student['preps'] ?? '', $student['predist'] ?? '']))) ?: '—' ?></strong>.
                    </p>

                    <p class="mt-3">
                        To the best of my knowledge and institutional records, <?= strtolower($pronoun_he_she) ?> bears a good moral character and conduct. <?= $pronoun_he_she ?> has not taken part in any activity subversive of the state or of institutional discipline.
                    </p>

                    <p class="mt-3">
                        I wish <?= $pronoun_him_her ?> every success, sound health, and a bright future in life.
                    </p>
                </div>

                <!-- Signatures -->
                <div class="mt-5 pt-5">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-top border-dark pt-1 mx-2">
                                <small class="fw-bold text-dark d-block">Checked By</small>
                                <small class="text-muted">Head Clerk / Office</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-top border-dark pt-1 mx-2">
                                <small class="fw-bold text-dark d-block">Class Teacher</small>
                                <small class="text-muted">Signature &amp; Date</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-top border-dark pt-1 mx-2">
                                <small class="fw-bold text-dark d-block">Head of Institution</small>
                                <small class="text-muted">Principal / Headmaster (Seal)</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>
</body>
</html>
