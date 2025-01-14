<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audio Files</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldFile = $_POST['oldFile'];
    $uploadDir = 'audio/';
    $uploadFile = $uploadDir . basename($_FILES['audioFile']['name']);
    
    // Check for errors in the uploaded file
    if ($_FILES['audioFile']['error'] !== UPLOAD_ERR_OK) {
        echo "<script>alert('Error uploading file. Check logs for details.');</script>";
    } else {
        // Attempt to move the uploaded file
        if (move_uploaded_file($_FILES['audioFile']['tmp_name'], $uploadFile)) {
            // Optionally, remove the old file
            if (file_exists($uploadDir . $oldFile)) {
                unlink($uploadDir . $oldFile);
            }
            echo "<script>alert('File uploaded and replaced successfully!'); window.location.href='files.php';</script>";
        } else {
            echo "<script>alert('Error uploading file. Check logs for details.');</script>";
        }
    }
}

?>

<div class="container mx-auto">
    <h1 class="text-3xl font-bold mb-6">Audio Files</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php
        $audioFiles = array_diff(scandir('audio/'), array('..', '.')); // Get audio files
	rsort($audioFiles);

        foreach ($audioFiles as $audioFile) {
            echo '
            <div class="bg-white rounded-lg shadow-md p-4 flex flex-col items-center">
                <h2 class="text-lg font-semibold mb-2">' . htmlspecialchars($audioFile) . '</h2>
                <audio controls class="mb-2">
                    <source src="audio/' . htmlspecialchars($audioFile) . '" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
                <form action="files.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="audioFile" class="mb-2" required>
                    <input type="hidden" name="oldFile" value="' . htmlspecialchars($audioFile) . '">
                    <button type="submit" class="bg-blue-500 text-white rounded px-4 py-2">Upload & Replace</button>
                </form>
            </div>
            ';
        }
        ?>
    </div>
</div>

</body>
</html>
