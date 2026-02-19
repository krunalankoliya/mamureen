<?php
require_once __DIR__ . '/session.php';
// Uncomment if you want to restrict access
// require_once __DIR__ . '/requires_login.php';

// Get parameters
$mauze = $_GET['mauze'];

// Query to get multiple certificates
$query = "SELECT * FROM `hifz_nasaih` WHERE `jamaat` = '$mauze'";
$result = $mysqli->query($query);
$data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Validate parameters
if (empty($mauze)) {
    die('Missing required parameters');
}

// Check if we have any results
if (empty($data)) {
    die('No certificates found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>hifz_nasaih Certificates</title>
    <meta charset="UTF-8">
    <link rel="apple-touch-icon" sizes="72x72" href="https://www.talabulilm.com/images/favicons/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="https://www.talabulilm.com/images/favicons/apple-touch-icon-114x114.png" />
    <script src="assets/js/html2canvas.min.js"></script>
    <!-- Add jsPDF library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        @font-face {
            font-family: 'kanz';
            src: url('/fonts/Kanz-al-Marjaan.ttf');
        }
        body, html {
            font-family: 'kanz';
            text-align: center;
        }
        
        .cert-container {
            position: relative;
            width: 100%;
            max-width: 210mm;
            margin: auto;
            page-break-after: always;
        }
        .img-cert {
            width: 100%;
            height: auto;
        }
        .cert-name {
            position: absolute;
            right: 229px;
            top: 230px;
            font-weight: 500;
            font-size: 18px;
            font-family: 'kanz';
        }
        .cert-date {
            position: absolute;
            right: 670px;
            top: 514px;
            letter-spacing:1px;
            font-weight: 300;
            font-size: 14px;
            font-family: 'kanz';
        }
        .cert-its {
            position: absolute;
            right: 60px;
            top: 514px;
            letter-spacing:1px;
            font-weight: 300;
            font-size: 14px;
            font-family: 'kanz';
        }
        .btn {
            margin: 20px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: 4px solid #2b6a2b;
            border-radius: 4px;
        }
        .btn-primary {
            background-color: #2b6a2b;
            color: white;
        }
        @media print {
            .btn { display: none; }
            @page { size: A4; margin: 12mm; }
            .cert-date, .cert-its{
                top:504px;
            }
        }
        
        /* Add loading indicator styles */
        .loading {
            display: none;
            margin: 20px auto;
            text-align: center;
        }
        .loading-spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #2b6a2b;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 10px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body dir="rtl">
    
    <button onclick="printAllCertificates()" class="btn btn-primary">Print All</button>
    <button onclick="downloadAsPDF()" class="btn btn-outline">Download as PDF</button>
    <!--<a href="hifz_new.php" class="btn btn-outline">Back to List</a>-->
    
    <!-- Add loading indicator -->
    <div id="loading" class="loading">
        <div class="loading-spinner"></div>
        <p>Creating PDF, please wait...</p>
    </div>
    
    <?php foreach ($data as $index => $certificate): ?>
        <?php
            $its_id = $certificate['its_id'];
            $jamaat = $certificate['jamaat'];
            $gender = $certificate['gender'];
            $name = $certificate['full_name_ar'];
            $date = date("d/m/Y", strtotime($certificate['added_ts']));
            
            // Determine certificate image based on gender
            $certificateImage = $gender === 'F' 
                ? 'certificate_female.jpg' 
                : 'certificate_male.jpg';
            
            $containerId = "certificate-container-" . $index;
        ?>
        
        <div class="cert-container" id="<?= $containerId ?>">
            <img class="img-cert" src="https://www.talabulilm.com/mamureen/certificates/<?= $certificateImage ?>" alt="Certificate"/>
            <div class="cert-name"><?= htmlspecialchars($name) ?></div>
            <div class="cert-date"><?= $date ?></div>
            <div class="cert-its"><?= $jamaat .' - '. $its_id ?></div>
        </div>
        
    <?php endforeach; ?>
    
    <script>
        // Import jsPDF
        const { jsPDF } = window.jspdf;
    
        function printAllCertificates() {
            window.print();
        }

        async function downloadAsPDF() {
            // Show loading indicator
            document.getElementById('loading').style.display = 'block';
            
            const containers = document.querySelectorAll('.cert-container');
            
            // Create a new PDF document (A4 size in landscape orientation)
            const pdf = new jsPDF({
                orientation: 'landscape',
                unit: 'mm',
                format: 'a4'
            });
            
            // Process each certificate
            for (let i = 0; i < containers.length; i++) {
                const container = containers[i];
                
                // Convert the certificate container to canvas
                const canvas = await html2canvas(container, {
                    scale: 2, // Higher quality
                    useCORS: true,
                    logging: false,
                    windowHeight: container.scrollHeight
                });
                
                // Convert canvas to an image
                const imgData = canvas.toDataURL('image/jpeg', 1.0);
                
                // Add a new page for certificates after the first one
                if (i > 0) {
                    pdf.addPage();
                }
                
                // Add the image to the PDF (centered)
                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const imgWidth = pdfWidth;
                const imgHeight = (imgProps.height * imgWidth) / imgProps.width;
                
                pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, imgHeight);
            }
            
            // Generate and download the PDF
            pdf.save('BQI_Certificates.pdf');
            
            // Hide loading indicator
            document.getElementById('loading').style.display = 'none';
        }
    </script>
</body>
</html>