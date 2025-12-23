<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$auth = new Auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitize($_POST['phone']);
    
    // Валидация
    $errors = [];
    
    if (empty($name)) $errors[] = 'Введите имя';
    if (empty($email)) $errors[] = 'Введите email';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Неверный формат email';
    if (empty($password)) $errors[] = 'Введите пароль';
    if (strlen($password) < 6) $errors[] = 'Пароль должен быть не менее 6 символов';
    if ($password !== $confirm_password) $errors[] = 'Пароли не совпадают';
    
    if (empty($errors)) {
        $result = $auth->register($name, $email, $password, $phone);
        
        if ($result['success']) {
            $_SESSION['message'] = 'Регистрация успешно завершена!';
            $_SESSION['message_type'] = 'success';
            header('Location: index.php');
            exit();
        } else {
            $errors[] = $result['message'];
        }
    }
}

$pageTitle = "Регистрация";
include 'includes/header.php';
?>

<section class="auth-form">
    <h1>Регистрация</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="register.php">
        <div class="form-group">
            <label for="name">Имя:</label>
            <input type="text" name="name" id="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Телефон:</label>
            <input type="tel" name="phone" id="phone">
        </div>
        
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" name="password" id="password" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Подтвердите пароль:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
    </form>
    
    <div class="auth-links">
        <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>