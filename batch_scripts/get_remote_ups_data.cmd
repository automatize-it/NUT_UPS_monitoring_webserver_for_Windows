@ECHO Off
SETLOCAL ENABLEDELAYEDEXPANSION

SET UPSNAME=%1
SET HOSTNAME=
SET KEYS=id,ts,
SET RAWDATA=
SET UPSNOTOL=0
SET BMFRD=0
SET SQLPASS=

:: GET HOSTNAME
FOR /f "tokens=2 delims=@" %%H IN ('ECHO %UPSNAME%') DO SET HOSTNAME=%%H

::IF HAVE ANY NON-NUT-COMPATIBLE UPSS, THEY GO HERE
find "%UPSNAME%" nutuncap.txt>NUL
IF %ERRORLEVEL% EQU 0 (
	
:: HERE MUST DO IFS FOR EVERY SPECIAL UPS CASE
	cd usrcmd\ippon
		
	for /f "tokens=1,2 delims=:" %%I IN ('powershell .\ippon_get_data.ps1') DO (
	
		rem ECHO %%I %%J
		find "%%I" ..\..\standart_fieldset.txt /C>nul
		IF !ERRORLEVEL!==0 IF %%I NEQ device.type SET KEYS=!KEYS!`%%I`, && SET RAWDATA=!RAWDATA!'%%J',
		IF !ERRORLEVEL!==0 IF %%I==device.type SET KEYS=!KEYS!`%%I`, && SET RAWDATA=!RAWDATA!'!HOSTNAME!',
		IF %%I==ups.status IF "%%J" NEQ " OL" SET UPSNOTOL=1 && echo !UPSNAME! !date! !time! %%J >>pflog.txt
		IF !ERRORLEVEL!==1 IF %%I==battery.mfr.date SET KEYS=!KEYS!`%%I`, && SET RAWDATA=!RAWDATA!0000-00-00,
	)
	
	cd .. && cd ..
	goto :NXT
)

:: CHECK FOR HOST AVAILABILITY
etimeout 2000 bin\upsc %UPSNAME% ups.status
IF %ERRORLEVEL% NEQ 0 GOTO :HOSTUNAVLBL 

:: HOST AVAILABLE, GET INFO
SET SCR=bin\upsc %UPSNAME%
for /f "tokens=1,2 delims=:" %%I IN ('%SCR%') DO (
	
	find "%%I" standart_fieldset.txt /C>nul
	IF !ERRORLEVEL!==0 IF %%I NEQ device.type SET KEYS=!KEYS!`%%I`, && SET RAWDATA=!RAWDATA!'%%J',
	IF !ERRORLEVEL!==0 IF %%I==device.type SET KEYS=!KEYS!`%%I`, && SET RAWDATA=!RAWDATA!'!HOSTNAME!',
	IF %%I==ups.status IF "%%J" NEQ " OL" SET UPSNOTOL=1 && echo !UPSNAME! !date! !time! %%J >>pflog.txt
	IF !ERRORLEVEL!==1 IF %%I==battery.mfr.date SET KEYS=!KEYS!`%%I`, && SET RAWDATA=!RAWDATA!0000-00-00,
)

:NXT
SET RAWDATA=%RAWDATA:~0,-1%
SET KEYS=%KEYS:~0,-2%
SET RAWDATA=%RAWDATA:  =%
SET RAWDATA=%RAWDATA:' ='%
SET RAWDATA=%RAWDATA: '='%
SET RAWDATA=NULL,now(),%RAWDATA%

SET QRY=INSERT INTO `%UPSNAME%` (%KEYS%) VALUES (%RAWDATA%);

mysql.exe -e "%QRY%" -h 127.0.0.1 --user=upsmonW --password=%SQLPASS% ups_list
IF %UPSNOTOL% NEQ 0 EXIT 12
exit 0

:HOSTUNAVLBL
REM ping -n 2 -w 100 %HOSTNAME% | find "мс"
rem ECHO %ERRORLEVEL%
REM IF %ERRORLEVEL%==0 exit 11
exit 11