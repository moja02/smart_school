<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\Fee;

class FeeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'student']);
    }

    public function test_api_fee_creation()
    {
        $school = School::create(['name' => 'Fee School']);
        $admin = User::factory()->create(['role' => 'admin', 'school_id' => $school->id]);
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)
                         ->withHeaders(['Accept' => 'application/json'])
                         ->postJson(route('admin.fees.store'), [
                             'title' => 'Tuition 2026',
                             'amount' => 500,
                             'due_date' => '2026-09-01'
                         ]);

        $this->assertDatabaseHas('fees', [
            'title' => 'Tuition 2026',
            'amount' => 500
        ]);
    }
}
