<?php
class Document {
    private $pdo;
    
    public function __construct() {
        require_once __DIR__ . '/../config/db.php';
        $this->pdo = db();
    }
    
    // Get all documents with filter
    public function getAll($filters = []) {
        global $pdo;
        $sql = "SELECT d.*, u.full_name as applicant_name, u.no_hp as applicant_phone, de.name as department_name,
                (SELECT COUNT(*) FROM approvals a WHERE a.document_id = d.id) as approval_count
                FROM documents d
                LEFT JOIN users u ON d.applicant_id = u.id
                LEFT JOIN departments de ON d.department_id = de.id";
        
        $params = [];
        $where = [];
        
        if (isset($filters['status'])) {
            $where[] = "d.status = ?";
            $params[] = $filters['status'];
        }
        if (isset($filters['applicant_id'])) {
            $where[] = "d.applicant_id = ?";
            $params[] = $filters['applicant_id'];
        }
        if (isset($filters['department_id'])) {
            $where[] = "d.department_id = ?";
            $params[] = $filters['department_id'];
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Order by updated_at descending
        $sql .= " ORDER BY d.updated_at DESC";
        
$stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Get all documents with filter + pagination (for daftar surat)
    public function getAllDocuments($filters = [], $limit = 15, $offset = 0) {
        global $pdo;
        $sql = "SELECT d.*, u.full_name as applicant_name, u.no_hp as applicant_phone, de.name as department_name,
                (SELECT COUNT(*) FROM approvals a WHERE a.document_id = d.id) as approval_count
                FROM documents d
                LEFT JOIN users u ON d.applicant_id = u.id
                LEFT JOIN departments de ON d.department_id = de.id";

        $params = [];
        $where = [];

        if (isset($filters['status'])) {
            $where[] = "d.status = ?";
            $params[] = $filters['status'];
        }
        if (isset($filters['applicant_id'])) {
            $where[] = "d.applicant_id = ?";
            $params[] = $filters['applicant_id'];
        }
        if (isset($filters['department_id'])) {
            $where[] = "d.department_id = ?";
            $params[] = $filters['department_id'];
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $where[] = "(d.title LIKE ? OR d.document_number LIKE ? OR d.type LIKE ?
                         OR d.category LIKE ? OR d.description LIKE ? OR d.notes LIKE ?
                         OR u.full_name LIKE ? OR u.no_hp LIKE ? OR de.name LIKE ?
                         OR d.status LIKE ? OR d.created_at LIKE ? OR d.updated_at LIKE ?)";
            for ($i = 0; $i < 12; $i++) $params[] = $searchTerm;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY d.updated_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Count documents matching filters (for pagination)
    public function countAllDocuments($filters = []) {
        global $pdo;
        $sql = "SELECT COUNT(*) FROM documents d
                LEFT JOIN users u ON d.applicant_id = u.id
                LEFT JOIN departments de ON d.department_id = de.id";
        $params = [];
        $where = [];

        if (isset($filters['status'])) {
            $where[] = "d.status = ?";
            $params[] = $filters['status'];
        }
        if (isset($filters['applicant_id'])) {
            $where[] = "d.applicant_id = ?";
            $params[] = $filters['applicant_id'];
        }
        if (isset($filters['department_id'])) {
            $where[] = "d.department_id = ?";
            $params[] = $filters['department_id'];
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $where[] = "(d.title LIKE ? OR d.document_number LIKE ? OR d.type LIKE ?
                         OR d.category LIKE ? OR d.description LIKE ? OR d.notes LIKE ?
                         OR u.full_name LIKE ? OR u.no_hp LIKE ? OR de.name LIKE ?
                         OR d.status LIKE ? OR d.created_at LIKE ? OR d.updated_at LIKE ?)";
            for ($i = 0; $i < 12; $i++) $params[] = $searchTerm;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Get single document with attachments and approvals
    public function getById($id) {
        global $pdo;
        
        $stmt = $this->pdo->prepare("SELECT d.*, u.full_name as applicant_name, 
                (SELECT COUNT(*) FROM approvals a WHERE a.document_id = d.id) as approval_count
                FROM documents d
                LEFT JOIN users u ON d.applicant_id = u.id
                WHERE d.id = ?");
        $stmt->execute([$id]);
        $document = $stmt->fetch();
        
        // Get attachments
        $attStmt = $this->pdo->prepare("SELECT * FROM document_attachments WHERE document_id = ?");
        $attStmt->execute([$id]);
        $attachments = $attStmt->fetchAll();
        
        // Get approvals
        $appStmt = $this->pdo->prepare("SELECT a.*, u.full_name as approver_name 
                                       FROM approvals a 
                                       LEFT JOIN users u ON a.approver_id = u.id 
                                       ORDER BY COALESCE(a.approved_at, a.rejected_at) ASC");
        $appStmt->execute([$id]);
        $approvals = $appStmt->fetchAll();
        
        return [
            'document' => $document,
            'attachments' => $attachments,
            'approvals' => $approvals
        ];
    }
    
    // Get pending approvals
    public function getPendingApprovals($filters = []) {
        global $pdo;
        $sql = "SELECT d.*, u.full_name as applicant_name, u.no_hp as applicant_phone, de.name as department_name
                FROM documents d
                LEFT JOIN users u ON d.applicant_id = u.id
                LEFT JOIN departments de ON d.department_id = de.id
                WHERE d.status = ?";
        
        $params = [STATUS_SUBMITTED];
        
        if (!empty($filters['departmentId'])) {
            $sql .= " AND d.department_id = ?";
            $params[] = $filters['departmentId'];
        }
        
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $sql .= " AND (d.title LIKE ? OR u.full_name LIKE ? OR de.name LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY d.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Approve/Reject document
    public function approveOrReject($id, $action, $comment, $approverId) {
        global $pdo;
        
        if ($action === 'approve') {
            $stmt = $this->pdo->prepare("INSERT INTO approvals (document_id, approver_id, action, comment) 
                                         VALUES (?, ?, 'approve', ?)");
            $stmt->execute([$id, $approverId, $comment]);
            
            // Update document status and approval stage
            if ($approverId === ROLE_ADMIN || $approverId === ROLE_STAFF) {
                $updateStmt = $this->pdo->prepare("UPDATE documents 
                                                   SET status = ?, approval_stage = ? 
                                                   WHERE id = ?");
                $updateStmt->execute([STATUS_APPROVED, APPROVAL_STAGE_2, $id]);
            } else {
                $updateStmt = $this->pdo->prepare("UPDATE documents 
                                                   SET status = 'pending', approval_stage = ? 
                                                   WHERE id = ?");
                $updateStmt->execute([STATUS_SUBMITTED, APPROVAL_STAGE_1, $id]);
            }
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO approvals (document_id, approver_id, action, comment) 
                                         VALUES (?, ?, 'reject', ?)");
            $stmt->execute([$id, $approverId, $comment]);
            
            // Update document status to rejected
            $updateStmt = $this->pdo->prepare("UPDATE documents SET status = ? WHERE id = ?");
            $updateStmt->execute([STATUS_REJECTED, $id]);
        }
        
        return true;
    }
    
    // Get statistics
    // $all = true → hitung seluruh data surat (admin/staff); false → hanya milik user terkait
    public function getStatistics($userId, $all = false) {
        global $pdo;

        $where = $all ? '' : ' WHERE applicant_id = ?';
        $params = $all ? [] : [$userId];

        $counts = [
            'total'     => 0,
            'submitted' => 0,
            'approved'  => 0,
            'rejected'  => 0,
        ];

        $stmt = $this->pdo->prepare("SELECT status, COUNT(*) AS count FROM documents" . $where . " GROUP BY status");
        $stmt->execute($params);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $counts['total'] += (int)$row['count'];
            switch ($row['status']) {
                case STATUS_SUBMITTED: $counts['submitted'] += (int)$row['count']; break;
                case STATUS_APPROVED:  $counts['approved']  += (int)$row['count']; break;
                case STATUS_REJECTED:  $counts['rejected']  += (int)$row['count']; break;
            }
        }

        return $counts;
    }
}
