<?php
include 'App.php';

$response = [];
$App = new App;
$array = [];


if(isset($_POST['staff_id']) && isset($_POST['value']) && isset($_POST['allow_id'])&& isset($_POST['counter']))
{
    if($_POST['value'] > 0 && !empty($_POST['allow_id'])){
    $staff_id = $_POST['staff_id'];
    $value = $_POST['value'];
    $allow_id = $_POST['allow_id'];
    $user_id = $_SESSION['SESS_MEMBER_ID'];
    $counter = $_POST['counter'];
    $array = [
        ':staff_id' => $staff_id,
        ':allow_id' => $allow_id,
        ':value' => $value,
        ':counter' => $counter,
        ':inserted_by' => $user_id
    ];

    $checkQuery = "SELECT COUNT(*) as count FROM allow_deduc WHERE staff_id = :staff_id AND allow_id = :allow_id";
    $checkParams = [
        ':staff_id' => $staff_id,
        ':allow_id' => $allow_id
    ];

    $result =  $App->selectOne($checkQuery,$checkParams);

    if (($result) && ($result['count'] > 0)){
        $success = $App->updateAllowances($value,$user_id,$staff_id,$allow_id,$counter);
    }else {

        $success = $App->insertAllowances($value,$user_id,$staff_id,$allow_id,$counter);
    }
        if($success){
            $response['status'] = 'success';
            $response['message'] = 'Save Successfully';
        }else{
            $response['status'] = 'error';
            $response['message'] = 'Error Saving';
        }

}else{
        $response['status'] = 'error';
        $response['message'] = 'Check your Inputs';

    }
    echo  json_encode($response);
}