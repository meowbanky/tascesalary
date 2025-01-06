<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();
$return_arr = array();

// Get search term
$searchTerm = '%' . $_GET['term'] . '%';

// Prepare the query with placeholders
$query = $App->link->prepare("SELECT
	tbl_bank.bank_ID, 
	tbl_bank.BCODE, 
	tbl_bank.BNAME
FROM
	tbl_bank
    WHERE BNAME LIKE :searchTerm  ORDER BY bank_ID ASC");

// Bind the parameter
$query->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);

// Execute the query
$query->execute();

// Fetch the results
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $data['id'] = $row['bank_ID'];
    $data['label'] = $row['BNAME'] ;
    $data['value'] = $row['bank_ID'];
    array_push($return_arr, $data);
}

// Return JSON data
echo json_encode($return_arr);
?>
