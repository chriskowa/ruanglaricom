<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=ruanglari', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("
        SELECT t.id, t.admin_fee, COUNT(p.id) as participants_count
        FROM transactions t
        LEFT JOIN participants p ON p.transaction_id = t.id
        WHERE t.event_id = 8 AND t.payment_gateway = 'manual' AND t.payment_status IN ('paid', 'cod')
        GROUP BY t.id, t.admin_fee
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Manual Transactions Detail:\n";
    $zeroCount = 0;
    foreach ($rows as $r) {
        if ($r['admin_fee'] == 0) {
            $zeroCount++;
        }
        // print first 10
        static $i = 0;
        if ($i++ < 10) {
            echo "TxID: {$r['id']} | Fee: {$r['admin_fee']} | PartCount: {$r['participants_count']}\n";
        }
    }
    echo "Total manual transactions: " . count($rows) . "\n";
    echo "Transactions with admin_fee = 0: {$zeroCount}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
