<?php
$conn = mysqli_connect('localhost','root','','RamalToko_db');
echo "=== TRANSAKSI TABLE STRUCTURE ===\n";
$result = mysqli_query($conn, 'DESCRIBE transaksi');
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Key'] . "\n";
}
?>
