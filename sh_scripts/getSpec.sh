declare -A defVals

defVals["battery.charge"]=".batCapacity"

defVals+=( ["battery.voltage"]=".batV" ["input.voltage"]=".inVolt" ["ups.load"]=".loadPercent" ["device.type"]=".model" ["ups.status"]=".device | .statusIcon" ["battery.runtime"]=".batTimeRemain")

upsurl="https://server1:3456/0/json"

jsn=$(wget -qO- --no-check-certificate $upsurl)

#${orig//[xyz]/_}
# Write-Host "battery.voltage:"(($jsn | .\jq ".batV" -r) -replace "V")
# Write-Host "input.voltage:"(($jsn | .\jq ".inVolt" -r) -replace "V")
# Write-Host "ups.load:"(($jsn | .\jq ".loadPercent" -r) -replace "%")
# Write-Host "device.type:"

# $tmp = ($jsn | .\jq ".batTimeRemain" -r)
# Write-Host "battery.runtime:"(([int]($tmp[0])*60)+([int]($tmp[1] -replace "s")))
# $tmp = ($jsn | .\jq ".device | .statusIcon" -r)
# if ($tmp -eq "online"){
	# Write-Host "ups.status: OL"
# }
# else{
	# Write-Host "ups.status: OB"
# }

#echo ${key} ${defVals[${key}]}
for key in ${!defVals[@]}; do
    
	tmp=$(echo $jsn | jq "${defVals[${key}]}" -r)
	#remove symbols of non-NUT standard
	tmp=${tmp//%/}
	tmp=${tmp//V/}
	
	if printf '%s\n' "${key}" | grep -Fq "battery.runtime" ; then
		
		unset IFS
		IFS='m s'
		tmpArr=($tmp)
		#echo ${tmpArr[0]} ${tmpArr[1]}
		unset IFS
		
		tmp=$(((${tmpArr[0]}*60)+${tmpArr[1]}))
	fi;
	
	if [ "${key}" = "ups.status" ] ; then
		
		if [ "$tmp" = "online" ] ; then
			tmp="OL"
		else
			tmp="OB"
		fi
	fi;
	
	echo ${key}': '$tmp
done


