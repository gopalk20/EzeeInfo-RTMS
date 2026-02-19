<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps   = true;
    protected $allowedFields   = ['name', 'start_date', 'end_date', 'max_allowed_hours', 'github_repo_url', 'product_lead_id', 'is_disabled'];

    public function getMembers(int $productId): array
    {
        $db = $this->db;
        return $db->table('product_members')
            ->select('product_members.*, users.id as user_id, users.email, users.first_name, users.last_name')
            ->join('users', 'users.id = product_members.user_id')
            ->where('product_members.product_id', $productId)
            ->get()
            ->getResultArray();
    }

    public function addMember(int $productId, int $userId, string $role = 'member'): bool
    {
        return $this->db->table('product_members')->insert([
            'product_id'      => $productId,
            'user_id'         => $userId,
            'role_in_product' => $role,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    public function removeMember(int $productId, int $userId): bool
    {
        return $this->db->table('product_members')
            ->where(['product_id' => $productId, 'user_id' => $userId])
            ->delete();
    }

    public function isMember(int $productId, int $userId): bool
    {
        $row = $this->db->table('product_members')
            ->where(['product_id' => $productId, 'user_id' => $userId])
            ->get()
            ->getRow();
        return $row !== null;
    }

    public function getProductsForUser(int $userId): array
    {
        $asMember = $this->db->table('product_members')
            ->select('product_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();
        $memberIds = array_column($asMember, 'product_id');

        $asLead = $this->where('product_lead_id', $userId)->groupStart()->where('is_disabled', null)->orWhere('is_disabled', 0)->groupEnd()->findAll();
        $leadIds = array_column($asLead, 'id');

        $allIds = array_unique(array_merge($memberIds, $leadIds));
        if (empty($allIds)) {
            return [];
        }
        return $this->whereIn('id', $allIds)->groupStart()->where('is_disabled', null)->orWhere('is_disabled', 0)->groupEnd()->findAll();
    }
}
