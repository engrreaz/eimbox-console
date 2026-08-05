<?php
$einame = $scname;
$eiadd = $scaddress;
$eicontact = $scmobile;
?>

<style>
  /* ===== PRINT SETUP ===== */
  @page {
    size: A4 landscape;
    margin: 0;
  }

  @media print {
    /* Ensure html and body take full page dimensions */
    html, body {
      width: 100%;
      height: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden; /* Prevent scrollbars in print preview */
    }
    body {
      margin: 0;
      padding: 0;
    }
    /* Override any padding from .page-a4 class when printing */
    .page-a4 {
      padding: 0 !important;
    }
    .full-box {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%; /* Ensure it takes full available width */
      height: 100%; /* Ensure it takes full available height */
      margin: 0; /* Explicitly remove any auto margin in print */
      padding: 0 !important; /* Remove any padding that might be applied */
      background-size: cover; /* Ensure background covers the full area */
      background-position: center center; /* Ensure background is centered */
      background-repeat: no-repeat; /* Ensure no repetition */
    }
  }

  /* ===== FULL PAGE COVER ===== */
  .full-box {
    width: 297mm;
    height: 210mm;
    margin: 0 auto;
    position: relative;
    text-align: center;
    page-break-after: always;

    font-family: "Segoe UI", sans-serif;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;

    background-image: url('progress-report/background/cover-02.jpg');
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
  }

  .full-box-2 {
    page-break-after: always;
  }


  /* ===== CENTER CONTENT BLOCK ===== */
  .center-wrap {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }

  /* ===== TITLES ===== */
  .title-main {
    font-size: 64px;
    font-weight: 800;
    font-family: "Rosewood Std Regular", "Segoe UI", serif;
    color: #8a42c4;
    letter-spacing: 1px;
    margin: 0;
  }

  .title-exam {
    font-size: 42px;
    font-weight: 700;
    color: #008765;
    margin-top: 25px;
  }

  .title-class {
    font-size: 34px;
    color: seagreen;
    margin-top: 28px;
  }

  /* ===== FOOTER SCHOOL INFO ===== */
  .school-box {
    position: absolute;
    bottom: 28mm;
    left: 0;
    width: 100%;
    text-align: center;
    color: #1f2937;
  }

  .school-name {
    font-size: 30px;
    font-weight: 700;
  }

  .school-info {
    font-size: 22px;
    margin-top: 6px;
  }
</style>

<div class="full-box">

  <div class="center-wrap">
    <h1 class="title-main">Tabulation Sheet</h1>

    <div class="title-exam">
      <?= htmlspecialchars($exam) ?> Examination - <?= htmlspecialchars($sessionyear) ?>
    </div>

    <div class="title-class">
      CLASS : <b><?= htmlspecialchars($cn) ?></b>
      &nbsp;&nbsp;|&nbsp;&nbsp;
      <b><?= htmlspecialchars($secname) ?></b>
    </div>
  </div>

  <div class="school-box">
    <div class="school-name"><?= htmlspecialchars($einame) ?></div>
    <div class="school-info"><?= htmlspecialchars($eiadd) ?></div>
    <div class="school-info"><?= htmlspecialchars($eicontact) ?></div>
  </div>

</div>