# hackalog
Stupid little PHP script to scan log files and pull the IPs of people trying to break into sites

The only thing ignored more than a read me file is an ad for black market ED pills... SCH

What the heck is this thing? 
----------------------------
The best place to look for people trying to break into a site is the log files. I'd say 90% of the hack attempts I have blocked started with cruising the logs. This little piece of code is a simple little file processor that looks for people trying to do things they shouldn't.

Why is it in PHP?
-----------------
It is one of the languages I script in and it is easy for people to make their own changes. I might make this again in Rust when I get up to speed, but for now PHP is happy to do it. 

Isn't it slow? 
--------------
Probably not as slow as you'd think. I run it local on my XAMPP set up on my laptop. I ran a 30 day/file test that was 1.05 gig uncompressed. It ran the entire month in about 30 seconds. Sure I could probably run it in 5 or 10 seconds under C++, but it doesn't take long enough to run that I will mess with it.

How do I use it?
----------------
Create a directory where you want it to live and copy hackalog.php to it. Make a subdirectory there called 'logs'. Copy your log files to the logs folder, put in the URL for where Hackalog lives in your browser window and wait. That's it. I wanted it simple.

Can I change it?
----------------
Sure. Just don't try to take credit for it. 

What's with the name?
---------------------
I was cruising a log file and thought, "It'd sure be nice to be able to catalog these clowns easier. Like a hackalog of sorts."

OK so how does it work?
-----------------------
Skiddies run a lot of garbage against sites. Mostly just dumb crap that tries to open a WordPress admin page on non-WordPress sites or keep pounding on the admin login hoping something happens. Some people try to use exploits with base64 or eval scripts to try and insert code onto a site. Your log files record all of this. So we look for people doing those activities, and build a new file of the log entries to show what they did.
That results in the file hackalog.txt in the logs folder. Next I load the hackalog.txt file and sort it so the IPs group together then write that to hackalog2.txt. That file will let you see someone who is hitting you in small batches spread over a long period.
Then we write a file called BadIPs.txt. This is just the IP numbers of the offending parties with nothing else. Before you do anything with those IPs you need to look through hackalog2.txt for false positives. 

What do you mean by false positives?
------------------------------------
If you toss that entire list into the block bin, you could end up blocking people legitimately on your site. 
Some examples: A calendar module has an event with the word "admin" in it.
Your career page lists a sys admin job.
Your web manager logs in from home to fix a typo.

Anything else?
--------------
Right now the code will ignore any address that begins with 10. because our admin page can only be accessed from a 10. address. If an internal machine were compromised this code wouldn't catch it without removing that check. If your own network is a different octet, just change the "10." to whatever you need, like "192." and change the substr command to look for 4 characters instead of 3.
Also look for out of place IPs in the list. If you find one search hackalog2.txt file for the out of place IP. Some browsers will report a version number erroneously as an IP. 

For instance:

98.124.99.102

1.6.987.0

98.132.11.10

That middle number is both out of place and range. I plan to expand the regex that pulls the ip to pull in range, but that wouldn't catch all the version numbers in range.
The code is heavily commented. Almost every action is commented to explain what it does and why. I purposely kept the code as simple as possible so just about anyone could use and modify it.

I'm getting an error!
---------------------
The first thing to look at is how big the .txt files have gotten. Since the .txt files expand with each run it is probably too large to be loaded by the script. You can hit your php.ini and bump up the file size, but you should delete the .txt files and run with fewer log files. If you have one giant log file, break it into multiple files. Hackalog doesn't care if you split large files up. If you routinely run large single files you need to bump up the file size and memory php is allowed to use. (fyi this is another reason to run this on a local dev setup)
The other thing you could run into is a timeout. Just adjust the timeout in your php.ini to a couple minutes.

History
-------
Beta 1 - Initial test

Beta 2 - Cleaned up loops, changed the display of the log entry to a tick mark to prevent browsers from executing malicious code.

Beta 3 - Tick marks given a wider range of expression. Colors show how serious an attack was made. File not found error message changed. Cleaned up comments.
