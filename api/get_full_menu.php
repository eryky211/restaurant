<?php
require_once '../db.php';

try {
    // Get all categories
    $cat_stmt = $pdo->query("SELECT * FROM Categories ORDER BY category_name");
    $categories = $cat_stmt->fetchAll();

    // Get all items (not just available ones)
    $item_stmt = $pdo->query("SELECT * FROM MenuItems ORDER BY item_name");
    $items = $item_stmt->fetchAll();

    // Build the menu structure
    $menu = [];
    foreach ($categories as $category) {
        $cat_data = [
            'category_id' => $category['category_id'],
            'category_name' => $category['category_name'],
            'items' => []
        ];

        foreach ($items as $item) {
            if ($item['category_id'] == $category['category_id']) {
                // Cast is_available to a boolean for proper JSON
                $item['is_available'] = (bool)$item['is_available'];
                $cat_data['items'][] = $item;
            }
        }
        $menu[] = $cat_data;
    }

    // Send back both categories and the structured menu
    $response = [
        'categories' => $categories,
        'menu' => $menu
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>