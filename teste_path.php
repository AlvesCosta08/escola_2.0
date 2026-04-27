<?php
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script atual: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
echo "Caminho base: " . dirname(__DIR__) . "<br>";

// Lista arquivos na pasta atual
$files = scandir(__DIR__);
echo "<h3>Arquivos em " . __DIR__ . ":</h3><ul>";
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "<li>$file</li>";
    }
}
echo "</ul>";
?>