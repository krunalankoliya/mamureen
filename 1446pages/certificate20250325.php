<?php
require_once __DIR__ . '/session.php';
// Uncomment if you want to restrict access
// require_once __DIR__ . '/requires_login.php';

// Get parameters
$code = $_GET['code'];

$query = "SELECT * FROM `hifz_nasaih` WHERE `code` = '$code'";

$result = $mysqli->query($query);
$data = $result ? $result->fetch_assoc(): [];

$its_id = $data['its_id'];
$jamaat = $data['jamaat'];
$gender = $data['gender'];
$name = $data['full_name_ar'];
$date = date("d/m/Y", strtotime($data['added_ts']));


// Validate parameters
if (empty($code)) {
    die('Missing required parameters');
}

// Determine certificate image based on gender
$certificateImage = $gender === 'F' 
    ? 'certificate_female.jpg' 
    : 'certificate_male.jpg';


// Check if user exists in the BQI 17 Session Attendees
$query = "SELECT * FROM `hifz_nasaih` WHERE `its_id` = '$its_id'";
$result = $mysqli->query($query);
$attendee = $result ? $result->fetch_assoc() : null;

if (!$attendee) {
    die('Attendee not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>hifz_nasaih Certificate</title>
    <meta charset="UTF-8">
    <link rel="apple-touch-icon" sizes="72x72" href="https://www.talabulilm.com/images/favicons/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="https://www.talabulilm.com/images/favicons/apple-touch-icon-114x114.png" />
    <script src="assets/js/html2canvas.min.js"></script>
    <style>
        @font-face {
            font-family: 'kanz';
            src: url('/fonts/Kanz-al-Marjaan.ttf');
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        html, body {
            height: 100%;
            width: 100%;
            overflow: hidden;
        }
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        @media print {
            html, body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                overflow: hidden;
            }
            body * {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'kanz';
            background-color: #f0f0f0;
        }
        .cert-wrapper {
            width: 277mm;  /* Reduced to account for 10mm margins on each side */
            height: 190mm; /* Reduced to account for 10mm margins on top and bottom */
            position: relative;
            overflow: hidden;
        }
        .cert-container {
            position: relative;
            width: 100%;
            height: 100%;
        }
        .img-cert {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .cert-name {
            position: absolute;
            right: 25%;
            top: 45%;
            font-weight: 500;
            font-size: 24px;
            font-family: 'kanz';
            transform: translateY(-50%);
        }
        .cert-date {
            position: absolute;
            right: 85%;
            top: 91.5%;
            letter-spacing: 1px;
            font-weight: 300;
            font-size: 18px;
            font-family: 'kanz';
        }
        .cert-its {
            position: absolute;
            right: 10%;
            top: 91.5%;
            letter-spacing: 1px;
            font-weight: 300;
            font-size: 18px;
            font-family: 'kanz';
        }
        .btn-container {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 20px;
        }
        .btn {
            padding: 10px 20px;
            min-width: 140px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            border: 4px solid #2b6a2b;
        }
        .btn-outline {
            background-color: transparent;
            color: #2b6a2b;
        }
        .btn-primary {
            background-color: #2b6a2b;
            color: white;
        }
        @media print {
            .btn-container {
                display: none;
            }
        }
    </style>
</head>
<body dir="rtl">
    <div class="cert-wrapper">
        <div class="cert-container" id="certificate-container">
            <img class="img-cert" src="https://www.talabulilm.com/mamureen/certificates/<?= $certificateImage ?>" alt="Certificate"/>
            <div class="cert-name"><?= htmlspecialchars($name) ?></div>
            <div class="cert-date"><?= $date ?></div>
            <div class="cert-its"><?= $jamaat .' - '. $its_id ?></div>
        </div>
        <div class="btn-container">
            <button onclick="printCertificate()" class="btn btn-primary">Print</button>
            <button onclick="downloadCertificate()" class="btn btn-outline">Download</button>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            convertToCanvasAndReplace();
        });

        function convertToCanvasAndReplace() {
            html2canvas(document.getElementById('certificate-container'), {
                scrollY: -window.scrollY,
                windowHeight: document.getElementById('certificate-container').scrollHeight,
                scale: 2 // Increased scale for better quality
            }).then(function(canvas) {
                var certificateContainer = document.getElementById('certificate-container');
                certificateContainer.innerHTML = '';
                certificateContainer.appendChild(canvas);
            });
        }

        function downloadCertificate() {
            var certificateCanvas = document.getElementById('certificate-container').querySelector('canvas');
            
            if (certificateCanvas) {
                // Create a temporary link element
                var downloadLink = document.createElement('a');
                downloadLink.download = 'BQI_Certificate_<?= str_replace(' ', '_', $name) ?>.png';
                
                // Convert canvas to data URL
                var dataURL = certificateCanvas.toDataURL('image/png');
                downloadLink.href = dataURL;
                
                // Append to body, click and remove
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            } else {
                alert('Canvas not available. Please try again.');
            }
        }

        function printCertificate() {
            window.print();
        }
    </script>
</body>
</html>