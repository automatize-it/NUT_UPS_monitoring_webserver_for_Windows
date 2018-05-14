rem SET QRY=SELECT table_name FROM information_schema.tables WHERE table_schema='ups_list'
FOR /F "tokens=*" %%I IN ('mysql.exe --silent -s -r -h localhost --user^=root --password^=pass ups_list ^< ups_query.sql') DO (
	
	IF %%I NEQ main get_remote_ups_data.cmd %%I
)