<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class CsvImportService
{
    const BANKS = [
        'desjardins' => [
            'name' => 'Desjardins',
            'delimiter' => ',',
            'date_column' => 0,
            'description_column' => 1,
            'debit_column' => 3,
            'credit_column' => 4,
            'has_header' => true,
            'date_format' => 'Y-m-d',
            'description' => 'Date,No de chèque,Description,Retrait,Dépôt,Solde',
        ],
        'rbc' => [
            'name' => 'RBC',
            'delimiter' => ',',
            'date_column' => 0,
            'description_column' => 1,
            'debit_column' => null,
            'credit_column' => null,
            'amount_column' => 2,
            'has_header' => true,
            'date_format' => 'Y-m-d',
            'description' => 'Date,Description,Amount',
        ],
        'td' => [
            'name' => 'TD Canada Trust',
            'delimiter' => ',',
            'date_column' => 0,
            'description_column' => 1,
            'debit_column' => 2,
            'credit_column' => 3,
            'has_header' => true,
            'date_format' => 'Y/m/d',
            'description' => 'Date,Description,Debit,Credit',
        ],
        'scotia' => [
            'name' => 'Banque Scotia',
            'delimiter' => ',',
            'date_column' => 0,
            'description_column' => 1,
            'debit_column' => null,
            'credit_column' => null,
            'amount_column' => 2,
            'has_header' => true,
            'date_format' => 'Y-m-d',
            'description' => 'Transaction Date,Description,Amount (negatives as debits)',
        ],
        'tangerine' => [
            'name' => 'Tangerine',
            'delimiter' => ',',
            'date_column' => 0,
            'description_column' => 2,
            'debit_column' => 3,
            'credit_column' => 4,
            'has_header' => true,
            'date_format' => 'Y-m-d',
            'description' => 'Date,Transaction ID,Description,Debit,Credit',
        ],
        'cibc_visa' => [
            'name' => 'CIBC Visa (carte de crédit)',
            'delimiter' => ',',
            'date_column' => 0,
            'description_column' => 1,
            'debit_column' => 2,
            'credit_column' => 3,
            'has_header' => true,
            'auto_detect_columns' => true,
            'date_format' => 'Y-m-d',
            'description' => 'Date,Description,Debit,Credit (achats = débit, paiements = crédit)',
        ],
        'generic' => [
            'name' => 'Générique',
            'delimiter' => ',',
            'has_header' => true,
            'description' => 'Format libre avec mappage manuel des colonnes',
        ],
    ];

    public function parse(UploadedFile $file, string $bank, ?array $columnMap = null): array
    {
        $config = self::BANKS[$bank] ?? self::BANKS['generic'];
        $rows = $this->readCsv($file, $config['delimiter'] ?? ',');
        $transactions = [];

        if (empty($rows)) {
            throw new \Exception('Fichier CSV vide ou invalide.');
        }

        $startIndex = ($config['has_header'] ?? true) ? 1 : 0;
        $header = $rows[0] ?? [];

        if ($bank === 'generic' && $columnMap) {
            return $this->parseGeneric($rows, $columnMap, $startIndex, $header);
        }

        if (! empty($config['auto_detect_columns']) && ! empty($header)) {
            $config = $this->resolveColumnsFromHeader($header, $config);
        }

        return $this->parseBankFormat($rows, $config, $startIndex);
    }

    public function preview(UploadedFile $file, string $bank): array
    {
        $config = self::BANKS[$bank] ?? self::BANKS['generic'];
        $rows = $this->readCsv($file, $config['delimiter'] ?? ',');
        $header = !empty($rows) ? $rows[0] : [];

        $data = [];
        for ($i = 0; $i < min(5, count($rows)); $i++) {
            $row = $rows[$i];
            $data[] = array_combine(
                array_map(fn($k) => "Colonne $k", array_keys($row)),
                $row
            );
        }

        return [
            'headers' => $header,
            'preview' => $data,
            'total_rows' => count($rows) - (($config['has_header'] ?? true) ? 1 : 0),
            'bank' => $bank,
        ];
    }

    public function store(int $userId, array $transactions): array
    {
        $stored = 0;
        $errors = [];

        // Get user's categories keyed by name
        $categories = Category::where('user_id', $userId)->get()->keyBy(fn($c) => strtolower($c->name));
        $defaultExpense = Category::where('user_id', $userId)->where('type', 'expense')->first();
        $defaultIncome = Category::where('user_id', $userId)->where('type', 'income')->first();

        foreach ($transactions as $tx) {
            $tx['user_id'] = $userId;
            $tx['category_id'] = $tx['category_id'] ?? $defaultExpense->id;

            $validator = Validator::make($tx, [
                'user_id' => 'required|exists:users,id',
                'category_id' => 'required|exists:categories,id',
                'amount' => 'required|numeric|min:0.01',
                'type' => 'required|in:income,expense',
                'description' => 'nullable|string|max:255',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                $errors[] = ['row' => $tx, 'errors' => $validator->errors()->all()];
                continue;
            }

            \App\Models\Transaction::create($validator->validated());
            $stored++;
        }

        return ['stored' => $stored, 'errors' => $errors];
    }

    private function readCsv(UploadedFile $file, string $delimiter): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = array_map('trim', $row);
        }
        fclose($handle);
        return $rows;
    }

    private function parseBankFormat(array $rows, array $config, int $startIndex): array
    {
        $transactions = [];
        for ($i = $startIndex; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter($row))) {
                continue;
            }

            $date = $row[$config['date_column']] ?? '';
            $description = $row[$config['description_column']] ?? '';
            $amount = null;
            $type = 'expense';

            if (isset($config['amount_column'])) {
                $raw = $this->parseAmount($row[$config['amount_column']] ?? '0');
                $amount = abs($raw);
                $type = $raw >= 0 ? 'income' : 'expense';
            } elseif (isset($config['debit_column']) && isset($config['credit_column'])) {
                $debit = $this->parseAmount($row[$config['debit_column']] ?? '0');
                $credit = $this->parseAmount($row[$config['credit_column']] ?? '0');
                if ($credit > 0) {
                    $amount = $credit;
                    $type = 'income';
                } elseif ($debit > 0) {
                    $amount = $debit;
                    $type = 'expense';
                }
            }

            if ($amount && $amount > 0 && $this->isValidDate($date)) {
                $transactions[] = [
                    'date' => $this->normalizeDate($date),
                    'description' => $description,
                    'amount' => $amount,
                    'type' => $type,
                ];
            }
        }

        return $transactions;
    }

    private function resolveColumnsFromHeader(array $header, array $config): array
    {
        $normalized = array_map(fn ($h) => strtolower(trim($h)), $header);

        $find = function (array $patterns) use ($normalized): ?int {
            foreach ($patterns as $pattern) {
                foreach ($normalized as $index => $column) {
                    if (str_contains($column, $pattern)) {
                        return $index;
                    }
                }
            }

            return null;
        };

        return array_merge($config, array_filter([
            'date_column' => $find(['date']),
            'description_column' => $find(['description', 'descr', 'details', 'merchant']),
            'debit_column' => $find(['debit', 'withdrawal', 'withdrawals', 'charge', 'retrait']),
            'credit_column' => $find(['credit', 'deposit', 'deposits', 'dépôt', 'depot']),
        ], fn ($value) => $value !== null));
    }

    private function parseAmount(string $value): float
    {
        $cleaned = str_replace(['$', ',', ' '], '', trim($value));
        if ($cleaned === '' || $cleaned === '-') {
            return 0.0;
        }

        if (preg_match('/^\((.+)\)$/', $cleaned, $matches)) {
            return -1 * (float) $matches[1];
        }

        return (float) $cleaned;
    }

    private function isValidDate(string $date): bool
    {
        if ($date === '' || strtolower($date) === 'date') {
            return false;
        }

        return strtotime($this->normalizeDate($date)) !== false;
    }

    private function normalizeDate(string $date): string
    {
        $date = trim($date);

        foreach (['Y-m-d', 'Y/m/d', 'm/d/Y', 'd/m/Y', 'M j, Y'] as $format) {
            $parsed = \DateTime::createFromFormat($format, $date);
            if ($parsed && $parsed->format($format) === $date) {
                return $parsed->format('Y-m-d');
            }
        }

        $timestamp = strtotime($date);

        return $timestamp ? date('Y-m-d', $timestamp) : $date;
    }

    private function parseGeneric(array $rows, array $columnMap, int $startIndex, array $header): array
    {
        $transactions = [];
        for ($i = $startIndex; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter($row))) continue;

            $date = $row[$columnMap['date'] ?? 0] ?? '';
            $description = $row[$columnMap['description'] ?? 1] ?? '';
            $rawAmount = str_replace(['$', ','], '', $row[$columnMap['amount'] ?? 2] ?? '0');
            $amount = abs((float) $rawAmount);
            $type = $columnMap['type'] ?? ((float) $rawAmount >= 0 ? 'income' : 'expense');

            if ($amount > 0) {
                $transactions[] = compact('date', 'description', 'amount', 'type');
            }
        }
        return $transactions;
    }
}
