<?php
require_once(__DIR__ . '/session.php');
$current_page = 'Closure_Report';
require_once(__DIR__ . '/inc/header.php');

// Check if user has already uploaded a report
$checkQuery = "SELECT * FROM `closure_report` WHERE `its_id` = '$user_its'";
$checkResult = mysqli_query($mysqli, $checkQuery);
$existingReport = mysqli_fetch_assoc($checkResult);

// Traditional form submission is kept for backward compatibility
if (isset($_POST['submit']) && isset($_FILES['upload_report']) && $_FILES['upload_report']['error'] === 0) {
    /***************For File Upload Code START*******************/
    $tempPath = $_FILES['upload_report']["tmp_name"];
    $fileName = str_replace(' ', '_', $_FILES['upload_report']['name']);
    $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
    
    // Check if file is a PDF
    if (strtolower($fileType) !== 'pdf') {
        $message = array('text' => "Only PDF files are allowed", 'tag' => 'danger');
    } else {
        $fileUploadName = $user_its . '_closure_report_' . time() . '.' . $fileType;
        $moved_file = move_uploaded_file($tempPath, __DIR__ . "/user_report_uploads/" . $fileUploadName);

        if ($moved_file) {
            if ($existingReport) {
                // Update existing record
                $oldFile = $existingReport['upload_report'];
                $oldFilePath = __DIR__ . "/user_report_uploads/" . $oldFile;
                
                // Delete old file if it exists
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
                
                $query = "UPDATE `closure_report` SET `upload_report` = '$fileUploadName' WHERE `its_id` = '$user_its'";
            } else {
                // Insert new record
                $query = "INSERT INTO `closure_report` (`its_id`, `upload_report`) VALUES ('$user_its', '$fileUploadName')";
            }

            try {
                $result = mysqli_query($mysqli, $query);
                $message['text'] = "Report Uploaded Successfully";
                $message['tag'] = 'success';
                
                // Refresh the existing report data
                $checkResult = mysqli_query($mysqli, $checkQuery);
                $existingReport = mysqli_fetch_assoc($checkResult);
            } catch (Exception $e) {
                $message = array('text' => $e->getMessage(), 'tag' => 'danger');
            }
        } else {
            $message = array('text' => "Failed to upload file", 'tag' => 'danger');
        }
    }
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Closure Report</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Closure Report</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/inc/messages.php'); ?>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body pt-4">
                                    <h5 class="card-title">Closure Report</h5>
                                    
                                    <?php if (!$existingReport): ?>
                                    <!-- User doesn't have a report yet, show upload form -->
                                    <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
                                    <ul>
                                        <li>Download the sample closure report and fill in the required details.</li>
                                        <li>Rename the completed file using the format: "BQI_Closure_YourMauze". (For example, if your Mauze is "Saifee Mohallah Nagpur," name your file as BQI_Closure_SaifeeMohallahNagpur.pdf).</li>
                                        <li>Upload your completed report in PDF format only using the upload option provided below.</li>
                                        <li>Once uploaded, you will not be able to submit another file; instead, a link to download your uploaded file will appear.</li>
                                    </ul>
                                    
                                    <div class="mb-4">
                                        <a href="https://www.talabulilm.com/mamureen/user_report_uploads/closure_report_format_checked.docx" class="btn btn-info">
                                            <i class="bi bi-download"></i> Download Sample Report Template
                                        </a>
                                    </div>
                                    
                                    <!-- Horizontal Form -->
                                    <form method="post" id="reportUploadForm" enctype="multipart/form-data">
                                        <div class="row mb-3">
                                            <label for="uploadFile" class="col-sm-4 col-form-label">Upload Report (PDF Only)<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="file" class="form-control file" name="upload_report" id="uploadFile" accept=".pdf" required>
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

                                        <div class="row">
                                            <div class="col-md-8"></div>
                                            <div class="col-md-2">
                                                <div class="text-center">
                                                    <div class="d-grid gap-2">
                                                        <input type="reset" value="Reset" name="reset" id="resetBtn" class="btn btn-outline-secondary" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="text-center">
                                                    <div class="d-grid gap-2">
                                                        <input type="button" value="Upload Report" name="submit" id="submitBtn" class="btn btn-primary" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form><!-- End Horizontal Form -->
                                    
                                    <?php else: ?>
                                    <!-- User already has a report, show download option -->
                                    <div class="alert alert-success">
                                        <p>You have already uploaded a closure report.</p>
                                    </div>
                                    
                                    <div class="d-flex justify-content-center gap-3">
                                        <!--<a href="user_report_uploads/<?php echo $existingReport['upload_report']; ?>" class="btn btn-primary" target="_blank">-->
                                        <!--    <i class="bi bi-file-earmark-pdf"></i> View Your Closure Report-->
                                        <!--</a>-->
                                        <a href="user_report_uploads/<?php echo $existingReport['upload_report']; ?>" class="btn btn-success" download>
                                            <i class="bi bi-download"></i> Download Your Closure Report
                                        </a>
                                        <!--<a href="https://www.talabulilm.com/mamureen/user_report_uploads/closure_report_format_checked.docx" class="btn btn-info">-->
                                        <!--    <i class="bi bi-download"></i> Download Sample Report Template-->
                                        <!--</a>-->
                                    </div>
                                    
                                    <div class="mt-4">
                                        <p>If you need to upload a new version of your report, please contact the administrator.</p>
                                    </div>
                                    
                                    <?php endif; ?>
                                </div>
                            </div>
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
<style>
.dashboard{
    height:75vh;
}
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
.required_star {
    color: red;
    margin-left: 3px;
}
</style>

<script>
// Function to validate file size and type before form submission
function validateFile() {
    var fileInput = document.getElementById('uploadFile');
    if (fileInput.files.length > 0) {
        // Check file size
        var fileSize = fileInput.files[0].size;
        var maxSize = 200 * 1024 * 1024; // 200 MB in bytes
        if (fileSize > maxSize) {
            alert('Uploaded file size must be less than 200 MB.');
            return false;
        }
        
        // Check file type
        var fileName = fileInput.files[0].name;
        var fileExt = fileName.split('.').pop().toLowerCase();
        if (fileExt !== 'pdf') {
            alert('Only PDF files are allowed.');
            return false;
        }
    }
    return true;
}

// Form submission handler
// Chunked upload implementation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById("reportUploadForm" );
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
        if (!validateFile()) {
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
                                    showSuccessMessage('<strong>Success!</strong> Report "' + fileName + '" uploaded successfully.');
                                    
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
// document.addEventListener('DOMContentLoaded', function() {
//     const form = document.getElementById('reportUploadForm');
//     const fileInput = document.getElementById('uploadFile');
//     const submitBtn = document.getElementById('submitBtn');
//     const resetBtn = document.getElementById('resetBtn');
//         const progressContainer = document.getElementById('progressContainer');         
//     const progressBar = document.getElementById('progressBar');
//     const uploadedSizeEl = document.getElementById('uploadedSize');
//     const totalSizeEl = document.getElementById('totalSize');
//     const uploadMessage = document.getElementById('uploadMessage');
    
//     if (submitBtn) {
//         submitBtn.addEventListener('click', function(e) {
//             // Basic form validation
//             if (!form.checkValidity()) {
//                 form.reportValidity();
//                 return;
//             }
            
//             const file = fileInput.files[0];
//             if (!file) {
//                 alert('Please select a file to upload');
//                 return;
//             }
            
//             // Validate file type and size
//             if (!validateFile()) {
//                 return;
//             }
            
//             // Show progress elements
//             progressContainer.classList.remove('d-none');
//             uploadMessage.classList.remove('d-none');
            
//             // Disable buttons during upload
//             submitBtn.disabled = true;
//             if (resetBtn) resetBtn.disabled = true;
//             submitBtn.value = 'Uploading...';
            
//             // Create a new FormData instance
//             const formData = new FormData(form);
//             formData.append('submit', 'true');
            
//             // Display total file size
//             const totalSizeMB = (file.size / (1024 * 1024)).toFixed(2);
//             totalSizeEl.textContent = totalSizeMB;
            
//             // Send the form data
//             const xhr = new XMLHttpRequest();
//             xhr.open('POST', window.location.href, true);
            
//             xhr.upload.onprogress = function(e) {
//                 if (e.lengthComputable) {
//                     const percentComplete = Math.round((e.loaded / e.total) * 100);
                    
//                     progressBar.style.width = percentComplete + '%';
//                     progressBar.textContent = percentComplete + '%';
//                     progressBar.setAttribute('aria-valuenow', percentComplete);
                    
//                     const uploadedMB = (e.loaded / (1024 * 1024)).toFixed(2);
//                     uploadedSizeEl.textContent = uploadedMB;
                    
//                     // Update the waiting message based on progress
//                     if (percentComplete < 50) {
//                         uploadMessage.textContent = 'Upload in progress, please wait...';
//                     } else if (percentComplete < 90) {
//                         uploadMessage.textContent = 'Still uploading, almost there...';
//                     } else {
//                         uploadMessage.textContent = 'Finalizing upload, please don\'t close this page...';
//                     }
//                 }
//             };
            
//             xhr.onload = function() {
//                 if (xhr.status === 200) {
//                     uploadMessage.textContent = 'Upload complete!';
//                     uploadMessage.style.color = 'green';
                    
//                     // Reload the page to show updated content
//                     setTimeout(function() {
//                         window.location.reload();
//                     }, 1000);
//                 } else {
//                     uploadMessage.textContent = 'Upload error: ' + xhr.status;
//                     uploadMessage.style.color = 'red';
//                     resetUploadForm();
//                 }
//             };
            
//             xhr.onerror = function() {
//                 uploadMessage.textContent = 'Network error during upload';
//                 uploadMessage.style.color = 'red';
//                 resetUploadForm();
//             };
            
//             xhr.timeout = 180000; // 3 minutes timeout
//             xhr.ontimeout = function() {
//                 uploadMessage.textContent = 'Upload timed out. Please check your internet connection and try again.';
//                 uploadMessage.style.color = 'red';
//                 resetUploadForm();
//             };
            
//             xhr.send(formData);
//         });
//     }
    
//     // Reset form handler
//     if (resetBtn) {
//         resetBtn.addEventListener('click', function() {
//             resetUploadForm();
//         });
//     }
    
//     function resetUploadForm() {
//         if (submitBtn) submitBtn.disabled = false;
//         if (resetBtn) resetBtn.disabled = false;
//         if (submitBtn) submitBtn.value = 'Submit';
//         uploadMessage.style.color = '#0d6efd';
//         uploadMessage.classList.add('d-none');
//         progressContainer.classList.add('d-none');
//         progressBar.style.width = '0%';
//         progressBar.textContent = '0%';
//         uploadedSizeEl.textContent = '0';
//         totalSizeEl.textContent = '0';
//     }
// });
</script>