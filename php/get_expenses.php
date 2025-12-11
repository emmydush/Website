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
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
    
    // Build query
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(description LIKE ? OR notes LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($category)) {
        $where_conditions[] = "category = ?";
        $params[] = $category;
    }
    
    if (!empty($date_from)) {
        $where_conditions[] = "expense_date >= ?";
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = "expense_date <= ?";
        $params[] = $date_to;
    }
    
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Get expenses
    $stmt = $pdo->prepare("
        SELECT id, description, amount, expense_date, category, notes
        FROM expenses 
        $where_clause
        ORDER BY expense_date DESC, id DESC
    ");
    $stmt->execute($params);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get categories for filter
    $stmt = $pdo->prepare("SELECT DISTINCT category FROM expenses WHERE category IS NOT NULL AND category != '' ORDER BY category");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get stats
    $monthly_start = date('Y-m-01');
    $yearly_start = date('Y-01-01');
    
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN expense_date >= ? THEN amount ELSE 0 END) as monthly_total,
            SUM(amount) as yearly_total
        FROM expenses 
        WHERE expense_date >= ?
    ");
    $stmt->execute([$monthly_start, $yearly_start]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'expenses' => $expenses,
        'categories' => $categories,
        'stats' => $stats
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>