<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['name', 'email', 'password', 'username', 'first_name', 'last_name', 'phone', 'role_id', 'team_id', 'reporting_manager_id', 'is_active'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'name'       => 'permit_empty|min_length[3]|max_length[255]',
        'email'      => 'required|valid_email',
        'password'   => 'required|min_length[8]',
        'username'   => 'permit_empty|max_length[64]',
        'first_name' => 'permit_empty|max_length[128]',
        'last_name'  => 'permit_empty|max_length[128]',
        'phone'      => 'permit_empty|max_length[32]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];
    protected $afterInsert    = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
        }

        return $data;
    }

    /**
     * Get role for a user
     */
    public function getRole(?array $user): ?array
    {
        if (!$user || empty($user['role_id'])) {
            return null;
        }
        $roleModel = new RoleModel();
        return $roleModel->find($user['role_id']);
    }

    /**
     * Get team for a user
     */
    public function getTeam(?array $user): ?array
    {
        if (!$user || empty($user['team_id'])) {
            return null;
        }
        $teamModel = new TeamModel();
        return $teamModel->find($user['team_id']);
    }

    /**
     * Get display name (first_name last_name or name or email)
     */
    public function getDisplayName(array $user): string
    {
        if (!empty($user['first_name']) || !empty($user['last_name'])) {
            return trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        }
        if (!empty($user['name'])) {
            return $user['name'];
        }
        return $user['email'] ?? '';
    }
}
