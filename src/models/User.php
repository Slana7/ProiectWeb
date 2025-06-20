<?php
class User {
    public $id;
    public $name;
    public $email;
    public $role;

    public function __construct($id, $name, $email, $role) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
    }
    public static function findById($conn, $id)
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public static function update($conn, $id, $name, $password = null)
{
    if ($password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET name = :name, password = :password WHERE id = :id";
        $params = ['name' => $name, 'password' => $hashed, 'id' => $id];
    } else {
        $sql = "UPDATE users SET name = :name WHERE id = :id";
        $params = ['name' => $name, 'id' => $id];
    }

    $stmt = $conn->prepare($sql);
    return $stmt->execute($params);
}
public function getId() {
        return $this->id;
    }

    public function getRole() {
        return $this->role;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getName() {
        return $this->name;
    }
}
