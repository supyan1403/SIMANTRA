<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Bidang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaturanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_pengaturan_and_edit_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $operator = User::factory()->create(['role' => 'operator']);

        $response = $this->actingAs($admin)->get(route('pengaturan.index'));
        $response->assertStatus(200);

        $responseEdit = $this->actingAs($admin)->get(route('pengaturan.edit', $operator));
        $responseEdit->assertStatus(200);

        $responseUpdate = $this->actingAs($admin)->put(route('pengaturan.update', $operator), [
            'name' => 'Operator Updated Name',
            'email' => $operator->email,
            'role' => 'operator',
        ]);
        $responseUpdate->assertRedirect(route('pengaturan.index'));
        $this->assertDatabaseHas('users', ['id' => $operator->id, 'name' => 'Operator Updated Name']);
    }

    public function test_admin_can_manage_master_sbml()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Index
        $response = $this->actingAs($admin)->get(route('master-sbml.index'));
        $response->assertStatus(200);

        // 2. Create
        $createResponse = $this->actingAs($admin)->post(route('master-sbml.store'), [
            'tahun' => 2026,
            'nominal_pencacahan' => 4600000,
            'nominal_pengolahan' => 3100000,
            'nominal' => 7700000,
        ]);
        $createResponse->assertRedirect(route('master-sbml.index'));
        $this->assertDatabaseHas('sbml_masters', [
            'tahun' => 2026,
            'nominal_pencacahan' => 4600000,
            'nominal_pengolahan' => 3100000,
            'nominal' => 7700000,
        ]);
    }
}
