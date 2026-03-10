<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Cms\ProductService;
use App\Services\FrontAuthService;
use App\Services\ProductCommentService;
use System\Http\Request;
use System\Http\Response;

class ProductCommentController
{
    public function __construct(
        private ?ProductService $productService = null,
        private ?ProductCommentService $productCommentService = null
    ) {
        $this->productService = $productService ?? new ProductService();
        $this->productCommentService = $productCommentService ?? new ProductCommentService();
    }

    public function store(Request $request, string $slug): Response
    {
        $slug = trim($slug);
        $redirect = '/products/' . rawurlencode($slug) . '.html?tab=reviews#product-review-section';

        if (!FrontAuthService::check()) {
            $_SESSION['_flash'] = [
                'type' => 'error',
                'message' => 'Silakan login sebagai client/pengguna sebelum menulis ulasan produk.',
            ];

            return Response::redirect('/login?redirect=' . rawurlencode($redirect));
        }

        $product = $this->productService->findPublishedBySlug($slug);
        if ($product === null) {
            $_SESSION['_flash'] = [
                'type' => 'error',
                'message' => 'Produk tidak ditemukan.',
            ];

            return Response::redirect('/');
        }

        if (strtoupper(trim((string) ($product['comment_active'] ?? 'N'))) !== 'Y') {
            $_SESSION['_flash'] = [
                'type' => 'error',
                'message' => 'Komentar untuk produk ini sedang dinonaktifkan.',
            ];

            return Response::redirect($redirect);
        }

        $user = FrontAuthService::user();
        $userId = (int) ($user['id'] ?? 0);

        try {
            $result = $this->productCommentService->submitForProduct(
                (int) ($product['id'] ?? 0),
                $userId,
                $request->all(),
                (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
                (string) ($_SERVER['HTTP_USER_AGENT'] ?? '')
            );

            $status = (string) ($result['status'] ?? 'pending');
            $message = $status === 'spam'
                ? 'Ulasan tersimpan tetapi terdeteksi mengandung kalimat yang perlu diperiksa admin.'
                : 'Ulasan berhasil dikirim dan menunggu approval admin.';

            $_SESSION['_flash'] = [
                'type' => 'success',
                'message' => $message,
            ];
        } catch (\Throwable $e) {
            $_SESSION['_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect($redirect);
    }
}
