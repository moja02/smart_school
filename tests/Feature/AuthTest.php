<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create basic roles needed for testing
        $roles = ['super_admin', 'manager', 'admin', 'teacher', 'student', 'parent'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'role' => 'student'
        ]);
        $user->assignRole('student');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('student.dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_banned_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'is_banned' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email' => 'عذراً، هذا الحساب محظور من قبل الإدارة. يرجى مراجعة المدرسة.']);
    }

    public function test_super_admin_redirected_to_system_schools(): void
    {
        $user = cloneAndAssignRole('super_admin');
        $response = $this->actingAs($user)->post('/login', ['email' => $user->email, 'password' => 'password']);
        $response->assertRedirect(route('system.schools.index'));
    }

    public function test_manager_redirected_to_manager_dashboard(): void
    {
        $user = cloneAndAssignRole('manager');
        $response = $this->actingAs($user)->post('/login', ['email' => $user->email, 'password' => 'password']);
        $response->assertRedirect(route('manager.dashboard'));
    }

    public function test_admin_redirected_to_admin_dashboard(): void
    {
        $user = cloneAndAssignRole('admin');
        $response = $this->actingAs($user)->post('/login', ['email' => $user->email, 'password' => 'password']);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_teacher_redirected_to_teacher_dashboard(): void
    {
        $user = cloneAndAssignRole('teacher');
        $response = $this->actingAs($user)->post('/login', ['email' => $user->email, 'password' => 'password']);
        $response->assertRedirect(route('teacher.dashboard'));
    }

    public function test_parent_redirected_to_parent_dashboard(): void
    {
        $user = cloneAndAssignRole('parent');
        $response = $this->actingAs($user)->post('/login', ['email' => $user->email, 'password' => 'password']);
        $response->assertRedirect(route('parent.dashboard'));
    }
}

function cloneAndAssignRole($roleName) {
    // We use a valid enum value for the 'role' column to avoid sqlite enum constraint errors
    $dbRole = in_array($roleName, ['admin', 'teacher', 'student', 'parent']) ? $roleName : 'admin';
    
    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'role' => $dbRole
    ]);
    $user->assignRole($roleName);
    return $user;
}
