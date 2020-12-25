:STRT
FOR /F "tokens=*" %%I IN ('mysql.exe --silent -s -r -h localhost --user^=root --password^=gfhjkm ups_list ^< ups_query.sql') DO (
	
	IF %%I NEQ main start get_remote_ups_data.cmd %%I
)
timeout 15
GOTO :STRT