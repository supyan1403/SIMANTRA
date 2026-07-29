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
}
