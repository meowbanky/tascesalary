<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';


$response = [];

function resizeImage($sourceImagePath, $targetImagePath, $width, $height) {
    $sourceImage = imagecreatefromstring(file_get_contents($sourceImagePath));
    $sourceWidth = imagesx($sourceImage);
    $sourceHeight = imagesy($sourceImage);

    $targetImage = imagecreatetruecolor($width, $height);
    imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

    // Save the resized image
    imagejpeg($targetImage, $targetImagePath, 90);

    // Free up memory
    imagedestroy($sourceImage);
    imagedestroy($targetImage);
}


if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profilePicture']['tmp_name'];
    $fileName = $_FILES['profilePicture']['name'];
    $fileSize = $_FILES['profilePicture']['size'];
    $fileType = $_FILES['profilePicture']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Sanitize file name
    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

    // Check if file has one of the following extensions
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
    if (in_array($fileExtension, $allowedfileExtensions)) {
        // Directory in which the uploaded file will be moved
        $uploadFileDir = '../assets/images/users/';
        $dest_path = $uploadFileDir . $newFileName;
        $resized_path = $uploadFileDir . 'resized_' . $newFileName;

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true); // Create the directory if it does not exist
        }

        if (move_uploaded_file($fileTmpPath, $dest_path)) {

            resizeImage($dest_path, $resized_path, 150, 150);


            // Here you save the new file name to the database
            $staff_id = $_SESSION['SESS_MEMBER_ID']; // Assuming you have user_id stored in session
            try {
                $dest_path = str_replace('../','',$dest_path);
                $sql = "UPDATE username SET profile_picture = :profile_picture WHERE staff_id = :staff_id";
                $params = [':profile_picture'=> $dest_path,
                    ':staff_id' => $staff_id];
                $result = $App->executeNonSelect($sql,$params);
                $_SESSION['profilePicture'] = $dest_path;
                $response = ['status' => 'success',
                    'message' =>'File is successfully uploaded and saved to the database.'
                        ];

            } catch (PDOException $e) {

                $response = ['status' => 'error',
                    'message' =>'Database error: ' . $e->getMessage()
                ];
            }
        } else {
            $message = 'There was some error moving the file to the upload directory. Please make sure the upload directory is writable by the web server.';
            $response = ['status' => 'error',
                'message' => $message,
            ];
        }
    } else {
        $message = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
        $response = ['status' => 'error',
            'message' => $message,
        ];
    }
} else {
    $message = 'There is some error in the file upload. Please check the following error.<br>';
    $message .= 'Error:' . $_FILES['profilePicture']['error'];
    $response = ['status' => 'error',
        'message' => $message,
    ];
}

echo json_encode($response);
//header("Location: profile.php");
exit;
?>

