<?php
echo "<h2>Loaded Extensions</h2>";
$extensions = get_loaded_extensions();
sort($extensions);
echo "<ul>";
foreach ($extensions as $ext) {
    echo "<li>" . $ext . "</li>";
}
echo "</ul>";

echo "<h2>PDO Drivers</h2>";
$drivers = PDO::getAvailableDrivers();
echo "<ul>";
foreach ($drivers as $driver) {
    echo "<li>" . $driver . "</li>";
}
echo "</ul>";
?>