<?php
if (extension_loaded('imagick')) {
    echo "Imagick ist installiert und aktiv.\n";
    $v = \Imagick::getVersion();
    echo "Version: " . ($v['versionString'] ?? 'unbekannt') . "\n";
} else {
    echo "Imagick ist NICHT installiert/aktiv.\n";
}