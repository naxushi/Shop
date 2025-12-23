<?php
require_once 'database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isManager() {
    return isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'manager' || $_SESSION['user_role'] === 'admin');
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function getProducts($filters = [], $page = 1) {
    $db = Database::getInstance()->getConnection();
    
    $where = [];
    $params = [];
    
    if (!empty($filters['category'])) {
        $where[] = "category = :category";
        $params[':category'] = $filters['category'];
    }
    
    if (!empty($filters['brand'])) {
        $where[] = "brand = :brand";
        $params[':brand'] = $filters['brand'];
    }
    
    if (!empty($filters['min_price'])) {
        $where[] = "price >= :min_price";
        $params[':min_price'] = $filters['min_price'];
    }
    
    if (!empty($filters['max_price'])) {
        $where[] = "price <= :max_price";
        $params[':max_price'] = $filters['max_price'];
    }
    
    if (!empty($filters['search'])) {
        $where[] = "(name LIKE :search OR description LIKE :search)";
        $params[':search'] = '%' . $filters['search'] . '%';
    }
    
    $where[] = "status = 'active'";
    
    $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";
    
    // Подсчет общего количества
    $countSql = "SELECT COUNT(*) as total FROM products $whereClause";
    $stmt = $db->prepare($countSql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / ITEMS_PER_PAGE);
    
    // Получение данных с пагинацией
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    $sql = "SELECT p.*, 
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image
            FROM products p 
            $whereClause 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', ITEMS_PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    return [
        'products' => $products,
        'total_pages' => $totalPages,
        'current_page' => $page,
        'total_items' => $total
    ];
}

function getProductById($id) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT p.* FROM products p WHERE p.id = :id AND p.status = 'active'");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();
    
    if ($product) {
        // Получаем размеры
        $stmt = $db->prepare("SELECT * FROM product_sizes WHERE product_id = :product_id AND quantity > 0");
        $stmt->execute([':product_id' => $id]);
        $product['sizes'] = $stmt->fetchAll();
        
        // Получаем изображения
        $stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = :product_id ORDER BY is_main DESC");
        $stmt->execute([':product_id' => $id]);
        $product['images'] = $stmt->fetchAll();
    }
    
    return $product;
}

function getCategories() {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT DISTINCT category FROM products WHERE status = 'active' ORDER BY category");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getBrands() {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT DISTINCT brand FROM products WHERE status = 'active' ORDER BY brand");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>