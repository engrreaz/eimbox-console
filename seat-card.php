<?php
require_once 'header.php';

$slot = $_COOKIE['chain-slot'] ?? null;
$sessionyear = $_COOKIE['chain-session'] ?? null;
$classname = $_COOKIE['chain-class'] ?? null;
$sectionname = $_COOKIE['chain-section'] ?? null;
$exam = $_COOKIE['chain-exam'] ?? null;



$admit_background = $_COOKIE['admit-background'] ?? 'sample_02.png';
$admit_title = $_COOKIE['admit-title'] ?? 'title_01.png';
$color1 = $_COOKIE['admit-color1'] ?? '#263547';
$color2 = $_COOKIE['admit-color2'] ?? '#000000';
$color3 = $_COOKIE['admit-color3'] ?? '#852357';


$subjectList = [];

$sqlSub = "SELECT subcode, subject 
           FROM subjects
           WHERE sccategory='$sctype'
           AND (sccode='0' OR sccode='$sccode')
           ORDER BY subcode";

$resSub = mysqli_query($conn, $sqlSub);

while ($rowSub = mysqli_fetch_assoc($resSub)) {
  $subjectList[] = $rowSub;
}

?>

<style>
  @media print {
    .admit-card {
      width: 210mm;
      height: 297mm;
    }
  }

  .admit-card {
    page-break-after: always;
  }
</style>

<style>
  .scroll-box {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding: 5px;
    border: 1px solid #ddd;
  }

  .thumb {
    height: 70px;
    cursor: pointer;
    border: 2px solid transparent;
    border-radius: 5px;
  }

  .thumb.active {
    border: 2px solid #0d6efd;
  }

  #settingsModal .modal-dialog {
    width: 700px;
    /* or your desired width */
    max-width: 90%;
  }

  .modal-dialog {
    transition: none !important;
  }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
  <?php
  $chain_param = '-c 12 -t Choose Values -u -r -b View Routine -h exam';
  include 'components/slot-tree-ui.php';
  ?>


  <div class="row">
    <div class="col-12">
      <div class="card" id="result-area">
        <div class="card-header">

        </div>
        <div class="card-body">

        </div>
      </div>
    </div>

  </div>

</div>



<div class="modal fade" id="settingsModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header" style="cursor: move;">
        <h5 class="modal-title">Admit Card Settings</h5>

        <!-- <button type="button" class="btn-close" data-bs-dismiss="modal"></button> -->
      </div>

      <div class="modal-body">

        <!-- 🎨 BACKGROUND SELECT -->
        <label>Admit Background</label>
        <div id="bgList" class="scroll-box"></div>

        <!-- 🏷 TITLE SELECT -->
        <label class="mt-3">Title Design</label>
        <div id="titleList" class="scroll-box"></div>

        <!-- ⚙️ SETTINGS -->
        <hr>

        <div class="row">
          <div class="col-md-3"><label class="mt-2 small">Grid | Layout</label>
            <select id="grid" class="form-control form-control-sm">
              <option value="2x4">2 X 4</option>
              <option value="2x5">2 X 5</option>
              <option value="2x6">2 X 6</option>
              <option value="3x3">3 X 3</option>
            </select>
          </div>
          <div class="col-md-3"> <label class="mt-2 small">Locale</label>
            <select id="locale" class="form-control form-control-sm" disabled>
              <option value="en">English</option>
              <option value="bn">Bengali</option>
            </select>
          </div>
          <div class="col-md-3"> <label class="mt-2 small">Bengali Name</label>
            <select id="showBen" class="form-control form-control-sm" disabled>
              <option value="yes">Yes</option>
              <option value="no">No</option>
            </select>
          </div>
          <div class="col-md-3"> <label class="mt-2 small">Photo</label>
            <select id="showPhoto" class="form-control form-control-sm" disabled>
              <option value="yes">Yes</option>
              <option value="no">No</option>
            </select>
          </div>
        </div>


        <div class="row">
          <div class="col-md-3">
            <label class="mt-2 small">Orientation</label>
            <select id="orientation" class="form-control form-control-sm">
              <option value="P">Protrait</option>
              <option value="L">Landscape</option>
            </select>
          </div>



          <div class="col-md-3">
            <label class="mt-2 small">Institute Name</label>
            <div class="">
              <input type="color" id="color1" value="<?= $color1 ?>" class="form-control form-control-sm p-0">

            </div>
          </div>

          <div class="col-md-3">
            <label class="mt-2 small">Address</label>
            <div class="">
              <input type="color" id="color2" value="<?= $color2 ?>" class="form-control form-control-sm p-0">

            </div>
          </div>

          <div class="col-md-3">
            <label class="mt-2 small">Exam Title</label>
            <div class="">
              <input type="color" id="color3" value="<?= $color3 ?>" class="form-control form-control-sm p-0">

            </div>
          </div>

        </div>




      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" onclick="applySettings()">Apply</button>
      </div>

    </div>
  </div>
</div>



<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->

<script>
  const modal = document.querySelector("#settingsModal .modal-dialog");
  const header = document.querySelector("#settingsModal .modal-header");

  let isDragging = false;
  let offsetX = 0;
  let offsetY = 0;

  // 🔥 when modal is shown, reset position
  document.getElementById('settingsModal').addEventListener('shown.bs.modal', function () {
    modal.style.position = "fixed";
    modal.style.left = "50%";
    modal.style.top = "50%";
    modal.style.transform = "translate(-50%, -50%)";
  });

  header.addEventListener("mousedown", function (e) {
    isDragging = true;

    const rect = modal.getBoundingClientRect();

    offsetX = e.clientX - rect.left;
    offsetY = e.clientY - rect.top;

    // 🔥 important: remove transform ONLY when drag starts
    modal.style.transform = "none";
  });

  document.addEventListener("mousemove", function (e) {
    if (!isDragging) return;

    modal.style.left = (e.clientX - offsetX) + "px";
    modal.style.top = (e.clientY - offsetY) + "px";
  });

  document.addEventListener("mouseup", function () {
    isDragging = false;
  });
</script>

<script>
  function chainBtnFunc(mode = 'preview') {

    let slot = $('#slot-main').val();
    let sessionyear = $('#session-main').val();
    let classname = $('#class-main').val();
    let sectionname = $('#section-main').val();
    let exam = $('#exam-main').val();

    $.ajax({
      url: 'exam/exam-seat-card.php',
      type: 'POST',
      data: {
        action: 'admit',
        mode: mode, // 🔥 add this
        slot,
        sessionyear,
        classname,
        sectionname,
        exam
      },
      success: function (res) {
        $('#result-area').html(res);
      }
    });
  }
</script>

<script>
  function waitForImagesAndPrint(win) {
    let images = win.document.images;
    let total = images.length;
    let loaded = 0;

    if (total === 0) {
      win.print();
      return;
    }

    for (let img of images) {
      if (img.complete) {
        loaded++;
      } else {
        img.onload = img.onerror = function () {
          loaded++;
          if (loaded === total) win.print();
        };
      }
    }

    if (loaded === total) win.print();
  }
</script>

<script>
  function printAdmit() {
    // 🔥 load FULL data first
    chainBtnFunc('print');

    setTimeout(() => {
      let content = document.getElementById('routineTable').innerHTML;

      let win = window.open('', '', 'width=900,height=700');

      win.document.write(`<html><body>${content}</body></html>`);
      win.document.close();

      win.onload = function () {
        waitForImagesAndPrint(win);
      };

    }, 1000); // wait for ajax
  }
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
  function downloadPDF() {
    let element = document.getElementById('routineTable');

    html2pdf().from(element).save('admit-card.pdf');
  }
</script>

<script>


  let settingsModal;

  $(document).ready(function () {
    settingsModal = new bootstrap.Modal(document.getElementById('settingsModal'));
  });
  function openSettings() {
    settingsModal.show();
  }

</script>



<script>

  const bgPath = "assets/admit/";
  const titlePath = "assets/admit/";

  // 🔥 current selected (PHP থেকে আসবে)
  let selectedBg = "<?= $admit_background ?? 'sample_01.png' ?>";
  let selectedTitle = "title_01.png";

  // 🔹 generate thumbnails
  function loadImages(prefix, count, container, selected) {
    let html = "";

    for (let i = 1; i <= count; i++) {
      let num = String(i).padStart(2, '0');
      let file = `${prefix}_${num}.png`;

      let active = (file === selected) ? 'active' : '';

      html += `<img src="${bgPath}${file}" 
                    class="thumb ${active}" 
                    data-file="${file}">`;
    }

    $("#" + container).html(html);
  }

  // 🔹 init
  $(document).ready(function () {
    loadImages("sample", 3, "bgList", selectedBg);
    loadImages("title", 2, "titleList", selectedTitle);
  });

  // 🔹 select background
  $(document).on("click", "#bgList .thumb", function () {

    $("#bgList .thumb").removeClass("active");
    $(this).addClass("active");

    let file = $(this).data("file");

    setCookie("admit-background", file);
    // 🔥 live update
    $(".admit-card").css({
      "background": `url(${bgPath}${file})`,
      "background-size": "cover"
    });

  });

  // 🔹 select title
  $(document).on("click", "#titleList .thumb", function () {

    $("#titleList .thumb").removeClass("active");
    $(this).addClass("active");

    let file = $(this).data("file");
    setCookie("admit-title", file);

    // example (adjust based on your HTML)
    $(".admit-title-img").attr("src", bgPath + file);

  });

</script>

<script>
  function applySettings() {

    let pad = $('#pad').val();
    let font = $('#fontSize').val();
    let gridSize = $('#grid').val();
    let orientation = $('#orientation').val();

    let c1 = $('#color1').val();
    let c2 = $('#color2').val();
    let c3 = $('#color3').val();

    $('.admit-card').css({
      'padding': pad + 'mm',
      'font-size': font + 'px'
    });

    // header alignment
    $('.admit-grid').css('text-align', gridSize);

    // color apply
    $('.inst-name').css('color', c1);
    $('.inst-address').css('color', c2);
    $('.exam-name').css('color', c3);


    setCookie("seat-grid", gridSize);
    setCookie("seat-orientation", orientation);

    setCookie("admit-color1", c1);
    setCookie("admit-color2", c2);
    setCookie("admit-color3", c3);


    const modalEl = document.getElementById('settingsModal');
    const modal = bootstrap.Modal.getInstance(modalEl);

    modal.hide();
    chainBtnFunc();
  }
</script>
<!-- ----------------------------------- -->




<script>

  let lastVal = "";

  setInterval(function () {
    let current = $('#section-main').val();

    if (current && current !== lastVal) {
      lastVal = current;
      chainBtnFunc();
    }
  }, 300);
</script>
</body>

</html>