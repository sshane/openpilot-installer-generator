# openpilot-installer-generator
A PHP webpage that uses string replacements to generate a binary on the fly that you can enter at setup in NEOS.

## What is this?
Previously to install a custom fork from scratch, you would enter `https://openpilot.comma.ai` in the Custom Software prompt for NEOS, then `ssh` in later and clone the actual fork you want. Now you can enter a URL during set up to install any openpilot fork available on GitHub without first cloning stock.

## Usage
The syntax is pretty simple, with up to 3 arguments you can pass the generator site: `https://smiskol.com/fork/[username]/{branch}/{loading_msg}`

Where `username` is the required username of the fork, `branch` is the branch to clone, and `loading_msg` is the text it displays when cloning the repo (`Installing {loading_msg}`). `branch` and `loading_msg` are optional.

- If `branch` is left blank (https://smiskol.com/fork/commaai), git will clone the default branch on GitHub.
- If `loading_msg` is left blank, then the installer will display `Installing {username}` unless the fork has a custom loading message (check the index.php for aliases).
- While `username` is required if you visit the website on your desktop, if you `wget` the site or enter just `/fork` during set up, it will install the release2 branch of stock openpilot.

**Example:** https://smiskol.com/fork/shanesmiskol installs the Stock Additions fork.

## Aliases
Defined in the [index.php](fork/index.php) file, aliases allow for more quickly installing a fork with a long username.

- To install [dragonpilot](https://github.com/dragonpilot-community/dragonpilot), you can simply use the following URL which uses the `devel-i18n` branch by default: https://smiskol.com/fork/dp
- To install [Stock Additions](https://github.com/shanesmiskol/openpilot), my fork, enter the URL: https://smiskol.com/fork/shane
- To install [stock openpilot](https://github.com/commaai/openpilot), enter the URL: https://smiskol.com/fork/commaai or just https://smiskol.com/fork

If you are a fork developer, have a repository that is not named `openpilot`, and want to allow users to install with this tool, you can take advantage of GitHub's internal compatibility aliasing and just temporaily rename your fork to `openpilot` and rename it back. You can see an example of this in action with "https://github.com/dragonpilot-community/openpilot" redirecting to "https://github.com/dragonpilot-community/openpilot". 

## Misc
### Custom loading message without branch
If you want to specify a custom loading message but use the default branch (not passing in a branch), you can pass an underscore for the branch. For example: https://smiskol.com/fork/shane/_/some%20additions%20or%20something

### Source code to build the custom installer
[Here's the source code for the custom installer binary.](/misc/installer_source.c)

[And here's the diff and a branch you can check out to build it on your EON/C2.](https://github.com/ShaneSmiskol/openpilot/compare/6f703eaf4e0de0cbd61d30cb8468004d2949298f...ShaneSmiskol:installer)

---
<sub>Idea by nelsonjchen on the [openpilot Discord](https://discord.comma.ai/)!</sub>
