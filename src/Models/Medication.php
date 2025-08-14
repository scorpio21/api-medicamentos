<?php

namespace App\Models;

class Medication
{
    private ?int $id;
    private string $name;
    private string $description;
    private string $active_ingredient;
    private string $dosage_form;
    private string $strength;
    private string $manufacturer;
    private string $batch_number;
    private \DateTime $expiry_date;
    private int $quantity;
    private string $storage_conditions;
    private \DateTime $created_at;
    private \DateTime $updated_at;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    private function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getActiveIngredient(): string { return $this->active_ingredient; }
    public function getDosageForm(): string { return $this->dosage_form; }
    public function getStrength(): string { return $this->strength; }
    public function getManufacturer(): string { return $this->manufacturer; }
    public function getBatchNumber(): string { return $this->batch_number; }
    public function getExpiryDate(): \DateTime { return $this->expiry_date; }
    public function getQuantity(): int { return $this->quantity; }
    public function getStorageConditions(): string { return $this->storage_conditions; }
    public function getCreatedAt(): \DateTime { return $this->created_at; }
    public function getUpdatedAt(): \DateTime { return $this->updated_at; }

    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    
    public function setName(string $name): void 
    { 
        if (empty(trim($name))) {
            throw new \InvalidArgumentException("El nombre del medicamento no puede estar vacío");
        }
        $this->name = trim($name); 
    }
    
    public function setDescription(string $description): void { $this->description = $description; }
    
    public function setActiveIngredient(string $active_ingredient): void 
    { 
        if (empty(trim($active_ingredient))) {
            throw new \InvalidArgumentException("El ingrediente activo no puede estar vacío");
        }
        $this->active_ingredient = trim($active_ingredient); 
    }
    
    public function setDosageForm(string $dosage_form): void { $this->dosage_form = $dosage_form; }
    public function setStrength(string $strength): void { $this->strength = $strength; }
    public function setManufacturer(string $manufacturer): void { $this->manufacturer = $manufacturer; }
    
    public function setBatchNumber(string $batch_number): void 
    { 
        if (empty(trim($batch_number))) {
            throw new \InvalidArgumentException("El número de lote no puede estar vacío");
        }
        $this->batch_number = trim($batch_number); 
    }
    
    public function setExpiryDate($expiry_date): void 
    { 
        if (is_string($expiry_date)) {
            $expiry_date = new \DateTime($expiry_date);
        }
        if ($expiry_date < new \DateTime()) {
            throw new \InvalidArgumentException("La fecha de vencimiento no puede ser en el pasado");
        }
        $this->expiry_date = $expiry_date; 
    }
    
    public function setQuantity(int $quantity): void 
    { 
        if ($quantity < 0) {
            throw new \InvalidArgumentException("La cantidad no puede ser negativa");
        }
        $this->quantity = $quantity; 
    }
    
    public function setStorageConditions(string $storage_conditions): void { $this->storage_conditions = $storage_conditions; }
    
    public function setCreatedAt($created_at): void 
    { 
        if (is_string($created_at)) {
            $created_at = new \DateTime($created_at);
        }
        $this->created_at = $created_at; 
    }
    
    public function setUpdatedAt($updated_at): void 
    { 
        if (is_string($updated_at)) {
            $updated_at = new \DateTime($updated_at);
        }
        $this->updated_at = $updated_at; 
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'active_ingredient' => $this->active_ingredient,
            'dosage_form' => $this->dosage_form,
            'strength' => $this->strength,
            'manufacturer' => $this->manufacturer,
            'batch_number' => $this->batch_number,
            'expiry_date' => $this->expiry_date->format('Y-m-d'),
            'quantity' => $this->quantity,
            'storage_conditions' => $this->storage_conditions,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }

    public function validate(): bool
    {
        if (empty($this->name) || empty($this->active_ingredient) || empty($this->batch_number)) {
            return false;
        }
        
        if ($this->quantity < 0) {
            return false;
        }
        
        if ($this->expiry_date < new \DateTime()) {
            return false;
        }
        
        return true;
    }
}