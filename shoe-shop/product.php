<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/cart.php';

if (!isset($_GET['id'])) {
    header('Location: catalog.php');
    exit();
}

$productId = (int)$_GET['id'];
$product = getProductById($productId);

if (!$product) {
    header('Location: catalog.php');
    exit();
}

// Добавление в корзину
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        $_SESSION['message'] = 'Для добавления товаров в корзину необходимо авторизоваться';
        $_SESSION['message_type'] = 'warning';
        header('Location: login.php');
        exit();
    }
    
    $sizeId = (int)$_POST['size_id'];
    $quantity = (int)$_POST['quantity'];
    
    $cart = new Cart();
    if ($cart->addToCart($_SESSION['user_id'], $productId, $sizeId, $quantity)) {
        $_SESSION['message'] = 'Товар добавлен в корзину';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Ошибка при добавлении товара в корзину';
        $_SESSION['message_type'] = 'error';
    }
}

$pageTitle = $product['name'];
include 'includes/header.php';
?>

<section class="product-detail">
    <div class="product-images">
        <?php if (!empty($product['images'])): ?>
            <div class="main-image">
                <img src="<?php echo SITE_URL . '/' . $product['images'][0]['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <?php if (count($product['images']) > 1): ?>
                <div class="thumbnails">
                    <?php foreach ($product['images'] as $image): ?>
                        <img src="<?php echo SITE_URL . '/' . $image['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-image-large">Нет изображения</div>
        <?php endif; ?>
    </div>
    
    <div class="product-info">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="product-meta">
            <span class="brand">Бренд: <?php echo htmlspecialchars($product['brand']); ?></span>
            <span class="category">Категория: <?php echo htmlspecialchars($product['category']); ?></span>
            <span class="material">Материал: <?php echo htmlspecialchars($product['material']); ?></span>
            <span class="color">Цвет: <?php echo htmlspecialchars($product['color']); ?></span>
        </div>
        
        <div class="product-price">
            <span class="price"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</span>
        </div>
        
        <div class="product-description">
            <h3>Описание</h3>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
        
        <?php if (!empty($product['sizes'])): ?>
            <form method="POST" action="" class="add-to-cart-form">
                <div class="size-selection">
                    <h3>Доступные размеры:</h3>
                    <div class="sizes">
                        <?php foreach ($product['sizes'] as $size): ?>
                            <label class="size-option">
                                <input type="radio" name="size_id" value="<?php echo $size['id']; ?>" required>
                                <span class="size-label"><?php echo $size['size']; ?></span>
                                <span class="size-quantity">(осталось: <?php echo $size['quantity']; ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="quantity-selection">
                    <label for="quantity">Количество:</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="10">
                </div>
                
                <?php if (isLoggedIn()): ?>
                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-large">
                        <i class="fas fa-shopping-cart"></i> Добавить в корзину
                    </button>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-large">Войдите, чтобы добавить в корзину</a>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <div class="out-of-stock">
                <p>Товар временно отсутствует</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>