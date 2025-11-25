<?php
if(isset($_FILES['file'])){
    $folder = dirname(dirname(__DIR__)) . '/students';
    $filename = $_FILES['file']['name'];
    $target = $folder . '/' . $filename;

    if(move_uploaded_file($_FILES['file']['tmp_name'], $target)){
        echo "Image saved successfully!";
    } else {
        echo "Error saving image!";
    }
} else {
    echo "No file received!";
}
