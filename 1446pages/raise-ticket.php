<?php
$current_page = 'ticket';
require_once(__DIR__ . '/inc/header.php');

if (isset($_POST['submit'])) {
    $purpose = $_POST['purpose'];
    $its_id = (int) $_POST['its_id'];
    $details = $_POST['details'];

    $updateQuery = "INSERT INTO `manage_tickets` (`its_id`, `user_its`, `purpose`, `details`) VALUES ('$its_id', '$user_its', '$purpose', '$details')";
    try {
        $result = mysqli_query($mysqli, $updateQuery);
        $message['text'] = "Ticket Updated Successfully";
        $message['tag'] = 'success';
    } catch (Exception $e) {
        $message = array('text' => $e->getMessage(), 'tag' => 'danger');
    }
} else {
    $message['text'] = "Invalid ITS ID";
    $message['tag'] = 'danger';
}

$expertise_select = '<select id="inputState" class="form-select" name="purpose" required>
                        <option value="" disabled selected hidden>Select Purpose<span class="required_star">*</span></option>
                        <optgroup label="Individual Tickets">
                            <option value="Financial Assistance for Higher Education required">Financial Assistance for Higher Education required</option>
                        </optgroup>
                        <optgroup label="Mauze Tickets">
                            <option value="No Umoor Talimiyyah Coordinator in mauze">No Umoor Talimiyyah Coordinator in mauze</option>
                            <option value="No Umoor Talimiyyah committee team leads in mauze">No Umoor Talimiyyah committee team leads in mauze</option>
                        </optgroup>
                    </select>';

$text_area = '<textarea name="details" required class="form-control" placeholder="Details of ticket*" id="floatingTextarea" style="height: 94px;" spellcheck="false"></textarea>';



?>
<!DOCTYPE html>
<html lang="en">

<body>
    <main id="main" class="main">

        <section class="section dashboard">
            <div class="row">
                <div class="card">
                    <div class="card-body" style="overflow-y: auto;">
                        <h5 class="card-title">Manage Tickets</h5>
                        <div class="row">
                            <div class="col-md-12">

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Add new ticket</h5>
                                        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
                                        <ul>
                                            <li>All fields marked with <span class="required_star">*</span> are required.</li>
                                        </ul>
                                        <!-- Horizontal Form -->
                                        <form class="row g-3 pt-3" method="post" enctype="multipart/form-data">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <?= $expertise_select ?>
                                                        </div>
                                                        <div class="col-md-12 mt-3">
                                                            <input type="number" name="its_id" class="form-control" placeholder="ITS ID *" id="inputText">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <?= $text_area ?>
                                                </div>

                                                <!-- TODO: ACTIVE INACTIVE, REMARKS-->

                                                <div class="col-md-2">
                                                    <div class="text-center">
                                                        <div class="d-grid gap-2">
                                                            <input type="submit" class="btn btn-primary" name="submit" value="Submit">
                                                        </div>
                                                        <div class="d-grid gap-2 mt-3">
                                                            <input type="reset" class="btn btn-outline-secondary" value="Reset Form">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form><!-- End Horizontal Form -->

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <h5 class="card-title">My Submitted Tickets</h5>
                                <table class="table table-striped" id="datatable10">
                                    <thead>
                                        <tr>
                                            <th scope="col">Date</th>
                                            <th scope="col">Purpose</th>
                                            <th scope="col" style="width:70%">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $query = "SELECT * FROM `manage_tickets` WHERE `user_its` = $user_its";
                                            $result=mysqli_query($mysqli,$query);
                                            $row = $result->fetch_all(MYSQLI_ASSOC);

                                            foreach ($row as $key => $data) {
                                                $submit_date = strtotime($data['submit_ts']);
                                                $date = date('Y-m-d',$submit_date);
                                        ?>
                                            <form>
                                                <tr>
                                                    <th scope="row"><?= $date ?></th>
                                                    <td><?= $data['purpose'] ?></td>
                                                    <td><?= $data['details'] ?></td>
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

    <script>
        $('#inputState').change(function() {

            if ($(this).find('option:selected').text() === 'No Umoor Talimiyyah Coordinator in mauze') {
                $('input#inputText').prop('disabled', true);
                $('input#inputText').val(0);
            } else if ($(this).find('option:selected').text() === 'No Umoor Talimiyyah committee team leads in mauze') {
                $('input#inputText').prop('disabled', true);
                $('input#inputText').val(0);
            } else {
                $('input#inputText').prop('disabled', '');
                $('input#inputText').prop("required", true);
            }
        });
        $("input").first().addClass("auto-fill");
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
              return 'raise_ticket -'  + Date.now();
          }
        },
        {
          extend: 'csv',
          filename: function() {
              return 'raise_ticket -'  + Date.now();
          }
        },
        {
          extend: 'excel',
          filename: function() {
              return 'raise_ticket -'  + Date.now();
          }
        }
      ]
    });
});
    </script>

    <?php
    require_once(__DIR__ . '/inc/footer.php');
    ?>