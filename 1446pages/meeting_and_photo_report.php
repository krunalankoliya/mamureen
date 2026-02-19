<?php
require_once(__DIR__ . '/session.php');
$current_page = 'meeting_and_photo_reports';

require_once(__DIR__ . '/inc/header.php');

if (isset($_POST['submit'])) {
    /***************For Image Upload Code STRART*******************/
    $errors     = array();
    $uploaded_file_count = 0;
    $haazreen_list = $uploadFileName1 = $uploadFileName2 =  $uploadFileName3 ='';

    function prossesFile($fileTag, $user_its)
    {
        $maxsize    = 4194304;
        $acceptable = array('jpeg', 'jpg', 'png');
        $is_error = false;

        $fileName = $_FILES[$fileTag]['name'];
        $tempPath = $_FILES[$fileTag]["tmp_name"];
        $file_size = $_FILES[$fileTag]['size'];
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

        if (($file_size >= $maxsize)) {
            $errors[] = "$fileTag : File must be less than 4 megabytes.";
            $is_error = true;
        }

        if ((!in_array($fileType, $acceptable))) {
            $errors[] = "$fileTag : Only JPEG, JPG and PNG types are accepted.";
            $is_error = true;
        }

        if (!$is_error) {
            $fileUploadName = $user_its . $fileTag . date('YmdHis') . '.' . $fileType;
            $moved_file = move_uploaded_file($tempPath, __DIR__ . "/user_uploads/" . $fileUploadName);
            if (!$moved_file) {
                $errors[] = "$fileTag : An error occurred while uploading the file.";
                return false;
            }
            return $fileUploadName;
        }
    }

    if (isset($_FILES['upload_photo_1']) && $_FILES['upload_photo_1']['tmp_name'] !== '') {
        $filename = 'upload_photo_1';
        $uploadFileName1 = prossesFile($filename, $user_its);

        if ($uploadFileName1) $uploaded_file_count++;
    }

    if (isset($_FILES['upload_photo_2']) && $_FILES['upload_photo_2']['tmp_name'] !== '') {
        $filename = 'upload_photo_2';
        $uploadFileName2 = prossesFile($filename, $user_its);
        if ($uploadFileName2) $uploaded_file_count++;
    }
    
    if (isset($_FILES['upload_photo_3']) && $_FILES['upload_photo_3']['tmp_name'] !== '') {
        $filename = 'upload_photo_3';
        $uploadFileName3 = prossesFile($filename, $user_its);
        if ($uploadFileName3) $uploaded_file_count++;
    }

    /***************For Image Upload Code END*******************/

    if (empty($errors)) {
       
        $category = mysqli_real_escape_string($mysqli, $_POST['category']);
        
        $upload_photo_1 = $uploadFileName1;
        $upload_photo_2 = $uploadFileName2;
        $upload_photo_3 = $uploadFileName3;

        $query = "INSERT INTO `meeting_and_photo_reports` (`user_its`, `category`, `jamaat`,  `upload_photo_1`, `upload_photo_2`,`upload_photo_3`,  `uploaded_file_count`)
        VALUES ('$user_its', '$category', '$mauze', '$upload_photo_1', '$upload_photo_2', '$upload_photo_3', '$uploaded_file_count')";

        try {
            $result = mysqli_query($mysqli, $query);
            $message['text'] = "Report Added Successfully";
            $message['tag'] = 'success';
        } catch (Exception $e) {
            $message = array('text' => $e->getMessage(), 'tag' => 'danger');
        }
        // $insertQuery = "INSERT INTO `mauze_report` ( `report_date`, `jamaat`, `jamiat`, `seminar_count`, `seminar_attendees_count`) VALUES ( '$date_of_barnamaj','$mauze', '$jamiat', '1', '$haazereen_count')
        // ON DUPLICATE KEY UPDATE `seminar_count` = (`seminar_count`+1), `seminar_attendees_count` = (`seminar_attendees_count`+$haazereen_count)";

        // $result = mysqli_query($mysqli, $insertQuery);
    }
}

$query = "SELECT *  FROM `meeting_and_photo_reports` WHERE `jamaat` = '$mauze'";
$result = mysqli_query($mysqli, $query);
$row = $result->fetch_all(MYSQLI_ASSOC);

$select = '<select id="inputState" class="form-select" name="category" required>
                <option value="" disabled selected hidden>Select Category...</option>
                <option value="Khidmat Guzar LIsan ud Dawat na Sessions Mumineen darmiyan conduct karta hua">Khidmat Guzar LIsan ud Dawat na Sessions Mumineen darmiyan conduct karta hua</option>
                <option value="BQI waaste Banners Display/ Hall Tazyeen">BQI waaste Banners Display/ Hall Tazyeen</option>
                <option value="Mumineen Nasihat Hifzan Tasmee karta hua">Mumineen Nasihat Hifzan Tasmee karta hua</option>
                <option value="Mumineen Lisan ud Dawat ni Activities karta hua">Mumineen Lisan ud Dawat ni Activities karta hua</option>
                <option value="Session ma hazereen sathe interaction">Session ma hazereen sathe interaction</option>
                <option value="Haazereen no aerial view">Haazereen no aerial view</option>
                <option value="Ilqa karnar khidmat guzar no closeup photo">Ilqa karnar khidmat guzar no closeup photo</option>
            </select>';

?>

<main id="main" class="main">

    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/inc/messages.php'); ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Photo Report</h5>
                    <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
                    <ul>
                        <li>Photos with extension .jpg, .jpeg or .png can only be uploaded</li>
                        <li>Maximum allowed photo size is 4 mb</li>
                        <li>All fields marked with <span class="required_star">*</span> are required.</li>
                    </ul>
                    <div class="row">
                        <div class="col-md-6">

                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Upload New Report</h5>
                                    <!-- Horizontal Form -->
                                    <form method="post" enctype="multipart/form-data">
                                         <div class="row mb-3">
                                            <label for="inputEmail3" class="col-sm-4 col-form-label">Select Photo Report Category<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <?= $select ?>
                                            </div>
                                        </div> 
                                        <!--<div class="row mb-3 ">-->
                                        <!--    <label for="title" class="col-sm-4 col-form-label">Title<span class="required_star">*</span></label>-->
                                        <!--    <div class="col-sm-8">-->
                                        <!--        <input type="text" class="form-control" id="title" name='barnamaj' required>-->
                                        <!--    </div>-->
                                        <!--</div>-->

                                        <!--<div class="row mb-3 toggel-class">-->
                                        <!--    <label for="other_details" class="col-sm-4 col-form-label">Other Details<span class="required_star">*</span></label>-->
                                        <!--    <div class="col-sm-8">-->
                                        <!--        <input type="text" class="form-control" id="otherDetails" name='other_details' required>-->
                                        <!--    </div>-->
                                        <!--</div>-->

                                        <!--<div class="row mb-3">-->
                                        <!--    <label for="date_of_barnamaj" class="col-sm-4 col-form-label">Date<span class="required_star">*</span></label>-->
                                        <!--    <div class="col-sm-8">-->
                                        <!--        <input type="date" class="form-control" id="date_of_barnamaj" name="date_of_barnamaj" required>-->
                                        <!--    </div>-->
                                        <!--</div>-->

                                        <!--<div class="row mb-3">-->
                                        <!--    <label for="haazreen_list" class="col-sm-4 col-form-label">Haazereen List</label>-->
                                        <!--    <div class="col-sm-8">-->
                                                <!--<input type="text" class="form-control" id="inputPassword" name='haazereen_count' required>-->
                                        <!--        <input type="file" class="form-control file" accept=".csv" name="haazreen_list" id="haazreen_list">-->
                                        <!--    </div>-->
                                        <!--</div>-->

                                        <!--<div class="row mb-3">-->
                                        <!--    <label for="haazereen_count" class="col-sm-4 col-form-label">Haazereen Count<span class="required_star">*</span></label>-->
                                        <!--    <div class="col-sm-8">-->
                                        <!--        <input type="text" class="form-control" id="haazereen_count" name='haazereen_count' required>-->
                                        <!--    </div>-->
                                        <!--</div>-->

                                        <!--<div class="row mb-3 ">-->
                                        <!--    <label for="venue" class="col-sm-4 col-form-label">Venue<span class="required_star">*</span></label>-->
                                        <!--    <div class="col-sm-8">-->
                                        <!--        <input type="text" class="form-control" id="venue" name='venue' required>-->
                                        <!--    </div>-->
                                        <!--</div>-->

                                        <!--<div class="row mb-3">-->
                                        <!--    <label for="meeting_details" class="col-sm-4 col-form-label">Details<span class="required_star">*</span></label>-->
                                        <!--    <div class="col-sm-8">-->
                                        <!--        <textarea class="form-control" name="meeting_details" id="meeting_details" style="height: 150px;" spellcheck="false" requierd></textarea>-->
                                        <!--    </div>-->
                                        <!--</div>-->

                                        <div class="row mb-3">
                                            <label for="file1" class="col-sm-4 col-form-label">Upload Photo 1<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="file" class="form-control file" accept="image/*" name="upload_photo_1" id="inputPassword" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="inputPassword3" class="col-sm-4 col-form-label">Upload Photo 2</label>
                                            <div class="col-sm-8">
                                                <input type="file" class="form-control file" accept="image/*" name="upload_photo_2" id="inputPassword" >
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="inputPassword3" class="col-sm-4 col-form-label">Upload Photo 3</label>
                                            <div class="col-sm-8">
                                                <input type="file" class="form-control file" accept="image/*" name="upload_photo_3" id="inputPassword">
                                            </div>
                                        </div>

                                        <!--<div class="row mb-3">-->
                                        <!--    <label for="inputPassword3" class="col-sm-4 col-form-label">Upload Photo 4</label>-->
                                        <!--    <div class="col-sm-8">-->
                                        <!--        <input type="file" class="form-control file" accept="image/*" name="upload_photo_4" id="inputPassword">-->
                                        <!--    </div>-->
                                        <!--</div>-->

                                        <!--<div class="row mb-3">-->
                                        <!--    <label for="inputPassword3" class="col-sm-4 col-form-label">Upload Photo 5</label>-->
                                        <!--    <div class="col-sm-8">-->
                                        <!--        <input type="file" class="form-control file" accept="image/*" name="upload_photo_5" id="inputPassword">-->
                                        <!--    </div>-->
                                        <!--</div>-->

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="text-center">
                                                    <div class="d-grid gap-2">
                                                        <input type="reset" value="Reset Form" name="reset" class="btn btn-outline-secondary" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-center">
                                                    <div class="d-grid gap-2">
                                                        <input type="submit" value="Submit" name="submit" class="btn btn-primary" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form><!-- End Horizontal Form -->

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6"  style="overflow-y: auto;">
                            <h5 class="card-title">Previously Uploaded Reports</h5>
                            <table class="table table-striped" id="datatable10">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Photo Report Category</th>
                                        <th scope="col">Uploads</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($row as $key => $data) {
                                    ?>
                                            <tr>
                                                <th scope="row"><?= $data['id'] ?></th>
                                                <td><?= $data['category'] ?></td>
                                                <td><?= $data['uploaded_file_count'] ?></td>
                                            </tr>
                                    <?php
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

</main><!-- End #main -->

<?php
require_once(__DIR__ . '/inc/footer.php');
?>
<script>
      function validateFileSize() {
        var fileInputs = document.querySelectorAll('input[type="file"]');
        var maxSize = 4 * 1024 * 1024; // 4 MB in bytes

        for (var i = 0; i < fileInputs.length; i++) {
            var fileInput = fileInputs[i];
            if (fileInput.files.length > 0) {
                var fileSize = fileInput.files[0].size;
                if (fileSize > maxSize) {
                    var fieldName = fileInput.getAttribute('name');
                    alert('Upload ' + fieldName + ' File size must be less than 4 MB.');
                    return false; // Prevent form submission
                }
            }
        }
        return true; // Proceed with form submission
    }

    // Add event listener to form submission
    document.querySelector('form').addEventListener('submit', function(event) {
        if (!validateFileSize()) {
            event.preventDefault(); // Prevent default form submission
        }
    });
    $('#inputState').on('change', function() {
        var element_type = $('#inputState').val();
        getElementFieldsByType(element_type);
    });

    $('#otherDetails').hide();
    $(".toggel-class").addClass("d-none");
    $('#otherDetails').removeAttr('required');

    function getElementFieldsByType(element_type) {
        if (element_type == '<?php echo '1'; ?>') {
            $('#otherDetails').show(600);
            $(".toggel-class").removeClass("d-none");
            $('#otherDetails').attr("required", true);
        } else {
            $('#otherDetails').hide();
            $(".toggel-class").addClass("d-none");
            $('#otherDetails').removeAttr('required');
        }
    }

$(document).ready(function () {
    $('#datatable10').DataTable( {
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
              return 'meeting_and_photo_reports -'  + Date.now();
          }
        },
        {
          extend: 'csv',
          filename: function() {
              return 'meeting_and_photo_reports -'  + Date.now();
          }
        },
        {
          extend: 'excel',
          filename: function() {
              return 'meeting_and_photo_reports -'  + Date.now();
          }
        }
      ]
    });
});
</script>