CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(100),
    action VARCHAR(255),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);

INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'FLUX'),
('site_description', 'L\'actualité du Mali et du Sahel en continu'),
('contact_email', 'contact@flux.ml'),
('maintenance_mode', '0');
