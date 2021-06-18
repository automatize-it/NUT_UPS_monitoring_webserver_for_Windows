#these fields are +- standard NUT info, can be modified if needed, then must redefine (def=NULL) or add DB fields 
fldsDefArr=(battery.charge battery.charge.low battery.charge.warning battery.mfr.date battery.runtime battery.type battery.voltage device.mfr device.model device.serial device.type driver.name driver.parameter.pollfreq driver.parameter.pollinterval driver.version driver.version.data driver.version.internal input.frequency input.voltage input.voltage.nominal output.frequency output.voltage output.voltage.nominal ups.beeper.status ups.date ups.delay.shutdown ups.load ups.mfr ups.model ups.productid ups.serial ups.status ups.test.result ups.timer.shutdown ups.vendorid)

fldsArr=()

valArr=()

curArr=()


trim() {
    local var="$*"
    # remove leading whitespace characters
    var="${var#"${var%%[![:space:]]*}"}"
    # remove trailing whitespace characters
    var="${var%"${var##*[![:space:]]}"}"   
    printf "'%s'" "$var"
}


genSqlUpdStr() {
	
	local tmp=$1
	tmpStr="ts=VALUES(ts),"
		
	unset IFS
	IFS=','
	
	for i in $tmp
	do
		tmpStr+="$i=VALUES($i),"
	done
	
	tmpStr=${tmpStr::-1}
	
	unset IFS
	echo $tmpStr
}


upsNm=$1

getCmd="$(timeout 1 upsc $upsNm)"

# all non-defined data cases will be put in DB
curUpsStatus="OB"

#add all your special non-NUT cases here
if [ "$upsNm" = "yourSpecialCaseLikeWMIorOther@host" ] ; then
	
	getCmd="$(timeout 1 bash getSpec.sh)"
fi

unset IFS
IFS='@'

tmpArr=($upsNm)
hstNm=${tmpArr[1]}

unset IFS
IFS=':
'
tmp=0

for i in $getCmd
do	
	if printf '%s\n' "${fldsDefArr[@]}" | grep -Fq "$i" ; then
		
		tmp=1
		fldsArr+=('`'$i'`')
		continue
	fi;
	
	if [ $tmp -eq 1 ]; then
		
		if printf '%s\n' "${fldsArr[${#fldsArr[@]}-1]}" | grep -Fq "\`device.type\`" ; then 
			valArr+=($(trim $hstNm))
			tmp=0
			continue
		fi
		
		if [ "${fldsArr[${#fldsArr[@]}-1]}" = "\`ups.status\`" ] ; then 
			
			curUpsStatus=$i
		fi
		
		if [ "${fldsArr[${#fldsArr[@]}-1]}" = "\`ups.load\`" ] ; then 
			
			curUpsLoad=$i
		fi
		
		tmp=0
		valArr+=($(trim $i))
	fi;
done;

unset IFS

tmp1=$(printf '%s,' "${fldsArr[@]}")
tmp1=${tmp1::-1}
tmp2=$(printf '%s,' "${valArr[@]}")
tmp2=${tmp2::-1}

if [ ${#valArr[@]} -eq 0 ]; then
	exit
fi

#dont forget to insert your DB creds file
lastUpsData=($(mysql --defaults-extra-file=mySqlCrds.txt --silent -s -r ups_list -N -e "SELECT \`ups.load\`,\`id\` FROM \`$upsNm\` ORDER BY id DESC LIMIT 1"))

loadDiff=$(($((curUpsLoad + 0)) - ${lastUpsData[0]}))
loadDiff=${loadDiff#-}

if [ "$curUpsStatus" = " OL" ] && [ $loadDiff -lt 3 ] ; then
	
	#just update last string if everything seems OK, this saves a lot of DB
	mysql --defaults-extra-file=/home/monadm/ntbksMap/shared/nutWebServer/mySqlCrds.txt --silent -s -r -e "INSERT INTO \`$upsNm\` (id,ts,$tmp1) VALUES (${lastUpsData[1]},now(),$tmp2) ON DUPLICATE KEY UPDATE $(genSqlUpdStr $tmp1)" ups_list
else
	
	#something's not OK, put in DB as new information
	mysql --defaults-extra-file=/home/monadm/ntbksMap/shared/nutWebServer/mySqlCrds.txt --silent -s -r -e "INSERT INTO \`$upsNm\` ($tmp1) VALUES ($tmp2)" ups_list
fi


