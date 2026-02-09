-- 用户表
CREATE TABLE user (
    u_id INT AUTO_INCREMENT PRIMARY KEY,
    u_name VARCHAR(50) NOT NULL,
    password VARCHAR(80) NOT NULL,
    phone VARCHAR(15) NOT NULL UNIQUE
) AUTO_INCREMENT = 1;

-- 药房表
CREATE TABLE pharmacy (
    pharmacy_id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_name VARCHAR(50) NOT NULL,
    p_address VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(15) NOT NULL,
    p_password VARCHAR(80) NOT NULL
) AUTO_INCREMENT = 1;

-- 产品表
CREATE TABLE product (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(50) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    is_prescription_required BOOLEAN NOT NULL DEFAULT FALSE
) AUTO_INCREMENT = 1;

-- 库存表
CREATE TABLE inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    pharmacy_id INT NOT NULL,
    product_id INT NOT NULL,
    stock_quantity INT NOT NULL,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacy(pharmacy_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE
) AUTO_INCREMENT = 1;

-- 处方表
CREATE TABLE prescription (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,
    u_id INT NOT NULL,
    image_url VARCHAR(200) NOT NULL,
    expiry_date DATE NOT NULL,
    p_status VARCHAR(20) NOT NULL,
    FOREIGN KEY (u_id) REFERENCES user(u_id) ON DELETE CASCADE
) AUTO_INCREMENT = 1;

-- 订单表
CREATE TABLE `order` (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    u_id INT NOT NULL,
    pharmacy_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    o_status VARCHAR(20) NOT NULL,
    order_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (u_id) REFERENCES user(u_id) ON DELETE CASCADE,
    FOREIGN KEY (pharmacy_id) REFERENCES pharmacy(pharmacy_id) ON DELETE CASCADE
) AUTO_INCREMENT = 1;

-- 订单详情表
CREATE TABLE order_detail (
    order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    prescription_id INT,
    FOREIGN KEY (order_id) REFERENCES `order`(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
    FOREIGN KEY (prescription_id) REFERENCES prescription(prescription_id) ON DELETE SET NULL
) AUTO_INCREMENT = 1;

-- 配送表
CREATE TABLE delivery (
    delivery_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    d_address VARCHAR(100) NOT NULL,
    d_status VARCHAR(20) NOT NULL,
    courier_contact VARCHAR(15),
    FOREIGN KEY (order_id) REFERENCES `order`(order_id) ON DELETE CASCADE
) AUTO_INCREMENT = 1;

