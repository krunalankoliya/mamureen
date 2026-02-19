<?php
require_once __DIR__ . '/session.php';

$mauze = $_GET['mauze'];
// Fetch all attendees
$query = "SELECT * FROM `hifz_nasaih` WHERE `jamaat` = '$mauze'";
$result = $mysqli->query($query);
$attendees = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

if (empty($attendees)) {
    die('No attendees found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>hifz_nasaih Certificates</title>
    <meta charset="UTF-8">
    <link rel="apple-touch-icon" sizes="72x72" href="https://www.talabulilm.com/images/favicons/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="https://www.talabulilm.com/images/favicons/apple-touch-icon-114x114.png" />
    <style>
        @font-face {
            font-family: 'kanz';
            src: url('/fonts/Kanz-al-Marjaan.ttf');
        }
        body {
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
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="btn btn-primary">Print All</button>
    
    <?php foreach ($attendees as $attendee): ?>
        <?php 
        $name = htmlspecialchars($attendee['full_name_ar']);
        $gender = $attendee['gender'];
        $date = date("d/m/Y", strtotime($attendee['added_ts']));
        $its_id = $attendee['its_id'];
        $jamaat = $attendee['jamaat'];
        $certificateImage = ($gender === 'F') ? 'certificate_female.png' : 'certificate_male.png';
        ?>
        <div class="cert-container">
            <img class="img-cert" src="https://www.talabulilm.com/mamureen/certificates/<?= $certificateImage ?>" alt="Certificate"/>
            <div class="cert-name"><?= $name ?></div>
            <div class="cert-date"><?= $date ?></div>
            <div class="cert-its"><?= $jamaat .' - '. $its_id ?></div>
        </div>
    <?php endforeach; ?>
</body>
</html>
