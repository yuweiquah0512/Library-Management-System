1. Create a database first.

CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;


2. Create table 'users'.

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL,
  password CHAR(40) NOT NULL,
  role ENUM('admin', 'user') NOT NULL
);


3. Create table 'books'.

CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  author VARCHAR(100),
  isbn VARCHAR(20),
  copies INT DEFAULT 1,
  category VARCHAR(50),
  publish_date DATE,
  language VARCHAR(50),
  cover_image VARCHAR(255)
);


4. Create table 'borrow_requests'.

CREATE TABLE IF NOT EXISTS borrow_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50),
  book_id INT,
  book_title VARCHAR(255),
  request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  rent_duration INT DEFAULT 7,
  due_date DATETIME,
  returned TINYINT(1) DEFAULT 0,
  returned_date DATETIME NULL,
  user_deleted TINYINT(1) DEFAULT 0,
  admin_deleted TINYINT(1) DEFAULT 0
);


5. Create table 'book_suggestions'.

CREATE TABLE book_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    name VARCHAR(100),
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255),
    suggested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'not added'
);


6. Insert admin into table 'users'.

INSERT INTO users (name, username, email, password, role)
VALUES ('admin', '111', 'admin111@student.mmu.edu.my', SHA1('admin111'), 'admin');


7. Insert a sample user into table 'users'.

INSERT INTO users (name, username, email, password, role)
VALUES ('Stephen How', 'Stephen', 'stephow@gmail.com', SHA1('222222'), 'user');


8. Inserts books into table 'books'.

INSERT INTO books (title, author, isbn, copies, cover_image, category, publish_date, language) VALUES
('Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', '978-0-7475-3269-9', 10, 'cover/harry_potter_1.jpg', 'Fantasy', '1997-06-26', 'English'),
('Harry Potter and the Chamber of Secrets', 'J.K. Rowling', '999-1-6475-3789-10', 10, 'cover/harry_potter_2.jpg', 'Fantasy', '1998-07-02', 'English'),
('Harry Potter and the Prisoner of Azkaban', 'J.K. Rowling', '978-0-7475-4511-08', 10, 'cover/harry_potter_3.jpg', 'Fantasy', '1999-07-08', 'English'),
('Harry Potter and the Goblet of Fire', 'J.K. Rowling', '978-0-4391-3960-1', 10, 'cover/harry_potter_4.jpg', 'Fantasy', '2000-07-08', 'English'),
('Harry Potter and the Order of the Phoenix', 'J.K. Rowling', '978-0-4393-5807-1', 10, 'cover/harry_potter_5.jpg', 'Fantasy', '2003-06-21', 'English'),
('Harry Potter and the Half-Blood Prince', 'J.K. Rowling', '978-1-4088-5570-6', 10, 'cover/harry_potter_6.jpg', 'Fantasy', '2005-07-16', 'English'),
('Harry Potter and the Deathly Hallows', 'J.K. Rowling', '978-0-5451-3970-0', 10, 'cover/harry_potter_7.jpg', 'Fantasy', '2007-07-21', 'English'),
('Dune - Frank Herbert', 'Frank Herbert', '978-0-441-17271-9', 10, 'cover/dune_1.jpg', 'Science Fiction', '1965-08-01', 'English'),
('Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', '978-0-06-231609-7', 10, 'cover/Sapiens_a_brief.jpg', 'History', '2011-06-04', 'English'),
('The Shining', 'Stephen King', '978-0-385-12167-5', 10, 'cover/The_Shining.jpg', 'Horror', '1977-01-28', 'English'),
('The Girl with the Dragon Tattoo', 'Stieg Larsson', '978-0-307-45454-6', 10, 'cover/The_Girl_with_the_dragon.jpg', 'Mystery', '2005-08-01', 'English'),
('IT', 'Stephen King', '978-0-451-16951-8', 10, 'cover/IT.jpg', 'Horror', '1986-09-15', 'English'),
('The Haunting of Hill House', 'Shirley Jackson', '978-0-14-303998-3', 10, 'cover/The_Haunting_Hill_House.jpg', 'Horror', '1959-10-16', 'English'),
('Ender\'s Game', 'Orson Scott Card', '978-0-8125-5070-2', 10, 'cover/Enders_game.jpg', 'Science Fiction', '1985-01-15', 'English'),
('The Hobbit', 'J.R.R. Tolkien', '978-0-618-00221-3', 5, 'cover/The_Hobbit.jpg', 'Fantasy', '1937-09-21', 'English'),
('Guns, Germs, and Steel', 'Jared Diamond', '978-0-393-31755-8', 10, 'cover/Guns_Gearms_and_Steel.jpg', 'History', '1997-03-01', 'English'),
('Coraline', 'Neil Gaiman', '978-0-06-113937-6', 3, 'cover/Coraline.jpg', 'Horror', '2002-08-04', 'English'),
('Gone Girl', 'Gillian Flynn', '978-0-307-58836-4', 3, 'cover/Gone_Girl.jpg', 'Mystery', '2012-06-05', 'English'),
('Me Before You', 'Jojo Moyes', '978-0-14-312454-2', 3, 'cover/Me_Before_you.jpg', 'Romance', '2012-01-05', 'English'),
('A Game of Thrones', 'George R.R. Martin', '978-0-553-89784-5', 5, 'cover/Game_of_Thrones.jpg', 'Fantasy', '1996-08-06', 'English'),
('Bird Box', 'Josh Malerman', '978-0-06-225965-3', 3, 'cover/Bird_Box.jpg', 'Horror', '2014-05-13', 'English');
('诛仙','萧鼎'，'978-7-80603-834-2', 6, 'cover/诛仙.jpg', 'Fantasy', '2003-07-01','Chinese');
('Pride and Prejudice', 'Jane Austen', '978-0-19-953556-9', 8, 'Romance', '1813-01-28','English');

9. You can login as admin or user.
Admin
Username: 111
Password: admin111

Sample user:
Username: Stephen
Password: 222222
