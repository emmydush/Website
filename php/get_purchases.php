<?php
header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once 'db_connect.php';

try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $supplier_id = isset($_GET['supplier_id']) ? intval($_GET['supplier_id']) : 0;
    $date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
    
    // Build query
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(p.description LIKE ? OR s.name LIKE ? OR pr.name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($status)) {
        $where_conditions[] = "p.status = ?";
        $params[] = $status;
    }
    
    if ($supplier_id > 0) {
        $where_conditions[] = "p.supplier_id = ?";
        $params[] = $supplier_id;
    }
    
    if (!empty($date_from)) {
        $where_conditions[] = "p.purchase_date >= ?";
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = "p.purchase_date <= ?";
        $params[] = $date_to;
    }
    
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Get purchases
    $stmt = $pdo->prepare("
        SELECT p.id, p.quantity, p.unit_price, p.total_amount, p.purchase_date, p.status, p.notes,
               s.name as supplier_name, pr.name as product_name
        FROM purchases p
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        LEFT JOIN products pr ON p.product_id = pr.id
        $where_clause
        ORDER BY p.purchase_date DESC, p.id DESC
    ");
    $stmt->execute($params);
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get suppliers for filter
    $stmt = $pdo->prepare("SELECT id, name FROM suppliers ORDER BY name");
    $stmt->execute();
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get stats
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(total_amount) as total_value
        FROM purchases
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'purchases' => $purchases,
        'suppliers' => $suppliers,
        'stats' => $stats
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>