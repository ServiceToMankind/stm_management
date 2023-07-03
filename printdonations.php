<?php
require('includes/functions.php');
if(isset($_GET['month']) && $_GET['month']!=''){
    $month = $_GET['month'];
    $year = $_GET['year'];
    $url = "https://apis.stmorg.in/logs/donations?month=".$month."&year=".$year."&print=true";
    $data = get_api_data($url);
    $data = json_decode($data, true);
    $data = $data['data'];
    // Define the CSV file name
    $filename = "donations_".$month.$year.".csv";

    // Set the HTTP headers for file download
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Open the output stream
    $output = fopen("php://output", "w");

    // Write the CSV column headers
    $headers = array_keys($data[0]);
    fputcsv($output, $headers);

    // Write the CSV data rows
    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    // Close the output stream
    fclose($output);
}else{
    ?>
<script>
return confirm('No paramaters were set.');
window.location.href = 'donations';
</script>
<?php
}