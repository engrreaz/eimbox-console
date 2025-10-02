$(document).ready(function () {
    // Dynamic content load function
    function loadPage(url, target = "#content") {
        $(target).fadeOut(200, function () {
            $(target).load(url, function (response, status, xhr) {
                if (status === "error") {
                    $(target).html("<div class='alert alert-danger'>Error loading page: " + xhr.status + "</div>");
                } else {
                    $(target).fadeIn(200);

                    // Re-initialize plugins after content load
                    reInitPlugins();
                }
            });
        });
    }

    // Function to reinitialize plugins after AJAX load
    function reInitPlugins() {
        // Bootstrap tooltips
        if (typeof bootstrap !== "undefined") {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // DataTables re-init
        if ($.fn.DataTable) {
            $(".datatable").DataTable();
        }

        // Waves re-init
        if (typeof Waves !== "undefined") {
            Waves.init();
        }

        // Pickr color picker (example)
        if (typeof Pickr !== "undefined") {
            // তোমার কাস্টম pickr init কোড বসাও
        }

        console.log("Plugins reinitialized after AJAX load");
    }

    // Example: menu click এ লোড করা
    $(document).on("click", ".ajax-link", function (e) {
        e.preventDefault();
        const url = $(this).attr("href");
        loadPage(url);
    });
});
