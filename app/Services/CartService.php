<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Cms\ProductService;

class CartService
{
    public function __construct(private ?ProductService $productService = null)
    {
        $this->productService = $productService ?? new ProductService();
    }

    public function add(int $productId, int $qty = 1): void
    {
        if ($productId <= 0) {
            throw new \RuntimeException('Produk tidak valid.');
        }

        $product = $this->productService->findById($productId);
        if ($product === null) {
            throw new \RuntimeException('Produk tidak ditemukan.');
        }

        $stock = max(0, (int) ($product['stok'] ?? 0));
        if ($stock < 1) {
            throw new \RuntimeException('Stok produk sedang habis.');
        }

        $qty = max(1, $qty);
        $cart = $this->rawCart();
        $currentQty = (int) ($cart[$productId]['qty'] ?? 0);
        $newQty = min($stock, $currentQty + $qty);

        $cart[$productId] = [
            'product_id' => $productId,
            'qty' => $newQty,
        ];

        $_SESSION['front_cart'] = $cart;
    }

    public function remove(int $productId): void
    {
        $cart = $this->rawCart();
        unset($cart[$productId]);
        $_SESSION['front_cart'] = $cart;
    }

    public function clear(): void
    {
        unset($_SESSION['front_cart']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function items(): array
    {
        $cart = $this->rawCart();
        if ($cart === []) {
            return [];
        }

        $products = $this->productService->findPublishedByIds(array_keys($cart));
        $items = [];
        foreach ($products as $product) {
            $productId = (int) ($product['id'] ?? 0);
            if ($productId <= 0 || !isset($cart[$productId])) {
                continue;
            }

            $qty = max(1, (int) ($cart[$productId]['qty'] ?? 1));
            $price = (int) ($product['price_sell'] ?? 0);
            $items[] = [
                'product' => $product,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $price * $qty,
            ];
        }

        return $items;
    }

    public function count(): int
    {
        $count = 0;
        foreach ($this->rawCart() as $item) {
            $count += max(1, (int) ($item['qty'] ?? 1));
        }

        return $count;
    }

    public function subtotal(): int
    {
        $subtotal = 0;
        foreach ($this->items() as $item) {
            $subtotal += (int) ($item['subtotal'] ?? 0);
        }

        return $subtotal;
    }

    /**
     * @return array<int, array{product_id:int, qty:int}>
     */
    private function rawCart(): array
    {
        $raw = $_SESSION['front_cart'] ?? [];
        if (!is_array($raw)) {
            return [];
        }

        $cart = [];
        foreach ($raw as $key => $item) {
            $productId = (int) ($item['product_id'] ?? $key);
            if ($productId <= 0) {
                continue;
            }
            $cart[$productId] = [
                'product_id' => $productId,
                'qty' => max(1, (int) ($item['qty'] ?? 1)),
            ];
        }

        return $cart;
    }
}
