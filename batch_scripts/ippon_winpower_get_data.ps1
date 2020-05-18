##################################################################################
# this is wrapper on powershell v1 for data from non-NUT UPSs, for IPPON SW 1500 #
# in my case. Data is being grabbed from Winpowers (native SW) webserver and     #
# being formatted to standard NUT format.                                        #
# ConvertFrom-Json could be used but it's for PS 2+ and MS wants soul be         #
# sold while installing it on W7 so jq is another good option                    #
# maybe wget also                                                                #
# but cmd completely fails in this task                                          #
# VERY DRAFT, v 0.2. Still, works                                                #
##################################################################################

##################################################################################
# so you need:                                                                   #
# - winpower web-server enabled                                                  #
# - wget for windows                                                             #
# - jq for windows                                                               #
# - modify original get_remote_ups_data.cmd like                                 #
# 		IF "%UPSNAME%"=="youripponups@host" (
#			for /f ('this_script.ps1') DO (
#					same as original	
#			)
#		)
#		then just skip original
#		goto :NXT rem 
#		                                                                         #
#                                                                                #
##################################################################################

$upsurl = "https://yourserver:yourport/0/json"

$jsn = .\wget.exe -qO- --no-check-certificate $upsurl 2> $null 

Write-Host "battery.charge:"(($jsn | .\jq ".batCapacity" -r) -replace "%")
Write-Host "battery.voltage:"(($jsn | .\jq ".batV" -r) -replace "V")
Write-Host "input.voltage:"(($jsn | .\jq ".inVolt" -r) -replace "V")
Write-Host "ups.load:"(($jsn | .\jq ".loadPercent" -r) -replace "%")
$tmp = ($jsn | .\jq ".batTimeRemain" -r)
Write-Host "battery.runtime:"(([int]($tmp[0])*60)+([int]($tmp[1] -replace "s")))
$tmp = ($jsn | .\jq ".device | .statusIcon" -r)
if ($tmp -eq "online"){
	Write-Host "ups.status: OL"
}
else{
	Write-Host "ups.status: OB"
}