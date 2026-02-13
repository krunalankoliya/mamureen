<?php
require_once(__DIR__ . '/../session.php');
$current_page = 'Manage_Resources';
require_once(__DIR__ . '/../inc/header.php');

if (isset($_POST['update'])) {
    $title = $_POST['title'];
    $id = $_POST['id'];

    $query = "UPDATE `my_downloads` SET `title` = '$title' WHERE `id` = '$id'";
    try {
        $result = mysqli_query($mysqli, $query);
        $message['text'] = "Update Record Successfully";
        $message['tag'] = 'success';
    } catch (Exception $e) {
        $message = array('text' => $e->getMessage(), 'tag' => 'danger');
    }
}


// Traditional form submission is kept for backward compatibility
if (isset($_POST['submit']) && isset($_FILES['upload_photo']) && $_FILES['upload_photo']['error'] === 0) {
    /***************For Image Upload Code STRART*******************/

    $tempPath = $_FILES['upload_photo']["tmp_name"];
    $fileName = str_replace(' ', '_', $_FILES['upload_photo']['name']);
    $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
    $fileUploadName = $fileName;
    $moved_file = move_uploaded_file($tempPath, __DIR__ . "/../uploads/" . $fileName);

    /***************For Image Upload Code END*******************/
    $title = $_POST['title'];
    $dot_color = $_POST['dot_color'];
    $date = date('Y-m-d');
    $upload_photo = $fileName;

    $query = "INSERT INTO `my_downloads` (`upload_date`, `title`, `file_name`, `dot_color`, `added_its`) VALUES ('$date', '$title', '$upload_photo', '$dot_color', '$user_its')";

    try {
        $result = mysqli_query($mysqli, $query);
        $message['text'] = "File Upload Successfully";
        $message['tag'] = 'success';
    } catch (Exception $e) {
        $message = array('text' => $e->getMessage(), 'tag' => 'danger');
    }
}


if (isset($_POST['delete'])) {
    $id = $_POST['id'];

    // Fetch the file name before deleting
    $fetchQuery = "SELECT file_name FROM my_downloads WHERE id = '$id'";
    $fetchResult = mysqli_query($mysqli, $fetchQuery);
    $fileData = mysqli_fetch_assoc($fetchResult);
    
    if ($fileData) {
        $filePath = __DIR__ . "/../uploads/" . $fileData['file_name'];
        
        // Delete file from the upload folder
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete the database entry
        $deleteQuery = "DELETE FROM my_downloads WHERE id = '$id'";
        try {
            $result = mysqli_query($mysqli, $deleteQuery);
            $message['text'] = "File deleted successfully";
            $message['tag'] = 'success';
        } catch (Exception $e) {
            $message = array('text' => $e->getMessage(), 'tag' => 'danger');
        }
    } else {
        $message = array('text' => "File not fousnd", 'tag' => 'danger');
    }
}


$query = "SELECT * FROM `my_downloads`";
$result = mysqli_query($mysqli, $query);
$row = $result->fetch_all(MYSQLI_ASSOC);

$select = '<select id="inputState" class="form-select" name="dot_color" required>
                <option value="" disabled selected hidden>Select...</option>
                <option value="text-danger">Text Danger</option>
                <option value="text-success">Text Success</option>
                <option value="text-warning">Text Warning</option>
                <option value="text-info">Text Info</option>
            </select>';
?>

<main id="main" class="main">

    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/../inc/messages.php'); ?>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">

                            <div class="card">
                                <div class="card-body pt-4">
                                    <h5 class="card-title">Upload Download Resource</h5>
                                    <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
                                    <ul>
                                        <li>All fields marked with <span class="required_star">*</span> are required.</li>
                                    </ul>
                                    <!-- Horizontal Form -->
                                    <form method="post" id="addUserForm" enctype="multipart/form-data">
                                        <div class="row mb-3">
                                            <label for="inputTitle" class="col-sm-4 col-form-label">Title<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="inputTitle" name='title' required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="uploadFile" class="col-sm-4 col-form-label">Upload file<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="file" class="form-control file" name="upload_photo" id="uploadFile" required>
                                                <!-- Progress bar will be added here via JS -->
                                                <div id="progressContainer" class="mt-2 d-none">
                                                    <div class="progress">
                                                        <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                                    </div>
                                                    <div class="mt-1 small text-muted">
                                                        <span id="uploadedSize">0</span> of <span id="totalSize">0</span> MB uploaded
                                                    </div>
                                                    <div id="uploadMessage" class="mt-2 text-center fw-bold text-primary d-none">
                                                        Upload in progress, please wait...
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="inputState" class="col-sm-4 col-form-label">Select Dot Color<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <?= $select ?>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-8"></div>
                                            <div class="col-md-2">
                                                <div class="text-center">
                                                    <div class="d-grid gap-2">
                                                        <input type="reset" value="Reset Form" name="reset" id="resetBtn" class="btn btn-outline-secondary" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="text-center">
                                                    <div class="d-grid gap-2">
                                                        <input type="button" value="Submit" name="submit" id="submitBtn" class="btn btn-primary" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form><!-- End Horizontal Form -->

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title">Previously Uploaded Reports</h5>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Upload date</th>
                                        <th scope="col">Title</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($row as $key => $data) {
                                    ?>
                                        <form method="post" enctype="multipart/form-data">
                                            <tr>
                                                <th scope="row"><?= $data['id'] ?></th>
                                                <td><?= $data['upload_date'] ?></td>
                                                <td>
                                                    <input type="text" name="title" class="form-control" placeholder="Expertise" value="<?= $data['title'] ?>" required />
                                                </td>
                                                <td>
                                                    <input type="hidden" name="id" value="<?= $data['id'] ?>" />
                                                    <input type="submit" name="update" value="Update" class="btn btn-outline-primary" />
                                                    <input type="submit" name="delete" value="Delete" class="btn btn-outline-danger" onclick="return confirmDelete();" />
                                                </td>
                                            </tr>
                                        </form>
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
require_once(__DIR__ . '/../inc/footer.php');
?>
<style>
.progress {
    height: 25px;
}
.progress-bar {
    line-height: 25px;
    font-weight: bold;
}
#uploadMessage {
    color: #0d6efd;
    font-size: 14px;
}
</style>

<script>
// Function to confirm deletion
function confirmDelete() {
    return confirm("Are you sure you want to delete this file?");
}

// Function to validate file size before form submission
function validateFileSize() {
    var fileInput = document.getElementById('uploadFile');
    if (fileInput.files.length > 0) {
        var fileSize = fileInput.files[0].size;
        var maxSize = 200 * 1024 * 1024; // 200 MB in bytes
        if (fileSize > maxSize) {
            var fieldName = fileInput.getAttribute('name');
            alert('Uploaded file size must be less than 200 MB.');
            return false; // Prevent form submission
        }
    }
    return true; // Proceed with form submission
}

// Chunked upload implementation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addUserForm');
    const fileInput = document.getElementById('uploadFile');
    const submitBtn = document.getElementById('submitBtn');
    const resetBtn = document.getElementById('resetBtn');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const uploadedSizeEl = document.getElementById('uploadedSize');
    const totalSizeEl = document.getElementById('totalSize');
    const uploadMessage = document.getElementById('uploadMessage');
    
    // Function to show global success message
    function showSuccessMessage(message) {
        // Create success message container if it doesn't exist
        let messageContainer = document.querySelector('.message-container');
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.className = 'message-container alert alert-success alert-dismissible fade show';
            messageContainer.role = 'alert';
            
            // Add the message container after the messages include
            const messagesSection = document.querySelector('.section.dashboard .row');
            if (messagesSection) {
                messagesSection.insertBefore(messageContainer, messagesSection.firstChild);
            }
        }
        
        // Set message content with dismiss button
        messageContainer.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Scroll to top to show the message
        window.scrollTo(0, 0);
        setTimeout(() => {
            window.location.reload();
        }, 1000);
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(messageContainer);
            bsAlert.close();
        }, 1000);
    }
    
    // Convert traditional form submit button to a button
    submitBtn.addEventListener('click', function(e) {
        // Basic form validation
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const file = fileInput.files[0];
        if (!file) {
            alert('Please select a file to upload');
            return;
        }
        
        // Basic validation
        if (!validateFileSize()) {
            return;
        }
        
        // Show progress elements and waiting message
        progressContainer.classList.remove('d-none');
        uploadMessage.classList.remove('d-none');
        
        // Disable buttons during upload
        submitBtn.disabled = true;
        resetBtn.disabled = true;
        submitBtn.value = 'Uploading...';
        
        // Get form data except file
        const formData = new FormData();
        formData.append('title', document.getElementById('inputTitle').value);
        formData.append('dot_color', document.getElementById('inputState').value);
        
        // Set up chunked upload
        const chunkSize = 5 * 1024 * 1024; // 5MB chunks for more reliable uploads
        const totalChunks = Math.ceil(file.size / chunkSize);
        const fileName = file.name.replace(/\s+/g, '_');
        let currentChunk = 0;
        let uploadedSize = 0;
        let uploadErrors = 0;
        
        // Display total file size
        const totalSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        totalSizeEl.textContent = totalSizeMB;
        
        // Create a unique upload ID for this file
        const uploadId = Date.now() + '-' + Math.random().toString(36).substring(2, 15);
        
        // Start upload process
        uploadNextChunk();
        
        function uploadNextChunk() {
            const start = currentChunk * chunkSize;
            const end = Math.min(start + chunkSize, file.size);
            const chunk = file.slice(start, end);
            
            // Create chunk form data
            const chunkFormData = new FormData();
            chunkFormData.append('chunk', chunk);
            chunkFormData.append('uploadId', uploadId);
            chunkFormData.append('chunkIndex', currentChunk);
            chunkFormData.append('totalChunks', totalChunks);
            chunkFormData.append('fileName', fileName);
            chunkFormData.append('fileSize', file.size);
            
            // If this is the last chunk, add all other form data
            if (currentChunk === totalChunks - 1) {
                for (const [key, value] of formData.entries()) {
                    chunkFormData.append(key, value);
                }
                chunkFormData.append('finalChunk', '1');
            }
            
            // Send the chunk
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload_chunk.php', true);
            
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const chunkProgress = e.loaded / e.total;
                    const overallProgress = (uploadedSize + (e.loaded * (end - start) / file.size)) / file.size;
                    const percentComplete = Math.round(overallProgress * 100);
                    
                    progressBar.style.width = percentComplete + '%';
                    progressBar.textContent = percentComplete + '%';
                    progressBar.setAttribute('aria-valuenow', percentComplete);
                    
                    const uploadedMB = ((uploadedSize + e.loaded) / (1024 * 1024)).toFixed(2);
                    uploadedSizeEl.textContent = uploadedMB;
                    
                    // Update the waiting message based on progress
                    if (percentComplete < 50) {
                        uploadMessage.textContent = 'Upload in progress, please wait...';
                    } else if (percentComplete < 90) {
                        uploadMessage.textContent = 'Still uploading, almost there...';
                    } else {
                        uploadMessage.textContent = 'Finalizing upload, please don\'t close this page...';
                    }
                }
            };
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            uploadedSize += (end - start);
                            currentChunk++;
                            
                            // Update progress
                            const percentComplete = Math.round((uploadedSize / file.size) * 100);
                            progressBar.style.width = percentComplete + '%';
                            progressBar.textContent = percentComplete + '%';
                            
                            if (currentChunk < totalChunks) {
                                // Upload next chunk
                                uploadNextChunk();
                            } else {
                                // All chunks uploaded successfully
                                if (response.data && response.data.allComplete) {
                                    // Show 100% complete status
                                    progressBar.style.width = '100%';
                                    progressBar.textContent = '100%';
                                    uploadMessage.textContent = 'Upload complete!';
                                    uploadMessage.style.color = 'green';
                                    
                                    // Display a success message instead of an alert
                                    showSuccessMessage('<strong>Success!</strong> File "' + fileName + '" uploaded successfully.');
                                    
                                    // Reset the form after a short delay
                                    setTimeout(() => {
                                        resetUploadForm();
                                        form.reset();
                                        
                                        // Refresh the table without reloading the page
                                        refreshResourceTable();
                                    }, 1000);
                                } else {
                                    uploadMessage.textContent = 'Error finalizing upload: ' + response.message;
                                    uploadMessage.style.color = 'red';
                                    setTimeout(resetUploadForm, 3000);
                                }
                            }
                        } else {
                            uploadErrors++;
                            if (uploadErrors < 3) {
                                // Retry the chunk up to 3 times
                                uploadMessage.textContent = 'Retrying chunk upload... Attempt ' + uploadErrors;
                                setTimeout(uploadNextChunk, 1000);
                            } else {
                                uploadMessage.textContent = 'Error uploading chunk: ' + response.message;
                                uploadMessage.style.color = 'red';
                                setTimeout(resetUploadForm, 3000);
                            }
                        }
                    } catch (e) {
                        uploadMessage.textContent = 'Invalid server response';
                        uploadMessage.style.color = 'red';
                        console.error('Parse error:', e, 'Response:', xhr.responseText);
                        setTimeout(resetUploadForm, 3000);
                    }
                } else {
                    uploadMessage.textContent = 'Upload error: ' + xhr.status;
                    uploadMessage.style.color = 'red';
                    setTimeout(resetUploadForm, 3000);
                }
            };
            
            xhr.onerror = function() {
                uploadMessage.textContent = 'Network error during upload';
                uploadMessage.style.color = 'red';
                setTimeout(resetUploadForm, 3000);
            };
            
            xhr.timeout = 180000; // 3 minutes timeout for each chunk
            xhr.ontimeout = function() {
                uploadMessage.textContent = 'Upload timed out. Please check your internet connection and try again.';
                uploadMessage.style.color = 'red';
                setTimeout(resetUploadForm, 3000);
            };
            
            xhr.send(chunkFormData);
        }
        
        function resetUploadForm() {
            submitBtn.disabled = false;
            resetBtn.disabled = false;
            submitBtn.value = 'Submit';
            uploadMessage.style.color = '#0d6efd'; // Reset color
            uploadMessage.classList.add('d-none');
            progressContainer.classList.add('d-none');
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
            uploadedSizeEl.textContent = '0';
            totalSizeEl.textContent = '0';
        }
        
        // Function to refresh the resources table without reloading the page
        function refreshResourceTable() {
            // Fetch the updated table data with AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('GET', window.location.href, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Parse the HTML response
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(xhr.responseText, 'text/html');
                    
                    // Find the table in the response
                    const newTable = doc.querySelector('.table.table-striped');
                    
                    // Replace the current table with the new one
                    if (newTable) {
                        const currentTable = document.querySelector('.table.table-striped');
                        if (currentTable) {
                            currentTable.innerHTML = newTable.innerHTML;
                        }
                    }
                }
            };
            xhr.send();
        }
    });
    
    // Reset button handler
    resetBtn.addEventListener('click', function() {
        progressContainer.classList.add('d-none');
        uploadMessage.classList.add('d-none');
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';
        submitBtn.disabled = false;
        submitBtn.value = 'Submit';
    });
});
</script>