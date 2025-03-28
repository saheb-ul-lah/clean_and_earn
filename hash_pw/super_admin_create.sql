INSERT INTO users (
    name, 
    email, 
    password, 
    role, 
    status, 
    created_at, 
    updated_at
) VALUES (
    'Super Admin1', 
    'superadmin1@gmail.com', 
    '$2y$10$IJnwgXX7Lye9ZgICL8yk2O3VHUCXRUiOu5XgRmW/fimbzs0BTXFOC', 
    'super_admin', 
    'active', 
    NOW(), 
    NOW()
);