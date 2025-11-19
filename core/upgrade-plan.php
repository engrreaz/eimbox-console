<div class="container-xxl flex-grow-1 container-p-y">

    <?php
    echo 'ABC';
    echo '<pre>';
    print_r($raw_json);
    echo '</pre>';
    echo 'XYZ';
    // JSON থেকে PHP অ্যারেতে কনভার্ট
    $array = json_decode($row_json, true);

    if ($array === null) {
        echo 'JSON Error: ' . json_last_error_msg();
    } else {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }

    echo $sccode_current_package;
    ?>
    Upgrade your plan .........

</div>