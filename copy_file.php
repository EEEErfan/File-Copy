<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Read form data
    $link = $_POST["link"];
    $fileName = $_POST["fileName"];
    $password = $_POST["password"];

    $pass = "123"; // change password here
    // Validate password
    if ($password === $pass) {
        // Perform the file copy
        if (copy($link, $fileName)) {
            echo "File copied successfully.";
        } else {
            echo "Error copying the file.";
        }
    } else {
        echo "Incorrect password. File copying is not allowed.";
    }
}
?>
