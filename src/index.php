<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

function runScenario(string $cmd): string {
    // Execute command and capture both stdout and stderr
    $output = [];
    $returnCode = 0;
    exec("php " . escapeshellarg($cmd) . " 2>&1", $output, $returnCode);

    return implode("\n", $output);
}

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['scenario']) && $_POST['scenario'] === '1') {
        $result = runScenario(__DIR__ . '/../examples/scenario1.php');
    }

    if (isset($_POST['scenario']) && $_POST['scenario'] === '2') {
        $result = runScenario(__DIR__ . '/../examples/scenario2.php');
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>GreatFood API Demo</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; }
        h1 { margin-bottom: 20px; }
        form { margin-bottom: 20px; }
        textarea { width: 100%; height: 300px; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; }
        .section { margin-bottom: 40px; }
    </style>
</head>
<body>

<h1>GreatFood API Demo</h1>

<div class="section">
    <form method="POST">
        <button name="scenario" value="1">Run Scenario 1</button>
        <button name="scenario" value="2">Run Scenario 2</button>
    </form>
</div>

<?php if ($result !== null): ?>
    <h2>Result:</h2>
    <textarea readonly><?= htmlspecialchars($result) ?></textarea>
<?php endif; ?>

</body>
</html>