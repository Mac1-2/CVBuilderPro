<?php
class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function register(string $email, string $password, string $firstName = '', string $lastName = ''): array {
        $existing = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $existing->execute([$email]);
        if ($existing->fetch()) {
            return ['success' => false, 'error' => 'Email already registered'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password_hash, first_name, last_name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $hash, $firstName, $lastName]);

        return ['success' => true, 'user_id' => $this->db->lastInsertId()];
    }

    public function login(string $email, string $password): array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }

        $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

        return ['success' => true, 'user' => $user];
    }

    public function logout(): void {
        session_destroy();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, email, first_name, last_name, avatar, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updateProfile(int $userId, array $data): bool {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['first_name', 'last_name', 'avatar'])) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        if (empty($fields)) return false;
        $values[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->prepare($sql)->execute($values);
    }
}
