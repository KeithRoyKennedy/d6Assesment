<?php

namespace Keith\D6assesment\Models;

use PDO;

class Customer extends Model
{
    protected $table = 'customers';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function updateCustomer($id, $data)
    {
        return $this->update($id, $data);
    }

    public function searchByName($name)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE name LIKE :name");
        $searchTerm = '%' . $name . '%';
        $stmt->bindParam(':name', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
