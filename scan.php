<?php
// Mengatur header agar outputnya adalah JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate'); // Mencegah caching

function scanMusic($dir) {
    $songs = [];
    // Membuat iterator untuk menjelajahi folder secara rekursif (termasuk subfolder)
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    // Daftar ekstensi file yang diizinkan
    $allowed_extensions = ['mp3', 'wav'];

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, $allowed_extensions)) {
                // Mengambil path relatif agar bisa diakses dari browser
                $relativePath = str_replace(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '', str_replace('\\', '/', $file->getRealPath()));
                
                // Membersihkan path (menghapus / di awal jika ada)
                if (substr($relativePath, 0, 1) == '/') {
                    $relativePath = substr($relativePath, 1);
                }

                $songs[] = [
                    // Mengambil nama file tanpa ekstensi untuk dijadikan judul
                    'name' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                    'path' => $relativePath
                ];
            }
        }
    }
    return $songs;
}

// Menentukan folder default 'music'
$music_directory = 'music';
$playlist = ['songs' => []];

if (is_dir($music_directory)) {
    $playlist['songs'] = scanMusic($music_directory);
}

// Meng-encode array menjadi format JSON dan menampilkannya
echo json_encode($playlist, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
