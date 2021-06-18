upsList=()

while true
do 
	# your mysql creds file path here
	for i in $(mysql --defaults-extra-file=mySqlCrds.txt --silent -s -r ups_list < upsListQry.sql)
	do
		bash getUpsDataEnh.sh $i
		
		#remove or modify for more/less active monitoring. Remember that more active monitoring may lead to glitches
		sleep 1
		
	done;
done;
