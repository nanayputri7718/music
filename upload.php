<?php
// Mengatur header agar outputnya adalah JSON
header('Content-Type: application/json');

// Menyiapkan array respons default
$response = [
    'status' => 'error',
    'message' => 'Tidak ada file yang diunggah atau terjadi kesalahan.'
];

// Menentukan folder tujuan
$targetDir = "music/";

// Membuat folder 'music' jika belum ada
if (!file_exists($targetDir)) {
    // mkdir() membuat direktori baru. 
    // Parameter kedua (0777) adalah mode izin (akses penuh).
    // Parameter ketiga (true) memungkinkan pembuatan direktori bersarang.
    mkdir($targetDir, 0777, true);
}

// Memeriksa apakah ada file yang dikirim melalui metode POST
if (isset($_FILES['musicFiles']) && !empty($_FILES['musicFiles']['name'][0])) {
    $allowedTypes = ['mp3', 'wav'];
    $uploadedFiles = [];
    $errorMessages = [];
    $fileCount = count($_FILES['musicFiles']['name']);

    // Loop melalui setiap file yang diunggah
    for ($i = 0; $i < $fileCount; $i++) {
        // Mengambil nama file dan membersihkannya dari karakter yang tidak diinginkan
        $fileName = basename($_FILES['musicFiles']['name'][$i]);
        $targetFilePath = $targetDir . $fileName;
        
        // Mengambil ekstensi file
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Memeriksa apakah file adalah tipe yang diizinkan
        if (in_array($fileType, $allowedTypes)) {
            // Memeriksa apakah terjadi error saat upload
            if ($_FILES['musicFiles']['error'][$i] === UPLOAD_ERR_OK) {
                // Mencoba memindahkan file dari folder temporary ke folder tujuan
                if (move_uploaded_file($_FILES['musicFiles']['tmp_name'][$i], $targetFilePath)) {
                    $uploadedFiles[] = $fileName;
                } else {
                    $errorMessages[] = "Gagal memindahkan file: $fileName";
                }
            } else {
                $errorMessages[] = "Error saat mengunggah file $fileName. Kode: " . $_FILES['musicFiles']['error'][$i];
            }
        } else {
            $errorMessages[] = "Jenis file tidak diizinkan untuk: $fileName";
        }
    }

    // Menyiapkan respons berdasarkan hasil upload
    if (!empty($uploadedFiles)) {
        $response['status'] = 'success';
        $response['message'] = count($uploadedFiles) . ' file berhasil diunggah!';
        if(!empty($errorMessages)) {
            $response['message'] .= ' Beberapa file gagal: ' . implode(', ', $errorMessages);
        }
    } else {
        $response['message'] = 'Upload gagal. Alasan: ' . implode(', ', $errorMessages);
    }
}

// Meng-encode array respons menjadi format JSON dan menampilkannya
echo json_encode($response);
?>
