-- Tạo database
CREATE DATABASE IF NOT EXISTS webnovel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE webnovel;

-- ========================
-- BẢNG NGƯỜI DÙNG (users)
-- ========================
CREATE TABLE users (
    user_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    email       VARCHAR(255) UNIQUE NOT NULL,
    password    VARCHAR(255) NOT NULL,
    avatar      VARCHAR(255),
    level       TINYINT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================
-- BẢNG TÁC GIẢ (authors)
-- ========================
CREATE TABLE authors (
    author_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================
-- BẢNG THỂ LOẠI CHÍNH (categories)
-- ========================
CREATE TABLE categories (
    category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT
);

-- ========================
-- BẢNG TAG (tags)
-- ========================
CREATE TABLE tags (
    tag_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL
);

-- ========================
-- BẢNG TRUYỆN (novels)
-- ĐÃ BỎ cột author_id, dùng bảng novel_author phía dưới
-- ========================
CREATE TABLE novels (
    novel_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    cover       VARCHAR(255),
    status      ENUM('Đang ra', 'Full', 'Drop') DEFAULT 'Đang ra',
    category_id INT UNSIGNED,  -- Mỗi truyện chỉ có 1 thể loại chính
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED 	 NULL,
    approval ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    rating INT,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- ========================
-- BẢNG KẾT NỐI TRUYỆN VÀ TÁC GIẢ (novel_author)
-- Một truyện có thể có nhiều tác giả, một tác giả có thể viết nhiều truyện
-- ========================
CREATE TABLE novel_author (
    novel_id    INT UNSIGNED,
    author_id   INT UNSIGNED,
    PRIMARY KEY (novel_id, author_id),
    FOREIGN KEY (novel_id) REFERENCES novels(novel_id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(author_id) ON DELETE CASCADE
);

-- ========================
-- BẢNG LIÊN KẾT TRUYỆN VÀ TAG (novel_tag)
-- ========================
CREATE TABLE novel_tag (
    novel_id INT UNSIGNED,
    tag_id   INT UNSIGNED,
    PRIMARY KEY (novel_id, tag_id),
    FOREIGN KEY (novel_id) REFERENCES novels(novel_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(tag_id) ON DELETE CASCADE
);

-- ========================
-- BẢNG CHƯƠNG TRUYỆN (chapters)
-- ========================
CREATE TABLE chapters (
chapter_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    novel_id    INT UNSIGNED,
    title       VARCHAR(255),
    content     LONGTEXT,
    number      INT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (novel_id) REFERENCES novels(novel_id) ON DELETE CASCADE
);
CREATE TABLE chapter_images (
    image_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chapter_id  INT UNSIGNED,
    novel_id    INT UNSIGNED,
    image_url   VARCHAR(255) NOT NULL,
    image_order INT DEFAULT 0,
    FOREIGN KEY (chapter_id) REFERENCES chapters(chapter_id) ON DELETE CASCADE,
    FOREIGN KEY (novel_id) REFERENCES novels(novel_id) ON DELETE CASCADE
);


-- ========================
-- BẢNG BÌNH LUẬN (comments)
-- ========================
CREATE TABLE comments (
    comment_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    novel_id    INT UNSIGNED,
    user_id     INT UNSIGNED,
    content     TEXT,
    rating      INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (novel_id) REFERENCES novels(novel_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ========================
-- BẢNG LỊCH SỬ ĐỌC (reading_history)
-- ========================
CREATE TABLE reading_history (
    user_id     INT UNSIGNED,
    novel_id    INT UNSIGNED,
    chapter_id  INT UNSIGNED,
    last_read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, novel_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (novel_id) REFERENCES novels(novel_id) ON DELETE CASCADE,
    FOREIGN KEY (chapter_id) REFERENCES chapters(chapter_id) ON DELETE SET NULL
);

-- ========================
-- BẢNG THƯ VIỆN (user_library)
-- ========================
CREATE TABLE user_library (
    user_id     INT UNSIGNED,
    novel_id    INT UNSIGNED,
    added_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, novel_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (novel_id) REFERENCES novels(novel_id) ON DELETE CASCADE
);

INSERT INTO novel_author (novel_id, author_id) VALUES (1, 1)
INSERT INTO authors (author_id, name) VALUES (1, 'Eiichiro Oda')
UPDATE novels
SET rating = 9
WHERE novel_id = 1;
ALTER TABLE novels
ADD COLUMN rating INT
UPDATE users SET level = 1 WHERE email = 'admin@email.com';
SELECT * FROM users;

ALTER TABLE novels
    ADD COLUMN created_by INT UNSIGNED 	 NULL,
    ADD COLUMN approval ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    ADD FOREIGN KEY (created_by) REFERENCES users(user_id);

SELECT * FROM novels 
