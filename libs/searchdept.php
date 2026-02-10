<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

$return_arr = array();

// Get search term
$searchTerm = '%' . $_GET['term'] . '%';

// Prepare the query with placeholders
$query = $App->link->prepare("SELECT
	tbl_dept.dept, tbl_dept.dept_auto FROM
	tbl_dept
    WHERE dept LIKE :searchTerm  ORDER BY dept_auto ASC");

// Bind the parameter
$query->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);

// Execute the query
$query->execute();

// Fetch the results
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $data['id'] = $row['dept_auto'];
    $data['label'] = $row['dept'] ;
    $data['value'] = $row['dept_auto'];
    array_push($return_arr, $data);
}

// Return JSON data
echo json_encode($return_arr);
?>
