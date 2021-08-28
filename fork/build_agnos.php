<?php
# Constants
define("E", "27182818284590452353602874713526624977572470936999595");  # placeholder for username, includes "openpilot" repo name
define("PI", "314159265358979323846264338327950288419");  # placeholder for loading msg
define("GOLDEN", "161803398874989484820458683436563811772030917980576286213544862270526046281890244970720720418939113748475408807538689175212663386222353693179318006076672635443338908659593958290563832266131992829026788067520876689250171169620703222104321626954862629631361");  # placeholder for branch

# Replaces placeholder with input + any needed NULs, plus does length checking
function fill_in_arg($placeholder, $replace_with, $binary, $padding, $arg_type) {
    $placeholder_len = mb_strlen($placeholder);
    if ($placeholder_len - strlen($replace_with) < 0) { echo "Error: Invalid " . $arg_type . " length!"; exit; }

    $replace_with .= str_repeat($padding, $placeholder_len - strlen($replace_with));
    return str_replace($placeholder, $replace_with, $binary);
}


# Load installer binary
$installer_binary = file_get_contents(getcwd() . "/installer_openpilot_agnos");  # load the unmodified installer

$username = $_GET["username"];
$branch = $_GET["branch"];
$loading_msg = $_GET["loading_msg"];

if ($username == "") exit;  # discount assertions
if ($loading_msg == "") exit;


# Handle username replacement:
$installer_binary = fill_in_arg(E, $username . "/openpilot.git", $installer_binary, "\0", "username");

# Handle branch replacement (3 occurrences):
$installer_binary = fill_in_arg(GOLDEN, $branch, $installer_binary, "\0", "branch");

# Handle loading message replacement:
$installer_binary = fill_in_arg(PI, $loading_msg, $installer_binary, " ", "loading message");  // QT actually displays null characters


# Now download
header("Content-Type: application/octet-stream");
header("Content-Length: " . strlen($installer_binary));  # we want actual bytes
header("Content-Disposition: attachment; filename=installer_openpilot");
echo $installer_binary;  # downloads without saving to a file
exit;
?>
