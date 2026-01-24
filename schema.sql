CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE fonts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  width INT NOT NULL,
  height INT NOT NULL,
  overlay_png_path VARCHAR(255) DEFAULT NULL,
  media_fit_mode ENUM('cover','contain') NOT NULL DEFAULT 'cover',
  background_color VARCHAR(20) DEFAULT '#000000',
  export_format ENUM('jpg','png') NOT NULL DEFAULT 'jpg',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE template_text_fields (
  id INT AUTO_INCREMENT PRIMARY KEY,
  template_id INT NOT NULL,
  field_key VARCHAR(40) NOT NULL,
  x INT NOT NULL,
  y INT NOT NULL,
  w INT NOT NULL,
  h INT NOT NULL,
  padding INT NOT NULL DEFAULT 0,
  font_id INT DEFAULT NULL,
  base_font_size INT NOT NULL,
  min_font_size INT NOT NULL,
  max_lines INT NOT NULL,
  line_height DECIMAL(4,2) NOT NULL,
  color VARCHAR(20) NOT NULL,
  align ENUM('left','center','right') NOT NULL DEFAULT 'left',
  valign ENUM('top','middle','bottom') NOT NULL DEFAULT 'top',
  stroke_enabled TINYINT(1) NOT NULL DEFAULT 0,
  stroke_width INT NOT NULL DEFAULT 2,
  stroke_color VARCHAR(20) NOT NULL DEFAULT '#000000',
  shadow_enabled TINYINT(1) NOT NULL DEFAULT 0,
  shadow_x INT NOT NULL DEFAULT 2,
  shadow_y INT NOT NULL DEFAULT 2,
  shadow_blur INT NOT NULL DEFAULT 4,
  shadow_color VARCHAR(20) NOT NULL DEFAULT '#000000',
  draw_order INT NOT NULL DEFAULT 1,
  UNIQUE KEY uniq_template_field (template_id, field_key),
  FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE CASCADE,
  FOREIGN KEY (font_id) REFERENCES fonts(id) ON DELETE SET NULL
);

CREATE TABLE renders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  template_id INT NOT NULL,
  input_path VARCHAR(255) NOT NULL,
  output_path VARCHAR(255) DEFAULT NULL,
  preview_path VARCHAR(255) DEFAULT NULL,
  params_json JSON NOT NULL,
  status ENUM('processing','done','failed') NOT NULL DEFAULT 'processing',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE CASCADE
);
