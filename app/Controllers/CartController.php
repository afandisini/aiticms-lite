<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CartService;
use App\Services\Cms\SystemSettingService;
use App\Services\FrontAuthService;
use System\Http\Request;
use System\Http\Response;

class CartController
{
    public function __construct(
        private ?CartService $cartService = null,
        private ?SystemSettingService $systemSettingService = null
    ) {
        $this->cartService = $cartService ?? new CartService();
        $this->systemSettingService = $systemSettingService ?? new SystemSettingService();
    }

    public function index(Request $request): Response
    {
        $guestRedirect = $this->ensureAuthenticated('/cart');
        if ($guestRedirect !== null) {
            return $guestRedirect;
        }

        $flash = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);

        $siteInfo = $this->systemSettingService->information();

        $html = app()->view()->renderWithLayout('cart', [
            'title' => 'Keranjang Belanja',
            'items' => $this->cartService->items(),
            'subtotal' => $this->cartService->subtotal(),
            'cartCount' => $this->cartService->count(),
            'siteInfo' => $siteInfo,
            'flash' => is_array($flash) ? $flash : null,
            'isLoggedIn' => FrontAuthService::check(),
        ], 'layouts/app');

        return Response::html($html);
    }

    public function add(Request $request): Response
    {
        $productId = (int) $request->input('product_id', 0);
        $redirect = trim((string) $request->input('redirect', '/cart'));
        if ($redirect === '') {
            $redirect = '/cart';
        }

        $guestRedirect = $this->ensureAuthenticated($redirect);
        if ($guestRedirect !== null) {
            return $guestRedirect;
        }

        try {
            $this->cartService->add($productId, 1);
            $_SESSION['_flash'] = [
                'type' => 'success',
                'message' => 'Produk berhasil ditambahkan ke keranjang.',
            ];
        } catch (\Throwable $e) {
            $_SESSION['_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect($redirect);
    }

    public function remove(Request $request): Response
    {
        $guestRedirect = $this->ensureAuthenticated('/cart');
        if ($guestRedirect !== null) {
            return $guestRedirect;
        }

        $productId = (int) $request->input('product_id', 0);
        $this->cartService->remove($productId);

        $_SESSION['_flash'] = [
            'type' => 'success',
            'message' => 'Produk dihapus dari keranjang.',
        ];

        return Response::redirect('/cart');
    }

    private function ensureAuthenticated(string $redirect): ?Response
    {
        if (FrontAuthService::check()) {
            return null;
        }

        $_SESSION['_front_flash'] = [
            'type' => 'error',
            'message' => 'Silakan login atau register terlebih dahulu untuk mengakses keranjang.',
        ];

        $redirect = trim($redirect);
        if ($redirect === '' || !str_starts_with($redirect, '/')) {
            $redirect = '/cart';
        }

        return Response::redirect('/login?redirect=' . rawurlencode($redirect));
    }
}
