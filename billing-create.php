<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4">
    <i class="bx bx-file"></i> নতুন ইনভয়েস তৈরি
  </h4>

  <form id="invoiceForm" method="post" action="billing-save.php">
    <div class="card mb-4">
      <div class="card-body">

        <!-- প্রতিষ্ঠান সিলেকশন -->
        <div class="mb-3">
          <label class="form-label">প্রতিষ্ঠান নির্বাচন করুন</label>
          <select name="sccode" id="sccode" class="form-select" required>
            <option value="">-- নির্বাচন করুন --</option>
            <?php
              $res = $conn->query("SELECT sccode, scname FROM scinfo ORDER BY scname");
              while($row = $res->fetch_assoc()) {
                echo "<option value='{$row['sccode']}'>{$row['scname']}</option>";
              }
            ?>
          </select>
        </div>

        <!-- ইনভয়েস আইটেম টেবিল -->
        <table class="table table-bordered" id="invoiceItems">
          <thead class="table-light">
            <tr>
              <th>বিবরণ</th>
              <th width="100">পরিমাণ</th>
              <th width="120">দর</th>
              <th width="120">মোট</th>
              <th width="50">#</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><input type="text" name="item_name[]" class="form-control" placeholder="বিবরণ" required></td>
              <td><input type="number" name="quantity[]" class="form-control quantity" min="1" value="1" required></td>
              <td><input type="number" name="rate[]" class="form-control rate" step="0.01" value="0" required></td>
              <td><input type="text" name="total[]" class="form-control total" readonly></td>
              <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bx bx-x"></i></button></td>
            </tr>
          </tbody>
        </table>
        <button type="button" class="btn btn-secondary btn-sm" id="addRow"><i class="bx bx-plus"></i> নতুন আইটেম</button>

        <hr>

        <!-- মোট হিসাব -->
        <div class="row justify-content-end">
          <div class="col-md-4">
            <div class="mb-2">
              <label class="form-label">মোট</label>
              <input type="text" id="subtotal" name="subtotal" class="form-control" readonly>
            </div>
            <div class="mb-2">
              <label class="form-label">ডিসকাউন্ট</label>
              <input type="number" id="discount" name="discount" class="form-control" value="0">
            </div>
            <div class="mb-2">
              <label class="form-label">গ্র্যান্ড টোটাল</label>
              <input type="text" id="grandtotal" name="grandtotal" class="form-control" readonly>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3"><i class="bx bx-save"></i> ইনভয়েস সংরক্ষণ</button>

      </div>
    </div>
  </form>
</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script>
$(document).ready(function() {

  // নতুন রো যোগ করা
  $("#addRow").click(function() {
    const row = `<tr>
      <td><input type="text" name="item_name[]" class="form-control" placeholder="বিবরণ" required></td>
      <td><input type="number" name="quantity[]" class="form-control quantity" min="1" value="1" required></td>
      <td><input type="number" name="rate[]" class="form-control rate" step="0.01" value="0" required></td>
      <td><input type="text" name="total[]" class="form-control total" readonly></td>
      <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bx bx-x"></i></button></td>
    </tr>`;
    $("#invoiceItems tbody").append(row);
  });

  // রো ডিলিট
  $(document).on("click", ".remove-row", function() {
    $(this).closest("tr").remove();
    calculateTotals();
  });

  // হিসাব আপডেট
  $(document).on("input", ".quantity, .rate, #discount", function() {
    calculateTotals();
  });

  function calculateTotals() {
    let subtotal = 0;
    $("#invoiceItems tbody tr").each(function() {
      const qty = parseFloat($(this).find(".quantity").val()) || 0;
      const rate = parseFloat($(this).find(".rate").val()) || 0;
      const total = qty * rate;
      $(this).find(".total").val(total.toFixed(2));
      subtotal += total;
    });

    const discount = parseFloat($("#discount").val()) || 0;
    const grand = subtotal - discount;

    $("#subtotal").val(subtotal.toFixed(2));
    $("#grandtotal").val(grand.toFixed(2));
  }

});
</script>
<!-- ----------------------------------- -->

</body>
</html>
