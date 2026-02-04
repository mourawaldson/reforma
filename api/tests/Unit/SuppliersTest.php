<?php
declare(strict_types=1);

namespace Tests\Unit;

use Supplier;
use Tests\DatabaseTestCase;

require_once __DIR__ . '/../../src/Models/Supplier.php';

class SuppliersTest extends DatabaseTestCase
{
    public function testCreateSupplier(): void
    {
        $id = Supplier::create([
            'name' => 'Teste Supplier',
            'company_name' => 'Teste LTDA',
            'cpf_cnpj' => '12345678000199'
        ]);

        $this->assertGreaterThan(0, $id);

        $row = Supplier::find($id);
        $this->assertNotNull($row);
        $this->assertEquals('Teste Supplier', $row['display_name']); // Alias in query
        $this->assertEquals('12345678000199', $row['cpf_cnpj']);
    }

    public function testUpdateSupplier(): void
    {
        $id = Supplier::create([
            'name' => 'Old Name',
            'company_name' => null,
            'cpf_cnpj' => null
        ]);

        $updated = Supplier::update($id, [
            'name' => 'New Name',
            'company_name' => 'New Company',
            'cpf_cnpj' => '11122233344'
        ]);

        $this->assertTrue($updated);

        $row = Supplier::find($id);
        $this->assertEquals('New Name', $row['display_name']);
        $this->assertEquals('11122233344', $row['cpf_cnpj']);
    }

    public function testDeleteSupplier(): void
    {
        $id = Supplier::create([
            'name' => 'To Delete',
            'company_name' => null,
            'cpf_cnpj' => null
        ]);

        $deleted = Supplier::delete($id);
        $this->assertTrue($deleted);

        $row = Supplier::find($id);
        $this->assertNull($row);
    }
}
