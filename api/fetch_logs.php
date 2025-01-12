<?php
require_once "config.php";
$query = "SELECT * FROM tbllogs ORDER BY datelog DESC, timelog DESC";
$search = isset($_POST['btnsearch']) ? $_POST['btnsearch'] : '';
if (!empty($search)) {
    $query = "SELECT * FROM tbllogs WHERE 
                datelog LIKE '%$search%' OR 
                timelog LIKE '%$search%' OR 
                id LIKE '%$search%' OR 
                performedby LIKE '%$search%' OR 
                action LIKE '%$search%' OR 
                module LIKE '%$search%'
              ORDER BY datelog DESC, timelog DESC";
}
$result = mysqli_query($conn, $query);
if ($result) {
    echo '<div class="main">';
    if (mysqli_num_rows($result) > 0) {
        echo "<table id='logsTable'>";
        echo "<tr><th>Date</th><th>Time</th><th>ID</th><th>Performed By</th><th>Action</th><th>Module</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr class='logEntry'>";
            echo "<td>{$row['datelog']}</td>";
            echo "<td>{$row['timelog']}</td>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['performedby']}</td>";
            echo "<td>{$row['action']}</td>";
            echo "<td>{$row['module']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No logs found.";
    }
} else {
    echo "Error fetching logs: " . mysqli_error($conn);
}
echo '</div>';
mysqli_close($conn);
?>
