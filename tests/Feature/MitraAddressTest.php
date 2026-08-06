<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Mitra;
use App\Models\Kecamatan;
use App\Models\Desa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MitraAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_address_separation_and_lookup(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $kec = Kecamatan::create(['kode_kec' => '010', 'nama' => 'CIPATUJAH']);
        $desa = Desa::create([
            'kecamatan_id' => $kec->id,
            'kode_desa' => '001',
            'kode_full' => '3206010001',
            'nama' => 'CIHERAS'
        ]);

        // 1. Create Mitra via POST with separated address fields & kabupaten_kota
        $response = $this->post(route('mitra.store'), [
            'nama' => 'Mitra BPS Test',
            'id_sobat' => '320699001',
            'no_hp' => '08123456789',
            'kabupaten_kota' => 'Kabupaten Tasikmalaya',
            'kecamatan' => 'CIPATUJAH',
            'desa' => 'CIHERAS',
            'alamat_detail' => 'Kp. Ciheras RT 01 RW 02',
            'pekerjaan' => 'Mitra Statistik',
            'jk' => 'L',
        ]);

        $response->assertRedirect(route('mitra.index'));
        $this->assertDatabaseHas('mitras', [
            'nama' => 'Mitra BPS Test',
            'kabupaten_kota' => 'Kabupaten Tasikmalaya',
            'kecamatan' => 'CIPATUJAH',
            'desa' => 'CIHERAS',
            'alamat_detail' => 'Kp. Ciheras RT 01 RW 02',
            'kode_alamat' => '3206010001'
        ]);

        $mitra = Mitra::where('id_sobat', '320699001')->first();
        $this->assertEquals('Kp. Ciheras RT 01 RW 02, Desa CIHERAS, Kec. CIPATUJAH, Kabupaten Tasikmalaya', $mitra->alamat_clean);

        // 2. Test AJAX route for Desas by Kecamatan
        $ajaxResponse = $this->get(route('mitra.desas', 'CIPATUJAH'));
        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertJsonFragment(['nama' => 'CIHERAS']);

        // 3. Update Mitra
        $updateResponse = $this->put(route('mitra.update', $mitra), [
            'nama' => 'Mitra BPS Test Updated',
            'kabupaten_kota' => 'Kota Tasikmalaya',
            'kecamatan' => 'CIPATUJAH',
            'desa' => 'CIHERAS',
            'alamat_detail' => 'Jl. Baru No. 45',
            'pekerjaan' => 'Pencacah',
            'jk' => 'L',
        ]);

        $updateResponse->assertRedirect(route('mitra.index'));
        $this->assertDatabaseHas('mitras', [
            'id' => $mitra->id,
            'kabupaten_kota' => 'Kota Tasikmalaya',
            'alamat_detail' => 'Jl. Baru No. 45',
        ]);
    }
}
