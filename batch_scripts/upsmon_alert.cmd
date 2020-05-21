CD C:\NUT
SET MSG=%1
SET SQLPASS=yourpass
SET upsnm=XXX
SET STTS=OL
FOR /F "tokens=2 delims= " %%F IN ('ECHO %MSG%') DO SET UPSNM=%%F

ECHO %MSG% | find "battery" 
IF %ERRORLEVEL%==0 SET STTS="OB"

get_remote_ups_data.cmd %UPSNM%
::IF HOST IF UNAVAILABLE DO RECORD MANUALLY
IF %ERRORLEVEL%==11 IF %STTS%==OB (
	
	SET QRY=INSERT INTO `%UPSNAME%` (`ups.status`) VALUES ('OB');
	mysql.exe -e "%QRY%" -h 127.0.0.1 --user=root --password=%SQLPASS% ups_list
)
