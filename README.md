# NUT_UPS_monitoring_webserver_for_Windows

Web server for Network UPS Tools (UPS) https://networkupstools.org/ Windows version.

NUT monitoring UPS in local network. Just monitoring yet.

06.2021 UPDATE:
+ shell scripts
+ on-battery last start value parameter added
V less DB usage with getUpsDataEnh.sh
V nicer authorization
V small fixes and improvements
! looking for someone to add http authorization, docker it all, and to make client installations less handy

05.2020 UPDATE:
Still alive!
+ major web-interface speed improvements
+ re-work on-battery time calculations
+ DBs performance checker
+ some php "backend"
+ optimizations and minor changes

08.2019 UPDATE:
+ added automatic UPS analytics (check it)
+ added database optimization
+ added UPS monitoring suspension/return
+ interface reorganized
+ fixed on-battery time calculations
+ optimizations and minor changes

Feel free to ask any questions.


Usage:
1. Install and configure NUT hosts;
2. Add dlls from dll if neccessary;
3. Get web server. I use http://www.uniformserver.com ;
4. Put MySQL db from db to server's db dir;
5. Put files from www to server's www dir;
6. Put scripts from batch_scripts in NUT dir, set your creds in MySQL;
7. Run web server, open http://localhost/index_ups.php and add some UPS in format UPS_name@host ;
8. Run get_ups_list_mysql.cmd ;
9. Check UPS info ;
10. If something's not working, check some checks in others.\

<img src="https://raw.githubusercontent.com/automatize-it/NUT_UPS_monitoring_webserver_for_Windows/master/scrshts/Main_screen.png"/>
<img src="https://raw.githubusercontent.com/automatize-it/NUT_UPS_monitoring_webserver_for_Windows/master/scrshts/Suspended_upss.png"/>
<img src="https://raw.githubusercontent.com/automatize-it/NUT_UPS_monitoring_webserver_for_Windows/master/scrshts/Ups_Info.png"/>
<img src="https://raw.githubusercontent.com/automatize-it/NUT_UPS_monitoring_webserver_for_Windows/master/scrshts/Opt_db.png"/>
<img src="https://raw.githubusercontent.com/automatize-it/NUT_UPS_monitoring_webserver_for_Windows/master/scrshts/batt_date.png"/>
