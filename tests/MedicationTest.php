<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Medication;

class MedicationTest extends TestCase
{
    public function testMedicationCreation()
    {
        $data = [
            'name' => 'Test Medication',
            'description' => 'Test Description',
            'active_ingredient' => 'Test Ingredient',
            'dosage_form' => 'Tablet',
            'strength' => '100mg',
            'manufacturer' => 'Test Manufacturer',
            'batch_number' => 'TEST001',
            'expiry_date' => '2025-12-31',
            'quantity' => 100,
            'storage_conditions' => 'Store in cool place'
        ];

        $medication = new Medication($data);

        $this->assertEquals('Test Medication', $medication->getName());
        $this->assertEquals('Test Ingredient', $medication->getActiveIngredient());
        $this->assertEquals('TEST001', $medication->getBatchNumber());
        $this->assertEquals(100, $medication->getQuantity());
    }

    public function testMedicationValidation()
    {
        $data = [
            'name' => 'Valid Medication',
            'active_ingredient' => 'Valid Ingredient',
            'batch_number' => 'VALID001',
            'expiry_date' => '2025-12-31',
            'quantity' => 50
        ];

        $medication = new Medication($data);
        $this->assertTrue($medication->validate());
    }

    public function testInvalidMedicationName()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $data = [
            'name' => '', // Nombre vacío
            'active_ingredient' => 'Valid Ingredient',
            'batch_number' => 'VALID001',
            'expiry_date' => '2025-12-31',
            'quantity' => 50
        ];

        new Medication($data);
    }

    public function testInvalidQuantity()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $data = [
            'name' => 'Valid Medication',
            'active_ingredient' => 'Valid Ingredient',
            'batch_number' => 'VALID001',
            'expiry_date' => '2025-12-31',
            'quantity' => -10 // Cantidad negativa
        ];

        new Medication($data);
    }

    public function testInvalidExpiryDate()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $data = [
            'name' => 'Valid Medication',
            'active_ingredient' => 'Valid Ingredient',
            'batch_number' => 'VALID001',
            'expiry_date' => '2020-01-01', // Fecha pasada
            'quantity' => 50
        ];

        new Medication($data);
    }

    public function testMedicationToArray()
    {
        $data = [
            'name' => 'Test Medication',
            'description' => 'Test Description',
            'active_ingredient' => 'Test Ingredient',
            'dosage_form' => 'Tablet',
            'strength' => '100mg',
            'manufacturer' => 'Test Manufacturer',
            'batch_number' => 'TEST001',
            'expiry_date' => '2025-12-31',
            'quantity' => 100,
            'storage_conditions' => 'Store in cool place'
        ];

        $medication = new Medication($data);
        $array = $medication->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Test Medication', $array['name']);
        $this->assertEquals('Test Ingredient', $array['active_ingredient']);
        $this->assertEquals('TEST001', $array['batch_number']);
        $this->assertEquals(100, $array['quantity']);
    }
}