<?php

require 'App.php';

$App = new App();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(!isset($_POST['staff_id'])){
        $staff_id = -1;
    }else{
        $staff_id = $_POST['staff_id'];
    }
$OGNO = trim($_POST['ogno']);
$EMAIL = trim($_POST['email']);
$NAME = trim($_POST['name']);
$GENDER = trim($_POST['gender']);
$EMPDATE = trim($_POST['empdate']);
$DOB = trim($_POST['dob']);
$DEPTCD = trim($_POST['deptcd']);
$GRADE = trim($_POST['grade']);
$STEP = trim($_POST['step']);
$ACCTNO = trim($_POST['acctno']);
$BANK_ID = trim($_POST['bankcode']);
$PFACODE = trim($_POST['pfacode']);
$PFAACCTNO = trim($_POST['pfaacctno']);
$SALARY_TYPE = trim($_POST['salarytype']);
$STATUS = trim($_POST['status']);

// Validation
$errors = [];


if (empty($EMAIL) || !filter_var($EMAIL, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}
if (empty($NAME)) {
    $errors[] = 'Name is required';
}
if (empty($GENDER)) {
    $errors[] = 'Gender is required';
}
if (empty($EMPDATE) || !validateDate($EMPDATE)) {
    $errors[] = 'Valid employment date is required';
}
if (empty($DOB) || !validateDate($DOB)) {
    $errors[] = 'Valid date of birth is required';
}
if (empty($DEPTCD)) {
    $errors[] = 'Department code is required';
}
if (empty($GRADE)|| !ctype_digit($GRADE)) {
    $errors[] = 'Grade is required and must be a number';
}
if (empty($STEP) || !ctype_digit($STEP)) {
    $errors[] = 'Step is required and must be digits';
}
if (empty($ACCTNO) || !ctype_digit($ACCTNO) || strlen($ACCTNO) != 10) {
    $errors[] = 'Account number is required and must be a 10-digit number';
}
if (empty($BANK_ID)) {
    $errors[] = 'Bank ID is required';
}

if (empty($SALARY_TYPE)) {
    $errors[] = 'Salary type is required';
}
if (empty($STATUS)) {
    $errors[] = 'Status is required';
}

if (empty($errors)) {
    // Check if the staff ID already exists
    $checkQuery = "SELECT * FROM employee WHERE staff_id = :staff_id";
    $checkParams = [':staff_id' => $staff_id];
    $existingEmployee = $App->selectOne($checkQuery, $checkParams);

    if ($existingEmployee) {
        // Update existing employee
        $query = "UPDATE employee SET 
                OGNO = :OGNO,
                EMAIL = :EMAIL,
                NAME = :NAME,
                GENDER = :GENDER,
                EMPDATE = :EMPDATE,
                DOB = :DOB,
                DEPTCD = :DEPTCD,
                GRADE = :GRADE,
                STEP = :STEP,
                ACCTNO = :ACCTNO,
                BANK_ID = :BANK_ID,
                PFACODE = :PFACODE,
                PFAACCTNO = :PFAACCTNO,
                SALARY_TYPE = :SALARY_TYPE,
                STATUSCD = :STATUS,
                userID = :user,
                editTime = now()
            WHERE staff_id = :staff_id";
        $arraystaff = [':staff_id' =>$staff_id];
    } else {
        // Save the new employee to the database
        $query = "INSERT INTO employee (OGNO, EMAIL, `NAME`, GENDER, EMPDATE, DOB, DEPTCD, GRADE, STEP, ACCTNO, BANK_ID, PFACODE, PFAACCTNO, SALARY_TYPE, STATUSCD,userID,editTime) VALUES (
    :OGNO, 
    :EMAIL, 
    :NAME, 
    :GENDER, 
    :EMPDATE, 
    :DOB, 
    :DEPTCD, 
    :GRADE, 
    :STEP, 
    :ACCTNO, 
    :BANK_ID, 
    :PFACODE, 
    :PFAACCTNO, 
    :SALARY_TYPE, 
    :STATUS,:user,NOW())";
    }
    $params = [
        ':OGNO'=> $OGNO,
        ':EMAIL'=> $EMAIL,
        ':NAME'=> $NAME,
        ':GENDER'=> $GENDER,
        ':EMPDATE'=> $EMPDATE,
        ':DOB'=> $DOB,
        ':DEPTCD'=> $DEPTCD,
        ':GRADE'=> $GRADE,
        ':STEP'=> $STEP,
        ':ACCTNO'=> $ACCTNO,
        ':BANK_ID'=> $BANK_ID,
        ':PFACODE'=> $PFACODE,
        ':PFAACCTNO'=> $PFAACCTNO,
        ':SALARY_TYPE'=> $SALARY_TYPE,
        ':STATUS'=> $STATUS,
        'user'=>$_SESSION['SESS_MEMBER_ID']
    ];
if(!empty($arraystaff)){
    $params = array_merge($params,$arraystaff);
}

    $result = $App->executeNonSelect($query, $params);

    if ($result) {
        echo 'Employee added successfully.';
    } else {
        echo 'Error adding employee.';
    }
} else {
    // Display validation errors
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
}
} else {
    echo 'Invalid request.';
}

// Function to validate date
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>
