<?php

namespace Keith\D6assesment\Models;

use PDO;

class Item extends Model
{
    protected $table = 'items';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function updateItem($id, $data)
    {
        return $this->update($id, $data);
    }

    public function findByCode($code)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE code = :code");
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function searchByDescription($description)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE description LIKE :description");
        $searchTerm = '%' . $description . '%';
        $stmt->bindParam(':description', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
