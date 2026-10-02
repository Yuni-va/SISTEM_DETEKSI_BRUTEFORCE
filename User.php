<?php

class User
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login($username)
    {
        $sql = "SELECT * FROM users WHERE username=? LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /*
    |--------------------------------------------------------------------------
    | Semua User
    |--------------------------------------------------------------------------
    */

    public function getAll()
    {
        $sql = "SELECT *
                FROM users
                ORDER BY id DESC";

        return mysqli_query($this->conn, $sql);
    }

    /*
    |--------------------------------------------------------------------------
    | Total User
    |--------------------------------------------------------------------------
    */

    public function total()
    {
        $sql = "SELECT COUNT(*) total FROM users";

        $query = mysqli_query($this->conn, $sql);

        return mysqli_fetch_assoc($query)['total'];
    }

    /*
    |--------------------------------------------------------------------------
    | Cari User
    |--------------------------------------------------------------------------
    */

    public function search($keyword)
    {
        $keyword = "%".$keyword."%";

        $sql = "SELECT *
                FROM users
                WHERE fullName LIKE ?
                OR username LIKE ?
                OR email LIKE ?
                ORDER BY id DESC";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sss",
            $keyword,
            $keyword,
            $keyword
        );

        $stmt->execute();

        return $stmt->get_result();
    }

    /*
    |--------------------------------------------------------------------------
    | Ambil User berdasarkan ID
    |--------------------------------------------------------------------------
    */

    public function find($id)
    {
        $sql = "SELECT *
                FROM users
                WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i",$id);

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /*
    |--------------------------------------------------------------------------
    | Tambah User
    |--------------------------------------------------------------------------
    */

    public function insert(
        $fullname,
        $username,
        $email,
        $password,
        $role,
        $status
    ){

        $password = password_hash($password,PASSWORD_DEFAULT);

        $sql = "INSERT INTO users
                (
                    fullName,
                    username,
                    email,
                    password,
                    role,
                    status
                )
                VALUES
                (
                    ?,?,?,?,?,?
                )";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ssssss",
            $fullname,
            $username,
            $email,
            $password,
            $role,
            $status
        );

        return $stmt->execute();

    }

    /*
    |--------------------------------------------------------------------------
    | Update User
    |--------------------------------------------------------------------------
    */

    public function update(
        $id,
        $fullname,
        $username,
        $email,
        $role,
        $status
    ){

        $sql = "UPDATE users
                SET
                    fullName=?,
                    username=?,
                    email=?,
                    role=?,
                    status=?
                WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sssssi",
            $fullname,
            $username,
            $email,
            $role,
            $status,
            $id
        );

        return $stmt->execute();

    }

    /*
    |--------------------------------------------------------------------------
    | Update Password
    |--------------------------------------------------------------------------
    */

    public function updatePassword($id,$password)
    {
        $password = password_hash($password,PASSWORD_DEFAULT);

        $sql = "UPDATE users
                SET password=?
                WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "si",
            $password,
            $id
        );

        return $stmt->execute();
    }

    public function updateProfile($id,$fullname,$username,$email)
    {
        $sql = "UPDATE users
                SET
                    fullName=?,
                    username=?,
                    email=?
                WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sssi",
            $fullname,
            $username,
            $email,
            $id
        );

        return $stmt->execute();
    }

    public function updateProfilePhoto($id,$photo)
    {
        $sql = "UPDATE users
                SET profile_photo=?
                WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "si",
            $photo,
            $id
        );

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | Hapus User
    |--------------------------------------------------------------------------
    */

    public function delete($id)
    {
        $sql = "DELETE FROM users WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i",$id);

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | Cek Username
    |--------------------------------------------------------------------------
    */

    public function usernameExists($username)
    {
        $sql = "SELECT id
                FROM users
                WHERE username=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("s",$username);

        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function usernameExistsExcept($username,$id)
    {
        $sql = "SELECT id
                FROM users
                WHERE username=?
                AND id<>?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("si",$username,$id);

        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Cek Email
    |--------------------------------------------------------------------------
    */

    public function emailExists($email)
    {
        $sql = "SELECT id
                FROM users
                WHERE email=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("s",$email);

        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function emailExistsExcept($email,$id)
    {
        $sql = "SELECT id
                FROM users
                WHERE email=?
                AND id<>?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("si",$email,$id);

        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

}
