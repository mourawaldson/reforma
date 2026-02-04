<?php
declare(strict_types=1);

namespace Tests\Unit;

use Expense;
use Supplier;
use Tag;
use Tests\DatabaseTestCase;

require_once __DIR__ . '/../../src/Models/Expense.php';
require_once __DIR__ . '/../../src/Models/Supplier.php';
require_once __DIR__ . '/../../src/Models/Tag.php';

class ExpensesTest extends DatabaseTestCase
{
    public function testCreateExpense(): void
    {
        $supplierId = Supplier::create([
            'name' => 'Sup Test',
            'company_name' => null,
            'cpf_cnpj' => null
        ]);

        $tagId = Tag::create(['name' => 'Tag Test']);

        $data = [
            'supplier_id' => $supplierId,
            'date' => '2025-10-10',
            'description' => 'Test Expense',
            'amount_nf' => 100.50,
            'amount_paid' => 100.50,
            'additional_discount' => 0,
            'calendar_year' => 2025,
            'is_confirmed' => 1
        ];

        $id = Expense::create($data, [$tagId]);
        $this->assertGreaterThan(0, $id);

        $found = Expense::find($id);
        $this->assertNotNull($found);
        $this->assertEquals('Test Expense', $found['description']);
        $this->assertEquals(100.50, (float) $found['amount_paid']);

        // Verify Tags
        $this->assertCount(1, $found['tags']);
        $this->assertEquals('Tag Test', $found['tags'][0]['name']);
    }

    public function testUpdateExpense(): void
    {
        $id = Expense::create([
            'supplier_id' => null,
            'date' => '2025-01-01',
            'description' => 'Original',
            'amount_nf' => 0,
            'amount_paid' => 50,
            'additional_discount' => 0,
            'calendar_year' => 2025,
            'is_confirmed' => 1
        ], []);

        Expense::update($id, [
            'supplier_id' => null,
            'date' => '2025-01-02',
            'description' => 'Updated',
            'amount_nf' => 0,
            'amount_paid' => 75,
            'additional_discount' => 0,
            'calendar_year' => 2025
        ], []);

        $found = Expense::find($id);
        $this->assertEquals('Updated', $found['description']);
        $this->assertEquals(75, (float) $found['amount_paid']);
    }

    public function testConfirmExpense(): void
    {
        $id = Expense::create([
            'supplier_id' => null,
            'date' => '2025-01-01',
            'description' => 'Pending',
            'amount_nf' => 0,
            'amount_paid' => 50,
            'additional_discount' => 0,
            'calendar_year' => 2025,
            'is_confirmed' => 0 // Pending
        ], []);

        Expense::confirm($id);

        $found = Expense::find($id);
        $this->assertEquals(1, $found['is_confirmed']);
    }

    public function testDeleteExpense(): void
    {
        $id = Expense::create([
            'supplier_id' => null,
            'date' => '2025-01-01',
            'description' => 'To Delete',
            'amount_nf' => 0,
            'amount_paid' => 50,
            'additional_discount' => 0,
            'calendar_year' => 2025,
            'is_confirmed' => 1
        ], []);

        Expense::delete($id);

        $found = Expense::find($id);
        $this->assertNull($found);
    }
}
