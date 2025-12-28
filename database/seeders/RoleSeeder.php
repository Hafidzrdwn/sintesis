<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates the three main roles for SINTESIS:
     * - admin: Full system access
     * - mentor: Manage interns, tasks, logbooks
     * - intern: Submit attendance, tasks, logbooks
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Applicant management
            'view applicants',
            'create applicants',
            'edit applicants',
            'delete applicants',
            'review applicants',
            
            // Internship management
            'view internships',
            'create internships',
            'edit internships',
            'delete internships',
            
            // Presence/Attendance
            'view presences',
            'create presences',
            'edit presences',
            'delete presences',
            'validate presences',
            
            // Task management
            'view tasks',
            'create tasks',
            'edit tasks',
            'delete tasks',
            'assign tasks',
            
            // Logbook management
            'view logbooks',
            'create logbooks',
            'edit logbooks',
            'delete logbooks',
            'approve logbooks',
            
            // Audit logs
            'view audit logs',
            'export audit logs',
            
            // Dashboard access
            'access admin dashboard',
            'access mentor dashboard',
            'access intern dashboard',
            
            // Analytics/SIE
            'view analytics',
            'export reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Admin role with all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        // Create Mentor role
        $mentorRole = Role::firstOrCreate(['name' => 'mentor', 'guard_name' => 'web']);
        $mentorRole->givePermissionTo([
            'view users',
            'view applicants',
            'view internships',
            'view presences',
            'validate presences',
            'view tasks',
            'create tasks',
            'edit tasks',
            'assign tasks',
            'view logbooks',
            'approve logbooks',
            'access mentor dashboard',
            'view analytics',
        ]);

        // Create Intern role
        $internRole = Role::firstOrCreate(['name' => 'intern', 'guard_name' => 'web']);
        $internRole->givePermissionTo([
            'view presences',
            'create presences',
            'view tasks',
            'edit tasks', // Can update task status
            'view logbooks',
            'create logbooks',
            'edit logbooks',
            'access intern dashboard',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
