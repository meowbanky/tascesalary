<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(!isset($_POST['staff_id'])){
        $staff_id = -1;
    } else {
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
    $employment_type = trim($_POST['employment_type']);

    // Validation
    $errors = [];

    if (!empty($EMAIL)) {
        if (!filter_var($EMAIL, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
    }
    if (empty($NAME)) {
        $errors[] = 'Name is required';
    }
    if (empty($GENDER)) {
        $errors[] = 'Gender is required';
    }
    if (empty($employment_type)) {
        $errors[] = 'Employment Type is required';
    }
    if (empty($EMPDATE) || !validateDate($EMPDATE)) {
//        $errors[] = 'Valid employment date is required';
        $EMPDATE = date('Y-m-d H:i:s');
    }
    if (empty($DOB) || !validateDate($DOB)) {
//        $errors[] = 'Valid date of birth is required';
        $DOB = date('Y-m-d H:i:s');
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
    if($BANK_ID != 47) {
        if (empty($ACCTNO) || !ctype_digit($ACCTNO) || strlen($ACCTNO) != 10) {
            $errors[] = 'Account number is required and must be a 10-digit number';
        }
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
                    employment_type = :employment_type,
                    editTime = now()
                WHERE staff_id = :staff_id";
            $arraystaff = [':staff_id' => $staff_id];
        } else {
            // Save the new employee to the database
            $query = "INSERT INTO employee (employment_type,OGNO, EMAIL, `NAME`, GENDER, EMPDATE, DOB, DEPTCD, GRADE, STEP, ACCTNO, BANK_ID, PFACODE, PFAACCTNO, SALARY_TYPE, STATUSCD, userID, editTime) VALUES (
        :employment_type,
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
        :STATUS, :user, NOW())";
        }
        $params = [
            ':employment_type' => $employment_type,
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


        if (!$existingEmployee) {
            $staff_id = $App->link->lastInsertId(); // Get the last inserted ID
            $App->log('INSERT','employee',$params,$_SESSION['SESS_MEMBER_ID']);

            $autoAllows = $App->getAutoAllowance();

            foreach ($autoAllows as $autoAllow) {
                $salaryValue = $App->getSalaryValue($GRADE, $STEP, $SALARY_TYPE, $autoAllow['allow_id']);

                if ($salaryValue) {
                    $value = $salaryValue['value'];
                    if ($value > 0) {
                        $array = [
                            ':staff_id' => $staff_id,
                            ':allow_id' => $autoAllow['allow_id'],
                            ':value' => $value,
                            ':counter' => 0,
                            ':inserted_by' => $_SESSION['SESS_MEMBER_ID']
                        ];

                        // Check if the record already exists
                        $checkQuery = "SELECT COUNT(*) as count FROM allow_deduc WHERE staff_id = :staff_id AND allow_id = :allow_id";
                        $checkParams = [
                            ':staff_id' => $staff_id,
                            ':allow_id' => $autoAllow['allow_id']
                        ];

                        $result = $App->selectOne($checkQuery, $checkParams);

                        if ($result && $result['count'] > 0) {
                            // Update existing record
                            $success = $App->updateAllowances($value, $_SESSION['SESS_MEMBER_ID'], $staff_id, $autoAllow['allow_id'], 0);
                        $App->log('UPDATE allow/deduction','allow_dedction',$checkParams,$_SESSION['SESS_MEMBER_ID']);
                        } else {

                            // Insert new record
                            $success = $App->insertAllowances($value, $_SESSION['SESS_MEMBER_ID'], $staff_id, $autoAllow['allow_id'], 0);
                            $App->log('INSERT allow/deduction','allow_dedction',$checkParams,$_SESSION['SESS_MEMBER_ID']);
                        }
                    }
                }
            }
        }

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
