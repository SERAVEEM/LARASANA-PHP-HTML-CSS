<?php
// src/controllers/ProductController.php
require_once __DIR__ . '/../models/Product.php';

class ProductController {
    public static function index() {
        return Product::all();
    }

    public static function show($id) {
        return Product::find($id);
    }

    public static function store($data) {
        return Product::create($data);
    }

    public static function update($id, $data) {
        return Product::update($id, $data);
    }

    public static function destroy($id) {
        return Product::delete($id);
    }
}
