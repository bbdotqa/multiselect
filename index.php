<?php
session_start(); // Start session to read success or error messages
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload</title>
    <!-- Correct CDN link for Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">

    <!-- Container for the form -->
    <div class="w-full max-w-2xl bg-white p-8 rounded-lg shadow-lg">

        <!-- Success or Error Message Display -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-500 text-white p-4 rounded-md mb-4">
                <?php echo $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-500 text-white p-4 rounded-md mb-4">
                <?php echo $_SESSION['error_message']; ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Title of the Page -->
        <h2 class="text-3xl font-bold text-center text-gray-700 mb-6">Upload Your Files</h2>

        <!-- File Upload Form -->
        <form action="upload.php" method="POST" enctype="multipart/form-data">

            <!-- File input with preview -->
            <div class="mb-4">
    <label for="files" class="block text-sm font-medium text-gray-700 mb-2">Choose files to upload</label>
    <input type="file" name="files[]" multiple id="files"
           class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
           onchange="previewFiles()">
</div>


            <!-- File Preview Section -->
            <div id="file-preview" class="mb-4"></div>

            <!-- Upload Button -->
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition-all duration-200">Upload Files</button>
        </form>
    </div>

    <!-- Javascript for file preview -->
    <script>
        function previewFiles() {
    const previewContainer = document.getElementById('filePreview');
    const files = document.getElementById('files').files;

    // Clear the previous preview content
    previewContainer.innerHTML = '';

    // Loop through the selected files
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const fileType = file.type;

        // Only allow previews for specific file types
        if (fileType.startsWith('image/')) {  // Image files (e.g., jpg, png)
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.classList.add('w-32', 'h-32', 'object-cover', 'mr-2', 'mb-2');
            previewContainer.appendChild(img);
        } else if (fileType === 'application/pdf') {  // PDF files
            const pdfPreview = document.createElement('div');
            pdfPreview.innerHTML = '<span class="text-gray-700">PDF: ' + file.name + '</span>';
            previewContainer.appendChild(pdfPreview);
        } else if (fileType.startsWith('application/msword') || fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {  // DOC/DOCX files
            const docPreview = document.createElement('div');
            docPreview.innerHTML = '<span class="text-gray-700">Word Document: ' + file.name + '</span>';
            previewContainer.appendChild(docPreview);
        } else {
            // If the file is not an image or PDF, we show its name
            const fileName = document.createElement('div');
            fileName.innerHTML = '<span class="text-gray-700">File: ' + file.name + '</span>';
            previewContainer.appendChild(fileName);
        }
    }
}
    </script>

</body>
</html>
