<?php
require_once 'database.php';

class Cart {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getCart($userId) {
        // Находим или создаем корзину
        $stmt = $this->db->prepare("SELECT id FROM carts WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $cart = $stmt->fetch();
        
        if (!$cart) {
            $stmt = $this->db->prepare("INSERT INTO carts (user_id) VALUES (:user_id)");
            $stmt->execute([':user_id' => $userId]);
            $cartId = $this->db->lastInsertId();
        } else {
            $cartId = $cart['id'];
        }
        
        return $cartId;
    }
    
    public function addToCart($userId, $productId, $sizeId, $quantity = 1) {
        $cartId = $this->getCart($userId);
        
        // Проверяем наличие товара в корзине
        $stmt = $this->db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id AND size_id = :size_id");
        $stmt->execute([
            ':cart_id' => $cartId,
            ':product_id' => $productId,
            ':size_id' => $sizeId
        ]);
        $existingItem = $stmt->fetch();
        
        if ($existingItem) {
            // Обновляем количество
            $newQuantity = $existingItem['quantity'] + $quantity;
            $stmt = $this->db->prepare("UPDATE cart_items SET quantity = :quantity WHERE id = :id");
            return $stmt->execute([
                ':quantity' => $newQuantity,
                ':id' => $existingItem['id']
            ]);
        } else {
            // Добавляем новый товар
            $stmt = $this->db->prepare("INSERT INTO cart_items (cart_id, product_id, size_id, quantity) VALUES (:cart_id, :product_id, :size_id, :quantity)");
            return $stmt->execute([
                ':cart_id' => $cartId,
                ':product_id' => $productId,
                ':size_id' => $sizeId,
                ':quantity' => $quantity
            ]);
        }
    }
    
    public function getCartItems($userId) {
        $cartId = $this->getCart($userId);
        
        $sql = "SELECT ci.*, p.name, p.price, ps.size, 
                       (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as image
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                JOIN product_sizes ps ON ci.size_id = ps.id
                WHERE ci.cart_id = :cart_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cart_id' => $cartId]);
        return $stmt->fetchAll();
    }
    
    public function updateCartItem($userId, $itemId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeFromCart($userId, $itemId);
        }
        
        $cartId = $this->getCart($userId);
        
        $stmt = $this->db->prepare("UPDATE cart_items SET quantity = :quantity WHERE id = :id AND cart_id = :cart_id");
        return $stmt->execute([
            ':quantity' => $quantity,
            ':id' => $itemId,
            ':cart_id' => $cartId
        ]);
    }
    
    public function removeFromCart($userId, $itemId) {
        $cartId = $this->getCart($userId);
        
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE id = :id AND cart_id = :cart_id");
        return $stmt->execute([
            ':id' => $itemId,
            ':cart_id' => $cartId
        ]);
    }
    
    public function clearCart($userId) {
        $cartId = $this->getCart($userId);
        
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
        return $stmt->execute([':cart_id' => $cartId]);
    }
    
    public function getCartTotal($userId) {
        $items = $this->getCartItems($userId);
        $total = 0;
        
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return $total;
    }
    
    public function mergeCarts($userId, $sessionCart = []) {
        if (empty($sessionCart)) return;
        
        foreach ($sessionCart as $item) {
            $this->addToCart($userId, $item['product_id'], $item['size_id'], $item['quantity']);
        }
    }
}
?>