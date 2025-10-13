<?php
class User
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
        $this->db = new mysqli('localhost', 'root', '', 'classes');
        
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

 // Fonction Register : crée un utilisateur en bdd
    public function register($login, $password, $email, $firstname, $lastname)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $login, $hashedPassword, $email, $firstname, $lastname);

        if ($stmt->execute()) {
            $this->id = $stmt->insert_id;
            $this->login = $login;
            $this->email = $email;
            $this->firstname = $firstname;
            $this->lastname = $lastname;
            $stmt->close();
            return $this->getAllInfos();
        }
        $stmt->close();
        return false;
    }

  // Fonction Connect
    public function connect($login, $password)
    {
        $stmt = $this->db->prepare("SELECT id, login, password, email, firstname, lastname FROM utilisateurs WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $this->id = $user['id'];
                $this->login = $user['login'];
                $this->email = $user['email'];
                $this->firstname = $user['firstname'];
                $this->lastname = $user['lastname'];
                $stmt->close();
                return true;
            }
        }
        $stmt->close();
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

    // Fonction Delete
    public function delete()
    {
        if (!$this->isConnected()) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM utilisateurs WHERE id = ?");
        $stmt->bind_param("i", $this->id);

        if ($stmt->execute()) {
            $this->disconnect();
            $stmt->close();
            return true;
        }
        $stmt->close();
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
        $stmt->bind_param("sssssi", $login, $hashedPassword, $email, $firstname, $lastname, $this->id);

        if ($stmt->execute()) {
            $this->login = $login;
            $this->email = $email;
            $this->firstname = $firstname;
            $this->lastname = $lastname;
            $stmt->close();
            return true;
        }
        $stmt->close();
        return false;
    }

// Fonction IsConnected
    public function isConnected()
    {
        return !empty($this->id);
    }

// Fonction GetAllInfos
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

    // Focntion getLogin
    public function getLogin()
    {
        return $this->login;
    }

    // Fonction Getemail
        public function getEmail()
    {
        return $this->email;
    }

    // Fonction getFirstName
    public function getFirstname()
    {
        return $this->firstname;
    }

// Fonction Getlastname
    public function getLastname()
    {
        return $this->lastname;
    }
}