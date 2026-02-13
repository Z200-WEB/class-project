<?php
// UTF-8 ENCODING - MUST BE FIRST!
header('Content-Type: text/html; charset=UTF-8');

// Authentication required
require_once 'auth.php';
requireAuth();

// Load database connection
require_once 'pdo.php';

// Generate CSRF token for payment form
$csrfToken = generateCsrfToken();

$sql = "
    SELECT m.*, SUM(i.price * o.amount) as totalAmount, COUNT(o.id) as itemCount
    FROM sManagement m
    LEFT JOIN sOrder o ON m.orderNo = o.orderNo
    LEFT JOIN sItem i ON o.itemNo = i.id
    WHERE m.state = 1
    GROUP BY m.id, m.state, m.orderNo, m.tableNo, m.dateA, m.dateB
    ORDER BY m.dateB DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文管理 - スマートオーダー</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Helvetica Neue", Arial, "Hiragino Kaku Gothic ProN", sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left h1 {
            font-size: 2em;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .header-left p {
            color: #7f8c8d;
            font-size: 0.95em;
        }

        .back-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .table-links {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .table-links-title {
            font-size: 1.1em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .table-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .table-btn {
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C42 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 3px 10px rgba(255, 107, 53, 0.3);
        }

        .table-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.5);
        }

        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .empty-state {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .empty-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }

        .empty-title {
            font-size: 1.5em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .empty-text {
            color: #7f8c8d;
            font-size: 1em;
        }

        .order-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border: 3px solid transparent;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: #FF6B35;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .table-badge {
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C42 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1em;
        }

        .order-time {
            color: #7f8c8d;
            font-size: 0.9em;
        }

        .order-body {
            margin-bottom: 20px;
        }

        .order-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95em;
        }

        .order-label {
            color: #7f8c8d;
            font-weight: 600;
        }

        .order-value {
            color: #2c3e50;
            font-weight: 700;
        }

        .order-total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px;
            border-radius: 15px;
            margin: 15px 0;
        }

        .order-total-row {
            display: flex;
            justify-content: space-between;
            color: white;
            font-size: 1.2em;
            font-weight: 700;
        }

        .order-actions {
            display: flex;
            gap: 10px;
        }

        .btn-details {
            flex: 1;
            background: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }

        .btn-details:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-payment {
            flex: 1;
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        .btn-payment:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.5);
        }

        .payment-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .payment-modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .payment-modal-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }

        .payment-modal-title {
            font-size: 1.8em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .payment-modal-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #666;
        }

        .detail-value {
            font-weight: 700;
            color: #2c3e50;
        }

        .total-row {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 15px -20px -20px -20px;
            padding: 15px 20px !important;
            border-radius: 0 0 12px 12px;
            border: none !important;
        }

        .total-row .detail-label,
        .total-row .detail-value {
            color: white;
            font-size: 1.3em;
        }

        .payment-modal-warning {
            background: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 0.95em;
        }

        .payment-modal-buttons {
            display: flex;
            gap: 15px;
        }

        .modal-btn {
            flex: 1;
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1.1em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .cancel-btn {
            background: #95a5a6;
            color: white;
        }

        .cancel-btn:hover {
            background: #7f8c8d;
        }

        .confirm-btn {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
        }

        .confirm-btn:hover {
            transform: translateY(-2px);
        }

        .success-notification {
            position: fixed;
            top: 30px;
            right: 30px;
            z-index: 10001;
        }

        .success-content {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(39, 174, 96, 0.3);
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 350px;
        }

        .success-icon {
            font-size: 2.5em;
            background: white;
            color: #27ae60;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .success-message strong {
            display: block;
            font-size: 1.2em;
            margin-bottom: 5px;
        }

        @media (max-width: 768px) {
            .orders-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <div class="header-left">
            <h1>📊 注文管理一覧</h1>
            <p>お客様からの注文をリアルタイムで管理</p>
        </div>
        <a href="admin.php" class="back-button">← 管理画面に戻る</a>
    </div>

    <div class="table-links">
        <div class="table-links-title">📱 お客様テーブルに置ける表示:</div>
        <div class="table-buttons">
            <?php for($i=1; $i<=5; $i++): ?>
                <a href="index.php?tableNo=<?php echo $i; ?>" class="table-btn">Table <?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <div class="empty-title">注文がありません</div>
            <div class="empty-text">新しい注文が入るとここに表示されます</div>
        </div>
    <?php else: ?>
        <div class="orders-grid">
            <?php foreach ($orders as $order):
                $ts = strtotime($order['dateB']);
                $dateStr = date('Y/m/d H:i', $ts);
            ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="table-badge">🍽️ Table <?php echo htmlspecialchars($order['tableNo']); ?></div>
                        <div class="order-time"><?php echo $dateStr; ?></div>
                    </div>

                    <div class="order-body">
                        <div class="order-info-row">
                            <span class="order-label">注文番号:</span>
                            <span class="order-value"><?php echo htmlspecialchars(substr($order['orderNo'], -8)); ?></span>
                        </div>
                        <div class="order-info-row">
                            <span class="order-label">商品点数:</span>
                            <span class="order-value"><?php echo $order['itemCount']; ?>点</span>
                        </div>
                    </div>

                    <div class="order-total">
                        <div class="order-total-row">
                            <span>合計金額</span>
                            <span>¥<?php echo number_format($order['totalAmount']); ?></span>
                        </div>
                    </div>

                    <div class="order-actions">
                        <a href="order.php?orderNo=<?php echo urlencode($order['orderNo']); ?>" class="btn-details">
                            📋 詳細
                        </a>
                        <button onclick="confirmPayment('<?php echo htmlspecialchars($order['orderNo']); ?>', <?php echo $order['tableNo']; ?>, <?php echo $order['totalAmount']; ?>)" class="btn-payment">
                            💳 会計済み
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="paymentModal" class="payment-modal-overlay" style="display: none;">
    <div class="payment-modal-content">
        <div class="payment-modal-icon">💳</div>
        <h2 class="payment-modal-title">会計を完了しますか？</h2>

        <div class="payment-modal-details">
            <div class="detail-row">
                <span class="detail-label">テーブル:</span>
                <span class="detail-value" id="modalTableNo">Table 1</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">注文番号:</span>
                <span class="detail-value" id="modalOrderNo">20260108...</span>
            </div>
            <div class="detail-row total-row">
                <span class="detail-label">合計金額:</span>
                <span class="detail-value" id="modalAmount">¥0</span>
            </div>
        </div>

        <p class="payment-modal-warning">⚠️ この注文は一覧から削除されます</p>

        <div class="payment-modal-buttons">
            <button class="modal-btn cancel-btn" onclick="closePaymentModal()">キャンセル</button>
            <button class="modal-btn confirm-btn" onclick="executePayment()" id="confirmPaymentBtn">✓ 会計完了</button>
        </div>
    </div>
</div>

<div id="successNotification" class="success-notification" style="display: none;">
    <div class="success-content">
        <div class="success-icon">✓</div>
        <div class="success-message">
            <strong>会計が完了しました！</strong>
            <p id="successDetails">Table 1 の注文をクリアしました</p>
        </div>
    </div>
</div>

<script>
const csrfToken = '<?php echo htmlspecialchars($csrfToken); ?>';
let currentOrderNo = '';
let currentTableNo = 0;
let currentAmount = 0;

function confirmPayment(orderNo, tableNo, totalAmount) {
    currentOrderNo = orderNo;
    currentTableNo = tableNo;
    currentAmount = totalAmount;

    document.getElementById('modalTableNo').textContent = 'Table ' + tableNo;
    document.getElementById('modalOrderNo').textContent = orderNo;
    document.getElementById('modalAmount').textContent = '¥' + totalAmount.toLocaleString();

    document.getElementById('paymentModal').style.display = 'flex';
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
}

function executePayment() {
    const btn = document.getElementById('confirmPaymentBtn');
    btn.disabled = true;
    btn.textContent = '処理中...';

    const formData = new FormData();
    formData.append('orderNo', currentOrderNo);
    formData.append('csrf_token', csrfToken);

    fetch('process_payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closePaymentModal();
            showSuccessNotification(currentTableNo);
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            alert('❌ エラー: ' + data.message);
            btn.disabled = false;
            btn.textContent = '✓ 会計完了';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('通信エラーが発生しました');
        btn.disabled = false;
        btn.textContent = '✓ 会計完了';
    });
}

function showSuccessNotification(tableNo) {
    const notification = document.getElementById('successNotification');
    document.getElementById('successDetails').textContent = 'Table ' + tableNo + ' の注文をクリアしました';
    notification.style.display = 'block';

    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

document.getElementById('paymentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePaymentModal();
    }
});

// Auto-refresh every 10 seconds (skip if modal is open)
setInterval(() => {
    if (document.getElementById('paymentModal').style.display === 'none') {
        location.reload();
    }
}, 10000);
</script>

</body>
</html>
