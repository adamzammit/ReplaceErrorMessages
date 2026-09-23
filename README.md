# LimeSurvey ReplaceErrorMessages plugin
LimeSurvey plugin for replacing the built in error messages

## Requirements
- LimeSurvey 3.X, 6.X, or 7.X

## Installation instructions
- Download the zip from the [releases](https://github.com/adamzammit/ReplaceErrorMessages/releases) page and extract to your plugins folder.
- You can also upload the same zip in *Configuration -> Plugins -> Upload & install*.
- Please note that LimeSurvey only accepts a zip file contains top level folder named exactly `ReplaceErrorMessages`. With any other name the upload stops with "Could not parse config.xml file.", and an update leaves the plugin folder without its `config.xml`. When you forked this repository and the repository name is changed or contains the branch name(for example `ReplaceErrorMessages-main`), you should change zip file or folder name after you downloaded from GitHub's "Code -> Download ZIP" button.

## Usage
- Enable the plugin
- Set the new messages you want to appear in the plugin configuration. Leave blank to keep the system default.
	- Changing the settings in the plugin settings will change the messages for all surveys in the system by default
- If you want to change the message that appears for a particular survey, you can go to the "Simple plugin settings" for the survey and change the text
	- Entering a message at the survey level will ensure that the message appears for that survey
    - If left blank, it will check for the settings at the system plugin level, if those are blank, the system default will apply

## Acknowledgements

- LimeSurvey: https://github.com/LimeSurvey/LimeSurvey
 
## Licence

GPLv3
