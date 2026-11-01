<?php
require_once __DIR__ . '/../src/DataLoader.php';
require_once __DIR__ . '/../src/BaseRecords.php';
require_once __DIR__ . '/../src/DataAnalyze.php';
$results = null;
$errorMessage = null;
$executionTimeMs = null;
$processingTimestamp = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dataset'])) {
    $startTime = microtime(true);

    try {
        if ($_FILES['dataset']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed with error code: " . $_FILES['dataset']['error']);
        }
        $loader = new DataLoader($_FILES['dataset']['tmp_name']);
        $records = $loader->loadRecords();
        $analyzer = new DataAnalyze($records);
        $results = $analyzer->analyze();
        $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);
        $processingTimestamp = date('Y-m-d H:i:s T');
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWE 432: Assignment 3 - Data Analysis with PHP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Traffic Stop Data Analyzer</h1>
    <p>Upload your dataset to analyze the statistics.</p>
    <form method="post" enctype="multipart/form-data" class="upload-box">
        <label for="dataset"><strong>Select JSON Dataset:</strong></label><br><br>
        <input type="file" name="dataset" id="dataset" accept=".json" required><br>
        <button type="submit">Process Dataset</button>
    </form>

    <?php if ($errorMessage !== null): ?>
        <div class="error-card">
            <strong>Error:</strong> <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
    <?php if ($results !== null): ?>
        <div class="metadata-card">
            <strong>Execution Metadata:</strong><br>
            Processed on: <em><?php echo htmlspecialchars($processingTimestamp, ENT_QUOTES, 'UTF-8'); ?></em><br>
            Execution Time: <em><?php echo htmlspecialchars((string)$executionTimeMs, ENT_QUOTES, 'UTF-8'); ?> ms</em>
        </div>
        <h2>Analysis Results</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Question</th>
                    <th>Computed Value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $index => $data): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($data['question'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><strong><?php echo htmlspecialchars($data['result'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>