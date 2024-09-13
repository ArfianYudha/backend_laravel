<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up test data before each test.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Buat approver
        $this->postJson('/approvers', ['name' => 'Ana']);
        $this->postJson('/approvers', ['name' => 'Ani']);
        $this->postJson('/approvers', ['name' => 'Ina']);

        // Buat approval stages untuk setiap approver
        $this->postJson('/approval-stages', ['approver_id' => 1]);
        $this->postJson('/approval-stages', ['approver_id' => 2]);
        $this->postJson('/approval-stages', ['approver_id' => 3]);
    }

    /**
     * Test for creating an expense.
     */
    public function test_create_expense()
    {
        $response = $this->postJson('/expense', ['amount' => 1000]);

        $response->assertStatus(201); // Pengeluaran berhasil dibuat
        $this->assertDatabaseHas('expenses', [
            'amount' => 1000,
            'status_id' => 1 // Status awal: menunggu persetujuan
        ]);
    }

    /**
     * Test for approving an expense in the correct order.
     */
    public function test_approve_expense_in_correct_order()
    {
        // Buat pengeluaran
        $this->postJson('/expense', ['amount' => 500]);

        // Approver pertama (Ana) melakukan approve
        $response = $this->patchJson('/expense/1/approve', ['approver_id' => 1]);
        $response->assertStatus(200);

        // Approver kedua (Ani) melakukan approve
        $response = $this->patchJson('/expense/1/approve', ['approver_id' => 2]);
        $response->assertStatus(200);

        // Approver ketiga (Ina) melakukan approve, status pengeluaran harus disetujui
        $response = $this->patchJson('/expense/1/approve', ['approver_id' => 3]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('expenses', [
            'id' => 1,
            'status_id' => 2 // Status: disetujui
        ]);
    }

    /**
     * Test for rejecting approval in the wrong order.
     */
    public function test_cannot_approve_expense_in_wrong_order()
    {
        // Buat pengeluaran
        $this->postJson('/expense', ['amount' => 500]);

        // Approver kedua (Ani) mencoba approve sebelum approver pertama (Ana)
        $response = $this->patchJson('/expense/1/approve', ['approver_id' => 2]);
        $response->assertStatus(403); // Forbidden, karena tahapan tidak sesuai

        // Pastikan pengeluaran tetap menunggu persetujuan
        $this->assertDatabaseHas('expenses', [
            'id' => 1,
            'status_id' => 1 // Status: menunggu persetujuan
        ]);
    }

    /**
     * Test for fetching expense details.
     */
    public function test_fetch_expense_details()
    {
        // Buat pengeluaran
        $this->postJson('/expense', ['amount' => 1500]);

        // Dapatkan detail pengeluaran
        $response = $this->getJson('/expense/1');
        $response->assertStatus(200);

        // Periksa apakah detail pengeluaran sesuai
        $response->assertJson([
            'id' => 1,
            'amount' => 1500,
            'status' => [
                'id' => 1,
                'name' => 'menunggu persetujuan'
            ]
        ]);
    }
}
