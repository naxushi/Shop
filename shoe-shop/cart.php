<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/cart.php';

if (!isLoggedIn()) {
    $_SESSION['message'] = 'Для просмотра корзины необходимо авторизоваться';
    $_SESSION['message_type'] = 'warning';
    header('Location: login.php');
    exit();
}

$cart = new Cart();
$cartItems = $cart->getCartItems($_SESSION['user_id']);
$cartTotal = $cart->getCartTotal($_SESSION['user_id']);

// Обновление корзины
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $itemId => $quantity) {
            $cart->updateCartItem($_SESSION['user_id'], $itemId, $quantity);
        }
        $_SESSION['message'] = 'Корзина обновлена';
        $_SESSION['message_type'] = 'success';
        header('Location: cart.php');
        exit();
    } elseif (isset($_POST['remove_item'])) {
        $itemId = (int)$_POST['item_id'];
        $cart->removeFromCart($_SESSION['user_id'], $itemId);
        $_SESSION['message'] = 'Товар удален из корзины';
        $_SESSION['message_type'] = 'success';
        header('Location: cart.php');
        exit();
    } elseif (isset($_POST['clear_cart'])) {
        $cart->clearCart($_SESSION['user_id']);
        $_SESSION['message'] = 'Корзина очищена';
        $_SESSION['message_type'] = 'success';
        header('Location: cart.php');
        exit();
    }
}

$pageTitle = "Корзина";
include 'includes/header.php';
?>

<section class="cart">
    <h1>Корзина покупок</h1>
    
    <?php if (!empty($cartItems)): ?>
        <form method="POST" action="cart.php">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="item-image">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo SITE_URL . '/' . $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <div class="no-image-small">Нет изображения</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="item-info">
                            <h3><a href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></h3>
                            <div class="item-size">Размер: <?php echo $item['size']; ?></div>
                            <div class="item-price"><?php echo number_format($item['price'], 0, '', ' '); ?> ₽</div>
                        </div>
                        
                        <div class="item-quantity">
                            <input type="number" name="quantity[<?php echo $item['id']; ?>]" 
                                   value="<?php echo $item['quantity']; ?>" min="1" max="10">
                        </div>
                        
                        <div class="item-total">
                            <?php echo number_format($item['price'] * $item['quantity'], 0, '', ' '); ?> ₽
                        </div>
                        
                        <div class="item-actions">
                            <button type="submit" name="remove_item" value="1" class="btn btn-danger btn-small">
                                <i class="fas fa-trash"></i>
                            </button>
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <div class="summary-total">
                    <h3>Итого: <?php echo number_format($cartTotal, 0, '', ' '); ?> ₽</h3>
                </div>
                
                <div class="cart-actions">
                    <button type="submit" name="update_cart" class="btn btn-outline">
                        <i class="fas fa-sync-alt"></i> Обновить корзину
                    </button>
                    
                    <button type="submit" name="clear_cart" class="btn btn-danger" 
                            onclick="return confirm('Вы уверены, что хотите очистить корзину?')">
                        <i class="fas fa-trash"></i> Очистить корзину
                    </button>
                    
                    <a href="checkout.php" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> Оформить заказ
                    </a>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h3>Ваша корзина пуста</h3>
            <p>Добавьте товары из каталога</p>
            <a href="catalog.php" class="btn btn-primary">Перейти в каталог</a>
        </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>