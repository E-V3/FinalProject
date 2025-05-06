<html>
<head><title>Add User</title></head>
<body>
    <h1>Add New User</h1>
    <form action="PharmacyServer.php?action=addUser" method="post">
        <label for="userName">Username:</label><br>
        <input type="text" name="userName" required><br><br>

        <label for="contactInfo">Contact Info:</label><br>
        <input type="text" name="contactInfo"><br><br>

        <label for="userType">User Type:</label><br>
        <select name="userType" required>
            <option value="pharmacist">Pharmacist</option>
            <option value="patient">Patient</option>
        </select><br><br>

        <button type="submit">Add User</button>
    </form>
    <a href="PharmacyServer.php">Back to Home</a>
</body>
</html>
