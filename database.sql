-- Restaurant ordering system schema
-- Import in phpMyAdmin (or: mysql -u root < database.sql)

CREATE DATABASE IF NOT EXISTS restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurant_db;

DROP TABLE IF EXISTS waiter_calls;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS dining_tables;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS settings;

CREATE TABLE settings (
  k VARCHAR(60) PRIMARY KEY,
  v TEXT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  username VARCHAR(40) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','manager','waiter','kitchen','cashier') NOT NULL DEFAULT 'waiter',
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  phone VARCHAR(20) NOT NULL UNIQUE,
  email VARCHAR(120) NULL,
  visits INT NOT NULL DEFAULT 1,
  last_visit DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE dining_tables (
  id INT AUTO_INCREMENT PRIMARY KEY,
  table_no VARCHAR(20) NOT NULL UNIQUE,
  seats INT NOT NULL DEFAULT 4,
  token CHAR(32) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255) NOT NULL DEFAULT '',
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(150) NOT NULL DEFAULT '',
  available TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  table_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('card','jazzcash','easypaisa','cash') NOT NULL,
  reference VARCHAR(80) NOT NULL DEFAULT '',
  status ENUM('pending','paid') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  paid_at DATETIME NULL,
  FOREIGN KEY (customer_id) REFERENCES customers(id),
  FOREIGN KEY (table_id) REFERENCES dining_tables(id)
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  table_id INT NOT NULL,
  status ENUM('pending','preparing','ready','served','cancelled') NOT NULL DEFAULT 'pending',
  subtotal DECIMAL(10,2) NOT NULL,
  tax DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  notes VARCHAR(255) NOT NULL DEFAULT '',
  payment_status ENUM('unpaid','cash_pending','paid') NOT NULL DEFAULT 'unpaid',
  payment_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  paid_at DATETIME NULL,
  FOREIGN KEY (customer_id) REFERENCES customers(id),
  FOREIGN KEY (table_id) REFERENCES dining_tables(id),
  FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
  INDEX (status), INDEX (payment_status), INDEX (created_at)
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  item_id INT NULL,
  name VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  qty INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (item_id) REFERENCES menu_items(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE waiter_calls (
  id INT AUTO_INCREMENT PRIMARY KEY,
  table_id INT NOT NULL,
  customer_id INT NULL,
  reason VARCHAR(60) NOT NULL,
  status ENUM('open','done') NOT NULL DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (table_id) REFERENCES dining_tables(id) ON DELETE CASCADE,
  INDEX (status)
) ENGINE=InnoDB;

INSERT INTO settings (k, v) VALUES
 ('restaurant_name', 'Burgundy Table'),
 ('tagline', 'Fire, spice and everything nice'),
 ('phone', '+92 300 0000000'),
 ('address', 'Karachi, Pakistan'),
 ('tax_percent', '13'),
 ('wifi_ssid', 'BurgundyTable-Guest'),
 ('wifi_password', 'welcome2026'),
 ('wifi_security', 'WPA');


-- Seed data
INSERT INTO categories (id, name, sort_order) VALUES
 (1, 'Starters', 1),
 (2, 'BBQ & Grill', 2),
 (3, 'Main Course', 3),
 (4, 'Biryani & Rice', 4),
 (5, 'Breads', 5),
 (6, 'Desserts', 6),
 (7, 'Drinks', 7);

INSERT INTO menu_items (category_id, name, description, price, image) VALUES
 (1, 'Chicken Wings', 'Crispy wings tossed in smoky chilli glaze', 650, 'chicken-wings.svg'),
 (1, 'Spring Rolls', 'Golden rolls with vegetable filling and sweet chilli dip', 450, 'spring-rolls.svg'),
 (1, 'Chaat Platter', 'Tangy street-style chaat with chutneys', 400, 'chaat-platter.svg'),
 (2, 'Chicken Tikka', 'Charcoal grilled, marinated overnight', 750, 'chicken-tikka.svg'),
 (2, 'Seekh Kabab', 'Juicy minced beef kababs with mint chutney', 850, 'seekh-kabab.svg'),
 (2, 'Malai Boti', 'Creamy, melt-in-mouth chicken boti', 900, 'malai-boti.svg'),
 (3, 'Chicken Karahi', 'Wok-cooked with tomato, ginger and green chilli', 1800, 'chicken-karahi.svg'),
 (3, 'Beef Nihari', 'Slow cooked overnight, served with naan', 1400, 'beef-nihari.svg'),
 (3, 'Daal Makhni', 'Black lentils simmered in butter and cream', 650, 'daal-makhni.svg'),
 (4, 'Chicken Biryani', 'Fragrant basmati layered with spiced chicken', 700, 'chicken-biryani.svg'),
 (4, 'Kabuli Pulao', 'Rice with tender meat, carrots and raisins', 600, 'kabuli-pulao.svg'),
 (5, 'Garlic Naan', 'Tandoor baked with garlic butter', 150, 'garlic-naan.svg'),
 (5, 'Tandoori Roti', 'Fresh from the clay oven', 60, 'tandoori-roti.svg'),
 (6, 'Gulab Jamun', 'Warm milk dumplings in rose syrup', 300, 'gulab-jamun.svg'),
 (6, 'Kheer', 'Creamy rice pudding with pistachio', 350, 'kheer.svg'),
 (7, 'Mint Margarita', 'Fresh mint, lemon and soda', 300, 'mint-margarita.svg'),
 (7, 'Sweet Lassi', 'Chilled yoghurt drink', 250, 'sweet-lassi.svg'),
 (7, 'Soft Drink', 'Choice of Coke, Sprite, Fanta', 120, 'soft-drink.svg');

INSERT INTO users (name, username, password, role) VALUES
 ('Admin', 'admin', '$2y$10$drAg/5IHlAZPsZYMwP2U7OktRUp/P5IXFpiCyVKXXpZ6oVQRIe9hW', 'admin'),
 ('Manager', 'manager', '$2y$10$.dWa7AEx3sVl8CSyplO1fuDtAoE1fzlMLUfNtpew3/nX/RxiL26FG', 'manager'),
 ('Waiter One', 'waiter', '$2y$10$jAHp8PevTa2CylU9gDTJF.QhKy5Qql6RGNnlf2eW6D8q8Zb7OhkVq', 'waiter'),
 ('Kitchen', 'kitchen', '$2y$10$99EWpbcvytSLHSrhh0N8KelooQwPxUqeT4RbxXcQAsfr2XFb0pzda', 'kitchen'),
 ('Cashier', 'cashier', '$2y$10$Da2GPBxW66/l6lALQJMrpO2ATJKBwYqHiJiiGnKRR5VpLTAJ4Mnfu', 'cashier');

INSERT INTO dining_tables (table_no, seats, token) VALUES
 ('1', 4, 'e72811fc7060d0c7825128ade5738f6d'),
 ('2', 6, 'ed8cd185429226c2ce810c2a1c833944'),
 ('3', 4, '81be8702a5471364a440db1516934d32'),
 ('4', 6, '622614cd47e1a7baf676b7626cf59656'),
 ('5', 4, '0cd9848dfb63d15038c05339e5080a94'),
 ('6', 6, '755653e3d82fc0bd6790baedf905e303'),
 ('7', 4, 'be51eb98b36f9ff0b5fb7b13d6c04ed8'),
 ('8', 6, 'cdfb546855e9730ac2baf895e0f6999f');
