<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
                    <i class="fas fa-shoe-prints"></i> <?php echo SITE_NAME; ?>
                </a>
                
                <div class="nav-menu">
                    <a href="<?php echo SITE_URL; ?>/catalog.php">Каталог</a>
                    <a href="<?php echo SITE_URL; ?>/cart.php" class="cart-link">
                        <i class="fas fa-shopping-cart"></i> Корзина
                        <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                            <span class="cart-count"><?php echo $_SESSION['cart_count']; ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo SITE_URL; ?>/orders.php">Мои заказы</a>
                        <a href="<?php echo SITE_URL; ?>/profile.php"><?php echo $_SESSION['user_name']; ?></a>
                        <a href="<?php echo SITE_URL; ?>/logout.php">Выйти</a>
                        
                        <?php if (isAdmin() || isManager()): ?>
                            <a href="<?php echo SITE_URL; ?>/admin/" class="admin-link">Панель управления</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/login.php">Войти</a>
                        <a href="<?php echo SITE_URL; ?>/register.php">Регистрация</a>
                    <?php endif; ?>
                </div>
                
                <div class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </nav>
        
        <div class="mobile-menu">
            <a href="<?php echo SITE_URL; ?>/catalog.php">Каталог</a>
            <a href="<?php echo SITE_URL; ?>/cart.php">Корзина</a>
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo SITE_URL; ?>/orders.php">Мои заказы</a>
                <a href="<?php echo SITE_URL; ?>/profile.php">Профиль</a>
                <a href="<?php echo SITE_URL; ?>/logout.php">Выйти</a>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/login.php">Войти</a>
                <a href="<?php echo SITE_URL; ?>/register.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </header>
    
    <main class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?>">
                <?php echo $_SESSION['message']; ?>
                <?php unset($_SESSION['message']); ?>
                <?php unset($_SESSION['message_type']); ?>
            </div>
        <?php endif; ?>