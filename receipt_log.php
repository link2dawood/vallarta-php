<?php
// 420 Vallarta E-Receipt Log Viewer

require_once('settings/db.php');
require_once('settings/receipt_config.php');
session_start();

// Check if user is logged in (admin access)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    header('location: LogReg/login.php');
    exit();
}

// Pagination
$limit = 50;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get receipt logs with order information
$logs_query = "SELECT rl.*, o.name, o.email, o.total_price, o.valid as order_status
               FROM receipt_log rl
               LEFT JOIN ordere o ON rl.order_id = o.id
               ORDER BY rl.generated_at DESC
               LIMIT $limit OFFSET $offset";

$logs_result = mysqli_query($con, $logs_query);

// Get total count for pagination
$count_query = mysqli_query($con, "SELECT COUNT(*) as total FROM receipt_log");
$total_records = mysqli_fetch_array($count_query)['total'];
$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Receipt Log - 420 Vallarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .log-table { font-size: 0.9em; }
        .receipt-id { font-family: monospace; font-weight: bold; color: #2a561f; }
        .status-sent { color: #28a745; }
        .status-failed { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>📜 E-Receipt Log</h1>
                    <div>
                        <a href="receipt_settings.php" class="btn btn-info me-2">⚙️ Settings</a>
                        <a href="admin.php" class="btn btn-secondary">← Back to Admin</a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-success">📧 Total Sent</h5>
                                <h3 class="card-text">
                                    <?php
                                    $sent_count = mysqli_query($con, "SELECT COUNT(*) as count FROM receipt_log WHERE email_sent = 1");
                                    echo mysqli_fetch_array($sent_count)['count'];
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-warning">⏰ Today</h5>
                                <h3 class="card-text">
                                    <?php
                                    $today_count = mysqli_query($con, "SELECT COUNT(*) as count FROM receipt_log WHERE DATE(generated_at) = CURDATE()");
                                    echo mysqli_fetch_array($today_count)['count'];
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-info">📅 This Week</h5>
                                <h3 class="card-text">
                                    <?php
                                    $week_count = mysqli_query($con, "SELECT COUNT(*) as count FROM receipt_log WHERE generated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                                    echo mysqli_fetch_array($week_count)['count'];
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-primary">📊 This Month</h5>
                                <h3 class="card-text">
                                    <?php
                                    $month_count = mysqli_query($con, "SELECT COUNT(*) as count FROM receipt_log WHERE MONTH(generated_at) = MONTH(NOW()) AND YEAR(generated_at) = YEAR(NOW())");
                                    echo mysqli_fetch_array($month_count)['count'];
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Receipt Log Table -->
                <div class="card">
                    <div class="card-header">
                        <h5>📋 Receipt Log (Page <?php echo $page; ?> of <?php echo $total_pages; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped log-table">
                                <thead>
                                    <tr>
                                        <th>Receipt ID</th>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Generated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($logs_result) > 0): ?>
                                        <?php while ($log = mysqli_fetch_array($logs_result)): ?>
                                            <tr>
                                                <td>
                                                    <span class="receipt-id"><?php echo htmlspecialchars($log['receipt_id']); ?></span>
                                                </td>
                                                <td>
                                                    <a href="oreder_info.php?id=<?php echo $log['order_id']; ?>" target="_blank">
                                                        #<?php echo $log['order_id']; ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($log['name'] ?: 'N/A'); ?></td>
                                                <td>
                                                    <small><?php echo htmlspecialchars($log['email'] ?: 'N/A'); ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($log['total_price']): ?>
                                                        <span class="text-success">$<?php echo number_format($log['total_price'], 2); ?> MXN</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($log['email_sent']): ?>
                                                        <span class="badge bg-success status-sent">✓ Sent</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger status-failed">✗ Failed</span>
                                                    <?php endif; ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($log['order_status'] ?: 'Unknown'); ?></small>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?php echo date('M j, Y', strtotime($log['generated_at'])); ?><br>
                                                        <?php echo date('g:i A', strtotime($log['generated_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group-vertical btn-group-sm">
                                                        <a href="receipt.php?order_id=<?php echo $log['order_id']; ?>&preview=1"
                                                           class="btn btn-outline-info btn-sm" target="_blank" title="Preview Receipt">
                                                            👁️
                                                        </a>
                                                        <a href="finalize_order.php?order_id=<?php echo $log['order_id']; ?>"
                                                           class="btn btn-outline-success btn-sm" title="Resend Receipt">
                                                            📧
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                No receipts found. <a href="admin.php">Create your first order</a> to get started.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Receipt log pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);

                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>