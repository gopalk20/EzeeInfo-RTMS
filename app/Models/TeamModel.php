<?php

namespace App\Models;

use CodeIgniter\Model;

class TeamModel extends Model
{
    protected $table            = 'teams';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = ['name'];

    public function getAllForSelect(): array
    {
        $rows = $this->orderBy('name')->findAll();
        return array_column($rows, 'name', 'id');
    }
}
