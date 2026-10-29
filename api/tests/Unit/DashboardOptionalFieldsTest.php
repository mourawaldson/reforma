<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../dashboards/src/Helpers.php';

class DashboardOptionalFieldsTest extends TestCase
{
    public function testSupplierDashboardDisplaysDashForMissingCpfCnpj(): void
    {
        $data = [[], [['cpf_cnpj' => null, 'name' => 'Fornecedor sem documento']]];

        ob_start();
        require __DIR__ . '/../../../dashboards/views/dashboard/suppliers.php';
        $html = ob_get_clean();

        $this->assertStringContainsString('<td>-</td>', $html);
        $this->assertSame('-', \Dashboard\Support\formatCnpjCpf(null));
        $this->assertSame('-', \Dashboard\Support\formatCnpjCpf('   '));
        $this->assertSame('123.456.789-01', \Dashboard\Support\formatCnpjCpf('12345678901'));
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
        $this->assertMatchesRegularExpression('/<td class="text-end">\s*-\s*<\/td>/', $html);
        $this->assertMatchesRegularExpression('/<td class="text-end">\s*R\$ 0,00\s*<\/td>/', $html);
    }
}
