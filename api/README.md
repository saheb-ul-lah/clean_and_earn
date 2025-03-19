/api/
├── config/
│   ├── database.php
│   └── config.php
├── middleware/
│   ├── auth.php
│   ├── rate_limit.php
│   └── cors.php
├── controllers/
│   ├── auth_controller.php
│   ├── user_controller.php
│   ├── waste_listing_controller.php
│   ├── waste_collection_controller.php
│   ├── storage_controller.php
│   ├── inventory_controller.php
│   ├── purchase_controller.php
│   ├── waste_type_controller.php
│   └── points_controller.php
├── models/
│   ├── user.php
│   ├── waste_listing.php
│   ├── waste_collection.php
│   ├── storage_inventory.php
│   ├── waste_purchase.php
│   ├── waste_type.php
│   └── points_transaction.php
├── utils/
│   ├── response.php
│   ├── validator.php
│   └── jwt.php
├── .htaccess
└── index.php


# Navigate to api directory
cd api

# Create subdirectories
mkdir -p config middleware controllers models utils

# Create files in config directory
touch config/database.php config/config.php

# Create files in middleware directory
touch middleware/auth.php middleware/rate_limit.php middleware/cors.php

# Create files in controllers directory
touch controllers/auth_controller.php controllers/user_controller.php controllers/waste_listing_controller.php \
controllers/waste_collection_controller.php controllers/storage_controller.php controllers/inventory_controller.php \
controllers/purchase_controller.php controllers/waste_type_controller.php controllers/points_controller.php

# Create files in models directory
touch models/user.php models/waste_listing.php models/waste_collection.php models/storage_inventory.php \
models/waste_purchase.php models/waste_type.php models/points_transaction.php

# Create files in utils directory
touch utils/response.php utils/validator.php utils/jwt.php

# Create root-level files
touch .htaccess index.php

echo "✅ Folder structure and files created successfully!"










### Clean and Earn India - Complete API Implementation

I'll create a comprehensive RESTful API for the Clean and Earn India project that app developers can use to interact with the system. This API will be secure, well-structured, and follow best practices.

## API Structure

The API will be organized in the following directory structure:

```plaintext
/api/
├── config/
│   ├── database.php
│   └── config.php
├── middleware/
│   ├── auth.php
│   ├── rate_limit.php
│   └── cors.php
├── controllers/
│   ├── auth_controller.php
│   ├── user_controller.php
│   ├── waste_listing_controller.php
│   ├── waste_collection_controller.php
│   ├── storage_controller.php
│   ├── inventory_controller.php
│   ├── purchase_controller.php
│   ├── waste_type_controller.php
│   └── points_controller.php
├── models/
│   ├── user.php
│   ├── waste_listing.php
│   ├── waste_collection.php
│   ├── storage_inventory.php
│   ├── waste_purchase.php
│   ├── waste_type.php
│   └── points_transaction.php
├── utils/
│   ├── response.php
│   ├── validator.php
│   └── jwt.php
├── .htaccess
└── index.php
```

## API Implementation

### 1. Configuration Files

```php file="api/config/database.php"
...
```

```php file="api/config/config.php"
...
```

### 2. Middleware

```php file="api/middleware/auth.php"
...
```

```php file="api/middleware/rate_limit.php"
...
```

```php file="api/middleware/cors.php"
...
```

### 3. Utilities

```php file="api/utils/response.php"
...
```

```php file="api/utils/validator.php"
...
```

```php file="api/utils/jwt.php"
...
```

### 4. Models

```php file="api/models/user.php"
...
```

```php file="api/models/waste_listing.php"
...
```

```php file="api/models/waste_collection.php"
...
```

```php file="api/models/storage_inventory.php"
...
```

```php file="api/models/waste_purchase.php"
...
```

```php file="api/models/waste_type.php"
...
```

```php file="api/models/points_transaction.php"
...
```

### 5. Controllers

```php file="api/controllers/auth_controller.php"
...
```

```php file="api/controllers/user_controller.php"
...
```

```php file="api/controllers/waste_listing_controller.php"
...
```

```php file="api/controllers/waste_collection_controller.php"
...
```

```php file="api/controllers/storage_controller.php"
...
```

```php file="api/controllers/purchase_controller.php"
...
```

```php file="api/controllers/waste_type_controller.php"
...
```

```php file="api/controllers/points_controller.php"
...
```

### 6. API Entry Point and Routing

```php file="api/.htaccess"
...
```

```php file="api/index.php"
...
```

I'll continue with the rest of the `index.php` file and then provide a summary of the API endpoints:

```php file="api/index.php"
...
```

## API Endpoints Documentation

### Authentication Endpoints

1. **Register a new user**

1. **URL**: `/api/auth/register`
2. **Method**: `POST`
3. **Body**:

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "phone": "9876543210",
  "role": "household"
}
```


4. **Response**: JWT token and user details



2. **Login**

1. **URL**: `/api/auth/login`
2. **Method**: `POST`
3. **Body**:

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```


4. **Response**: JWT token and user details



3. **Forgot Password**

1. **URL**: `/api/auth/forgot-password`
2. **Method**: `POST`
3. **Body**:

```json
{
  "email": "john@example.com"
}
```


4. **Response**: Success message



4. **Reset Password**

1. **URL**: `/api/auth/reset-password`
2. **Method**: `POST`
3. **Body**:

```json
{
  "token": "reset_token",
  "password": "newpassword123",
  "confirm_password": "newpassword123"
}
```


4. **Response**: Success message



5. **Change Password** (Authenticated)

1. **URL**: `/api/change-password`
2. **Method**: `POST`
3. **Body**:

```json
{
  "current_password": "password123",
  "new_password": "newpassword123",
  "confirm_password": "newpassword123"
}
```


4. **Response**: Success message





### User Profile Endpoints

1. **Get User Profile** (Authenticated)

1. **URL**: `/api/profile`
2. **Method**: `GET`
3. **Response**: User profile details



2. **Update User Profile** (Authenticated)

1. **URL**: `/api/profile`
2. **Method**: `PUT`
3. **Body**:

```json
{
  "name": "John Doe",
  "phone": "9876543210",
  "address": "123 Main St",
  "city": "Mumbai",
  "state": "Maharashtra",
  "pincode": "400001"
}
```


4. **Response**: Success message





### Waste Types Endpoints

1. **Get All Waste Types**

1. **URL**: `/api/waste-types`
2. **Method**: `GET`
3. **Response**: List of waste types



2. **Get Single Waste Type**

1. **URL**: `/api/waste-types/{id}`
2. **Method**: `GET`
3. **Response**: Waste type details



3. **Get Waste Subtypes**

1. **URL**: `/api/waste-types/{id}/subtypes`
2. **Method**: `GET`
3. **Response**: List of waste subtypes for the specified waste type





### Waste Listings Endpoints (Household Users)

1. **Create Waste Listing** (Authenticated, Household)

1. **URL**: `/api/waste-listings`
2. **Method**: `POST`
3. **Body**:

```json
{
  "waste_type_id": 1,
  "waste_subtype_id": 2,
  "weight": 5.5,
  "quantity": 10,
  "description": "Old newspapers",
  "pickup_date": "2025-04-01",
  "pickup_time_slot": "Morning (8 AM - 12 PM)",
  "pickup_address": "123 Main St, Mumbai"
}
```


4. **Response**: Success message with listing ID



2. **Get User's Waste Listings** (Authenticated, Household)

1. **URL**: `/api/waste-listings`
2. **Method**: `GET`
3. **Response**: List of user's waste listings



3. **Get Single Waste Listing** (Authenticated, Household)

1. **URL**: `/api/waste-listings/{id}`
2. **Method**: `GET`
3. **Response**: Waste listing details



4. **Update Waste Listing** (Authenticated, Household)

1. **URL**: `/api/waste-listings/{id}`
2. **Method**: `PUT`
3. **Body**: Same as create
4. **Response**: Success message



5. **Delete Waste Listing** (Authenticated, Household)

1. **URL**: `/api/waste-listings/{id}`
2. **Method**: `DELETE`
3. **Response**: Success message





### Waste Collections Endpoints (Collector Users)

1. **Get Active Waste Listings** (Authenticated, Collector)

1. **URL**: `/api/waste-listings`
2. **Method**: `GET`
3. **Response**: List of active waste listings



2. **Create Waste Collection** (Authenticated, Collector)

1. **URL**: `/api/collections`
2. **Method**: `POST`
3. **Body**:

```json
{
  "listing_id": 1,
  "notes": "Will arrive at 10 AM"
}
```


4. **Response**: Success message with collection ID



3. **Get Collector's Collections** (Authenticated, Collector)

1. **URL**: `/api/collections`
2. **Method**: `GET`
3. **Response**: List of collector's waste collections



4. **Get Single Collection** (Authenticated, Collector/Household)

1. **URL**: `/api/collections/{id}`
2. **Method**: `GET`
3. **Response**: Collection details



5. **Update Collection Status** (Authenticated, Collector)

1. **URL**: `/api/collections/{id}`
2. **Method**: `PUT`
3. **Body**:

```json
{
  "actual_weight": 5.2,
  "status": "collected",
  "notes": "Collected successfully"
}
```


4. **Response**: Success message





### Storage Inventory Endpoints (Storage Users)

1. **Add Inventory Item** (Authenticated, Storage)

1. **URL**: `/api/inventory`
2. **Method**: `POST`
3. **Body**:

```json
{
  "waste_type_id": 1,
  "waste_subtype_id": 2,
  "weight": 10.5,
  "collection_id": 5,
  "status": "available"
}
```


4. **Response**: Success message with inventory ID



2. **Get Storage's Inventory** (Authenticated, Storage)

1. **URL**: `/api/inventory`
2. **Method**: `GET`
3. **Response**: List of storage's inventory items



3. **Get Single Inventory Item** (Authenticated)

1. **URL**: `/api/inventory/{id}`
2. **Method**: `GET`
3. **Response**: Inventory item details



4. **Update Inventory Status** (Authenticated, Storage)

1. **URL**: `/api/inventory/{id}`
2. **Method**: `PUT`
3. **Body**:

```json
{
  "status": "sold"
}
```


4. **Response**: Success message





### Purchase Endpoints (Buyer Users)

1. **Get Available Inventory** (Authenticated, Buyer)

1. **URL**: `/api/inventory`
2. **Method**: `GET`
3. **Response**: List of available inventory items



2. **Create Purchase** (Authenticated, Buyer)

1. **URL**: `/api/purchases`
2. **Method**: `POST`
3. **Body**:

```json
{
  "storage_id": 3,
  "inventory_id": 10,
  "weight": 5.0,
  "amount": 250.0,
  "pickup_date": "2025-04-05",
  "pickup_time": "10:00 AM"
}
```


4. **Response**: Success message with purchase ID



3. **Get Buyer's Purchases** (Authenticated, Buyer)

1. **URL**: `/api/purchases`
2. **Method**: `GET`
3. **Response**: List of buyer's purchases



4. **Get Storage's Sales** (Authenticated, Storage)

1. **URL**: `/api/purchases`
2. **Method**: `GET`
3. **Response**: List of storage's sales



5. **Get Single Purchase** (Authenticated, Buyer/Storage)

1. **URL**: `/api/purchases/{id}`
2. **Method**: `GET`
3. **Response**: Purchase details



6. **Update Purchase Status** (Authenticated, Buyer/Storage)

1. **URL**: `/api/purchases/{id}`
2. **Method**: `PUT`
3. **Body**:

```json
{
  "status": "paid",
  "payment_method": "UPI",
  "payment_reference": "UPI123456789"
}
```


4. **Response**: Success message



7. **Cancel Purchase** (Authenticated, Buyer)

1. **URL**: `/api/purchases/{id}`
2. **Method**: `DELETE`
3. **Response**: Success message





### Points Endpoints

1. **Get User's Total Points** (Authenticated)

1. **URL**: `/api/points`
2. **Method**: `GET`
3. **Response**: User's total points



2. **Get User's Points Transactions** (Authenticated)

1. **URL**: `/api/points/transactions`
2. **Method**: `GET`
3. **Response**: List of user's points transactions





### Admin Endpoints

1. **Create Waste Type** (Authenticated, Admin)

1. **URL**: `/api/admin/waste-types`
2. **Method**: `POST`
3. **Body**:

```json
{
  "name": "Glass",
  "description": "Glass waste including bottles and containers",
  "rate_per_kg": 10.0
}
```


4. **Response**: Success message with waste type ID



2. **Update Waste Type** (Authenticated, Admin)

1. **URL**: `/api/admin/waste-types/{id}`
2. **Method**: `PUT`
3. **Body**: Same as create
4. **Response**: Success message



3. **Create Waste Subtype** (Authenticated, Admin)

1. **URL**: `/api/admin/waste-types/{id}/subtypes`
2. **Method**: `POST`
3. **Body**:

```json
{
  "name": "Glass Bottles",
  "description": "Clear and colored glass bottles",
  "rate_per_kg": 12.0
}
```


4. **Response**: Success message with waste subtype ID



4. **Create Points Transaction** (Authenticated, Admin)

1. **URL**: `/api/admin/points/transactions`
2. **Method**: `POST`
3. **Body**:

```json
{
  "user_id": 5,
  "points": 100,
  "transaction_type": "earned",
  "reference_type": "manual",
  "description": "Bonus points for Earth Day"
}
```


4. **Response**: Success message with transaction ID





## Security Measures

1. **Authentication**: JWT-based authentication with token expiration
2. **Authorization**: Role-based access control for different endpoints
3. **Input Validation**: Comprehensive validation of all input data
4. **SQL Injection Protection**: Using PDO with prepared statements
5. **XSS Protection**: Sanitizing all input and output data
6. **CSRF Protection**: JWT tokens help prevent CSRF attacks
7. **Rate Limiting**: Preventing abuse with request rate limiting
8. **CORS Protection**: Configurable CORS policy
9. **Password Security**: Secure password hashing with bcrypt
10. **Error Handling**: Proper error handling without exposing sensitive information


## How to Use the API

1. **Register a user** using the `/api/auth/register` endpoint
2. **Login** using the `/api/auth/login` endpoint to get a JWT token
3. **Include the JWT token** in the Authorization header for all authenticated requests:

```plaintext
Authorization: Bearer your_jwt_token
```


4. **Make API calls** to the appropriate endpoints based on the user's role


This API provides a complete solution for the Clean and Earn India project, allowing app developers to build mobile or web applications that interact with the system. The API is secure, well-structured, and follows RESTful principles.