<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DashboardOptionalFieldsTest extends TestCase
{
    public function testSupplierDashboardDisplaysDashForMissingCpfCnpj(): void
    {
        $data = [[], [['cpf_cnpj' => null, 'name' => 'Fornecedor sem documento']]];

        ob_start();
        require __DIR__ . '/../../../dashboards/views/dashboard/suppliers.php';
        $html = ob_get_clean();

        $this->assertStringContainsString('<td>-</td>', $html);
        $this->assertSame('-', \formatCnpjCpf(null));
        $this->assertSame('-', \formatCnpjCpf('   '));
        $this->assertSame('123.456.789-01', \formatCnpjCpf('12345678901'));
    }

    public function testExpenseDashboardDisplaysDashForMissingOptionalValuesAndPreservesZero(): void
    {
        $data = [
            'years' => [],
            'pending' => ['summary' => ['count' => 0, 'total_paid' => 0], 'years' => []],
            'discounts' => [
                'items' => [[
                    'date' => null,
                    'supplier_name' => null,
                    'description' => null,
                    'amount_paid' => null,
                    'additional_discount' => 0,
                    'vet' => null,
                ]],
                'totals' => ['amount_paid' => 0, 'additional_discount' => 0, 'vet' => 0],
            ],
        ];

        ob_start();
        require __DIR__ . '/../../../dashboards/views/dashboard/expenses.php';
        $html = ob_get_clean();

        $this->assertStringContainsString('<td>-</td>', $html);
        $this->assertStringContainsString('<td class="text-end">-</td>', $html);
        $this->assertStringContainsString('<td class="text-end">R$ 0,00</td>', $html);
    }
}
