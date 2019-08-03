# NUT_UPS_monitoring_webserver_for_Windows

Web server for Network UPS Tools (UPS) https://networkupstools.org/ Windows version.

NUT monitoring UPS in local network. Just monitoring yet.

08.2019 UPDATE:
+ added automatic UPS analytics (check it)
+ added database optimization
+ added UPS monitoring suspension/return
V interface reorganized
V fixed on-battery time calculations
V optimizations and minor changes

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

<img src="https://raw.githubusercontent.com/automatize-it/NUT_UPS_monitoring_webserver_for_Windows/master/scrshts/nut_ups_mon_scr_new1.PNG"/>
<img src="https://raw.githubusercontent.com/automatize-it/NUT_UPS_monitoring_webserver_for_Windows/master/scrshts/nut_ups_mon_scr_new2.PNG"/>

