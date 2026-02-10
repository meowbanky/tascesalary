<?php
class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        try {
            // Log login attempt
            error_log("Starting login process for email: $email");

            $query = "SELECT tbl_users.staff_id as id,  employee.`NAME` as name, employee.EMAIL as email, tbl_users.password_hash as password FROM tbl_users INNER JOIN employee ON  tbl_users.staff_id = employee.staff_id WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            error_log("Query executed, rows found: " . $stmt->rowCount());

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                error_log("User found, checking password");
                error_log("Stored password hash: " . $row['password']);
                error_log("Password being tested: " . $password);

                // Test password verification with debug info
                $verify_result = password_verify($password, $row['password']);
                error_log("Password verify result: " . ($verify_result ? 'true' : 'false'));

                if ($verify_result) {
                    error_log("Password verification successful");
                    return [
                        'success' => true,
                        'user' => [
                            'id' => $row['id'],
                            'name' => $row['name'],
                            'email' => $row['email'],
                        ]
                    ];
                } else {
                    error_log("Password verification failed");
                    // Additional debug info
                    if (password_needs_rehash($row['password'], PASSWORD_DEFAULT)) {
                        error_log("Password hash needs rehash");
                    }
                }
            } else {
                error_log("No user found with email: $email");
            }

            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];

        } catch (PDOException $e) {
            error_log("Database error in login: " . $e->getMessage());
            throw new Exception('Database error occurred');
        }
    }

    // Get user by email for forgot password functionality
    public function getUserByEmail($email) {
        try {
            $query = "SELECT tbl_users.staff_id as id, employee.`NAME` as name, employee.EMAIL as email 
                     FROM tbl_users 
                     INNER JOIN employee ON tbl_users.staff_id = employee.staff_id 
                     WHERE employee.EMAIL = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return false;
        } catch (PDOException $e) {
            error_log("Database error in getUserByEmail: " . $e->getMessage());
            return false;
        }
    }

    // Save password reset data (OTP, reset token, expiration)
    public function savePasswordResetData($userId, $otp, $resetToken, $expiresAt) {
        try {
            // First, clear any existing reset data for this user
            $clearQuery = "DELETE FROM password_resets WHERE user_id = :user_id";
            $clearStmt = $this->conn->prepare($clearQuery);
            $clearStmt->bindParam(':user_id', $userId);
            $clearStmt->execute();

            // Insert new reset data
            $query = "INSERT INTO password_resets (user_id, otp, reset_token, expires_at, created_at) 
                     VALUES (:user_id, :otp, :reset_token, :expires_at, NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':otp', $otp);
            $stmt->bindParam(':reset_token', $resetToken);
            $stmt->bindParam(':expires_at', $expiresAt);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error in savePasswordResetData: " . $e->getMessage());
            return false;
        }
    }

    // Verify password reset OTP
    public function verifyPasswordResetOTP($email, $otp) {
        try {
            // Get user by email
            $user = $this->getUserByEmail($email);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Check OTP and expiration
            $query = "SELECT reset_token, expires_at FROM password_resets 
                     WHERE user_id = :user_id AND otp = :otp AND expires_at > NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user['id']);
            $stmt->bindParam(':otp', $otp);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return [
                    'success' => true,
                    'reset_token' => $row['reset_token']
                ];
            } else {
                return ['success' => false, 'message' => 'Invalid or expired OTP'];
            }
        } catch (PDOException $e) {
            error_log("Database error in verifyPasswordResetOTP: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    // Reset password
    public function resetPassword($email, $newPassword, $resetToken) {
        try {
            // Get user by email
            $user = $this->getUserByEmail($email);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Verify reset token
            $query = "SELECT id FROM password_resets 
                     WHERE user_id = :user_id AND reset_token = :reset_token AND expires_at > NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user['id']);
            $stmt->bindParam(':reset_token', $resetToken);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return ['success' => false, 'message' => 'Invalid or expired reset token'];
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password in tbl_users
            $updateQuery = "UPDATE tbl_users SET password_hash = :password_hash, plain_password = :plain_password 
                           WHERE staff_id = :staff_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':password_hash', $hashedPassword);
            $updateStmt->bindParam(':plain_password', $newPassword);
            $updateStmt->bindParam(':staff_id', $user['id']);

            if ($updateStmt->execute()) {
                // Clear reset data
                $clearQuery = "DELETE FROM password_resets WHERE user_id = :user_id";
                $clearStmt = $this->conn->prepare($clearQuery);
                $clearStmt->bindParam(':user_id', $user['id']);
                $clearStmt->execute();

                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Failed to update password'];
            }
        } catch (PDOException $e) {
            error_log("Database error in resetPassword: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
}