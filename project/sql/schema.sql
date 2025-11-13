CREATE TABLE contact_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_main VARCHAR(50) NOT NULL,
    phone_secondary VARCHAR(50),
    email VARCHAR(120) NOT NULL,
    whatsapp_link VARCHAR(255),
    telegram_link VARCHAR(255),
    address TEXT,
    map_embed TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO admins (username, password) VALUES ('admin', '$2y$12$8fQXMLH/Fmb6n.KZIUomau9vdc6BysdWN9i8LU1KqKA1Pt7zRlYWW');

CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    short_text TEXT,
    full_text MEDIUMTEXT,
    image MEDIUMTEXT,
    meta_title VARCHAR(255),
    meta_description VARCHAR(255),
    meta_keywords VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    hero_image MEDIUMTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_category_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    image_path MEDIUMTEXT NOT NULL,
    CONSTRAINT fk_category_image_category FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    group_title VARCHAR(255) NOT NULL,
    main_image MEDIUMTEXT,
    left_description TEXT,
    seo_text TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_group_category FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_group_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    image_path MEDIUMTEXT NOT NULL,
    CONSTRAINT fk_group_image_group FOREIGN KEY (group_id) REFERENCES product_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_group_columns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    column_name VARCHAR(255) NOT NULL,
    order_index INT DEFAULT 0,
    CONSTRAINT fk_group_column_group FOREIGN KEY (group_id) REFERENCES product_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_group_rows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    row_index INT DEFAULT 0,
    CONSTRAINT fk_group_row_group FOREIGN KEY (group_id) REFERENCES product_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_group_cells (
    id INT AUTO_INCREMENT PRIMARY KEY,
    row_id INT NOT NULL,
    column_id INT NOT NULL,
    value TEXT,
    CONSTRAINT fk_cell_row FOREIGN KEY (row_id) REFERENCES product_group_rows(id) ON DELETE CASCADE,
    CONSTRAINT fk_cell_column FOREIGN KEY (column_id) REFERENCES product_group_columns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(120) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE site_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_key VARCHAR(100) NOT NULL,
    image_data MEDIUMTEXT NOT NULL,
    alt_text VARCHAR(255),
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_site_images_key (asset_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
