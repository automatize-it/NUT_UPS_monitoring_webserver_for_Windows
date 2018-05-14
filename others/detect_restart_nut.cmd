tasklist | find "upsd"
IF %ERRORLEVEL%==1 NET STOP "Network UPS Tools" && NET START "Network UPS Tools"