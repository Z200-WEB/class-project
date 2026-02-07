<?php
// Start output buffering to prevent header issues
ob_start();

// Authentication first
require_once 'auth.php';
requireAuth();

// Load database
require_once 'pdo.php';

// Get ID parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin.php?error=' . urlencode('Invalid ID'));
    exit;
}

$id = (int)$_GET['id'];

try {
    // Delete item from database (lowercase table name)
    $sql = "DELETE FROM sItem WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Delete image file if exists
    $imagePath = 'itemImages/' . $id . '.jpg';
    if (file_exists($imagePath)) {
        @unlink($imagePath);
    }

    // Clear output buffer and redirect
    ob_end_clean();
    header('Location: admin.php?success=item_deleted&t=' . time());
    exit;

} catch (Exception $e) {
    ob_end_clean();
    header('Location: admin.php?error=' . urlencode($e->getMessage()));
    exit;
}
