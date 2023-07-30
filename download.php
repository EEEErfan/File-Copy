<!DOCTYPE html>
<html>
<head>
    <title>File Copy Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
            margin: 0;
        }

        h1 {
            text-align: center;
        }

        #fileCopyForm {
            max-width: 500px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.2);
        }

        #fileCopyForm label {
            display: block;
            margin-bottom: 10px;
        }

        #fileCopyForm input[type="text"],
        #fileCopyForm input[type="password"],
        #fileCopyForm input[type="submit"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 10px;
        }

        #fileCopyForm input[type="submit"] {
            background-color: #4CAF50;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #fileCopyForm input[type="submit"]:hover {
            background-color: #45a049;
        }

        #progressBarContainer {
            max-width: 500px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.2);
            display: none;
        }

        #progressBar {
            width: 100%;
            height: 20px;
            margin-top: 10px;
            border: none;
            background-color: #f0f0f0;
            border-radius: 4px;
        }

        #progressStatus {
            text-align: center;
            margin: 10px 0;
            font-size: 16px;
        }

        #download_link {
            display: none;
            text-align: center;
            font-size: 18px;
            text-decoration: none;
            color: #fff;
            background-color: #4CAF50;
            padding: 10px 20px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.2);
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        #download_link:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>File Copy Form</h1>
    <form id="fileCopyForm">
        <label for="link">Link:</label>
        <input type="text" id="link" name="link" required><br>

        <label for="fileName">File Name:</label>
        <input type="text" id="fileName" name="fileName"><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>

        <input type="submit" value="Copy File">
    </form>

    <div id="progressBarContainer" style="display: none;">
        <p>Downloading...</p>
        <progress id="progressBar" value="0" max="100"></progress>
        <p id="progressStatus">0 MB of 0 MB</p>
        <p id="downloadSpeed">Download Speed: Calculating...</p>
    </div>
    <a id="download_link" href="" style="display: none;">download file</a>

    <script>
        let FileSize = 0;
        let lastProgressTime = null;
        let lastProgress = 0;
        
        function calculateSpeed(progress, currentTime) {
            if (lastProgressTime) {
                const timeElapsed = (currentTime - lastProgressTime) / 1000; // Convert to seconds
                const progressDelta = progress - lastProgress;
                const speed = (progressDelta / timeElapsed) / (1024 * 1024); // Convert to MB/s
                return speed.toFixed(2); // Return speed rounded to 2 decimal places
            }
            return "Calculating...";
        }
        
        document.getElementById("fileCopyForm").addEventListener("submit", function(event) {
            event.preventDefault();

            const link = document.getElementById("link").value;
            let fileName = document.getElementById("fileName").value;
            const password = document.getElementById("password").value;

            // Set the fileName if it is empty
            if (fileName === "") {
                const file_name = link.split("/").pop().split("?")[0];
                fileName = file_name || "untitled";
                document.getElementById("fileName").value = fileName;
            }

            // Show the progress bar
            const progressBarContainer = document.getElementById("progressBarContainer");
            progressBarContainer.style.display = "block";
            document.getElementById("download_link").style.display = "none";

            // Fetch the total file size from the server
            fetch("get_file_size.php", {
                method: "POST",
                body: JSON.stringify({ link }),
                headers: {
                    "Content-Type": "application/json"
                }
            })
            .then(response => response.json())
            .then(data => {
                const totalFileSize = parseInt(data.fileSize);
                FileSize = totalFileSize;
                console.log(totalFileSize);
                if (totalFileSize === 0) {
                    alert("Error fetching file size. Please check the link.");
                    progressBarContainer.style.display = "none";
                    return;
                }

                // Start the file copy process
                const formData = new FormData();
                formData.append("link", link);
                formData.append("fileName", fileName);
                formData.append("password", password);

                const xhr = new XMLHttpRequest();
                xhr.open("POST", "copy_file.php", true);
                xhr.upload.onprogress = function(event) {
                    if (event.lengthComputable) {
                        console.log("started");
                        setTimeout(() => checkProgress(fileName), 1000);
                    }
                };

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const message = xhr.responseText;
                        alert(message);
                    }
                };

                xhr.onerror = function() {
                    console.error("An error occurred during the request.");
                };

                xhr.send(formData);
            })
            .catch(error => {
                console.error(error);
                alert("Error fetching file size. Please try again later.");
                progressBarContainer.style.display = "none";
            });
        });



        function checkProgress(fileName) {
            fetch("check_progress.php", {
                method: "POST",
                body: JSON.stringify({ fileName }),
                headers: {
                    "Content-Type": "application/json"
                }
            })
            .then(response => response.json())
            .then(data => {
                const progress = data.progress;
                const progressBar = document.getElementById("progressBar");
                progressBar.value = (progress / FileSize) * 100;
                
                const remainingMB = Math.floor((FileSize - progress) / (1024 * 1024));
                const totalMB = Math.floor(FileSize / (1024 * 1024));
                const progressStatus = document.getElementById("progressStatus");
                progressStatus.textContent = `${totalMB - remainingMB} MB of ${totalMB} MB`;
                
                // Update download speed
                const downloadSpeed = document.getElementById("downloadSpeed");
                const currentTime = Date.now();
                const speed = calculateSpeed(progress, currentTime);
                downloadSpeed.textContent = `Download Speed: ${speed} MB/s`;

                // Store current progress and time for next calculation
                lastProgress = progress;
                lastProgressTime = currentTime;
            
                console.log(progress);
                if (progress < FileSize) {
                    // Continue checking progress after 1 second
                    setTimeout(() => checkProgress(fileName), 1000);
                } else {
                    // Hide the progress bar when the download is complete
                    document.getElementById("progressBarContainer").style.display = "none";
                    document.getElementById("download_link").style.display = "block";
                    document.getElementById("download_link").setAttribute("href", window.location.href.replace("download.php", "") + fileName);
                }
            })
            .catch(error => console.error(error));
        }
    </script>
</body>
</html>
