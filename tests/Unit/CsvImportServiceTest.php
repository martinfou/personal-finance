<?php

namespace Tests\Unit;

use App\Services\CsvImportService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CsvImportServiceTest extends TestCase
{
    public function test_parses_cibc_visa_csv_with_debit_and_credit_columns(): void
    {
        $csv = <<<'CSV'
Date,Description,Debit,Credit
2025-03-01,AMAZON.CA*AB1C2D3,45.67,
2025-03-05,PAYMENT THANK YOU,,500.00
2025-03-10,REFUND MERCHANT,,12.50
CSV;

        $file = UploadedFile::fake()->createWithContent('cibc-visa.csv', $csv);
        $service = new CsvImportService;

        $transactions = $service->parse($file, 'cibc_visa');

        $this->assertCount(3, $transactions);

        $this->assertSame('2025-03-01', $transactions[0]['date']);
        $this->assertSame('AMAZON.CA*AB1C2D3', $transactions[0]['description']);
        $this->assertSame(45.67, $transactions[0]['amount']);
        $this->assertSame('expense', $transactions[0]['type']);

        $this->assertSame('income', $transactions[1]['type']);
        $this->assertSame(500.0, $transactions[1]['amount']);

        $this->assertSame('income', $transactions[2]['type']);
        $this->assertSame(12.5, $transactions[2]['amount']);
    }

    public function test_parses_cibc_visa_csv_with_withdrawal_deposit_headers(): void
    {
        $csv = <<<'CSV'
Date,Description,Withdrawals ($),Deposits ($),Balance ($)
2025/04/10,STARBUCKS STORE 1234,6.75,,1234.56
2025/04/11,ONLINE PAYMENT,,200.00,1034.56
CSV;

        $file = UploadedFile::fake()->createWithContent('cibc-visa-alt.csv', $csv);
        $service = new CsvImportService;

        $transactions = $service->parse($file, 'cibc_visa');

        $this->assertCount(2, $transactions);
        $this->assertSame('2025-04-10', $transactions[0]['date']);
        $this->assertSame('expense', $transactions[0]['type']);
        $this->assertSame(6.75, $transactions[0]['amount']);
        $this->assertSame('income', $transactions[1]['type']);
        $this->assertSame(200.0, $transactions[1]['amount']);
    }
}
