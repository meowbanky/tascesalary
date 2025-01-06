<?php
require 'app.php';
$test = password_verify('h6rV@R', '$2y$10$bfJqOPjdn3XJJVJBMzi4kuGNqwrk3ovzL0L5A5sgw.Hawo0BhRNl6');

$TEST2 =  password_hash('h6rV@R',PASSWORD_BCRYPT);
echo '___';
echo '$2y$10$bfJqOPjdn3XJJVJBMzi4kuGNqwrk3ovzL0L5A5sgw.Hawo0BhRNl6';
if($test){
    echo 'ok';
}else{
    echo '---invalie';
}

 $test = password_verify('h6rV@R', $TEST2);

echo $TEST2;

