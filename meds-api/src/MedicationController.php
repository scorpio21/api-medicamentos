<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class MedicationController {
	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}

	public function listMedications(): void {
		$query = 'SELECT id, name, dosage, stock, notes, created_at, updated_at FROM medications ORDER BY name ASC';
		$rows = $this->db->query($query)->fetchAll();
		sendJson(200, ['data' => $rows]);
	}

	public function getMedication(string $id): void {
		$stmt = $this->db->prepare('SELECT id, name, dosage, stock, notes, created_at, updated_at FROM medications WHERE id = :id');
		$stmt->execute([':id' => (int)$id]);
		$med = $stmt->fetch();
		if (!$med) {
			sendJson(404, ['error' => 'Medication not found']);
			return;
		}
		sendJson(200, ['data' => $med]);
	}

	public function createMedication(): void {
		$body = readJsonBody();
		$name = trim((string)($body['name'] ?? ''));
		$dosage = isset($body['dosage']) ? trim((string)$body['dosage']) : null;
		$stock = isset($body['stock']) ? (int)$body['stock'] : 0;
		$notes = isset($body['notes']) ? trim((string)$body['notes']) : null;

		if ($name === '') {
			sendJson(422, ['error' => 'Field "name" is required']);
			return;
		}

		$now = (new \DateTimeImmutable('now'))->format(DATE_ATOM);
		$stmt = $this->db->prepare('INSERT INTO medications(name, dosage, stock, notes, created_at, updated_at) VALUES(:name, :dosage, :stock, :notes, :created_at, :updated_at)');
		try {
			$stmt->execute([
				':name' => $name,
				':dosage' => $dosage,
				':stock' => $stock,
				':notes' => $notes,
				':created_at' => $now,
				':updated_at' => $now,
			]);
			$id = (int)$this->db->lastInsertId();
			$this->getMedication((string)$id);
		} catch (PDOException $e) {
			sendJson(500, ['error' => 'Failed to create medication', 'details' => $e->getMessage()]);
		}
	}

	public function updateMedication(string $id): void {
		$body = readJsonBody();
		$fields = [];
		$params = [':id' => (int)$id, ':updated_at' => (new \DateTimeImmutable('now'))->format(DATE_ATOM)];

		if (array_key_exists('name', $body)) {
			$fields[] = 'name = :name';
			$params[':name'] = trim((string)$body['name']);
		}
		if (array_key_exists('dosage', $body)) {
			$fields[] = 'dosage = :dosage';
			$params[':dosage'] = $body['dosage'] !== null ? trim((string)$body['dosage']) : null;
		}
		if (array_key_exists('stock', $body)) {
			$fields[] = 'stock = :stock';
			$params[':stock'] = (int)$body['stock'];
		}
		if (array_key_exists('notes', $body)) {
			$fields[] = 'notes = :notes';
			$params[':notes'] = $body['notes'] !== null ? trim((string)$body['notes']) : null;
		}

		if (empty($fields)) {
			sendJson(400, ['error' => 'No fields to update']);
			return;
		}

		$setClause = implode(', ', $fields) . ', updated_at = :updated_at';
		$stmt = $this->db->prepare("UPDATE medications SET $setClause WHERE id = :id");

		try {
			$stmt->execute($params);
			if ($stmt->rowCount() === 0) {
				sendJson(404, ['error' => 'Medication not found']);
				return;
			}
			$this->getMedication($id);
		} catch (PDOException $e) {
			sendJson(500, ['error' => 'Failed to update medication', 'details' => $e->getMessage()]);
		}
	}

	public function deleteMedication(string $id): void {
		$stmt = $this->db->prepare('DELETE FROM medications WHERE id = :id');
		try {
			$stmt->execute([':id' => (int)$id]);
			if ($stmt->rowCount() === 0) {
				sendJson(404, ['error' => 'Medication not found']);
				return;
			}
			sendJson(200, ['message' => 'Medication deleted']);
		} catch (PDOException $e) {
			sendJson(500, ['error' => 'Failed to delete medication', 'details' => $e->getMessage()]);
		}
	}

	public function adjustStock(string $id): void {
		$body = readJsonBody();
		$delta = (int)($body['delta'] ?? 0);
		if ($delta === 0) {
			sendJson(422, ['error' => 'Field "delta" must be non-zero']);
			return;
		}

		try {
			$this->db->beginTransaction();
			$row = $this->db->prepare('SELECT stock FROM medications WHERE id = :id');
			$row->execute([':id' => (int)$id]);
			$current = $row->fetchColumn();
			if ($current === false) {
				$this->db->rollBack();
				sendJson(404, ['error' => 'Medication not found']);
				return;
			}
			$newStock = (int)$current + $delta;
			if ($newStock < 0) {
				$this->db->rollBack();
				sendJson(422, ['error' => 'Stock cannot be negative']);
				return;
			}
			$upd = $this->db->prepare('UPDATE medications SET stock = :stock, updated_at = :updated_at WHERE id = :id');
			$upd->execute([
				':stock' => $newStock,
				':updated_at' => (new \DateTimeImmutable('now'))->format(DATE_ATOM),
				':id' => (int)$id,
			]);
			$this->db->commit();
			$this->getMedication($id);
		} catch (PDOException $e) {
			if ($this->db->inTransaction()) {
				$this->db->rollBack();
			}
			sendJson(500, ['error' => 'Failed to adjust stock', 'details' => $e->getMessage()]);
		}
	}
}