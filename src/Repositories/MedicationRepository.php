<?php

namespace App\Repositories;

use App\Database\Database;
use App\Models\Medication;
use PDO;

class MedicationRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM medications ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->query($sql, [':limit' => $limit, ':offset' => $offset]);
        
        $medications = [];
        while ($row = $stmt->fetch()) {
            $medications[] = new Medication($row);
        }
        
        return $medications;
    }

    public function findById(int $id): ?Medication
    {
        $sql = "SELECT * FROM medications WHERE id = :id";
        $stmt = $this->db->query($sql, [':id' => $id]);
        $row = $stmt->fetch();
        
        return $row ? new Medication($row) : null;
    }

    public function findByName(string $name): array
    {
        $sql = "SELECT * FROM medications WHERE name LIKE :name ORDER BY name ASC";
        $stmt = $this->db->query($sql, [':name' => "%{$name}%"]);
        
        $medications = [];
        while ($row = $stmt->fetch()) {
            $medications[] = new Medication($row);
        }
        
        return $medications;
    }

    public function findByActiveIngredient(string $ingredient): array
    {
        $sql = "SELECT * FROM medications WHERE active_ingredient LIKE :ingredient ORDER BY name ASC";
        $stmt = $this->db->query($sql, [':ingredient' => "%{$ingredient}%"]);
        
        $medications = [];
        while ($row = $stmt->fetch()) {
            $medications[] = new Medication($row);
        }
        
        return $medications;
    }

    public function findExpiringSoon(int $days = 30): array
    {
        $sql = "SELECT * FROM medications WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY) ORDER BY expiry_date ASC";
        $stmt = $this->db->query($sql, [':days' => $days]);
        
        $medications = [];
        while ($row = $stmt->fetch()) {
            $medications[] = new Medication($row);
        }
        
        return $medications;
    }

    public function findLowStock(int $threshold = 10): array
    {
        $sql = "SELECT * FROM medications WHERE quantity <= :threshold ORDER BY quantity ASC";
        $stmt = $this->db->query($sql, [':threshold' => $threshold]);
        
        $medications = [];
        while ($row = $stmt->fetch()) {
            $medications[] = new Medication($row);
        }
        
        return $medications;
    }

    public function create(Medication $medication): int
    {
        $sql = "INSERT INTO medications (
            name, description, active_ingredient, dosage_form, strength, 
            manufacturer, batch_number, expiry_date, quantity, storage_conditions, 
            created_at, updated_at
        ) VALUES (
            :name, :description, :active_ingredient, :dosage_form, :strength,
            :manufacturer, :batch_number, :expiry_date, :quantity, :storage_conditions,
            NOW(), NOW()
        )";

        $params = [
            ':name' => $medication->getName(),
            ':description' => $medication->getDescription(),
            ':active_ingredient' => $medication->getActiveIngredient(),
            ':dosage_form' => $medication->getDosageForm(),
            ':strength' => $medication->getStrength(),
            ':manufacturer' => $medication->getManufacturer(),
            ':batch_number' => $medication->getBatchNumber(),
            ':expiry_date' => $medication->getExpiryDate()->format('Y-m-d'),
            ':quantity' => $medication->getQuantity(),
            ':storage_conditions' => $medication->getStorageConditions()
        ];

        $this->db->query($sql, $params);
        return (int) $this->db->lastInsertId();
    }

    public function update(Medication $medication): bool
    {
        $sql = "UPDATE medications SET 
            name = :name, description = :description, active_ingredient = :active_ingredient,
            dosage_form = :dosage_form, strength = :strength, manufacturer = :manufacturer,
            batch_number = :batch_number, expiry_date = :expiry_date, quantity = :quantity,
            storage_conditions = :storage_conditions, updated_at = NOW()
            WHERE id = :id";

        $params = [
            ':id' => $medication->getId(),
            ':name' => $medication->getName(),
            ':description' => $medication->getDescription(),
            ':active_ingredient' => $medication->getActiveIngredient(),
            ':dosage_form' => $medication->getDosageForm(),
            ':strength' => $medication->getStrength(),
            ':manufacturer' => $medication->getManufacturer(),
            ':batch_number' => $medication->getBatchNumber(),
            ':expiry_date' => $medication->getExpiryDate()->format('Y-m-d'),
            ':quantity' => $medication->getQuantity(),
            ':storage_conditions' => $medication->getStorageConditions()
        ];

        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM medications WHERE id = :id";
        $stmt = $this->db->query($sql, [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function updateQuantity(int $id, int $quantity): bool
    {
        $sql = "UPDATE medications SET quantity = :quantity, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->query($sql, [':id' => $id, ':quantity' => $quantity]);
        return $stmt->rowCount() > 0;
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) as total FROM medications";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();
        return (int) $row['total'];
    }

    public function search(string $term): array
    {
        $sql = "SELECT * FROM medications 
                WHERE name LIKE :term 
                OR active_ingredient LIKE :term 
                OR manufacturer LIKE :term 
                OR batch_number LIKE :term
                ORDER BY name ASC";
        
        $stmt = $this->db->query($sql, [':term' => "%{$term}%"]);
        
        $medications = [];
        while ($row = $stmt->fetch()) {
            $medications[] = new Medication($row);
        }
        
        return $medications;
    }
}