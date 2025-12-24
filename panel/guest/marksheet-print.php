<style>
    @page {
        size: A4 portrait;
        margin: 12mm;
        
    }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        padding: 15mm;
    }
</style>
    <script>
        window.onload = function () {
            window.print();
        };

        window.onafterprint = function () {
            window.close();
        };
    </script>
<?php
require 'marksheet-data.php';
include 'marksheet-view.php';
