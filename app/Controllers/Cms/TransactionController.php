<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\TransactionService;
use System\Http\Request;
use System\Http\Response;

class TransactionController
{
    public function __construct(private ?TransactionService $service = null)
    {
        $this->service = $service ?? new TransactionService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $q = trim((string) $request->input('q', ''));
        $rows = $this->service->latest(200, $q);

        foreach ($rows as &$row) {
            $row['payment_status'] = $this->service->paymentStatusLabel((int) ($row['status_bayar'] ?? -1));
            $row['file_status'] = $this->service->fileStatusLabel((int) ($row['status_barang'] ?? -1));
            $row['payment_type_label'] = $this->service->paymentTypeLabel((string) ($row['payment_type'] ?? ''));
        }

        $html = app()->view()->renderWithLayout('cms/transactions/index', [
            'title' => 'Transaksi',
            'user' => AuthService::user(),
            'transactions' => $rows,
            'search' => $q,
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function detail(Request $request, string $midtransId): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $transaction = $this->service->findByMidtransId($midtransId);
        if ($transaction === null) {
            $_SESSION['_cms_flash'] = [
                'type' => 'error',
                'message' => 'Detail transaksi tidak ditemukan.',
            ];
            return Response::redirect('/cms/transactions');
        }

        $details = $this->service->detailsByTransactionId((int) ($transaction['id'] ?? 0));
        $paymentStatus = $this->service->paymentStatusLabel((int) ($transaction['status_bayar'] ?? -1));
        $fileStatus = $this->service->fileStatusLabel((int) ($transaction['status_barang'] ?? -1));
        $paymentType = $this->service->paymentTypeLabel((string) ($transaction['payment_type'] ?? ''));

        $html = app()->view()->renderWithLayout('cms/transactions/detail', [
            'title' => 'Detail Transaksi',
            'user' => AuthService::user(),
            'transaction' => $transaction,
            'details' => $details,
            'paymentStatus' => $paymentStatus,
            'fileStatus' => $fileStatus,
            'paymentType' => $paymentType,
        ], 'cms/layouts/app');

        return Response::html($html);
    }
}

