<?php
// Upload diagnostic script - DELETE THIS FILE AFTER TESTING
echo "<h2>Upload Diagnostics</h2>";

// Check paths
$base_path = __DIR__;
$uploads_path = $base_path . '/uploads';
$services_path = $uploads_path . '/services';

echo "<h3>1. Directory Paths</h3>";
echo "Base path: $base_path<br>";
echo "Uploads path: $uploads_path<br>";
echo "Services path: $services_path<br>";

echo "<h3>2. Directory Existence</h3>";
echo "uploads/ exists: " . (is_dir($uploads_path) ? '✅ YES' : '❌ NO') . "<br>";
echo "uploads/services/ exists: " . (is_dir($services_path) ? '✅ YES' : '❌ NO') . "<br>";

echo "<h3>3. Directory Permissions</h3>";
if (is_dir($uploads_path)) {
    echo "uploads/ writable: " . (is_writable($uploads_path) ? '✅ YES' : '❌ NO') . "<br>";
    echo "uploads/ permissions: " . substr(sprintf('%o', fileperms($uploads_path)), -4) . "<br>";
}
if (is_dir($services_path)) {
    echo "uploads/services/ writable: " . (is_writable($services_path) ? '✅ YES' : '❌ NO') . "<br>";
    echo "uploads/services/ permissions: " . substr(sprintf('%o', fileperms($services_path)), -4) . "<br>";
}

echo "<h3>4. PHP Upload Settings</h3>";
echo "file_uploads: " . ini_get('file_uploads') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "upload_tmp_dir: " . (ini_get('upload_tmp_dir') ?: 'System default') . "<br>";

echo "<h3>5. Test Write</h3>";
$test_file = $services_path . '/test_write.txt';
if (@file_put_contents($test_file, 'test')) {
    echo "Can write to uploads/services/: ✅ YES<br>";
    @unlink($test_file);
} else {
    echo "Can write to uploads/services/: ❌ NO<br>";

    // Try creating the directory
    if (!is_dir($services_path)) {
        if (@mkdir($services_path, 0755, true)) {
            echo "Created uploads/services/ directory: ✅ YES<br>";
        } else {
            echo "Failed to create uploads/services/: ❌ Check permissions<br>";
        }
    }
}

echo "<h3>6. Test Form</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
    echo "<strong>Upload attempt results:</strong><br>";
    echo "File name: " . $_FILES['test_image']['name'] . "<br>";
    echo "File size: " . $_FILES['test_image']['size'] . " bytes<br>";
    echo "File type: " . $_FILES['test_image']['type'] . "<br>";
    echo "Temp location: " . $_FILES['test_image']['tmp_name'] . "<br>";
    echo "Error code: " . $_FILES['test_image']['error'] . "<br>";

    if ($_FILES['test_image']['error'] === UPLOAD_ERR_OK) {
        $dest = $services_path . '/test_' . time() . '_' . $_FILES['test_image']['name'];
        if (move_uploaded_file($_FILES['test_image']['tmp_name'], $dest)) {
            echo "✅ <strong>Upload SUCCESS!</strong> File saved to: $dest<br>";
            echo "<img src='uploads/services/" . basename($dest) . "' style='max-width:200px'><br>";
        } else {
            echo "❌ <strong>move_uploaded_file() FAILED</strong><br>";
            echo "Error: " . error_get_last()['message'] . "<br>";
        }
    } else {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk',
            UPLOAD_ERR_EXTENSION => 'PHP extension blocked upload'
        ];
        echo "❌ <strong>Upload error:</strong> " . ($errors[$_FILES['test_image']['error']] ?? 'Unknown') . "<br>";
    }
}
?>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_image" accept="image/*">
    <button type="submit">Test Upload</button>
</form>

<p><em>Delete this file (test_upload.php) after testing!</em></p>
