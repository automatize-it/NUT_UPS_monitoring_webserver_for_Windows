@ECHO Off
SETLOCAL ENABLEDELAYEDEXPANSION

SET UPSNAME=%1
SET KEYS=id,ts,
SET RAWDATA=
rem SET ERRCOM=0
for /f "tokens=1,2 delims=:" %%I IN ('bin\upsc %UPSNAME%') DO (
	
	find "%%I" standart_fieldset.txt /C>nul
	IF !ERRORLEVEL!==0 SET KEYS=!KEYS!`%%I` && SET KEYS=!KEYS!, && SET RAWDATA=!RAWDATA!'%%J' && SET RAWDATA=!RAWDATA!,
)

SET RAWDATA=%RAWDATA:~0,-1%
SET KEYS=%KEYS:~0,-2%
SET RAWDATA=%RAWDATA:  =%
SET RAWDATA=%RAWDATA:' ='%
SET RAWDATA=%RAWDATA: '='%
SET RAWDATA=NULL,now(),%RAWDATA%

SET QRY=INSERT INTO `%UPSNAME%` (%KEYS%) VALUES (%RAWDATA%);

mysql.exe -e "%QRY%" -h localhost --user=root --password=pass ups_list