<?php
// create_hash.php

$passwordToHash = '111';
$hashedPassword = password_hash($passwordToHash, PASSWORD_DEFAULT);

echo "<h3>Password Hash Generator</h3>";
echo "<p>Use this information to update the 'encoder' user in your database.</p>";
echo "<b>Username:</b> encoder<br>";
echo "<b>Password:</b> " . htmlspecialchars($passwordToHash) . "<br><br>";
echo "<b>Copy this entire hash:</b><br>";
echo "<textarea rows='4' cols='80' readonly>" . htmlspecialchars($hashedPassword) . "</textarea>";

?>