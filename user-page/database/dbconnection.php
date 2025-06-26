<?php
$host = 'localhost';
$dbname = 'intrack';
$username = 'root';
$password = ''; 

$conn = mysqli_connect($host, $username, $password, $dbname);

if(!$conn){
    echo "Connection Failed";
}


// $hash = '$2y$10$ZsvU59Foj2jYQBwI2v7ePeM/Wk0ccULUD7JV672e1.PgKHLLlSgAW';

// $guesses = ['test123', 'admin123', 'hello123', 'password1', '12345678', 'secret123'];

// foreach ($guesses as $guess) {
//     if (password_verify($guess, $hash)) {
//         echo "Match found! Password is: $guess\n";
//         exit;
//     } else {
//         echo "No match for: $guess\n";
//     }
// }
?>
