<?php
$password = 'Admin@2026!';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
echo $hash . PHP_EOL;
echo password_verify($password, $hash) ? 'VERIFY: MATCH' : 'VERIFY: FAIL';
echo PHP_EOL;
// Also output the UPDATE SQL
echo "UPDATE \`users\` SET \`password\`='" . $hash . "' WHERE email='admin@fmsport.biz';" . PHP_EOL;
