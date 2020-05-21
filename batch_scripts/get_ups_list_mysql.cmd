@ECHO ON
SETLOCAL ENABLEDELAYEDEXPANSION

SET SQLPASS=yourpass

:START
FOR /F "tokens=*" %%I IN ('mysql.exe --silent -s -r -h localhost --user^=root --password^=%SQLPASS% ups_list ^< ups_query.sql') DO (
	
	IF %%I NEQ main START /WAIT /min get_remote_ups_data.cmd %%I
	rem SET TMPERRL=!ERRORLEVEL!
	rem SET TMPUPS=%%I
	rem SET TMPUPS=!TMPUPS: =!
)