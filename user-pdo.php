<?php
class Userpdo
{
    private $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;
    private $db;

// Fonction Construct 
    public function __construct()
    {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=classes', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

// Fonction register
    public function register($login, $password, $email, $firstname, $lastname)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$login, $hashedPassword, $email, $firstname, $lastname])) {
            $this->id = $this->db->lastInsertId();
            $this->login = $login;
            $this->email = $email;
            $this->firstname = $firstname;
            $this->lastname = $lastname;
            return $this->getAllInfos();
        }
        return false;
    }

// Fonction Connect 
    public function connect($login, $password)
    {
        $stmt = $this->db->prepare("SELECT id, login, password, email, firstname, lastname FROM utilisateurs WHERE login = ?");
        $stmt->execute([$login]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $this->id = $user['id'];
            $this->login = $user['login'];
            $this->email = $user['email'];
            $this->firstname = $user['firstname'];
            $this->lastname = $user['lastname'];
            return true;
        }
        return false;
    }

   // Fonction disconnect
    public function disconnect()
    {
        $this->id = null;
        $this->login = null;
        $this->email = null;
        $this->firstname = null;
        $this->lastname = null;
        return true;
    }

// Fonction Disconnect 
    public function delete()
    {
        if (!$this->isConnected()) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM utilisateurs WHERE id = ?");
        
        if ($stmt->execute([$this->id])) {
            $this->disconnect();
            return true;
        }
        return false;
    }


// Fonction Update
    public function update($login, $password, $email, $firstname, $lastname)
    {
        if (!$this->isConnected()) {
            return false;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("UPDATE utilisateurs SET login = ?, password = ?, email = ?, firstname = ?, lastname = ? WHERE id = ?");
        
        if ($stmt->execute([$login, $hashedPassword, $email, $firstname, $lastname, $this->id])) {
            $this->login = $login;
            $this->email = $email;
            $this->firstname = $firstname;
            $this->lastname = $lastname;
            return true;
        }
        return false;
    }

// Fonction isConnceted
    public function isConnected()
    {
        return !empty($this->id);
    }

// Fonction getAllinfos
    public function getAllInfos()
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname
        ];
    }

// Fonction getlogin
    public function getLogin()
    {
        return $this->login;
    }


    public function getEmail()
    {
        return $this->email;
    }

// Fonction getFirstName
    public function getFirstname()
    {
        return $this->firstname;
    }

// Fonction getLastname
    public function getLastname()
    {
        return $this->lastname;
    }
}