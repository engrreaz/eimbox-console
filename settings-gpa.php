<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="row mb-4">
        <div class="col-md-3">
            <label>Slot</label>
            <select id="slot" class="form-select form-select-sm">
                <option value="">Select Slot</option>
                <?php
                $q = mysqli_query($conn, "SELECT slotname FROM slots WHERE sccode='$sccode'");
                while ($r = mysqli_fetch_assoc($q)) {
                    echo "<option value='{$r['slotname']}'>{$r['slotname']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-3 mt-4">
            <button class="btn btn-sm btn-primary" id="addNewBtn" disabled>New Grade</button>
        </div>
    </div>

    <div id="table_area"></div>

</div>



<div class="modal fade" id="gpaModal">
    <div class="modal-dialog modal-dialog-centered ">

        <div class="modal-content">
            <form id="gpaForm">
                <div class="modal-header">
                    <h5 class="modal-title">GPA Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="mode" id="mode"> <!-- add | edit -->
                    <input type="hidden" name="base_sccode" id="base_sccode">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label>Min</label>
                            <input type="number" name="minv" id="minv" class="form-control form-control-sm" step="0.01">
                        </div>

                        <div class="col-md-6 mb-2">
                            <label>Max</label>
                            <input type="number" name="maxv" id="maxv" class="form-control form-control-sm" step="0.01">
                        </div>

                        <div class="col-md-6 mb-2">
                            <label>GP</label>
                            <input type="text" name="gp" id="gp" class="form-control form-control-sm" step="0.01">
                        </div>

                        <div class="col-md-6 mb-2">
                            <label>GL</label>
                            <input type="text" name="gl" id="gl" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-6 mb-2">
                            <label>Remark</label>
                            <input type="text" name="remark" id="remark" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-6 mb-2">
                            <label>Color Code</label>
                            <input type="color" name="color" id="color" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary btn-sm" type="submit">Save</button>
                </div>
            </form>
        </div>

    </div>
</div>


<?php require_once 'footer.php'; ?>

<script>
    function renderMeter() {
        let meter = document.getElementById("range-meter");
        let meterValues = document.getElementById("range-value");

        meter.innerHTML = "";
        meterValues.innerHTML = "";

        let totalScale = 100; // 0–100

        gradeRanges.forEach(item => {

            let min = item.min;
            let max = item.max;

            // width generate (min-max difference inclusive)
            let width = (((max - min) + 1) / totalScale) * 100;

            let color = "#" + item.color;

            // --------------------------
            // 1) Color Block (Upper Bar)
            // --------------------------
            let block = document.createElement("div");
            block.style.width = width + "%";
            block.style.height = "100%";
            block.style.background = color;
            block.style.color = "#fff";
            block.style.fontSize = "12px";
            block.style.textAlign = "center";
            block.style.lineHeight = "30px";

            block.innerText = item.gl;

            meter.appendChild(block);


            // --------------------------
            // 2) Value Block (Lower Bar)
            // --------------------------
            let valueBlock = document.createElement("div");
            valueBlock.style.width = width + "%";
            valueBlock.style.height = "100%";
            valueBlock.style.background = "#f8f9fa";
            valueBlock.style.color = "#333";
            valueBlock.style.fontSize = "11px";
            valueBlock.style.textAlign = "center";
            valueBlock.style.lineHeight = "25px";


            valueBlock.innerText = `${min}–${max}`;

            meterValues.appendChild(valueBlock);
        });
    }

</script>

<script>
    $(document).ready(function () {
        const modalEl = document.getElementById('gpaModal');
        const modal = new bootstrap.Modal(modalEl);

        $("#slot").change(function () {
            let slot = $(this).val();
            $("#addNewBtn").prop("disabled", slot == "");
            loadGPA(slot);
        });

        function loadGPA(slot) {
            $.post("result/fetch-gpa.php", { slot: slot }, function (data) {
                $("#table_area").html(data);
                renderMeter();
            });

        }

        $("#addNewBtn").click(function () {
            $("#mode").val("add");
            $("#id").val("");
            $("#base_sccode").val("");
            $("#gpaForm")[0].reset();
            modal.show();
        });

        $(document).on("click", ".editBtn", function () {
            let id = $(this).data("id");

            $.post("result/fetch-single-gpa.php", { id: id }, function (d) {
                let v = JSON.parse(d);

                $("#mode").val("edit");
                $("#id").val(v.id);
                $("#base_sccode").val(v.sccode);

                $("#minv").val(v.minvalues);
                $("#maxv").val(v.maxvalues);
                $("#gp").val(v.gp);
                $("#gl").val(v.gl);
                $("#remark").val(v.remark);
                $("#color").val('#' + v.colorcode);

                // const modalEl = document.getElementById('gpaModal');
                // const modal = new bootstrap.Modal(modalEl);
                modal.show();
            });
        });

        $("#gpaForm").submit(function (e) {
            e.preventDefault();

            let slot = $("#slot").val();

            $.post("result/save-gpa.php", $(this).serialize() + "&slot=" + slot, function (res) {
                alert(res);
                if (res == "OK") {
                    loadGPA(slot);
                    modal.hide();
                }
            });
        });

        $(document).on("click", ".delBtn", function () {
            if (!confirm("Delete this record?")) return;

            let id = $(this).data("id");
            let slot = $("#slot").val();

            $.post("result/delete-gpa.php", { id: id }, function (res) {
                if (res == "OK") {
                    loadGPA(slot);
                }
            });
        });

        loadGPA('');
    });

</script>

<script>




</script>
</body>

</html>