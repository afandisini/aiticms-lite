<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Cms\SystemSettingService;
use App\Services\FrontAuthService;
use App\Services\FrontTransactionService;
use App\Services\FrontUserService;
use System\Http\Request;
use System\Http\Response;

class FrontUserController
{
    public function __construct(
        private ?FrontUserService $userService = null,
        private ?FrontTransactionService $transactionService = null,
        private ?SystemSettingService $systemSettingService = null
    ) {
        $this->userService = $userService ?? new FrontUserService();
        $this->transactionService = $transactionService ?? new FrontTransactionService();
        $this->systemSettingService = $systemSettingService ?? new SystemSettingService();
    }

    public function account(Request $request): Response
    {
        $guard = $this->ensureAuthenticated('/users/account');
        if ($guard !== null) {
            return $guard;
        }

        $flash = $_SESSION['_front_flash'] ?? null;
        unset($_SESSION['_front_flash']);

        $siteInfo = $this->systemSettingService->information();
        $user = $this->currentUserRow();

        $html = app()->view()->renderWithLayout('front/users/account', [
            'title' => 'Profil Saya',
            'siteInfo' => $siteInfo,
            'user' => $user,
            'flash' => is_array($flash) ? $flash : null,
            'footerText' => '',
            'extraCssFiles' => ['/assets/css/front-user.css'],
        ], 'layouts/app');

        return Response::html($html);
    }

    public function updateAccount(Request $request): Response
    {
        $guard = $this->ensureAuthenticated('/users/account');
        if ($guard !== null) {
            return $guard;
        }

        $current = FrontAuthService::user();
        $userId = (int) ($current['id'] ?? 0);

        try {
            $updated = $this->userService->updateProfile($userId, [
                'name' => (string) $request->input('name', ''),
                'username' => (string) $request->input('username', ''),
                'email' => (string) $request->input('email', ''),
                'phone' => (string) $request->input('phone', ''),
            ]);

            $_SESSION['front_user'] = [
                'id' => (int) ($updated['id'] ?? 0),
                'name' => (string) ($updated['name'] ?? ''),
                'username' => (string) ($updated['username'] ?? ''),
                'email' => (string) ($updated['email'] ?? ''),
                'phone' => (string) ($updated['phone'] ?? ''),
                'roles' => (int) ($updated['roles'] ?? 0),
            ];

            $_SESSION['_front_flash'] = [
                'type' => 'success',
                'message' => 'Profil berhasil diperbarui.',
            ];
        } catch (\Throwable $e) {
            $_SESSION['_front_flash'] = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return Response::redirect('/users/account');
    }

    public function transactions(Request $request): Response
    {
        $guard = $this->ensureAuthenticated('/users/transaction');
        if ($guard !== null) {
            return $guard;
        }

        $siteInfo = $this->systemSettingService->information();
        $current = FrontAuthService::user();
        $userId = (int) ($current['id'] ?? 0);
        $transactions = $this->transactionService->latestByUserId($userId, 100);

        foreach ($transactions as &$row) {
            $row['payment_status'] = $this->transactionService->paymentStatusLabel((int) ($row['status_bayar'] ?? -1));
            $row['file_status'] = $this->transactionService->fileStatusLabel((int) ($row['status_barang'] ?? -1));
            $row['payment_type_label'] = $this->transactionService->paymentTypeLabel((string) ($row['payment_type'] ?? ''));
        }

        $html = app()->view()->renderWithLayout('front/users/transactions', [
            'title' => 'Histori Transaksi',
            'siteInfo' => $siteInfo,
            'transactions' => $transactions,
            'footerText' => '',
            'extraCssFiles' => ['/assets/css/front-user.css'],
        ], 'layouts/app');

        return Response::html($html);
    }

    public function transactionDetail(Request $request, string $midtransId): Response
    {
        $guard = $this->ensureAuthenticated('/users/transaction-details/' . rawurlencode($midtransId));
        if ($guard !== null) {
            return $guard;
        }

        $current = FrontAuthService::user();
        $userId = (int) ($current['id'] ?? 0);
        $transaction = $this->transactionService->findByMidtransIdAndUserId($midtransId, $userId);
        if ($transaction === null) {
            return Response::redirect('/not-found?from=' . rawurlencode($request->uri()));
        }

        $flash = $_SESSION['_front_flash'] ?? null;
        unset($_SESSION['_front_flash']);

        $details = $this->transactionService->detailsByTransactionId((int) ($transaction['id'] ?? 0));
        $paymentStatus = $this->transactionService->paymentStatusLabel((int) ($transaction['status_bayar'] ?? -1));
        $fileStatus = $this->transactionService->fileStatusLabel((int) ($transaction['status_barang'] ?? -1));
        $paymentType = $this->transactionService->paymentTypeLabel((string) ($transaction['payment_type'] ?? ''));
        $siteInfo = $this->systemSettingService->information();

        $html = app()->view()->renderWithLayout('front/users/transaction-detail', [
            'title' => 'Detail Transaksi',
            'siteInfo' => $siteInfo,
            'transaction' => $transaction,
            'details' => $details,
            'flash' => is_array($flash) ? $flash : null,
            'paymentStatus' => $paymentStatus,
            'fileStatus' => $fileStatus,
            'paymentType' => $paymentType,
            'footerText' => '',
            'extraCssFiles' => ['/assets/css/front-user.css'],
        ], 'layouts/app');

        return Response::html($html);
    }

    public function files(Request $request): Response
    {
        $guard = $this->ensureAuthenticated('/users/file');
        if ($guard !== null) {
            return $guard;
        }

        $current = FrontAuthService::user();
        $userId = (int) ($current['id'] ?? 0);
        $files = $this->transactionService->downloadableFilesByUserId($userId);
        $siteInfo = $this->systemSettingService->information();

        foreach ($files as &$row) {
            $row['payment_status'] = $this->transactionService->paymentStatusLabel((int) ($row['status_bayar'] ?? -1));
            $row['file_status'] = $this->transactionService->fileStatusLabel((int) ($row['status_barang'] ?? -1));
        }

        $html = app()->view()->renderWithLayout('front/users/files', [
            'title' => 'File Saya',
            'siteInfo' => $siteInfo,
            'files' => $files,
            'footerText' => '',
            'extraCssFiles' => ['/assets/css/front-user.css'],
        ], 'layouts/app');

        return Response::html($html);
    }

    public function lainnya(Request $request): Response
    {
        $guard = $this->ensureAuthenticated('/users/lainnya');
        if ($guard !== null) {
            return $guard;
        }

        $siteInfo = $this->systemSettingService->information();
        $html = app()->view()->renderWithLayout('front/users/others', [
            'title' => 'Lainnya',
            'siteInfo' => $siteInfo,
            'footerText' => '',
            'extraCssFiles' => ['/assets/css/front-user.css'],
        ], 'layouts/app');

        return Response::html($html);
    }

    private function ensureAuthenticated(string $redirect): ?Response
    {
        if (FrontAuthService::check()) {
            return null;
        }

        $_SESSION['_front_flash'] = [
            'type' => 'error',
            'message' => 'Silakan login atau register terlebih dahulu untuk mengakses area pengguna.',
        ];

        return Response::redirect('/login?redirect=' . rawurlencode($redirect));
    }

    private function currentUserRow(): array
    {
        $current = FrontAuthService::user();
        $userId = (int) ($current['id'] ?? 0);
        $row = $this->userService->findById($userId);

        return is_array($row) ? $row : ($current ?? []);
    }
}
