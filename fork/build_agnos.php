<?php
# Constants
define("E", "271828182845904523536028747135266249775");  # placeholder for username
define("PI", "3141592653589793238462643383");  # placeholder for loading msg
define("GOLDEN", "1618033988749894848204586834");  # placeholder for branch
define("NUM_USERNAME_CHARS", mb_strlen(E));
define("NUM_LOADING_CHARS", mb_strlen(PI));
define("NUM_BRANCH_CHARS", mb_strlen(GOLDEN));
define("BRANCH_START_STR", "--depth=1 openpilot");

$installer_binary = file_get_contents(getcwd() . "/installer_openpilot_agnos");  # load the unmodified installer

$username = $_GET["username"];
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
