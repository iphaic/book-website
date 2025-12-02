DROP DATABASE IF EXISTS online_bookstore;
CREATE DATABASE online_bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE online_bookstore;

-- user table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- book table
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100),
    image VARCHAR(255)
);

-- cart items, per user
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- past orders, per user
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- order items, per user
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- books available
INSERT INTO books (title, author, price, category, image, description) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', 10.99, 'Classic', NULL, 'A novel set in the Jazz Age.'),
('To Kill a Mockingbird', 'Harper Lee', 8.99, 'Classic', NULL, 'Story about racial injustice.'),
('1984', 'George Orwell', 9.99, 'Dystopian', NULL, 'A dystopian novel about surveillance.'),
('Pride and Prejudice', 'Jane Austen', 7.99, 'Romance', NULL, 'Classic romantic fiction.'),
('The Hobbit', 'J.R.R. Tolkien', 12.50, 'Fantasy', NULL, 'Prequel to the Lord of the Rings.'),
('Harry Potter and the Sorcerer''s Stone', 'J.K. Rowling', 10.50, 'Fantasy', NULL, 'First book in the Harry Potter series.');
