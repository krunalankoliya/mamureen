<div class="annual_file">
    <div class="question">
        <h3>What challenges did you face in data collection - and how did you overcome them?</h3>
        <p><?= $data['data_a1'] ?></p>
    </div>
    <div class="question">
        <h3>How can we achieve 100% data collection of Migrated Out students from your zone before Ashara Mubaraka 1446H?</h3>
        <p><?= $data['data_a2'] ?></p>
    </div>
    <div class="question">
        <h3>Mention innovative sdivategies that you used for acquiring data of elusive students.</h3>
        <p><?= $data['data_a3'] ?></p>
    </div>
    <div class="question">
        <h3>What challenges did you face while conducting house visits - and how did you overcome them?</h3>
        <p><?= $data['mig_in_a1'] ?></p>
    </div>
    <div>
        <h3>How can we complete house visits of all remaining migrated students before Ashara Mubaraka 1446H?</h3>
        <p><?= $data['mig_in_a2'] ?></p>
    </div>
    <div>
        <h3>What Silat/Hadiya were presented to students by local jamaat?</h3>
        <p><?= $data['mig_in_a3'] ?></p>
    </div>
    <div>
        <h3>What challenges did you face in this task - and how did you overcome them?</h3>
        <p><?= $data['mig_out_a1'] ?></p>
    </div>
    <div>
        <h3>What are the common reasons of students not receiving thaali in your mawze?</h3>
        <p>
            <?php
            $reasons_array = array();
            if ($data['fmb_a1']) $reasons_array[] = "Sabeel/Jamaat nizaam";
            if ($data['fmb_a2']) $reasons_array[] = "Affordability";
            if ($data['fmb_a3']) $reasons_array[] = "Taste";
            if ($data['fmb_a4']) $reasons_array[] = "Distance and divansport";
            if ($data['fmb_a5']) $reasons_array[] = "Unsuitable timings";
            if ($data['fmb_a6']) $reasons_array[] = $data['fmb_a6'];
            echo join(', ', $reasons_array);
            ?>
        </p>
    </div>
    <div>
        <h3>How did you overcome these challenges?</h3>
        <p><?= $data['fmb_a7'] ?></p>
    </div>
    <div>
        <h3>How many migrated students started FMB thaali during Shehrullah 1445H? </h3>
        <p><?= $data['fmb_a8'] ?></p>
    </div>
    <div>
        <h3>What should be done to ensure that 100% migrated students receive FMB Thaali after Shehrullah?</h3>
        <p><?= $data['fmb_a9'] ?></p>
    </div>
    <div>
        <h3>What challenges did you face while identifying and tagging 'muraqibeen' to students, and how did you overcome them?</h3>
        <p><?= $data['muraqabat_a1'] ?></p>
    </div>
    <div>
        <h3>What aspects were covered in the 'Muraqibeen divaining' during shehrullah?</h3>
        <p><?= $data['muraqabat_a2'] ?></p>
    </div>
    <div>
        <h2>What measures are taken to establish a hostel/jamaat provided accommodation for migrated-in students?</h2>
        <h3>Dedicated hostel committee</h3>
        <p><?= $data['accommodation_a1'] ?></p>
    </div>
    <div>
        <h3>Need analysis</h3>
        <p><?= $data['accommodation_a2'] ?></p>
    </div>
    <div>
        <h3>Cost analysis</h3>
        <p><?= $data['accommodation_a3'] ?></p>
    </div>
    <div>
        <h3>Location assessment</h3>
        <p><?= $data['accommodation_a4'] ?></p>
    </div>
    <div>
        <h2>How would you describe the current living conditions of migrated-in students who live in -</h2>
        <h3>Sharing rental w/ mumin</h3>
        <p><?= $data['accommodation_a5'] ?></p>
    </div>
    <div>
        <h3>Sharing rental w/non-mumin</h3>
        <p><?= $data['accommodation_a6'] ?></p>
    </div>
    <div>
        <h3>Individual rental</h3>
        <p><?= $data['accommodation_a7'] ?></p>
    </div>
    <div>
        <h3>Hostel</h3>
        <p><?= $data['accommodation_a8'] ?></p>
    </div>
    <div>
        <h3>How many students were shifted from non-recommended accommodation during shehrullah?</h3>
        <p><?= $data['accommodation_a9'] ?></p>
    </div>
    <div>
        <h3>What was the role of UT Coordinator/ Higher Education Team lead in BQI activities?</h3>
        <p><?= $data['ut_committee_a1'] ?></p>
    </div>
    <div>
        <h3>Mention the names of different local committees and their condivibution towards BQI activities.</h3>
        <p><?= $data['ut_committee_a2'] ?></p>
    </div>
    <div>
        <h3>What are your observations regarding the overall working of umoor taalimiyah committee in your mawze?</h3>
        <p><?= $data['ut_committee_a3'] ?></p>
    </div>
    <div>
        <h3>What preparations were done by Umoor Taalimiyah and Umaal Kiraam for BQI tasks - before shehrullah?</h3>
        <p><?= $data['ut_committee_a4'] ?></p>
    </div>
    <div>
        <h3>Did you notice any substantial growth/development in the working of Umoor Taalimiyah committee as a result of BQI project?</h3>
        <p><?= $data['ut_committee_a5'] ?></p>
    </div>
    <div>
        <h3>How many students in your mawze will need financial assistance for the upcoming academic year?</h3>
        <p><?= $data['minhat_a1'] ?></p>
    </div>
    <div>
        <h3>How much financial assistance will be required for these students?</h3>
        <p><?= $data['minhat_a3'] . ': ' . $data['minhat_a2'] ?></p>
    </div>
    <div>
        <h3>How many children were granted admission in Imani Schools during Shehrullah as a result of BQI program?</h3>
        <p><?= $data['school_a1'] ?></p>
    </div>
    <div>
        <h3>What different media were used to convey the paighaam of Maula TUS regarding higher education and migration?</h3>
        <p><?= $data['asbaaq_a1'] ?></p>
    </div>
    <div>
        <h3>You can upload Majhoodaat or Presentations of your seminars here.</h3>
        <p><?= $data['asbaaq_a2'] ?></p>
    </div>
    <div>
        <h3>What are the most preferred cities for students migrating out from your city?</h3>
        <p><?= $data['trends_a1'] ?></p>
    </div>
    <div>
        <h3>Where do most migrated-in students in your city come from?</h3>
        <p><?= $data['trends_a2'] ?></p>
    </div>
    <div>
        <h3>What are the most popular courses/careers in your city?</h3>
        <p><?= $data['trends_a3'] ?></p>
    </div>
    <div>
        <h3>Which courses/career options offer great opportunities in your city?</h3>
        <p><?= $data['trends_a4'] ?></p>
    </div>
</div>