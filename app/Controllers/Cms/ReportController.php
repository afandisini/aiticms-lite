<?php

declare(strict_types=1);

namespace App\Controllers\Cms;

use App\Services\AuthService;
use App\Services\Cms\ReportService;
use System\Http\Request;
use System\Http\Response;

class ReportController
{
    public function __construct(private ?ReportService $service = null)
    {
        $this->service = $service ?? new ReportService();
    }

    public function index(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $periods = $this->service->periods();
        $period = $this->resolvePeriod($request, $periods);
        $overview = $this->service->overview($period);

        $html = app()->view()->renderWithLayout('cms/reports/index', [
            'title' => 'Laporan',
            'user' => AuthService::user(),
            'overview' => $overview,
            'period' => $period,
            'periodLabel' => $this->periodLabel($period),
            'periods' => $periods,
            'missingTables' => $this->service->missingTables(),
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function ledger(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $q = trim((string) $request->input('q', ''));
        $rows = $this->service->latestLedgers(500, $q);

        $html = app()->view()->renderWithLayout('cms/reports/ledger', [
            'title' => 'Ledger',
            'user' => AuthService::user(),
            'rows' => $rows,
            'search' => $q,
            'missingTables' => $this->service->missingTables(),
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function createLedger(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $jenis = ((int) $request->input('jenis', 1)) === 2 ? 2 : 1;
        $html = app()->view()->renderWithLayout('cms/reports/ledger_form', [
            'title' => 'Tambah Ledger',
            'user' => AuthService::user(),
            'formTitle' => 'Tambah Ledger',
            'action' => '/cms/reports/ledger/store',
            'item' => null,
            'suggestedCode' => $this->service->nextLedgerCode($jenis),
            'defaultJenis' => $jenis,
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function storeLedger(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        try {
            $this->service->createLedger($request->all());
            $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Ledger berhasil ditambahkan.'];
            return Response::redirect('/cms/reports/ledger');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
            return Response::redirect('/cms/reports/ledger/create');
        }
    }

    public function editLedger(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $item = $this->service->findLedgerById((int) $id);
        if ($item === null) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => 'Data ledger tidak ditemukan.'];
            return Response::redirect('/cms/reports/ledger');
        }

        $html = app()->view()->renderWithLayout('cms/reports/ledger_form', [
            'title' => 'Edit Ledger',
            'user' => AuthService::user(),
            'formTitle' => 'Edit Ledger',
            'action' => '/cms/reports/ledger/update/' . (int) $id,
            'item' => $item,
            'suggestedCode' => (string) ($item['no_ledger'] ?? ''),
            'defaultJenis' => (int) ($item['jenis'] ?? 1),
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function updateLedger(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        try {
            $this->service->updateLedger((int) $id, $request->all());
            $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Ledger berhasil diperbarui.'];
            return Response::redirect('/cms/reports/ledger');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
            return Response::redirect('/cms/reports/ledger/edit/' . (int) $id);
        }
    }

    public function deleteLedger(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $this->service->softDeleteLedger((int) $id);
        $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Ledger berhasil dihapus.'];
        return Response::redirect('/cms/reports/ledger');
    }

    public function finance(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $periods = $this->service->periods();
        $period = $this->resolvePeriod($request, $periods);
        $q = trim((string) $request->input('q', ''));
        $ledger = trim((string) $request->input('no_ledger', ''));

        $rows = $this->service->latestFinance(700, $q, $ledger, $period);
        $totals = $this->service->financeTotals($ledger, $period);
        $ledgerOptions = $this->service->ledgerOptions();

        $html = app()->view()->renderWithLayout('cms/reports/finance', [
            'title' => 'Keuangan',
            'user' => AuthService::user(),
            'rows' => $rows,
            'search' => $q,
            'period' => $period,
            'periodLabel' => $this->periodLabel($period),
            'periods' => $periods,
            'selectedLedger' => $ledger,
            'ledgerOptions' => $ledgerOptions,
            'totals' => $totals,
            'missingTables' => $this->service->missingTables(),
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function createFinance(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $html = app()->view()->renderWithLayout('cms/reports/finance_form', [
            'title' => 'Tambah Keuangan',
            'user' => AuthService::user(),
            'formTitle' => 'Tambah Keuangan',
            'action' => '/cms/reports/finance/store',
            'item' => null,
            'ledgerOptions' => $this->service->ledgerOptions(),
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function storeFinance(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $user = AuthService::user();
        $userId = (int) ($user['id'] ?? 0);

        try {
            $this->service->createFinance($request->all(), $userId);
            $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Data keuangan berhasil ditambahkan.'];
            return Response::redirect('/cms/reports/finance');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
            return Response::redirect('/cms/reports/finance/create');
        }
    }

    public function editFinance(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $item = $this->service->findFinanceById((int) $id);
        if ($item === null) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => 'Data keuangan tidak ditemukan.'];
            return Response::redirect('/cms/reports/finance');
        }

        $html = app()->view()->renderWithLayout('cms/reports/finance_form', [
            'title' => 'Edit Keuangan',
            'user' => AuthService::user(),
            'formTitle' => 'Edit Keuangan',
            'action' => '/cms/reports/finance/update/' . (int) $id,
            'item' => $item,
            'ledgerOptions' => $this->service->ledgerOptions(),
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    public function updateFinance(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $user = AuthService::user();
        $userId = (int) ($user['id'] ?? 0);

        try {
            $this->service->updateFinance((int) $id, $request->all(), $userId);
            $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Data keuangan berhasil diperbarui.'];
            return Response::redirect('/cms/reports/finance');
        } catch (\Throwable $e) {
            $_SESSION['_cms_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
            return Response::redirect('/cms/reports/finance/edit/' . (int) $id);
        }
    }

    public function deleteFinance(Request $request, string $id): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $this->service->deleteFinance((int) $id);
        $_SESSION['_cms_flash'] = ['type' => 'success', 'message' => 'Data keuangan berhasil dihapus.'];
        return Response::redirect('/cms/reports/finance');
    }

    public function cashFlow(Request $request): Response
    {
        if (!AuthService::check()) {
            return Response::redirect('/cms/login');
        }

        $periods = $this->service->periods();
        $period = $this->resolvePeriod($request, $periods);
        $cashflow = $this->service->cashFlow($period);

        $html = app()->view()->renderWithLayout('cms/reports/cashflow', [
            'title' => 'Report Cash Flow',
            'user' => AuthService::user(),
            'cashflow' => $cashflow,
            'period' => $period,
            'periodLabel' => $this->periodLabel($period),
            'periods' => $periods,
            'missingTables' => $this->service->missingTables(),
        ], 'cms/layouts/app');

        return Response::html($html);
    }

    /**
     * @param array<int, string> $periods
     */
    private function resolvePeriod(Request $request, array $periods): string
    {
        $period = trim((string) $request->input('periode', ''));
        $y = trim((string) $request->input('y', ''));
        $m = trim((string) $request->input('m', ''));

        if ($period === '' && preg_match('/^\d{4}$/', $y) === 1 && preg_match('/^\d{1,2}$/', $m) === 1) {
            $period = $y . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
        }

        if ($this->service->isValidPeriod($period)) {
            return $period;
        }

        return (string) ($periods[0] ?? date('Y-m'));
    }

    private function periodLabel(string $period): string
    {
        if (!$this->service->isValidPeriod($period)) {
            return $period;
        }

        $month = (int) substr($period, 5, 2);
        $year = (int) substr($period, 0, 4);
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return ($months[$month] ?? $period) . ' ' . $year;
    }
}
