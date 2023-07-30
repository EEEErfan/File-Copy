<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Read JSON data sent from JavaScript
    $data = json_decode(file_get_contents("php://input"), true);

    // Extract the file name from the data
    $fileName = $data["fileName"];

    // Get the current file size
    $currentFileSize = filesize($fileName);

    // Send the progress back to the client
    echo json_encode(array("progress" => $currentFileSize));
}
?>
