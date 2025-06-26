<?php
// download_reports.php
if (isset($_POST['reports']) && !empty($_POST['reports'])) {
    $reports = json_decode($_POST['reports'], true);
    
    if (!empty($reports)) {
        // Set the ZIP file name
        $zipFileName = 'reports_' . time() . '.zip';

        // Create a new ZIP archive
        $zip = new ZipArchive();
        if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
            foreach ($reports as $file) {
                // Path to the files
                $filePath = __DIR__ . '/media/Patient_reports/' . basename($file); // Adjust path as necessary
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($file));
                }
            }
            $zip->close();

            // Set headers for the download
            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename=' . $zipFileName);
            header('Content-Length: ' . filesize($zipFileName));

            // Read the file and send it to the output
            readfile($zipFileName);

            // Delete the ZIP file from the server after download
            unlink($zipFileName);
            exit;
        }
    }
}
?>
