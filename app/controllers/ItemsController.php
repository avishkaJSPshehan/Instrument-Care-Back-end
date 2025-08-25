<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use PDO;

final class ItemsController
{
    private PDO $pdo;

    public function __construct(private Database $db)
    {
        $this->pdo = $db->pdo();
    }

    // GET /api/items
    public function index(Request $req): void
    {
        $stmt = $this->pdo->query('SELECT id, name, price, created_at, updated_at FROM items ORDER BY id DESC');
        $rows = $stmt->fetchAll();
        Response::json($rows);
    }

    // GET /api/items/{id}
    public function show(Request $req, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $stmt = $this->pdo->prepare('SELECT id, name, price, created_at, updated_at FROM items WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            Response::json(['error' => 'Item not found'], 404);
            return;
        }
        Response::json($row);
    }

    // POST /api/items
    public function store(Request $req): void
    {
        $data = $req->json();

        $missing = require_fields($data, ['name', 'price']);
        if ($missing) {
            Response::json(['error' => 'Missing fields', 'fields' => $missing], 422);
            return;
        }

        $name = trim((string)$data['name']);
        $price = (float)$data['price'];

        $stmt = $this->pdo->prepare('INSERT INTO items (name, price, created_at, updated_at) VALUES (?, ?, NOW(), NOW())');
        $stmt->execute([$name, $price]);

        $id = (int)$this->pdo->lastInsertId();
        Response::json(['message' => 'Created', 'id' => $id], 201);
    }

    // PUT/PATCH /api/items/{id}
    public function update(Request $req, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            Response::json(['error' => 'Invalid id'], 400);
            return;
        }

        $data = $req->json();
        $fields = [];
        $values = [];

        if (array_key_exists('name', $data)) {
            $fields[] = 'name = ?';
            $values[] = trim((string)$data['name']);
        }
        if (array_key_exists('price', $data)) {
            $fields[] = 'price = ?';
            $values[] = (float)$data['price'];
        }

        if (!$fields) {
            Response::json(['error' => 'No updatable fields provided'], 400);
            return;
        }

        $values[] = $id;
        $sql = 'UPDATE items SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);

        Response::json(['message' => 'Updated']);
    }

    // DELETE /api/items/{id}
    public function destroy(Request $req, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $stmt = $this->pdo->prepare('DELETE FROM items WHERE id = ?');
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            Response::json(['error' => 'Item not found or already deleted'], 404);
            return;
        }
        Response::json(['message' => 'Deleted']);
    }
}
