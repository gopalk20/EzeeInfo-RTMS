<?php

namespace App\Models;

use CodeIgniter\Model;

class ResourceCostModel extends Model
{
    protected $table            = 'resource_costs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps   = true;
    protected $allowedFields   = ['user_id', 'monthly_cost', 'effective_from', 'effective_to'];

    public function getForUser(int $userId, ?string $date = null): ?array
    {
        $row = $this->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->first();
        return $row ?: null;
    }

    public function getAllWithUsers(): array
    {
        $rows = $this->db->table('resource_costs')
            ->select('resource_costs.*, users.email, users.first_name, users.last_name')
            ->join('users', 'users.id = resource_costs.user_id')
            ->orderBy('users.email')
            ->get()
            ->getResultArray();
        return $rows;
    }
}
