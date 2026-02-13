<?php
require_once __DIR__ . '/../session.php';
$current_page = 'manage_mamureen';
require_once __DIR__ . '/../inc/header.php';

// Include the necessary fileinfo extension for MIME type validation
if (!extension_loaded('fileinfo')) {
    echo 'Fileinfo extension is not loaded. Please enable it.';
    exit();
}

// Function to establish a PDO connection
function connectToDatabase($dbConfig)
{
    try {
        return new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbName']}", $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        exit();
    }
}

// Function to validate CSV file MIME type
function validateCsvFile($file)
{
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    return in_array($mimeType, ['text/plain', 'text/csv']);
}

// Function to validate CSV header
function validateCsvHeader($file)
{
    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        return false;
    }

    // Get the expected header fields
    $expectedHeaders = [
        'its_id', 'fullname', 'fullname_ar', 'avs_idara', 'email_id', 'mobile', 
        'mauze_type_name', 'khidmat_type_name', 'miqaat_name', 'kg_age', 
        'miqaat_mauze', 'zone', 'miqaat_india_foreign', 'miqaat_jamiat', 
        'miqaat_ilaqa', 'farmaan_seen', 'jamea_degree', 'jamea_std', 'is_musaid'
    ];

    // Read the first row as header
    $header = fgetcsv($handle);
    fclose($handle);

    // Check if header exists and matches the expected format
    if (!$header || count($header) < count($expectedHeaders)) {
        return false;
    }

    // Convert all headers to lowercase for case-insensitive comparison
    $header = array_map('strtolower', $header);
    $expectedHeaders = array_map('strtolower', $expectedHeaders);

    // Check if all required headers exist
    foreach ($expectedHeaders as $expectedHeader) {
        if (!in_array($expectedHeader, $header)) {
            return false;
        }
    }

    return true;
}

// Function to handle CSV file insertion into the database
function handleCsvInsertion($pdo, $file)
{
    $handle = fopen($file['tmp_name'], 'r');

    if (!$handle) {
        throw new Exception('Cannot open the uploaded file.');
    }

    // Skip header row
    $header = fgetcsv($handle);
    
    $stmt = $pdo->prepare('INSERT INTO users_mamureen (
        its_id, fullname, fullname_ar, avs_idara, email_id, mobile, 
        mauze_type_name, khidmat_type_name, miqaat_name, kg_age, 
        miqaat_mauze, zone, miqaat_india_foreign, miqaat_jamiat, 
        miqaat_ilaqa, farmaan_seen, jamea_degree, jamea_std, is_musaid
    ) VALUES (
        :its_id, :fullname, :fullname_ar, :avs_idara, :email_id, :mobile, 
        :mauze_type_name, :khidmat_type_name, :miqaat_name, :kg_age, 
        :miqaat_mauze, :zone, :miqaat_india_foreign, :miqaat_jamiat, 
        :miqaat_ilaqa, :farmaan_seen, :jamea_degree, :jamea_std, :is_musaid
    )');

    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        // Clear the user table safely
        $pdo->exec('TRUNCATE TABLE users_mamureen');

        $rowCount = 0;
        $lineNumber = 2; // Start from line 2 (after header)
        $errors = [];
        $emptyItsIdLines = []; // To collect lines with empty ITS ID
        
        while (($data = fgetcsv($handle)) !== false) {
            try {
                // Check if the row has the expected number of columns
                if (count($data) < 19) {
                    throw new Exception("Row at line {$lineNumber} has incomplete data. Expected 19 columns.");
                }

                // Check if ITS ID is provided and not empty
                if (empty($data[0])) {
                    $emptyItsIdLines[] = $lineNumber;
                    throw new Exception("Empty ITS ID. ITS ID is required.");
                }

                $stmt->execute([
                    ':its_id' => $data[0],
                    ':fullname' => $data[1],
                    ':fullname_ar' => $data[2],
                    ':avs_idara' => $data[3],
                    ':email_id' => $data[4],
                    ':mobile' => $data[5],
                    ':mauze_type_name' => $data[6],
                    ':khidmat_type_name' => $data[7],
                    ':miqaat_name' => $data[8],
                    ':kg_age' => $data[9],
                    ':miqaat_mauze' => $data[10],
                    ':zone' => $data[11],
                    ':miqaat_india_foreign' => $data[12],
                    ':miqaat_jamiat' => $data[13],
                    ':miqaat_ilaqa' => $data[14],
                    ':farmaan_seen' => $data[15],
                    ':jamea_degree' => $data[16],
                    ':jamea_std' => $data[17],
                    ':is_musaid' => $data[18]
                ]);
                $rowCount++;
            } catch (Exception $e) {
                // Collect error for specific row
                $errors[] = "Line {$lineNumber}: " . $e->getMessage();
                error_log("Error in CSV import at line {$lineNumber}: " . $e->getMessage());
            }
            
            $lineNumber++;
        }

        // If there are lines with empty ITS ID, group them in a single error message
        if (!empty($emptyItsIdLines)) {
            $errors[] = "Lines " . implode(', ', $emptyItsIdLines) . " not uploaded: Rows have empty ITS ID. ITS ID is required.";
        }

        // Only commit if we have successful rows
        if ($rowCount > 0) {
            $pdo->commit();
            return [
                'rowCount' => $rowCount,
                'errors' => $errors
            ];
        } else {
            // Explicitly roll back if no rows were inserted
            $pdo->rollBack();
            throw new Exception("No valid data rows found in the CSV file.");
        }
    } catch (Exception $e) {
        // Make sure to roll back if any error occurs in the main process
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fclose($handle);
        throw $e;
    }
    
    fclose($handle);
}

// Process POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Database configuration
        $dbConfig = [
            'host' => $host,
            'dbName' => $db,
            'username' => $user,
            'password' => $password,
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ];
        // Check if file is uploaded
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Please upload a valid CSV file.");
        }
        // Validate file format
        if (!validateCsvFile($_FILES['csv_file'])) {
            throw new Exception("Invalid file format. Please upload a CSV file.");
        }
        // Validate CSV header
        if (!validateCsvHeader($_FILES['csv_file'])) {
            throw new Exception("Invalid CSV format. The CSV file must include headers in the first row with all required fields. Please download the sample CSV for reference.");
        }
        // Establish database connection
        $pdo = connectToDatabase($dbConfig);
        
        // Handle CSV insertion
        $result = handleCsvInsertion($pdo, $_FILES['csv_file']);
        
        // Set success message
        $message['text'] = "Data successfully updated. {$result['rowCount']} records imported.";
        $message['tag'] = 'success';
        
        // If there were errors, append them to the message
        if (!empty($result['errors'])) {
            $message['text'] .= "<br><br><strong>The following errors were encountered:</strong><ul>";
            foreach ($result['errors'] as $error) {
                $message['text'] .= "<li>" . htmlspecialchars($error) . "</li>";
            }
            $message['text'] .= "</ul>";
            
            // If we have both successes and errors, use warning tag
            if ($result['rowCount'] > 0) {
                $message['tag'] = 'warning';
            }
        }
        
    } catch (Exception $e) {
        // Safely try to rollback transaction in case of error
        if (isset($pdo)) {
            try {
                // Only rollback if there's actually a transaction active
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            } catch (PDOException $pdoEx) {
                // Silently handle rollback errors - we're already in an error state
                error_log("Rollback error: " . $pdoEx->getMessage());
            }
        }
        $message['text'] = "Error: " . $e->getMessage();
        $message['tag'] = 'danger';
    }
}

// Fetch data from the database
$query = "SELECT * FROM `users_mamureen`";
$result = mysqli_query($mysqli, $query);
$data = $result->fetch_all(MYSQLI_ASSOC);
?>
<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/../inc/messages.php'); ?>
            <div class="card">
                <div class="card-body" style="overflow-y: auto;">
                    <h5 class="card-title">Manage Mamureen</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body" style="padding: 10px;">
                                    <div class="d-flex justify-content-between mb-3">
                                        <button id="addUserBtn" class="btn btn-primary">Upload CSV</button>
                                        <a href="https://www.talabulilm.com/mamureen/assets/sample.csv" class="btn btn-success" download>Download Sample CSV</a>
                                    </div>
                                    <form id="addUserForm" style="display: none;" action="" method="post" enctype="multipart/form-data">
                                        <div class="alert alert-info">
                                            <strong>Note:</strong> CSV file must include headers in the first row. ITS ID is required for all records.
                                            <br>
                                            Records with errors will be skipped, but valid records will still be processed.
                                        </div>
                                        <label for="csv_file">Upload CSV file:</label>
                                        <input type="file" name="csv_file" accept=".csv" id="csv_file" required>
                                        <button type="submit" class="btn btn-primary">Upload</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <h5 class="card-title">List of Mamureen</h5>
                            <table class="table table-border cell-border" id="datatable_manage_mamureen">
                                <!-- Table Headers -->
                                <thead>
                                    <tr>
                                        <th scope="col">its_id</th>
                                        <th scope="col">Photo</th>
                                        <th scope="col">fullname</th>
                                        <!-- <th scope="col">fullname_ar</th> -->
                                        <th scope="col">avs_idara</th>
                                        <th scope="col">email_id</th>
                                        <th scope="col">mobile</th>
                                        <th scope="col">mauze_type_name</th>
                                        <!-- <th scope="col">khidmat_type_name</th> -->
                                        <!-- <th scope="col">miqaat_name</th> -->
                                        <th scope="col">kg_age</th>
                                        <th scope="col">miqaat_mauze</th>
                                        <th scope="col">miqaat_jamiat</th>
                                        <th scope="col">Zone</th>
                                    </tr>
                                </thead>
                                <!-- Table Body -->
                                <tbody>
                                    <?php foreach ($data as $admin) : ?>
                                        <tr>
                                            <td><?= $admin['its_id'] ?></td>
                                            <td><img style="max-width:70px" src="https://www.talabulilm.com/mumin_images/<?= $admin['its_id'] ?>.png" alt="Profile" class="rounded-circle"></td>
                                            <td><?= $admin['fullname'] ?></td>
                                            <td><?= $admin['avs_idara'] ?></td>
                                            <td><?= $admin['email_id'] ?></td>
                                            <td><?= $admin['mobile'] ?></td>
                                            <td><?= $admin['mauze_type_name'] ?></td>
                                            <td><?= $admin['kg_age'] ?></td>
                                            <td><?= $admin['miqaat_mauze'] ?></td>
                                            <td><?= $admin['miqaat_jamiat'] ?></td>
                                            <td><?= $admin['zone'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<script>
$(document).ready(function () {
    $('#datatable_manage_mamureen').DataTable( {
      dom: 'Bfrtip',
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, 'All']
      ],
      buttons: [
        {
          extend: 'copy',
          filename: function() {
              return 'mamureen_list -'  + Date.now();
          },
          exportOptions: {
            columns: ':not(:nth-child(2))'
          }
        },
        {
          extend: 'csv',
          filename: function() {
              return 'mamureen_list -'  + Date.now();
          },
          exportOptions: {
            columns: ':not(:nth-child(2))'
          }
        },
        {
          extend: 'excel',
          filename: function() {
              return 'mamureen_list -'  + Date.now();
          },
          exportOptions: {
            columns: ':not(:nth-child(2))'
          }
        }
      ]
    });
});
</script>
<?php
require_once(__DIR__ . '/../inc/footer.php');
require_once(__DIR__ . '/../inc/js-block.php');
?>
</body>
</html>

<script>
    // Function to validate file size before form submission
    function validateFileSize() {
        var fileInput = document.getElementById('csv_file');
        if (fileInput.files.length > 0) {
            var fileSize = fileInput.files[0].size;
            var maxSize = 10 * 1024 * 1024; // 10 MB in bytes
            if (fileSize > maxSize) {
                var fieldName = fileInput.getAttribute('name');
                alert(fieldName + ' file size must be less than 10 MB.');
                return false; // Prevent form submission
            }
        }
        return true; // Proceed with form submission
    }

    // Add event listener to form submission
    document.getElementById('addUserForm').addEventListener('submit', function(event) {
        if (!validateFileSize()) {
            event.preventDefault(); // Prevent default form submission
        }
    });
    
    // Add User Button Click Event
    document.getElementById('addUserBtn').addEventListener('click', function () {
        // Toggle the visibility of the add user form
        var form = document.getElementById('addUserForm');
        form.style.display = form.style.display === 'block' ? 'none' : 'block';
    });
</script>