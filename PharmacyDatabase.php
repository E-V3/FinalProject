<?php
class PharmacyDatabase {
    private $host = "localhost";
    private $port = "3306";
    private $database = "pharmacy_portal_db";
    private $user = "root";
    private $password = "YourPassword";
    public $connection;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->connection = new mysqli($this->host, $this->user, $this->password, $this->database, $this->port);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        echo "Successfully connected to the database";
    }

    public function addPrescription($patientUserName, $medicationId, $dosageInstructions, $quantity)  {
        $stmt = $this->connection->prepare(
            "SELECT userId FROM Users WHERE userName = ? AND userType = 'patient'"
        );
        $stmt->bind_param("s", $patientUserName);
        $stmt->execute();
        $stmt->bind_result($patientId);
        $stmt->fetch();
        $stmt->close();
        
        if ($patientId){
            $stmt = $this->connection->prepare(
                "INSERT INTO prescriptions (userId, medicationId, dosageInstructions, quantity) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("iisi", $patientId, $medicationId, $dosageInstructions, $quantity);
            $stmt->execute();
            $stmt->close();
            echo "Prescription added successfully";
        } else {
            echo "Failed to add prescription";
        }
    }

    public function getAllPrescriptions() {
        $result = $this->connection->query("SELECT * FROM prescriptions JOIN medications ON prescriptions.medicationId = medications.medicationId");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function MedicationInventory() {
        // Fetching from MedicationInventoryView to show medication stock levels
        $result = $this->connection->query("SELECT * FROM MedicationInventoryView");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addUser($userName, $contactInfo, $userType) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM Users WHERE userName = ?");
        $stmt->bind_param("s", $userName);
        $stmt->execute();
        $stmt->bind_result($userCount);
        $stmt->fetch();
        $stmt->close();

        if ($userCount == 0) {
            // User doesn't exist, add them
            $stmt = $this->connection->prepare("CALL AddOrUpdateUser(NULL, ?, ?, ?)");
            $stmt->bind_param("sss", $userName, $contactInfo, $userType);
            $stmt->execute();
            $stmt->close();
            echo "User added successfully";
        } else {
            echo "User already exists";
        }
    }

    // Function for processing a sale
    public function processSale($prescriptionId, $quantitySold, $saleAmount) {
        $stmt = $this->connection->prepare("CALL ProcessSale(?, ?, ?)");
        $stmt->bind_param("iis", $prescriptionId, $quantitySold, $saleAmount);
        $stmt->execute();
        $stmt->close();
    }

    // Function to get user details based on userId
    public function getUserDetails($userId) {
        $stmt = $this->connection->prepare("SELECT * FROM Users WHERE userId = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Secure login
    public function authenticateUser($userName, $password) {
        $stmt = $this->connection->prepare("SELECT * FROM Users WHERE userName = ?");
        $stmt->bind_param("s", $userName);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // Function to check if a user exists
    public function userExists($userName) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM Users WHERE userName = ?");
        $stmt->bind_param("s", $userName);
        $stmt->execute();
        $stmt->bind_result($userCount);
        $stmt->fetch();
        $stmt->close();
        return $userCount > 0;
    }

    // Function to close the database connection
    public function close() {
        $this->connection->close();
    }
}
?>