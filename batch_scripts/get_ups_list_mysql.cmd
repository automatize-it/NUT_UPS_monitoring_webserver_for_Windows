@ECHO ON
SETLOCAL ENABLEDELAYEDEXPANSION
SET TMPUPS=XXX
SET AWOLUPSLIST=empty,
SET OBUPSLIST=empty,
set msg=
set tmperrl=0
set send=0
SET STATUS=OLDEF

:START
FOR /F "tokens=*" %%I IN ('mysql.exe --silent -s -r -h localhost --user^=root --password^=mypass ups_list ^< ups_query.sql') DO (
	
	IF %%I NEQ main START /WAIT /min get_remote_ups_data.cmd %%I 
	SET TMPERRL=!ERRORLEVEL!
	SET TMPUPS=%%I
	SET TMPUPS=!TMPUPS: =!
	IF !TMPERRL! GTR 10 CALL :ALERTSUBR
)

GOTO :START

:CHECKSUBR
ECHO %AWOLUPSLIST% | find "%TMPUPS%"
IF %ERRORLEVEL%==0 (
	
	::YOUR CODE TO SEND MAIL ALERT HERE
	::call SET AWOLUPSLIST=%%AWOLUPSLIST:%TMPUPS%=%%
	::SET MSG="RETURNED FROM AWOL"
	::pushd D:\scripts\mailsend
	::start ups_alert.cmd %TMPUPS% !MSG!
	::popd	
)
GOTO :EOF

:ALERTSUBR

ECHO %OBUPSLIST% | find "%TMPUPS%" 
IF %ERRORLEVEL% NEQ 0 IF %TMPERRL%==12 SET OBUPSLIST=%OBUPSLIST%%TMPUPS%, && SET MSG="POWER GONE OR OTHER LINE FAIL" && SET SEND=1
for /f "tokens=*" %%Z in ('bin\upsc %TMPUPS% ups.status') DO SET STATUS=%%Z

IF %SEND%==1 (
	
	::YOUR CODE TO SEND MAIL ALERT HERE
)