<?php
// view-certificate.php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

// Get parameters
$id = isset($_GET['id']) ? $_GET['id'] : '';
$name = isset($_GET['name']) ? $_GET['name'] : '';
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';

// Validate parameters
if (empty($id) || empty($name) || empty($gender)) {
    echo "Invalid certificate parameters.";
    exit;
}

$certPath = $gender == 'Female' ? '/mamureen/certificates/certificate_female.png' : '/mamureen/certificates/certificate_male.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - <?php echo htmlspecialchars($name); ?></title>
    <style>
        @font-face {
            font-family: 'kanz';
            src: url('/fonts/Kanz-al-Marjaan.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }

        .certificate-container {
            width: 297mm; /* A4 landscape width */
            height: 210mm; /* A4 landscape height */
            position: relative;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .certificate-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .certificate-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .certificate-name {
            font-family: 'kanz', Arial, sans-serif;
            font-size: 32px;
            color: #000;
            text-align: center;
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            transform: translateY(-50%);
            direction: rtl;
        }

        .controls {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        @media print {
            .controls {
                display: none;
            }
            
            body {
                background-color: white;
            }
            
            .certificate-container {
                box-shadow: none;
                margin: 0;
                width: 100%;
                height: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-page">
        <div class="certificate-container">
            <img src="<?php echo $certPath; ?>" class="certificate-bg" alt="Certificate">
            <div class="certificate-content">
                <div class="certificate-name"><?php echo htmlspecialchars($name); ?></div>
            </div>
        </div>
        <div class="controls">
            <button class="btn" onclick="window.print()">Print Certificate</button>
            <button class="btn" onclick="window.close()">Close</button>
        </div>
    </div>
</body>
</html>
<?php
// Don't include footer for this page
?>