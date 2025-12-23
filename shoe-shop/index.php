<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = "Главная";
$products = getProducts([], 1);

include 'includes/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <h1>Магазин качественной обуви</h1>
        <p>Большой выбор обуви для всей семьи</p>
        <a href="catalog.php" class="btn btn-primary">Смотреть каталог</a>
    </div>
</section>

<section class="featured-products">
    <h2>Популярные товары</h2>
    <div class="products-grid">
        <?php foreach ($products['products'] as $product): ?>
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
                    <div class="product-price"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</div>
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline">Подробнее</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="features">
    <div class="feature">
        <i class="fas fa-shipping-fast"></i>
        <h3>Быстрая доставка</h3>
        <p>Доставка по всей России</p>
    </div>
    <div class="feature">
        <i class="fas fa-undo-alt"></i>
        <h3>Возврат товара</h3>
        <p>30 дней на возврат</p>
    </div>
    <div class="feature">
        <i class="fas fa-shield-alt"></i>
        <h3>Гарантия качества</h3>
        <p>Официальная гарантия</p>
    </div>
    <div class="feature">
        <i class="fas fa-credit-card"></i>
        <h3>Удобная оплата</h3>
        <p>Различные способы оплаты</p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>