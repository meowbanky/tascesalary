<?php

require '/Users/mac/Desktop/Project/64_folder/tasceSalary/config/config.php';



//require '../config/config.php';


class App
{
    public $host = HOST;
    public $dbname = DBNAME;
    public $pass = PASS;
    public $user = USER;

    public $link;

    public $businessName;

    public $town;
    public $state;
    public $tel;

    public $logged_in ;
    public $loggeduser;
    public $SESS_MEMBER_ID;
    public $email ;
    public $SESS_FIRST_NAME;
    public $SESS_LAST_NAME;

    public $role;
    public $emptrack;
    public $empDataTrack;

    public function __construct()
    {
        $this->construct();

    }

    function sendPasswordEmail($to, $username, $password) {
        $subject = "Your New Account Details";
        $message = "Hello,\n\nYour account has been created. Here are your login details:\nUsername: $username\nPassword: $password\n\nPlease change your password after your first login.";
        $headers = "From: no-reply@example.com";

        mail($to, $subject, $message, $headers);
    }

    public function insertProrateAllowance($staff_id, $allow_id, $value, $type, $inserted_by, $date_insert) {
        $query = 'INSERT INTO prorate_allow_deduc (staff_id, allow_id, value, type, inserted_by, date_insert, counter) 
              VALUES (:staff_id, :allow_id, :value, :type, :inserted_by, :date_insert, 1)';
        $array = [':staff_id' => $staff_id, ':allow_id' => $allow_id,
            ':value' => $value, 'type'=>$type, 'inserted_by'=>$inserted_by, 'date_insert' => $date_insert];
        return $this->executeNonSelect($query,$array);
    }

    public function deleteProrateAllowance($staff_id, $allow_id) {
        $query = 'DELETE FROM prorate_allow_deduc WHERE staff_id = :staff_id AND allow_id = :allow_id';
        $params = [':staff_id' => $staff_id, ':allow_id' => $allow_id];
        $this->executeNonSelect($query, $params);
    }

    public function updateRunningCounter($running_counter,$staff_id,$allow_id){
        $query = 'update allow_deduc set running_counter = :running_counter
                   WHERE staff_id = :staff_id AND allow_id = :allow_id';
        $array = [
            ':running_counter' => $running_counter,
            ':staff_id' => $staff_id,
            ':allow_id' => $allow_id
        ];
        return $this->executeNonSelect($query, $array);
    }

    public function deleteMasterStaff($staff_id,$period){
        $query = 'DELETE FROM master_staff WHERE staff_id = :staff_id AND period = :period';
        $array = [
            ':staff_id' => $staff_id,
            ':period' => $period
        ];
        return $this->executeNonSelect($query, $array);
    }

    public function deleteMaster($staff_id,$period){
        $query = 'DELETE FROM tbl_master WHERE staff_id = :staff_id AND period = :period';
        $array = [
            ':staff_id' => $staff_id,
            ':period' => $period
        ];
        return $this->executeNonSelect($query, $array);
    }
    public function checkCompleted($staff_id,$period,$user_id){
        $query = "SELECT * FROM completedloan WHERE staff_id = :staff_id AND period = :period";
        $array = [
           ':staff_id'=> $staff_id,
           ':period'=> $period
        ];
        $selects = $this->selectAll($query,$array);
        if($selects){
            foreach ($selects as $select){
                //restore selection
                $this->insertAllowances($select['value'],$user_id,$staff_id,$select['allow_id'],$counter=1,$running_counter=0);
            }
            $query = "DELETE FROM completedloan where period = :period AND staff_id = :staff_id";
            $this->executeNonSelect($query,$array);
        }

    }

    function deleteAllowance($staff_id, $allow_id) {
        $query = 'DELETE FROM allow_deduc WHERE staff_id = :staff_id AND allow_id = :allow_id';
        $array = [
            ':allow_id'=> $allow_id,
            ':staff_id'=> $staff_id
        ];
        return $this->executeNonSelect($query,$array);
    }

    function insertProratedAllow($staff_id, $allow_id,$value) {
        $query = 'INSERT INTO allow_deduc (staff_id,allow_id,value) VALUES (:staff_id,:allow_id,:value)';
        $array = [
            ':allow_id'=> $allow_id,
            ':staff_id'=> $staff_id,
            ':value'=> $value
        ];
        return $this->executeNonSelect($query,$array);
    }

    public function getEmployeeDetails($staff_id = null)
    {
        // Initialize the query and parameters
        $query = 'SELECT
	employee.staff_id, 
	`NAME`, 
	DEPTCD, 
	employee.EMAIL, 
	employee.OGNO, 
	employee.TIN, 
	employee.`NAME`, 
	employee.GENDER, 
	employee.EMPDATE, 
	employee.DOB, 
	employee.DEPTCD, 
	employee.POST, 
	employee.GRADE, 
	employee.STEP, 
	employee.PFACODE, 
	employee.PFAACCTNO, 
	employee.SALARY_TYPE, 
	employee.ACCTNO, 
	employee.BANK_ID, 
	dept, 
	employee.STATUSCD, 
	staff_status.`STATUS`, 
	tbl_salaryType.SalaryType, 
	tbl_bank.BNAME, 
	tbl_pfa.PFANAME
FROM
	employee
	LEFT JOIN
	tbl_dept
	ON 
		employee.DEPTCD = tbl_dept.dept_id
	LEFT JOIN
	staff_status
	ON 
		employee.STATUSCD = staff_status.STATUSCD
	LEFT JOIN
	tbl_salaryType
	ON 
		employee.SALARY_TYPE = tbl_salaryType.salaryType_id
	LEFT JOIN
	tbl_bank
	ON 
		employee.BANK_ID = tbl_bank.bank_ID
	LEFT JOIN
	tbl_pfa
	ON 
		employee.PFACODE = tbl_pfa.PFACODE';

        $params = [];

        if ($staff_id !== null) {
            $query .= ' WHERE staff_id = :staff_id';
            $params = [':staff_id' => $staff_id];
            return $this->selectOne($query, $params);
        } else {
            return $this->selectAll($query, $params);
        }
    }


    public function getEmployeeDetailsPayslip($staff_id,$period)
    {
        $array = [
            ':staff_id' => $staff_id,
            ':period' => $period
        ];

        $query = 'SELECT
	master_staff.staff_id, 
	master_staff.`NAME`, 
	tbl_dept.dept, 
	tbl_bank.BNAME, 
	master_staff.ACCTNO, 
	master_staff.GRADE, 
	master_staff.STEP
    FROM
	master_staff
	LEFT JOIN
	tbl_dept
	ON 
		master_staff.DEPTCD = tbl_dept.dept_id
	LEFT JOIN
	tbl_bank
	ON 
		master_staff.BCODE = tbl_bank.BANK_ID
        WHERE staff_id = :staff_id and period =:period';


        return $this->selectOne($query, $array);
    }
    public function insertMasterRecordAllow($staff_id, $allow_id, $value, $type, $period, $editTime, $userID) {
        $query = "INSERT INTO tbl_master (staff_id, allow_id, allow, type, period, editTime, userID) 
                  VALUES (:staff_id, :allow_id, :value, :type, :period, :editTime, :userID)";
        $params = [
            ':staff_id' => $staff_id,
            ':allow_id' => $allow_id,
            ':value' => $value,
            ':type' => $type,
            ':period' => $period,
            ':editTime' => $editTime,
            ':userID' => $userID
        ];

        return $this->executeNonSelect($query, $params);
    }


    public function insertMasterRecordDeduc($staff_id, $allow_id, $value, $type, $period, $editTime, $userID) {
        $query = "INSERT INTO tbl_master (staff_id, allow_id, deduc, type, period, editTime, userID) 
                  VALUES (:staff_id, :allow_id, :value, :type, :period, :editTime, :userID)";
        $params = [
            ':staff_id' => $staff_id,
            ':allow_id' => $allow_id,
            ':value' => $value,
            ':type' => $type,
            ':period' => $period,
            ':editTime' => $editTime,
            ':userID' => $userID
        ];

        return $this->executeNonSelect($query, $params);
    }

    public function completedEarnings($staff_id,$allow_id,$period,$value,$type){
        $query = 'INSERT INTO completedLoan (staff_id,allow_id,period,value,type)VALUES (:staff_id,:allow_id,:period,:value,:type)';
        $array = [  ':staff_id'=> $staff_id,
                    ':allow_id'=> $allow_id,
                    ':period'=> $period,
                    ':value'=> $value,
                    ':type'=> $type
        ];

       return  $this->executeNonSelect($query,$array);
    }
    public function getEmployeesEarnings($staff_id,$edType) {
        $earning_array = [
            ':staff_id' => $staff_id,
            ':edType' => $edType
        ];
    $query =    "SELECT
	tbl_earning_deduction.ed,
	allow_deduc.`value`,
	allow_deduc.allow_id,allow_deduc.temp_id,
	tbl_earning_deduction.edType,
	allow_deduc.staff_id,allow_deduc.running_counter,allow_deduc.counter 
    FROM
	allow_deduc
	INNER JOIN tbl_earning_deduction ON allow_deduc.allow_id = tbl_earning_deduction.ed_id 
    WHERE staff_id = :staff_id AND edType = :edType ORDER BY allow_deduc.allow_id";

      return  $this->selectAll($query, $earning_array);

    }

    public function getPaySlip($staff_id,$period) {
        $array = [
            ':staff_id' => $staff_id,
            ':period' => $period
        ];
        $query =    "SELECT
                tbl_master.allow_id,
                tbl_master.allow,
                tbl_master.deduc,
                tbl_earning_deduction.ed 
            FROM
                tbl_master
                INNER JOIN tbl_earning_deduction ON tbl_master.allow_id = tbl_earning_deduction.ed_id  
            WHERE staff_id = :staff_id and period = :period ORDER BY allow_id";

        return  $this->selectAll($query, $array);

    }

    public function insertStaffMaster($staff_id, $name, $deptcd, $bcode, $acctno, $grade, $step, $period, $pfacode, $pfaacctno) {
        $query = "INSERT INTO master_staff (staff_id, NAME, DEPTCD, BCODE, ACCTNO, GRADE, STEP, period, PFACODE, PFAACCTNO) 
              VALUES (:staff_id, :NAME, :DEPTCD, :BCODE, :ACCTNO, :GRADE, :STEP, :period, :PFACODE, :PFAACCTNO)";
        $params = [
            ':staff_id' => $staff_id,
            ':NAME' => $name,
            ':DEPTCD' => $deptcd,
            ':BCODE' => $bcode,
            ':ACCTNO' => $acctno,
            ':GRADE' => $grade,
            ':STEP' => $step,
            ':period' => $period,
            ':PFACODE' => $pfacode,
            ':PFAACCTNO' => $pfaacctno
        ];

        return $this->executeNonSelect($query, $params);
    }


    public function updateAllowances($value,$user_id,$staff_id,$allow_id,$counter=0,$running_counter=0)
    {
        $query = "UPDATE allow_deduc SET value = :value, running_counter = :running_counter,counter = :counter, inserted_by = :inserted_by, date_insert = NOW() 
                        WHERE staff_id = :staff_id AND allow_id = :allow_id";
        $params = [
            ':value' => $value,
            ':counter' => $counter,
            ':inserted_by' => $user_id,
            ':staff_id' => $staff_id,
            ':allow_id' => $allow_id,
            'running_counter' => $running_counter,
        ];
        return $this->executeNonSelect($query, $params);
    }
    public function insertAllowances($value,$user_id,$staff_id,$allow_id,$counter=0,$running_counter=0){
        $params = [
            ':value' => $value,
            ':counter' => $counter,
            ':inserted_by' => $user_id,
            ':staff_id' => $staff_id,
            ':allow_id' => $allow_id,
            'running_counter' => $running_counter,
        ];
        $query = 'INSERT INTO allow_deduc(staff_id,allow_id,value,counter,inserted_by,date_insert,running_counter)
                                            VALUES (:staff_id,:allow_id,:value,:counter,:inserted_by,NOW(),:running_counter)';
        return $this->executeNonSelect($query, $params);
    }


    public function updateEmployeeProrate($staff_id,$step){
        $query = 'update employee set STEP = :step WHERE staff_id = :staff_id';
        $array = [
            ':staff_id' => $staff_id,
            ':step' => $step,
        ];
      return  $this->executeNonSelect($query, $array);
    }
    function getProratedAllowances($staff_id) {
        $query = 'SELECT prorate_allow_deduc.`value`, prorate_allow_deduc.allow_id, tbl_earning_deduction.edDesc 
              FROM tbl_earning_deduction 
              INNER JOIN prorate_allow_deduc ON tbl_earning_deduction.ed_id = prorate_allow_deduc.allow_id 
              WHERE staff_id = :staff_id 
              ORDER BY allow_id ASC';

        $array = [
            ':staff_id'=> $staff_id
        ];

        return $this->selectAll($query,$array);


    }
    public function construct()
    {
        try {
            $this->link = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname,
                $this->user, $this->pass,
                array(PDO::ATTR_PERSISTENT => true));
            $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

           $this->startingSession();

        } catch (PDOException $e) {
            echo "Failed Connection: " . $e->getMessage();
        }

    }

    public function isPayslipAvailable($staff_id, $period){
        $array = [':staff_id' => $staff_id, ':period' => $period];

        $query = 'SELECT master_staff.staff_id, master_staff.period FROM master_staff 
                    WHERE staff_id = :staff_id and period = :period';
        return $this->selectOne($query,$array);

    }
    public function calculateProratedValue($value, $DaysToCal, $no_days) {
        $value = intval($value);
        $DaysToCal = intval($DaysToCal);
        $no_days = intval($no_days);
        return ($DaysToCal * $value) / $no_days;
    }

    public function getDaysOfMonth($date){
        $split = explode(' ',$date, 2);
        $mon = date('m', strtotime($split[0]));
        $yr = $split[1];
       return $daysInCurrentMonth = cal_days_in_month(CAL_GREGORIAN, $mon, $yr);

    }

    function displayAllowances($allowances) {
        echo '<table class="table table-bordered table-hover">';
        echo '<thead>';
        echo '<tr class="earnings-ded-header">';
        echo '<th> Code </th>';
        echo '<th> Description </th>';
        echo '<th> Amount </th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($allowances as $allowance) {
            echo '<tr class="odd gradeX">';
            echo '<td>' . $allowance['allow_id'] . '</td>';
            echo '<td>' . $allowance['edDesc'] . '</td>';
            echo '<td class="align-right">' . number_format($allowance['value']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }
    public function startingSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function validateSession($path){
        if(!$_SESSION['$SESS_MEMBER_ID']){
        header('location:'.$path);
        }
    }
    public function businessInfo()
    {
     $query_bus = $this->link->prepare('SELECT * FROM tbl_business');
        $fin_bus = $query_bus->execute(array());
        $row_bus = $query_bus->fetch();

        $_SESSION['businessname'] = $this->businessName = $row_bus['business_name'];
        $this->town = $row_bus['town'];
        $this->state = $row_bus['state'];
        $this->tel = $row_bus['tel'];

        $data['success'] = 'true';
        $data['message'] = 'Successfully Login';
    }
public function updateGradeStep($grade,$step,$staff_id)
{
    $query = 'UPDATE employee SET grade = :grade , step = :step, userID = :userID, editTime = :editTime WHERE staff_id = :staff_id';
    $recordtime = date('Y-m-d H:i:s');
    $array = [
        ':grade' => $grade,
        ':step' => $step,
        ':staff_id' => $staff_id,
        'userID' => $_SESSION['SESS_MEMBER_ID'],
        'editTime' => $recordtime
    ];
    return $this->executeNonSelect($query,$array);
}
    public function getSalaryValue($grade,$step,$salaryType,$allow_id){
        $array = [':grade' => $grade,
            ':step' => $step,
            ':salaryType' => $salaryType,
            ':allow_id' => $allow_id
            ];
     return  $this->selectOne("SELECT allowancetable.`value` FROM allowancetable WHERE
	                grade = :grade AND step = :step AND SALARY_TYPE = :salaryType AND allowcode = :allow_id",$array);

    }

    public function selectAll($query,$array=[]){
        $rows = $this->link->prepare($query);
        $rows->execute($array);
        $allRows = $rows->fetchAll(PDO::FETCH_ASSOC);
        if($allRows){
            return $allRows;
        }else{
            return false;
        }
    }

    public function executeNonSelect($query, $array = []) {
        $stmt = $this->link->prepare($query);
        return $stmt->execute($array);  // Returns true on success or false on failure
    }

    public function insertWithLastInsertID($query, $array = []) {
        $stmt = $this->link->prepare($query);
        $result = $stmt->execute($array);
        if ($result) {
            return $this->link->lastInsertId();  // Returns the ID of the inserted row
        } else {
            return false;
        }
    }


    public function selectOne($query,$array=[]){
        $row = $this->link->prepare($query);
        $row->execute($array);

        $singleRow = $row->fetch(PDO::FETCH_ASSOC);
        if($singleRow){
            return $singleRow;
        }else{
            return false;
        }
    }
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function selectDrop($query = "SELECT
	tbl_earning_deduction.ed, 
	tbl_earning_deduction.ed_id
    FROM
	tbl_earning_deduction",$array = []){
        $rows = $this->link->prepare($query);
        $rows->execute($array);
        $allRows = $rows->fetchAll(PDO::FETCH_ASSOC);
        if($allRows){
            return $allRows;
        }else{
            return false;
        }
    }
    public function login($username, $password)
    {

		$errmsg_arr = array();
		$errflag = false;

		$errors = array();      // array to hold validation errors
		$data = array();      // array to pass back data

		$uname = filter_var((filter_var($username)));
		$pass = filter_var($password);

		if ($uname == '') {
            $errmsg_arr[] = 'Username missing';
            $errflag = true;
        }
        if ($pass == '') {
            $errmsg_arr[] = 'Password missing';
            $errflag = true;
        }

        try {
            $query = $this->link->prepare('SELECT employee.name, role_id,employee.EMAIL,username.username, username.`password`, username.role, username.staff_id FROM username
                    INNER JOIN employee ON employee.staff_id = username.staff_id WHERE username = ? AND deleted = ?');
            $fin = $query->execute(array($uname, '0'));


            if (isset($_SESSION['periodstatuschange'])) {
                unset($_SESSION['periodstatuschange']);
            }
            // password_verify(
            if (($row = $query->fetch()) and (password_verify($pass, $row['password']))) {

                $_SESSION['logged_in'] = '1';
                $_SESSION['user'] = $row['username'];
                $_SESSION['SESS_MEMBER_ID'] = $row['staff_id'];
                $_SESSION['email'] = $row['EMAIL'];
                $_SESSION['SESS_FIRST_NAME'] = $row['name'];
                $_SESSION['SESS_LAST_NAME'] = $row['name'];
                $_SESSION['role_id'] = $row['role_id'];
                $_SESSION['emptrack'] = 0;
                $_SESSION['empDataTrack'] = 'next';
                $_SESSION['staff'] = 1;

                $this->businessInfo();
                //Get current active period for the organization
                $payp = $this->link->prepare('SELECT periodId, description, periodYear FROM payperiods WHERE active = ? ORDER BY periodId DESC LIMIT 1');
                $myperiod = $payp->execute(array(1));
                $final = $payp->fetch();
                $_SESSION['currentactiveperiod'] = $final['periodId'];
                $_SESSION['activeperiodDescription'] = $final['description'] . " " . $final['periodYear'];

                //If temp period change, reset session
                if (isset($_SESSION['periodstatuschange'])) {
                    unset($_SESSION['periodstatuschange']);
                }

                $data['success'] = 'true';
                $data['message'] = 'Login successful';

            } else {

                $data['success'] = 'false';
                $data['message'] = 'Invalid Username and Password';

            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        return json_encode($data);

        }
}

