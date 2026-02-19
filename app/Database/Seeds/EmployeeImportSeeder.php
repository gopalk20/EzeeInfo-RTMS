<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Import employees from Excel and replace dummy users.
 * Run: php spark db:seed EmployeeImportSeeder
 *
 * Set EMPLOYEE_IMPORT_FILE in .env or copy Excel to writable/import/employees.xlsx
 * Default path: writable/import/All Employees - EzeeInfo Cloud Solutions.xlsx
 */
class EmployeeImportSeeder extends Seeder
{
    protected string $password = 'Ezeeinfo123!';

    protected array $dummyEmails = [
        'john@example.com',
        'jane@example.com',
        'manager@example.com',
        'finance@example.com',
        'admin@example.com',
    ];

    public function run()
    {
        $path = env('EMPLOYEE_IMPORT_FILE')
            ?: WRITEPATH . 'import/All Employees - EzeeInfo Cloud Solutions.xlsx';

        if (! file_exists($path)) {
            echo "Excel file not found: {$path}\n";
            echo "Copy the Excel file to writable/import/ or set EMPLOYEE_IMPORT_FILE in .env\n";
            return;
        }

        $xlsx = \Shuchkin\SimpleXLSX::parse($path);
        if (! $xlsx) {
            echo 'Parse error: ' . \Shuchkin\SimpleXLSX::parseError() . "\n";
            return;
        }

        $rows = $xlsx->rows();
        if (empty($rows)) {
            echo "Excel is empty\n";
            return;
        }

        $header = array_shift($rows);
        $col = array_flip($header);

        $empNumIdx = $col['Employee Number'] ?? 0;
        $fullNameIdx = $col['Full Name'] ?? 1;
        $emailIdx = $col['Email'] ?? 2;
        $jobTitleIdx = $col['Job Title'] ?? 3;
        $deptIdx = $col['Department'] ?? 4;
        $reportingToIdx = $col['Reporting To'] ?? 5;
        $roleIdx = $col['Role'] ?? 6;

        $this->ensureRolesAndTeams($rows, $deptIdx);

        $this->removeDummyUsers();

        $userModel = model(\App\Models\UserModel::class);
        $roleMap = $this->getRoleMap();
        $teamMap = $this->getTeamMap();
        $nameToId = [];
        $pendingReporting = [];

        foreach ($rows as $r) {
            $email = trim($r[$emailIdx] ?? '');
            if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $fullName = trim($r[$fullNameIdx] ?? '');
            $parts = preg_split('/\s+/', $fullName, 2);
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';

            $empNum = trim($r[$empNumIdx] ?? '') ?: null;
            $dept = trim($r[$deptIdx] ?? '');
            $reportingToName = trim($r[$reportingToIdx] ?? '');
            $roleName = trim($r[$roleIdx] ?? 'Employee');

            $roleId = $roleMap[$this->normalizeRole($roleName)] ?? $roleMap['Employee'];
            $teamId = $teamMap[$dept] ?? $teamMap['Default'] ?? 1;

            $username = $empNum ?: substr($email, 0, strpos($email, '@'));
            $name = trim($firstName . ' ' . $lastName) ?: $email;

            $userModel->skipValidation(true);
            $existing = $this->db->table('users')->where('email', $email)->get()->getRowArray();
            $userId = null;

            if ($existing) {
                $userId = (int) $existing['id'];
                $userModel->update($userId, [
                    'name'       => $name,
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'username'   => $username,
                    'password'   => $this->password,
                    'role_id'    => $roleId,
                    'team_id'    => $teamId,
                    'is_active'  => 1,
                ]);
            } else {
                $userModel->insert([
                    'name'       => $name,
                    'email'      => $email,
                    'password'   => $this->password,
                    'username'   => $username,
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'role_id'    => $roleId,
                    'team_id'    => $teamId,
                    'is_active'  => 1,
                ]);
                $userId = (int) $this->db->insertID();
            }

            $nameToId[$this->normalizeName($fullName)] = $userId;
            if (! empty($reportingToName)) {
                $pendingReporting[] = ['user_id' => $userId, 'reporting_to' => $reportingToName];
            }
        }

        foreach ($pendingReporting as $p) {
            $reportingId = $nameToId[$this->normalizeName($p['reporting_to'])] ?? null;
            if ($reportingId && $reportingId !== $p['user_id']) {
                $this->db->table('users')->where('id', $p['user_id'])->update([
                    'reporting_manager_id' => $reportingId,
                    'updated_at'          => date('Y-m-d H:i:s'),
                ]);
            }
        }

        echo "Employee import complete. All passwords set to Ezeeinfo123!\n";
    }

    protected function normalizeRole(string $r): string
    {
        $r = strtolower(trim($r));
        if (str_contains($r, 'superadmin') || str_contains($r, 'super admin')) {
            return 'Super Admin';
        }
        if (str_contains($r, 'admin')) {
            return 'Super Admin';
        }
        if (str_contains($r, 'lead')) {
            return 'Product Lead';
        }
        if (str_contains($r, 'finance')) {
            return 'Finance';
        }
        if (str_contains($r, 'manager')) {
            return 'Manager';
        }
        return 'Employee';
    }

    protected function normalizeName(string $n): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($n)));
    }

    protected function getRoleMap(): array
    {
        $rows = $this->db->table('roles')->get()->getResultArray();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['name']] = (int) $r['id'];
        }
        return $map;
    }

    protected function getTeamMap(): array
    {
        $rows = $this->db->table('teams')->get()->getResultArray();
        $map = ['Default' => 1];
        foreach ($rows as $r) {
            $map[$r['name']] = (int) $r['id'];
        }
        return $map;
    }

    protected function ensureRolesAndTeams(array $rows, int $deptIdx): void
    {
        $this->call(RoleSeeder::class);

        $depts = ['Default'];
        foreach ($rows as $r) {
            $d = trim($r[$deptIdx] ?? '');
            if ($d && ! in_array($d, $depts, true)) {
                $depts[] = $d;
            }
        }

        foreach ($depts as $name) {
            if ($name === 'Default') {
                continue;
            }
            $exists = $this->db->table('teams')->where('name', $name)->get()->getRow();
            if (! $exists) {
                $this->db->table('teams')->insert([
                    'name'       => $name,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if (! $this->db->table('teams')->where('name', 'Default')->get()->getRow()) {
            $this->db->table('teams')->insert([
                'name'       => 'Default',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    protected function removeDummyUsers(): void
    {
        foreach ($this->dummyEmails as $email) {
            $user = $this->db->table('users')->where('email', $email)->get()->getRowArray();
            if ($user) {
                $id = (int) $user['id'];
                $this->db->table('users')->where('reporting_manager_id', $id)->update(['reporting_manager_id' => null]);
                $this->db->table('users')->where('id', $id)->delete();
            }
        }
    }
}
