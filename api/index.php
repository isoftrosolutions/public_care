<?php
require_once __DIR__ . '/helpers.php';

jsonResponse([
    'success' => true,
    'name' => 'Public Care Ayurveda API',
    'version' => '1.0',
    'endpoints' => [
        'GET /api/products' => 'List products (query: category, search, sort, min_price, max_price, page, per_page)',
        'GET /api/product?id=N' => 'Single product with reviews',
        'GET /api/categories' => 'List categories',
        'POST /api/login' => 'Login (body: email, password)',
        'POST /api/register' => 'Register (body: full_name, email, mobile, password)',
        'POST /api/logout' => 'Logout',
        'GET /api/user' => 'Get current user profile (auth required)',
        'PATCH /api/user' => 'Update profile (body: full_name, mobile)',
        'GET /api/cart' => 'Get cart items',
        'POST /api/cart' => 'Add/update/remove cart item (body: product_id, action: add|remove|delete)',
    ],
]);
