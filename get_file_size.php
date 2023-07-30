<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Read JSON data sent from JavaScript
    $data = json_decode(file_get_contents("php://input"), true);

    // Extract the link from the data
    $link = $data["link"];

    // Fetch the headers of the provided link
    $headers = get_headers($link, 1);

    // Get the total file size from the headers (if available)
    $totalFileSize = 0;
    if (isset($headers['Content-Length'])) {
        $totalFileSize = (int)$headers['Content-Length'];
    } elseif (isset($headers['content-length'])) {
        $totalFileSize = (int)$headers['content-length'];
    }

    // Send the total file size back to the client
    echo json_encode(array("fileSize" => $totalFileSize));
}
?>
