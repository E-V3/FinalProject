<?php
require_once 'PharmacyDatabase.php';

class PharmacyPortal {
    private $db;

    public function __construct() {
        $this->db = new PharmacyDatabase();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? 'home';

        switch ($action) {
            case 'addPrescription':
                $this->addPrescription();
                break;
            case 'viewPrescriptions':
                $this->viewPrescriptions();
                break;
            case 'viewInventory':
                $this->viewInventory();
                break;
            case 'addUser':
                $this->addUser();
                break;
            case 'logout':
                session_destroy();
                header("Location: login.php");
                exit();
            default:
                $this->home();
        }
    }

    private function home() {
        include 'templates/home.php';
    }

    private function addPrescription() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $patientUserName = $_POST['patient_username'];
            $medicationId = $_POST['medication_id'];
            $dosageInstructions = $_POST['dosage_instructions'];
            $quantity = $_POST['quantity'];

            $this->db->addPrescription($patientUserName, $medicationId, $dosageInstructions, $quantity);
            header("Location:?action=viewPrescriptions&message=Prescription Added");
        } else {
            include 'templates/addPrescription.php';
        }
    }

    private function viewPrescriptions() {
        $prescriptions = $this->db->getAllPrescriptions();
        include 'templates/viewPrescriptions.php';
    }

    private function viewInventory() {
        $inventory = $this->db->getInventory(); // Assumes a method in PharmacyDatabase
        include 'templates/viewInventory.php';
    }

    private function addUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $contactInfo = $_POST['contact_info'];
            $userType = $_POST['user_type'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $this->db->addUser($username, $contactInfo, $userType, $password);
            header("Location:?action=home&message=User Added");
        } else {
            include 'templates/addUser.php';
        }
    }
}

function loginUser($username, $password) {
    $db = new PharmacyDatabase();

    $stmt = $db->connection->prepare("SELECT password FROM Users WHERE userName = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    
    if ($stmt->fetch()) {
        $stmt->close();
        return password_verify($password, $hashedPassword);
    }

    $stmt->close();
    return false;
}

$portal = new PharmacyPortal();
$portal->handleRequest();