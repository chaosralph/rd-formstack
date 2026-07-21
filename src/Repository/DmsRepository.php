<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class DmsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function countDocuments(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM dms_documents')->fetchColumn();
    }

    public function countDocumentsByStatus(string $status): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM dms_documents WHERE status = :status');
        $stmt->execute([':status' => trim($status)]);

        return (int) $stmt->fetchColumn();
    }

    /** @return list<array<string,mixed>> */
    public function listDocuments(string $search = '', string $status = 'all'): array
    {
        $sql = 'SELECT d.id, d.user_id, d.title, d.category, d.summary, d.status,
                       d.current_version_id, d.approved_at, d.approved_by_user_id,
                       d.created_at, d.updated_at,
                       creator.display_name AS user_display_name,
                       approver.display_name AS approved_by_display_name,
                       current_version.version_number AS current_version_number,
                       current_version.original_filename AS current_original_filename,
                       current_version.mime_type AS current_mime_type,
                       current_version.file_size AS current_file_size,
                       current_version.change_note AS current_change_note,
                       current_version.created_at AS current_version_created_at,
                       uploader.display_name AS current_uploaded_by_display_name,
                       (SELECT COUNT(*) FROM dms_document_versions v WHERE v.document_id = d.id) AS version_count,
                       (SELECT COUNT(*) FROM dms_document_events e WHERE e.document_id = d.id) AS event_count
                FROM dms_documents d
                INNER JOIN users creator ON creator.id = d.user_id
                LEFT JOIN users approver ON approver.id = d.approved_by_user_id
                LEFT JOIN dms_document_versions current_version ON current_version.id = d.current_version_id
                LEFT JOIN users uploader ON uploader.id = current_version.uploaded_by_user_id';

        $conditions = [];
        $params = [];

        $search = trim($search);
        if ($search !== '') {
            $conditions[] = '(d.title LIKE :search OR d.category LIKE :search OR d.summary LIKE :search OR current_version.original_filename LIKE :search OR current_version.change_note LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        if ($status !== 'all') {
            $conditions[] = 'd.status = :status';
            $params[':status'] = trim($status);
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY d.updated_at DESC, d.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return array_map([$this, 'hydrateDocument'], $stmt->fetchAll());
    }

    /** @return array<string,mixed>|null */
    public function findDocumentById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT d.id, d.user_id, d.title, d.category, d.summary, d.status,
                    d.current_version_id, d.approved_at, d.approved_by_user_id,
                    d.created_at, d.updated_at,
                    creator.display_name AS user_display_name,
                    approver.display_name AS approved_by_display_name,
                    current_version.version_number AS current_version_number,
                    current_version.original_filename AS current_original_filename,
                    current_version.mime_type AS current_mime_type,
                    current_version.file_size AS current_file_size,
                    current_version.change_note AS current_change_note,
                    current_version.created_at AS current_version_created_at,
                    uploader.display_name AS current_uploaded_by_display_name,
                    (SELECT COUNT(*) FROM dms_document_versions v WHERE v.document_id = d.id) AS version_count,
                    (SELECT COUNT(*) FROM dms_document_events e WHERE e.document_id = d.id) AS event_count
             FROM dms_documents d
             INNER JOIN users creator ON creator.id = d.user_id
             LEFT JOIN users approver ON approver.id = d.approved_by_user_id
             LEFT JOIN dms_document_versions current_version ON current_version.id = d.current_version_id
             LEFT JOIN users uploader ON uploader.id = current_version.uploaded_by_user_id
             WHERE d.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return is_array($row) ? $this->hydrateDocument($row) : null;
    }

    public function createDocument(int $userId, string $title, string $category, string $summary): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO dms_documents (user_id, title, category, summary, status, current_version_id, approved_at, approved_by_user_id, created_at, updated_at)
             VALUES (:user_id, :title, :category, :summary, :status, NULL, NULL, NULL, NOW(), NOW())'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':title' => trim($title),
            ':category' => trim($category),
            ':summary' => trim($summary),
            ':status' => 'draft',
        ]);

        $documentId = (int) $this->pdo->lastInsertId();
        $this->recordEvent($documentId, $userId, 'document_created', 'Dokument angelegt');

        return $documentId;
    }

    public function updateDocumentMeta(int $documentId, string $title, string $category, string $summary, int $actorUserId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE dms_documents
             SET title = :title,
                 category = :category,
                 summary = :summary,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $documentId,
            ':title' => trim($title),
            ':category' => trim($category),
            ':summary' => trim($summary),
        ]);

        $this->recordEvent($documentId, $actorUserId, 'document_updated', 'Metadaten aktualisiert');
    }

    public function addVersion(
        int $documentId,
        int $actorUserId,
        string $originalFilename,
        string $mimeType,
        int $fileSize,
        string $binaryContent,
        string $changeNote
    ): int {
        $versionStmt = $this->pdo->prepare('SELECT COALESCE(MAX(version_number), 0) + 1 FROM dms_document_versions WHERE document_id = :document_id');
        $versionStmt->execute([':document_id' => $documentId]);
        $versionNumber = (int) $versionStmt->fetchColumn();

        $insert = $this->pdo->prepare(
            'INSERT INTO dms_document_versions (document_id, version_number, original_filename, mime_type, file_size, binary_content, change_note, uploaded_by_user_id, created_at)
             VALUES (:document_id, :version_number, :original_filename, :mime_type, :file_size, :binary_content, :change_note, :uploaded_by_user_id, NOW())'
        );
        $insert->bindValue(':document_id', $documentId, PDO::PARAM_INT);
        $insert->bindValue(':version_number', $versionNumber, PDO::PARAM_INT);
        $insert->bindValue(':original_filename', trim($originalFilename));
        $insert->bindValue(':mime_type', trim($mimeType) !== '' ? trim($mimeType) : 'application/octet-stream');
        $insert->bindValue(':file_size', $fileSize, PDO::PARAM_INT);
        $insert->bindValue(':binary_content', $binaryContent, PDO::PARAM_LOB);
        $insert->bindValue(':change_note', trim($changeNote) !== '' ? trim($changeNote) : null);
        $insert->bindValue(':uploaded_by_user_id', $actorUserId, PDO::PARAM_INT);
        $insert->execute();

        $versionId = (int) $this->pdo->lastInsertId();

        $update = $this->pdo->prepare(
            'UPDATE dms_documents
             SET current_version_id = :current_version_id,
                 status = :status,
                 approved_at = NULL,
                 approved_by_user_id = NULL,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $update->execute([
            ':id' => $documentId,
            ':current_version_id' => $versionId,
            ':status' => 'draft',
        ]);

        $this->recordEvent($documentId, $actorUserId, 'version_uploaded', 'Neue Version hochgeladen', [
            'version_id' => $versionId,
            'version_number' => $versionNumber,
            'original_filename' => trim($originalFilename),
            'file_size' => $fileSize,
        ]);

        return $versionId;
    }

    public function submitForApproval(int $documentId, int $actorUserId, string $reviewNote = ''): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE dms_documents
             SET status = :status,
                 approved_at = NULL,
                 approved_by_user_id = NULL,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $documentId,
            ':status' => 'in_review',
        ]);

        $details = [];
        $reviewNote = trim($reviewNote);
        if ($reviewNote !== '') {
            $details['review_note'] = $reviewNote;
        }

        $this->recordEvent($documentId, $actorUserId, 'submitted_for_approval', 'Dokument zur Freigabe eingereicht', $details);
    }

    public function approveDocument(int $documentId, int $actorUserId, string $reviewNote = ''): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE dms_documents
             SET status = :status,
                 approved_at = NOW(),
                 approved_by_user_id = :approved_by_user_id,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $documentId,
            ':status' => 'approved',
            ':approved_by_user_id' => $actorUserId,
        ]);

        $details = [];
        $reviewNote = trim($reviewNote);
        if ($reviewNote !== '') {
            $details['review_note'] = $reviewNote;
        }

        $this->recordEvent($documentId, $actorUserId, 'document_approved', 'Dokument freigegeben', $details);
    }

    public function resetToDraft(int $documentId, int $actorUserId, string $reviewNote = ''): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE dms_documents
             SET status = :status,
                 approved_at = NULL,
                 approved_by_user_id = NULL,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $documentId,
            ':status' => 'draft',
        ]);

        $details = [];
        $reviewNote = trim($reviewNote);
        if ($reviewNote !== '') {
            $details['review_note'] = $reviewNote;
        }

        $this->recordEvent($documentId, $actorUserId, 'reset_to_draft', 'Dokument zur Überarbeitung auf Draft zurückgesetzt', $details);
    }

    /** @return list<array<string,mixed>> */
    public function listVersions(int $documentId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT v.id, v.document_id, v.version_number, v.original_filename, v.mime_type, v.file_size,
                    v.change_note, v.uploaded_by_user_id, v.created_at,
                    uploader.display_name AS uploaded_by_display_name
             FROM dms_document_versions v
             LEFT JOIN users uploader ON uploader.id = v.uploaded_by_user_id
             WHERE v.document_id = :document_id
             ORDER BY v.version_number DESC, v.id DESC'
        );
        $stmt->execute([':document_id' => $documentId]);

        return array_map([$this, 'hydrateVersion'], $stmt->fetchAll());
    }

    /** @return list<array<string,mixed>> */
    public function listEvents(int $documentId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.id, e.document_id, e.user_id, e.event_type, e.summary, e.details_json, e.created_at,
                    u.display_name AS user_display_name
             FROM dms_document_events e
             LEFT JOIN users u ON u.id = e.user_id
             WHERE e.document_id = :document_id
             ORDER BY e.created_at DESC, e.id DESC'
        );
        $stmt->execute([':document_id' => $documentId]);

        return array_map([$this, 'hydrateEvent'], $stmt->fetchAll());
    }

    /** @return array<string,mixed>|null */
    public function findVersionBinaryById(int $versionId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT v.id, v.document_id, v.version_number, v.original_filename, v.mime_type, v.file_size,
                    v.binary_content, v.created_at, d.title, d.status
             FROM dms_document_versions v
             INNER JOIN dms_documents d ON d.id = v.document_id
             WHERE v.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $versionId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        return [
            'id' => (int) ($row['id'] ?? 0),
            'document_id' => (int) ($row['document_id'] ?? 0),
            'version_number' => (int) ($row['version_number'] ?? 0),
            'original_filename' => (string) ($row['original_filename'] ?? ''),
            'mime_type' => (string) ($row['mime_type'] ?? 'application/octet-stream'),
            'file_size' => (int) ($row['file_size'] ?? 0),
            'binary_content' => is_string($row['binary_content'] ?? null) ? $row['binary_content'] : '',
            'title' => (string) ($row['title'] ?? ''),
            'status' => (string) ($row['status'] ?? 'draft'),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }

    private function recordEvent(int $documentId, int $actorUserId, string $eventType, string $summary, array $details = []): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO dms_document_events (document_id, user_id, event_type, summary, details_json, created_at)
             VALUES (:document_id, :user_id, :event_type, :summary, :details_json, NOW())'
        );
        $stmt->execute([
            ':document_id' => $documentId,
            ':user_id' => $actorUserId > 0 ? $actorUserId : null,
            ':event_type' => trim($eventType),
            ':summary' => trim($summary),
            ':details_json' => $details !== [] ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);
    }

    /** @param array<string,mixed> $row @return array<string,mixed> */
    private function hydrateDocument(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'user_id' => (int) ($row['user_id'] ?? 0),
            'title' => (string) ($row['title'] ?? ''),
            'category' => (string) ($row['category'] ?? ''),
            'summary' => (string) ($row['summary'] ?? ''),
            'status' => (string) ($row['status'] ?? 'draft'),
            'current_version_id' => (int) ($row['current_version_id'] ?? 0),
            'approved_at' => (string) ($row['approved_at'] ?? ''),
            'approved_by_user_id' => (int) ($row['approved_by_user_id'] ?? 0),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
            'user_display_name' => (string) ($row['user_display_name'] ?? ''),
            'approved_by_display_name' => (string) ($row['approved_by_display_name'] ?? ''),
            'current_version_number' => (int) ($row['current_version_number'] ?? 0),
            'current_original_filename' => (string) ($row['current_original_filename'] ?? ''),
            'current_mime_type' => (string) ($row['current_mime_type'] ?? ''),
            'current_file_size' => (int) ($row['current_file_size'] ?? 0),
            'current_change_note' => (string) ($row['current_change_note'] ?? ''),
            'current_version_created_at' => (string) ($row['current_version_created_at'] ?? ''),
            'current_uploaded_by_display_name' => (string) ($row['current_uploaded_by_display_name'] ?? ''),
            'version_count' => (int) ($row['version_count'] ?? 0),
            'event_count' => (int) ($row['event_count'] ?? 0),
        ];
    }

    /** @param array<string,mixed> $row @return array<string,mixed> */
    private function hydrateVersion(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'document_id' => (int) ($row['document_id'] ?? 0),
            'version_number' => (int) ($row['version_number'] ?? 0),
            'original_filename' => (string) ($row['original_filename'] ?? ''),
            'mime_type' => (string) ($row['mime_type'] ?? 'application/octet-stream'),
            'file_size' => (int) ($row['file_size'] ?? 0),
            'change_note' => (string) ($row['change_note'] ?? ''),
            'uploaded_by_user_id' => (int) ($row['uploaded_by_user_id'] ?? 0),
            'uploaded_by_display_name' => (string) ($row['uploaded_by_display_name'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }

    /** @param array<string,mixed> $row @return array<string,mixed> */
    private function hydrateEvent(array $row): array
    {
        $details = $row['details_json'] ?? null;
        if (is_string($details) && $details !== '') {
            $decoded = json_decode($details, true);
            $details = is_array($decoded) ? $decoded : [];
        } else {
            $details = [];
        }

        return [
            'id' => (int) ($row['id'] ?? 0),
            'document_id' => (int) ($row['document_id'] ?? 0),
            'user_id' => (int) ($row['user_id'] ?? 0),
            'event_type' => (string) ($row['event_type'] ?? ''),
            'summary' => (string) ($row['summary'] ?? ''),
            'details' => $details,
            'created_at' => (string) ($row['created_at'] ?? ''),
            'user_display_name' => (string) ($row['user_display_name'] ?? ''),
        ];
    }
}
