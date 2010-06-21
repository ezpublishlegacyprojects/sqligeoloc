<?php /* #?ini charset="utf-8"?

[CronjobSettings]
ExtensionDirectories[]=sqligeoloc

# Update IP/Countries database. Should be run at least once a month
[CronjobPart-sqligeolocupdate]
Scripts[]=sqligeolocupdate.php
