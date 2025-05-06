<html>
<head><title>Medication Inventory</title>
<style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; }
    h1 { color: #007bff; }
    table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background: #007bff; color: white; }
    tr:nth-child(even) { background: #f2f2f2; }
    a { display: inline-block; margin-top: 20px; text-decoration: none; color: #007bff; }
</style>
</head>
<body>
    <h1>Medication Inventory</h1>
    <table>
        <tr>
            <th>Medication ID</th>
            <th>Medication Name</th>
            <th>Quantity Available</th>
            <th>Last Updated</th>
        </tr>
        <?php if (empty($inventory)): ?>
            <tr><td colspan="4">No inventory data found.</td></tr>
        <?php else: ?>
            <?php foreach ($inventory as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['medicationId']) ?></td>
                    <td><?= htmlspecialchars($item['medicationName']) ?></td>
                    <td><?= htmlspecialchars($item['quantityAvailable']) ?></td>
                    <td><?= htmlspecialchars($item['lastUpdated']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
    <a href="PharmacyServer.php">Back to Home</a>
</body>
</html>
