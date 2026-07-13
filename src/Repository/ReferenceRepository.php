<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class ReferenceRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return list<array<string,mixed>> */
    public function listVisible(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, title, industry, description, outcome, focus_lines, url, link_label, sort_order, is_visible
             FROM references_portfolio
             WHERE is_visible = 1
             ORDER BY sort_order ASC, id ASC'
        );

        return array_map([$this, 'hydrateReference'], $stmt->fetchAll());
    }

    /** @return list<array<string,mixed>> */
    public function listAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, title, industry, description, outcome, focus_lines, url, link_label, sort_order, is_visible
             FROM references_portfolio
             ORDER BY sort_order ASC, id ASC'
        );

        return array_map([$this, 'hydrateReference'], $stmt->fetchAll());
    }

    /** @return array<string,mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, title, industry, description, outcome, focus_lines, url, link_label, sort_order, is_visible
             FROM references_portfolio
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();
        return is_array($row) ? $this->hydrateReference($row) : null;
    }

    public function save(array $payload, ?int $id = null): int
    {
        if ($id === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO references_portfolio (title, industry, description, outcome, focus_lines, url, link_label, sort_order, is_visible, created_at, updated_at)
                 VALUES (:title, :industry, :description, :outcome, :focus_lines, :url, :link_label, :sort_order, :is_visible, NOW(), NOW())'
            );
            $stmt->execute($this->bind($payload));
            return (int) $this->pdo->lastInsertId();
        }

        $stmt = $this->pdo->prepare(
            'UPDATE references_portfolio
             SET title = :title,
                 industry = :industry,
                 description = :description,
                 outcome = :outcome,
                 focus_lines = :focus_lines,
                 url = :url,
                 link_label = :link_label,
                 sort_order = :sort_order,
                 is_visible = :is_visible,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $data = $this->bind($payload);
        $data[':id'] = $id;
        $stmt->execute($data);

        return $id;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM references_portfolio WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /** @param array<string,mixed> $row @return array<string,mixed> */
    private function hydrateReference(array $row): array
    {
        $focusLines = preg_split('/\r\n|\r|\n/', (string) ($row['focus_lines'] ?? '')) ?: [];
        $focus = [];
        foreach ($focusLines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $focus[] = $line;
            }
        }

        return [
            'id' => (int) ($row['id'] ?? 0),
            'title' => (string) ($row['title'] ?? ''),
            'industry' => (string) ($row['industry'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'outcome' => (string) ($row['outcome'] ?? ''),
            'focus' => $focus,
            'focus_lines' => implode("\n", $focus),
            'url' => (string) ($row['url'] ?? ''),
            'linkLabel' => (string) ($row['link_label'] ?? ''),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'is_visible' => (bool) ($row['is_visible'] ?? false),
        ];
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    private function bind(array $payload): array
    {
        return [
            ':title' => trim((string) ($payload['title'] ?? '')),
            ':industry' => trim((string) ($payload['industry'] ?? '')),
            ':description' => trim((string) ($payload['description'] ?? '')),
            ':outcome' => trim((string) ($payload['outcome'] ?? '')),
            ':focus_lines' => trim((string) ($payload['focus_lines'] ?? '')),
            ':url' => trim((string) ($payload['url'] ?? '')),
            ':link_label' => trim((string) ($payload['link_label'] ?? '')),
            ':sort_order' => (int) ($payload['sort_order'] ?? 0),
            ':is_visible' => !empty($payload['is_visible']) ? 1 : 0,
        ];
    }
}
