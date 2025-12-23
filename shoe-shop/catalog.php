<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$brand = isset($_GET['brand']) ? sanitize($_GET['brand']) : '';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : '';
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$filters = [
    'category' => $category,
    'brand' => $brand,
    'min_price' => $minPrice,
    'max_price' => $maxPrice,
    'search' => $search
];

$result = getProducts($filters, $page);
$products = $result['products'];
$totalPages = $result['total_pages'];
$currentPage = $result['current_page'];

$categories = getCategories();
$brands = getBrands();

$pageTitle = "Каталог товаров";
include 'includes/header.php';
?>

<section class="catalog">
    <h1>Каталог обуви</h1>
    
    <div class="catalog-container">
        <aside class="filters">
            <form method="GET" action="catalog.php">
                <div class="filter-group">
                    <h3>Поиск</h3>
                    <input type="text" name="search" placeholder="Название товара..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-group">
                    <h3>Категория</h3>
                    <select name="category">
                        <option value="">Все категории</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
                                <?php echo ucfirst($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <h3>Бренд</h3>
                    <select name="brand">
                        <option value="">Все бренды</option>
                        <?php foreach ($brands as $br): ?>
                            <option value="<?php echo $br; ?>" <?php echo $brand == $br ? 'selected' : ''; ?>>
                                <?php echo $br; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <h3>Цена</h3>
                    <div class="price-range">
                        <input type="number" name="min_price" placeholder="От" min="0" value="<?php echo $minPrice; ?>">
                        <input type="number" name="max_price" placeholder="До" min="0" value="<?php echo $maxPrice; ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Применить фильтры</button>
                <a href="catalog.php" class="btn btn-outline">Сбросить</a>
            </form>
        </aside>
        
        <main class="products-section">
            <?php if (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if (!empty($product['main_image'])): ?>
                                    <img src="<?php echo SITE_URL . '/' . $product['main_image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div class="no-image">Нет изображения</div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3><a href="product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                                <div class="product-brand"><?php echo htmlspecialchars($product['brand']); ?></div>
                                <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                                <div class="product-price"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</div>
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline">Подробнее</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?php echo $currentPage - 1; ?>&<?php echo http_build_query($filters); ?>" class="page-link">Назад</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $currentPage): ?>
                                <span class="page-link active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>" class="page-link"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?php echo $currentPage + 1; ?>&<?php echo http_build_query($filters); ?>" class="page-link">Вперед</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>Товары не найдены</h3>
                    <p>Попробуйте изменить параметры поиска</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</section>

<?php include 'includes/footer.php'; ?>