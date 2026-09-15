<?php
$theme_url = 'https://downloads.wordpress.org/theme/storefront.4.5.0.zip';
$zip_file = __DIR__ . '/storefront.zip';
echo "Downloading Storefront theme...\n";
$content = @file_get_contents($theme_url);
if ($content) {
    file_put_contents($zip_file, $content);
    $zip = new ZipArchive();
    if ($zip->open($zip_file) === TRUE) {
        $zip->extractTo(__DIR__ . '/wp-content/themes/');
        $zip->close();
        unlink($zip_file);
        echo "Storefront theme installed successfully!\n";
    } else {
        echo "Failed to extract zip.\n";
    }
} else {
    echo "Failed to download theme.\n";
}
