<?php
# Constants
define("E", "27182818284590452353602874713526624977572470936999595");  # placeholder for username
define("PI", "314159265358979323846264338327950288419");  # placeholder for loading msg
define("GOLDEN", "161803398874989484820458683436563811772030917980576286213544862270526046281890244970720720418939113748475408807538689175212663386222353693179318006076672635443338908659593958290563832266131992829026788067520876689250171169620703222104321626954862629631361");  # placeholder for branch
define("NUM_USERNAME_CHARS", mb_strlen(E));  # includes repo name
define("NUM_LOADING_CHARS", mb_strlen(PI));
define("NUM_BRANCH_CHARS", mb_strlen(GOLDEN));
define("BRANCH_START_STR", "--depth=1 openpilot");

$installer_binary = file_get_contents(getcwd() . "/installer_openpilot_agnos");  # load the unmodified installer

$username = $_GET["username"];
$branch = $_GET["branch"];
$loading_msg = $_GET["loading_msg"];

if ($username == "") exit;  # discount assertions
if ($loading_msg == "") exit;

# Handle username replacement:
# replaces placeholder with username and repo name + any needed NULs
$replace_with = $username . "/openpilot.git";
// echo $replace_with . "\n";
// echo NUM_USERNAME_CHARS - strlen($replace_with) . "\n";
$replace_with .= str_repeat("\0", NUM_USERNAME_CHARS - strlen($replace_with));
// echo $replace_with;
// echo strlen($replace_with);
if (strlen($replace_with) != NUM_USERNAME_CHARS) exit;
$installer_binary = str_replace(E, $replace_with, $installer_binary);
//
//
//
// $num_nulls_append = NUM_USERNAME_CHARS - mb_strlen($username);  # number of spaces we need to append to end of string before NUL
// $branch_start_idx = strpos($installer_binary, BRANCH_START_STR) + mb_strlen(BRANCH_START_STR);
//
// $installer_binary = substr_replace($installer_binary, str_repeat(" ", $num_nulls_append), $branch_start_idx, 0);  # 0 inserts, no replacing
//
// if ($branch != "") {
//     # Now add user-supplied branch
//     $branch_start_idx = strpos($installer_binary, BRANCH_START_STR) + mb_strlen(BRANCH_START_STR);
//     $branch_len = mb_strlen($branch) + 4;  # +4 for " -b "
//     $installer_binary = substr_replace($installer_binary, " -b " . $branch, $branch_start_idx, $branch_len);
// }
//
// # Replace loading msg
// $num_nulls_append = NUM_LOADING_CHARS - strlen($loading_msg);  # keep size the same
// $installer_binary = str_replace(PI, $loading_msg . str_repeat("\0", $num_nulls_append), $installer_binary);

# Now download
header("Content-Type: application/octet-stream");
header("Content-Length: " . strlen($installer_binary));  # we want actual bytes
header("Content-Disposition: attachment; filename=installer_openpilot");
echo $installer_binary;  # downloads without saving to a file
exit;
?>
