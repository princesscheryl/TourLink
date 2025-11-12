<?php

require_once __DIR__ . '/../settings/db_class.php';


class User extends db_connection
{
    private $user_id;
    private $name;
    private $email;
    private $password;
    private $role;
    private $date_created;
    private $phone_number;

    public function __construct($user_id = null)
    {
        parent::db_connect();
        if ($user_id) {
            $this->user_id = $user_id;
            $this->loadUser();
        }
    }
    private function loadUser($user_id = null)
    {
        if ($user_id) {
            $this->user_id = $user_id;
        }
        if (!$this->user_id) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            $this->name         = $result['customer_name'];
            $this->email        = $result['customer_email'];
            $this->password     = $result['customer_pass'];
            $this->role         = $result['user_role'];
            $this->date_created = isset($result['date_created']) ? $result['date_created'] : null;
            $this->phone_number = $result['customer_contact'];
        }
    }
    public function createUser($name,$email,$password,$country,$city,$phone_number,$role=2)
    {
        $hashed_password=password_hash($password,PASSWORD_DEFAULT);

        $stmt=$this->db->prepare(
            "INSERT INTO customer (customer_name,customer_email,customer_pass,customer_country,customer_city,customer_contact,user_role)
            VALUES (?,?,?,?,?,?,?)"
        );
        $stmt->bind_param("ssssssi",$name,$email,$hashed_password,$country,$city,$phone_number,$role);

        if($stmt->execute()){
            return $this->db->insert_id;
        }
        return false;
    }
    public function emailExists($email)
    {
        $stmt = $this->db->prepare("SELECT customer_id FROM customer WHERE customer_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    /** Get a user by email */
    public function getUserByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function loginCustomer($email,$password){
        $stmt=$this->db->prepare("SELECT * FROM customer WHERE customer_email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $customer=$stmt->get_result()->fetch_assoc();
        
        if($customer&&password_verify($password,$customer['customer_pass'])){
            unset($customer['customer_pass']);
            return $customer;
        }
        return false;
    }

    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function updateUser($id, $name, $phone_number, $role)
    {
        $stmt = $this->db->prepare(
            "UPDATE customer SET customer_name = ?, customer_contact = ?, user_role = ? WHERE customer_id = ?"
        );
        $stmt->bind_param("ssii", $name, $phone_number, $role, $id);
        return $stmt->execute();
    }
    public function updatePassword($id, $password)
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE customer SET customer_pass = ? WHERE customer_id = ?");
        $stmt->bind_param("si", $hashed_password, $id);
        return $stmt->execute();
    }
    public function deleteUser($id)
    {
        $stmt = $this->db->prepare("DELETE FROM customer WHERE customer_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}