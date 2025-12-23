<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$auth = new Auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    $result = $auth->login($email, $password);
    
    if ($result['success']) {
        $_SESSION['message'] = 'Добро пожаловать, ' . $result['user']['name'] . '!';
        $_SESSION['message_type'] = 'success';
        
        // Слияние корзин (если есть локальная корзина)
        if (isset($_COOKIE['cart'])) {
            $sessionCart = json_decode($_COOKIE['cart'], true);
            require_once 'includes/cart.php';
            $cart = new Cart();
            $cart->mergeCarts($result['user']['id'], $sessionCart);
            setcookie('cart', '', time() - 3600, '/'); // Удаляем куку
        }
        
        header('Location: index.php');
        exit();
    } else {
        $error = $result['message'];
    }
}

$pageTitle = "Вход";
include 'includes/header.php';
?>

<section class="auth-form">
    <h1>Вход в систему</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" name="password" id="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Войти</button>
    </form>
    
    <div class="auth-links">
        <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>