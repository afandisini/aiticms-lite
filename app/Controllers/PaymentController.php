<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\FrontAuthService;
use App\Services\FrontPaymentService;
use App\Services\FrontTransactionService;
use App\Services\MidtransService;
use App\Services\Cms\SystemSettingService;
use System\Http\Request;
use System\Http\Response;

class PaymentController
{
    public function __construct(
        private ?FrontPaymentService $paymentService = null,
        private ?FrontTransactionService $transactionService = null,
        private ?SystemSettingService $systemSettingService = null,
        private ?MidtransService $midtransService = null
    ) {
        $this->paymentService = $paymentService ?? new FrontPaymentService();
        $this->transactionService = $transactionService ?? new FrontTransactionService();
        $this->systemSettingService = $systemSettingService ?? new SystemSettingService();
        $this->midtransService = $midtransService ?? new MidtransService();
    }

    public function index(Request $request): Response
    {
        $guard = $this->ensureAuthenticated('/payment');
        if ($guard !== null) {
            return $guard;
        }

        $current = FrontAuthService::user();
        $userId = (int) ($current['id'] ?? 0);
        $midtransId = trim((string) $request->input('order', $request->input('id', '')));
        if ($midtransId === '') {
            $_SESSION['_front_flash'] = [
                'type' => 'error',
                'message' => 'Tagihan pembayaran belum dipilih.',
            ];
            return Response::redirect('/cart');
        }

        $transaction = $this->paymentService->findOrderForUser($midtransId, $userId);
        if ($transaction === null) {
            return Response::redirect('/not-found?from=' . rawurlencode($request->uri()));
        }

        $flash = $_SESSION['_front_flash'] ?? null;
        unset($_SESSION['_front_flash']);

        if ((int) ($transaction['status_bayar'] ?? -1) !== 2 || trim((string) ($transaction['snap_token'] ?? '')) === '') {
            $_SESSION['_front_flash'] = [
                'type' => 'error',
                'message' => 'Tagihan ini tidak lagi menunggu pembayaran. Status terbaru sudah diperbarui.',
            ];
            return Response::redirect('/users/transaction-details/' . rawurlencode($midtransId));
        }

        $siteInfo = $this->systemSettingService->information();
        $details = $this->transactionService->detailsByTransactionId((int) ($transaction['id'] ?? 0));
        $html = app()->view()->renderWithLayout('payment', [
            'title' => 'Pembayaran Midtrans',
            'siteInfo' => $siteInfo,
            'transaction' => $transaction,
            'details' => $details,
            'flash' => is_array($flash) ? $flash : null,
            'footerText' => '',
            'snapJsUrl' => $this->midtransService->snapJsUrl(),
            'snapClientKey' => $this->midtransService->clientKey(),
        ], 'layouts/app');

        return Response::html($html);
    }

    public function orders(Request $request): Response
    {
        $guard = $this->ensureAuthenticated('/cart');
        if ($guard !== null) {
            return $guard;
        }

        $current = FrontAuthService::user();
        $userId = (int) ($current['id'] ?? 0);

        try {
            $order = $this->paymentService->createOrderFromCart($userId);
            $_SESSION['_front_flash'] = [
                'type' => 'success',
                'message' => 'Tagihan Midtrans berhasil dibuat. Lanjutkan pembayaran.',
            ];

            return Response::redirect('/payment?order=' . rawurlencode((string) ($order['midtrans_id'] ?? '')));
        } catch (\Throwable $e) {
            $_SESSION['_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];

            return Response::redirect('/cart');
        }
    }

    public function finish(Request $request): Response
    {
        $guard = $this->ensureAuthenticated('/users/transaction');
        if ($guard !== null) {
            return $guard;
        }

        $current = FrontAuthService::user();
        $userId = (int) ($current['id'] ?? 0);
        $midtransId = trim((string) $request->input('order_id', $request->input('id', '')));
        if ($midtransId === '') {
            $_SESSION['_front_flash'] = [
                'type' => 'error',
                'message' => 'Order Midtrans tidak ditemukan.',
            ];
            return Response::redirect('/users/transaction');
        }

        try {
            $transaction = $this->paymentService->refreshOrderStatus($midtransId, $userId);
            $_SESSION['_front_flash'] = [
                'type' => $this->flashTypeByPaymentStatus((int) ($transaction['status_bayar'] ?? -1)),
                'message' => $this->messageByPaymentStatus((int) ($transaction['status_bayar'] ?? -1)),
            ];
        } catch (\Throwable $e) {
            $_SESSION['_front_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect('/users/transaction-details/' . rawurlencode($midtransId));
    }

    public function reload(Request $request): Response
    {
        $guard = $this->ensureAuthenticated('/users/transaction');
        if ($guard !== null) {
            return $guard;
        }

        $current = FrontAuthService::user();
        $userId = (int) ($current['id'] ?? 0);
        $midtransId = trim((string) $request->input('id', $request->input('order_id', '')));
        if ($midtransId === '') {
            $_SESSION['_front_flash'] = [
                'type' => 'error',
                'message' => 'Order Midtrans tidak ditemukan.',
            ];
            return Response::redirect('/users/transaction');
        }

        try {
            $transaction = $this->paymentService->refreshOrderStatus($midtransId, $userId);
            $_SESSION['_front_flash'] = [
                'type' => $this->flashTypeByPaymentStatus((int) ($transaction['status_bayar'] ?? -1)),
                'message' => 'Status pembayaran berhasil diperbarui.',
            ];
        } catch (\Throwable $e) {
            $_SESSION['_front_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect('/users/transaction-details/' . rawurlencode($midtransId));
    }

    public function cancel(Request $request, string $id): Response
    {
        $guard = $this->ensureAuthenticated('/users/transaction');
        if ($guard !== null) {
            return $guard;
        }

        $current = FrontAuthService::user();
        $userId = (int) ($current['id'] ?? 0);
        $midtransId = trim($id);
        if ($midtransId === '') {
            $_SESSION['_front_flash'] = [
                'type' => 'error',
                'message' => 'Order Midtrans tidak ditemukan.',
            ];
            return Response::redirect('/users/transaction');
        }

        try {
            $this->paymentService->cancelOrder($midtransId, $userId);
            $_SESSION['_front_flash'] = [
                'type' => 'success',
                'message' => 'Tagihan berhasil dibatalkan.',
            ];
        } catch (\Throwable $e) {
            $_SESSION['_front_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect('/users/transaction-details/' . rawurlencode($midtransId));
    }

    public function notificationHandler(Request $request): Response
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            return Response::json(['status' => 'error', 'message' => 'Invalid payload'], 400);
        }

        try {
            $result = $this->paymentService->processNotification($payload);
            return Response::json($result);
        } catch (\Throwable $e) {
            $statusCode = $e->getMessage() === 'Invalid Signature' ? 403 : 400;
            return Response::json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    private function ensureAuthenticated(string $redirect): ?Response
    {
        if (FrontAuthService::check()) {
            return null;
        }

        $_SESSION['_front_flash'] = [
            'type' => 'error',
            'message' => 'Silakan login atau register terlebih dahulu untuk melanjutkan pembayaran.',
        ];

        return Response::redirect('/login?redirect=' . rawurlencode($redirect));
    }

    private function flashTypeByPaymentStatus(int $statusBayar): string
    {
        return in_array($statusBayar, [2], true) ? 'error' : 'success';
    }

    private function messageByPaymentStatus(int $statusBayar): string
    {
        return match ($statusBayar) {
            3 => 'Pembayaran berhasil diterima.',
            2 => 'Pembayaran masih menunggu penyelesaian.',
            0 => 'Tagihan pembayaran sudah expired.',
            default => 'Pembayaran dibatalkan atau gagal diproses.',
        };
    }
}
