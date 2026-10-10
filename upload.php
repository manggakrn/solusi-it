<?php
require_once 'auth.php';
// upload.php
if ($_FILES['file']['name']) {
    $filename = time() . '_' . $_FILES['file']['name'];
    $destination = 'uploads/' . $filename;
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
        echo 'uploads/' . $filename;
    } else {
        echo 'Error';
    }
}
