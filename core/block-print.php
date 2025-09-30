<style media="print">
    body {
        display: none !important;
    }
</style>

<script>
    // Ctrl+P ব্লক করা
    document.addEventListener("keydown", function (e) {
        if (e.ctrlKey && (e.key === "p" || e.key === "P")) {
            e.preventDefault();
            alert("❌ প্রিন্ট করার অনুমতি নেই।");
        }
    });

    // প্রিন্ট শুরু হলে সতর্কবার্তা
    window.onbeforeprint = function () {
        alert("❌ এই পেজটি প্রিন্ট করার অনুমতি নেই।");
        setTimeout(() => {
            window.stop(); // কিছু ব্রাউজারে প্রিন্ট বন্ধ করে দেবে
        }, 10);
    };
</script>