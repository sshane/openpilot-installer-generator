<?php
# Constants
define("E", "271828182845904523536028747135266249775724709369995957496696762772407663035354759457138217852516642742746639193200305992181741359662904357290033429526059563073813232862794349076323382988075319525101901157383418793070215408914993488416750924476146066808226");  # placeholder for username
define("PI", "314159265358979323846264338327950288419716939937510582097494459230781640628620899862803482534211706798214808651328230664709384460955058223172535940812848111745028410270193852110555964462294895493038196442881097566593344612847564823378678316527120190914564");
define("NUM_USERNAME_CHARS", mb_strlen(E));
define("NUM_LOADING_CHARS", mb_strlen(PI));
define("BRANCH_START_STR", "--depth=1 openpilot");

$installer_binary = file_get_contents(getcwd() . "/installer_openpilot_neos");  # load the unmodified installer

$username = $_GET["username"];  # might want to make sure these are coming from index.php and not anyone injecting random values
$branch = $_GET["branch"];
$loading_msg = $_GET["loading_msg"];

if ($username == "") exit;  # discount assertions
if ($loading_msg == "") exit;

# Handle username replacement
$installer_binary = str_replace(E, $username, $installer_binary);  # replace placeholder with username

$num_nulls_append = NUM_USERNAME_CHARS - mb_strlen($username);  # number of spaces we need to append to end of string before NUL
$branch_start_idx = strpos($installer_binary, BRANCH_START_STR) + mb_strlen(BRANCH_START_STR);

$installer_binary = substr_replace($installer_binary, str_repeat(" ", $num_nulls_append), $branch_start_idx, 0);  # 0 inserts, no replacing

if ($branch != "") {
    # Now add user-supplied branch
    $branch_start_idx = strpos($installer_binary, BRANCH_START_STR) + mb_strlen(BRANCH_START_STR);
    $branch_len = mb_strlen($branch) + 4;  # +4 for " -b "
    $installer_binary = substr_replace($installer_binary, " -b " . $branch, $branch_start_idx, $branch_len);
}

# Replace loading msg
$num_nulls_append = NUM_LOADING_CHARS - strlen($loading_msg);  # keep size the same
$installer_binary = str_replace(PI, $loading_msg . str_repeat("\0", $num_nulls_append), $installer_binary);

# Now download
header("Content-Type: application/octet-stream");
header("Content-Length: " . strlen($installer_binary));  # we want actual bytes
header("Content-Disposition: attachment; filename=installer_openpilot");
echo $installer_binary;  # downloads without saving to a file
exit;
?>
