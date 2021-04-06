# openpilot-installer-generator
A PHP webpage that uses string replacements to generate a binary on the fly that you can enter at setup in NEOS.

## What is this?
Previously to install a custom fork from scratch, you would enter `https://openpilot.comma.ai` in the Custom Software prompt for NEOS, then `ssh` in later and clone the actual fork you want. Now you can enter a URL during set up to install any openpilot fork available on GitHub without first cloning stock.

## Usage
The syntax is pretty simple, with up to 3 arguments you can pass the generator site: `https://smiskol.com/fork/[username]/{branch}/{loading_msg}`

Where `username` is the required username of the fork, `branch` is the branch to clone, and `loading_msg` is the text it displays when cloning the repo (`Installing {loading_msg}`). `branch` and `loading_msg` are optional.

- If `branch` is left blank (`https://smiskol.com/fork/commaai`), git will clone the default branch on GitHub.
- If `loading_msg` is left blank, then the installer will display `Installing {username}` unless the fork has a custom loading message (check the index.php for aliases).
- While `username` is required if you visit the website on your desktop, if you `wget` the site or enter just `/fork` during set up, it will install the release2 branch of stock openpilot.

**Example:** https://smiskol.com/fork/shanesmiskol installs the Stock Additions fork.

Idea by nelsonjchen on the [openpilot Discord](https://discord.comma.ai/)!
