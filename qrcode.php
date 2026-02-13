<?php
require_once 'auth.php';
requireAuth();

$baseUrl = 'https://restaurant-project-production-a27b.up.railway.app/index.php?tableNo=';
$tables = 5;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRコード - スマートオーダー</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Helvetica Neue", Arial, "Hiragino Kaku Gothic ProN", sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .page-header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-left h1 { font-size: 2em; color: #2c3e50; margin-bottom: 5px; }
        .header-left p { color: #7f8c8d; font-size: 0.95em; }
        .back-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; padding: 12px 30px; border-radius: 50px;
            text-decoration: none; font-weight: 700; transition: all 0.3s;
        }
        .back-button:hover { transform: translateY(-2px); }
        .print-btn {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white; padding: 12px 30px; border-radius: 50px; border: none;
            font-weight: 700; font-size: 1em; cursor: pointer; transition: all 0.3s;
        }
        .print-btn:hover { transform: translateY(-2px); }
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        .qr-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .qr-card h2 { font-size: 1.5em; color: #2c3e50; margin-bottom: 5px; }
        .qr-card .table-label {
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C42 100%);
            color: white; display: inline-block; padding: 8px 25px;
            border-radius: 50px; font-weight: 700; font-size: 1.3em; margin-bottom: 15px;
        }
        .qr-card img { width: 250px; height: 250px; margin: 10px 0; }
        .qr-card .url-text { color: #7f8c8d; font-size: 0.75em; word-break: break-all; margin-top: 10px; }
        .qr-card .scan-text { color: #2c3e50; font-weight: 700; font-size: 1.1em; margin-top: 10px; }

        @media print {
            body { background: white; padding: 0; }
            .page-header, .back-button, .print-btn, .header-actions { display: none !important; }
            .qr-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
            .qr-card { box-shadow: none; border: 2px solid #ddd; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="page-header">
        <div class="header-left">
            <h1>QRコード一覧</h1>
            <p>各テーブルに設置するQRコード</p>
        </div>
        <div class="header-actions" style="display:flex;gap:10px;">
            <button class="print-btn" onclick="window.print()">印刷する</button>
            <a href="admin.php" class="back-button">管理画面に戻る</a>
        </div>
    </div>

    <div class="qr-grid">
        <?php for ($i = 1; $i <= $tables; $i++):
            $url = $baseUrl . $i;
            $qrApi = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($url);
        ?>
        <div class="qr-card">
            <div class="table-label">Table <?php echo $i; ?></div>
            <br>
            <img src="<?php echo $qrApi; ?>" alt="Table <?php echo $i; ?> QR Code">
            <div class="scan-text">QRコードを読み取って注文</div>
            <div class="url-text"><?php echo htmlspecialchars($url); ?></div>
        </div>
        <?php endfor; ?>
    </div>
</div>
</body>
</html>
