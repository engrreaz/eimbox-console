<!-- SweetAlert2 -->
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

<style media="print">
    body {
        display: none !important;
    }
</style>

<script>

// 🔴 Ctrl + P block
document.addEventListener("keydown", function (e) {
    if (e.ctrlKey && (e.key === "p" || e.key === "P")) {
        e.preventDefault();

        Swal.fire({
            icon: 'error',
            title: 'Access Denied',
            text: '❌ প্রিন্ট করার অনুমতি নেই।',
            timer: 1500,
            showConfirmButton: false
        });
    }
});


// 🔴 Print trigger block (before print)
window.onbeforeprint = function () {

    Swal.fire({
        icon: 'warning',
        title: 'Blocked',
        text: '❌ এই পেজটি প্রিন্ট করার অনুমতি নেই।',
        timer: 1500,
        showConfirmButton: false
    });

    // কিছু ব্রাউজারে print cancel করার চেষ্টা
    setTimeout(() => {
        window.stop();
    }, 50);
};


// 🔴 Extra protection (Chrome / modern browser)
window.matchMedia('print').addEventListener('change', function (mql) {
    if (mql.matches) {

        Swal.fire({
            icon: 'warning',
            title: 'Blocked',
            text: '❌ প্রিন্ট করার অনুমতি নেই।',
            timer: 1500,
            showConfirmButton: false
        });

        setTimeout(() => {
            window.stop();
        }, 50);
    }
});

</script>