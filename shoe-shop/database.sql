-- Создание базы данных
CREATE DATABASE IF NOT EXISTS shoe_store;
USE shoe_store;

-- Таблица пользователей
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'manager', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица товаров
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    brand VARCHAR(100),
    price DECIMAL(10, 2) NOT NULL,
    color VARCHAR(50),
    material VARCHAR(100),
    status ENUM('active', 'hidden') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица размеров товаров
CREATE TABLE product_sizes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    size DECIMAL(4,1) NOT NULL,
    quantity INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_size (product_id, size)
);

-- Таблица изображений товаров
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Таблица корзин
CREATE TABLE carts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_cart (user_id)
);

-- Таблица элементов корзины
CREATE TABLE cart_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    size_id INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (size_id) REFERENCES product_sizes(id),
    UNIQUE KEY unique_cart_product_size (cart_id, product_id, size_id)
);

-- Таблица заказов
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('created', 'paid', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'created',
    shipping_address TEXT,
    payment_method ENUM('card', 'cash', 'online') DEFAULT 'card',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Таблица элементов заказа
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    size_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (size_id) REFERENCES product_sizes(id)
);

-- Вставка тестовых данных
-- Администратор (пароль: admin123)
INSERT INTO users (email, password, name, phone, role) VALUES 
('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Администратор', '+79991234567', 'admin'),
('manager@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Менеджер', '+79991234568', 'manager'),
('user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Пользователь', '+79991234569', 'user');

-- Тестовые товары
INSERT INTO products (name, description, category, brand, price, color, material, status) VALUES 
('Кроссовки спортивные', 'Удобные кроссовки для занятий спортом и повседневной носки', 'sneakers', 'Nike', 4999.00, 'черный', 'текстиль', 'active'),
('Туфли офисные', 'Классические туфли для делового стиля', 'shoes', 'Ecco', 7999.00, 'коричневый', 'кожа', 'active'),
('Ботинки зимние', 'Теплые ботинки для холодной погоды', 'boots', 'Timberland', 12999.00, 'желтый', 'кожа', 'active'),
('Кеды повседневные', 'Стильные кеды для молодежи', 'sneakers', 'Converse', 3999.00, 'белый', 'хлопок', 'active');

-- Размеры для товаров
INSERT INTO product_sizes (product_id, size, quantity) VALUES 
(1, 40, 10), (1, 41, 15), (1, 42, 8), (1, 43, 5),
(2, 39, 7), (2, 40, 12), (2, 41, 9),
(3, 41, 6), (3, 42, 8), (3, 43, 4),
(4, 38, 15), (4, 39, 20), (4, 40, 18);

-- Изображения товаров
INSERT INTO product_images (product_id, image_url, is_main) VALUES 
(1, 'images/products/sneakers1.jpg', 1),
(1, 'images/products/sneakers2.jpg', 0),
(2, 'images/products/shoes1.jpg', 1),
(3, 'images/products/boots1.jpg', 1),
(4, 'images/products/sneakers3.jpg', 1);