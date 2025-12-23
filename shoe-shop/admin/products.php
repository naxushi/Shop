<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isManager()) {
    $_SESSION['message'] = 'Доступ запрещен';
    $_SESSION['message_type'] = 'error';
    header('Location: ../index.php');
    exit();
}

$db = Database::getInstance()->getConnection();
$pageTitle = "Управление товарами";

// Добавление нового товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $category = sanitize($_POST['category']);
    $brand = sanitize($_POST['brand']);
    $price = (float)$_POST['price'];
    $color = sanitize($_POST['color']);
    $material = sanitize($_POST['material']);
    
    $stmt = $db->prepare("INSERT INTO products (name, description, category, brand, price, color, material) 
                          VALUES (:name, :description, :category, :brand, :price, :color, :material)");
    $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':category' => $category,
        ':brand' => $brand,
        ':price' => $price,
        ':color' => $color,
        ':material' => $material
    ]);
    
    $productId = $db->lastInsertId();
    
    // Добавление размеров
    if (!empty($_POST['sizes'])) {
        $sizes = explode(',', $_POST['sizes']);
        foreach ($sizes as $size) {
            $size = trim($size);
            if (!empty($size)) {
                $stmt = $db->prepare("INSERT INTO product_sizes (product_id, size, quantity) VALUES (:product_id, :size, :quantity)");
                $stmt->execute([
                    ':product_id' => $productId,
                    ':size' => $size,
                    ':quantity' => $_POST['quantity_' . $size] ?? 0
                ]);
            }
        }
    }
    
    $_SESSION['message'] = 'Товар успешно добавлен';
    $_SESSION['message_type'] = 'success';
    header('Location: products.php');
    exit();
}

// Редактирование товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $productId = (int)$_POST['product_id'];
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $category = sanitize($_POST['category']);
    $brand = sanitize($_POST['brand']);
    $price = (float)$_POST['price'];
    $color = sanitize($_POST['color']);
    $material = sanitize($_POST['material']);
    $status = sanitize($_POST['status']);
    
    $stmt = $db->prepare("UPDATE products SET 
                          name = :name, description = :description, category = :category, 
                          brand = :brand, price = :price, color = :color, 
                          material = :material, status = :status 
                          WHERE id = :id");
    $stmt->execute([
        ':id' => $productId,
        ':name' => $name,
        ':description' => $description,
        ':category' => $category,
        ':brand' => $brand,
        ':price' => $price,
        ':color' => $color,
        ':material' => $material,
        ':status' => $status
    ]);
    
    $_SESSION['message'] = 'Товар успешно обновлен';
    $_SESSION['message_type'] = 'success';
    header('Location: products.php');
    exit();
}

// Получение списка товаров
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$stmt = $db->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

// Подсчет общего количества
$stmt = $db->query("SELECT COUNT(*) as total FROM products");
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $limit);

include '../includes/header.php';
?>

<div class="admin-panel">
    <h1>Управление товарами</h1>
    
    <div class="admin-actions">
        <button class="btn btn-primary" onclick="showAddForm()">
            <i class="fas fa-plus"></i> Добавить товар
        </button>
    </div>
    
    <!-- Форма добавления товара -->
    <div id="addProductForm" class="modal-form" style="display: none;">
        <h2>Добавить новый товар</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label>Название:</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Описание:</label>
                <textarea name="description" rows="4"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Категория:</label>
                    <input type="text" name="category" required>
                </div>
                
                <div class="form-group">
                    <label>Бренд:</label>
                    <input type="text" name="brand" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Цена:</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Цвет:</label>
                    <input type="text" name="color">
                </div>
                
                <div class="form-group">
                    <label>Материал:</label>
                    <input type="text" name="material">
                </div>
            </div>
            
            <div class="form-group">
                <label>Размеры (через запятую):</label>
                <input type="text" name="sizes" placeholder="40, 41, 42, 43">
            </div>
            
            <div class="form-actions">
                <button type="submit" name="add_product" class="btn btn-primary">Добавить</button>
                <button type="button" class="btn btn-outline" onclick="hideAddForm()">Отмена</button>
            </div>
        </form>
    </div>
    
    <!-- Список товаров -->
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Категория</th>
                    <th>Бренд</th>
                    <th>Цена</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                        <td><?php echo htmlspecialchars($product['brand']); ?></td>
                        <td><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</td>
                        <td>
                            <span class="status-badge status-<?php echo $product['status']; ?>">
                                <?php echo $product['status'] === 'active' ? 'Активен' : 'Скрыт'; ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-small btn-outline">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="../product.php?id=<?php echo $product['id']; ?>" class="btn btn-small btn-outline" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="page-link">Назад</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>" class="page-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="page-link">Вперед</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showAddForm() {
    document.getElementById('addProductForm').style.display = 'block';
}

function hideAddForm() {
    document.getElementById('addProductForm').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>