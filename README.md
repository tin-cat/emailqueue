# Emailqueue #
A fast, simple yet very efficient email queuing system for PHP/MySQL
https://github.com/lorenzoherrera/emailqueue
Copyright (C) 2015 Lorenzo Herrera (hi@lorenzoherrera.com)


Almost anyone who has created a web application that sends emails to users in the form of newsletters, notifications, etc. has tried first to simply send the email from their code using the PHP email functions, or maybe even some advanced emailing library like the beautifully crafted PHPMailer (https://github.com/Synchro/PHPMailer). Sooner or later, though, they come to realize that triggering an SMTP connection from within their code is not the most efficient way to make a web application communicate via email with their users, mostly because this will make your code responsible about any SMTP connection errors and, specially, add all the SMTP delays to the user experience.

This is where solutions like Emailqueue come in handy: Emailqueue is not an SMTP relay, and not an email sending library like PHPMailer (though it uses PHPMailer for final deliver, actually). Think of it as an intermediate, extremely fast, post office where all the emails your application needs to send are temporarily stored, ordered and organized, this is how it works:

* Your application needs to send an email to a user (or 10 thousand emails to 10 thousand users), so instead of using the PHP mail functions or PHPMailer, it simply adds the email to Emailqueue. You can add emails to Emailqueue by injecting directly into Emailqueue's database or using the provided PHP class.

* The insertion is made as fast as possible, and your application is free to go. Emailqueue will take care of them.

* Every minute, a cronjob calls the Emailqueue's delivery script, completely appart from your running application. Emailqueue checks the queue and sends the queued emails at its own pace. You can configure a delay between each email and the maximum number of emails sent each minute to even tune the delivery speed and be more friendly to external SMTPs.

* Emailqueue even does some basic job at retrying emails that cannot be sent for whatever reason, and stores a history of detected incidences.

* Sent emails are stored on emailqueue's database for you to check who received what. A purge script can be regularly called via cronjob to automatically delete old, already sent emails to avoid your emailqueue database grow too big.


##Best features##

* Inject any number of emails super-fast and inmediately free your app to do other things. Let Emailqueue do the job in the background.

* Prioritize emails: Specify a priority when injecting an email and it will be sent before any other queued emails with lower priorities. E.g: You can inject 100k emails for a newsletter with priority 10 (they will take a while to be sent), and still inject an important email (like a password reminder message) with priority 1 to be sent ASAP even before the huge newsletter has been sent.

* Schedule emails: Inject now an email and specify a future date/time for a scheduled delivery.

* The code is quite naive, built about 15 years ago. But boy, it's been tested! This means it will be very easy for you if you decide to branch/fork it and improve it. Emailqueue is a funny grown man.


##Caveats##

* Since the delivery of the emails is triggered via a cronjob, and the fastest rate at which a cronjob can be fired is once per minute, emails sent with Emailqueue may take up to 1 minute to be finally delivered.


##TO-DOs##

* Recode the file logging system.
* Emailqueue does not allows attachments yet.
* Recode it to modern standards.
* A way to provide secured hard-links to view individual emails, so that a hard link can be included within the email to the user with a link like "Can't see this message? click here to see it in your browser"


##How to install##

* Copy the entire emailqueue folder somewhere it can be accessed via web.
    It's not strictly mandatory to install emailqueue under a web public directory, but will make installation easier. Proper .htaccess files are already placed properly to avoid sensible directories from being served by your web server to the public.
    Don't forget to copy hidden .htaccess files

* Copy the provided .example files under /config to versions with .php extension and edit them to configure Emailqueue for your system.

* Create a database in your server with your desired name. e.g: emailqueue

* Run the provided docs/emailqueue.sql on that database to create the initial database structure.

* Be sure the shell scripts scripts/delivery and scripts/purge are executable.

* Modify the scripts/delivery and scripts/purge files to match the installation directory of your Emailqueue

* Setup two cronjobs in your linux to execute regularly the delivery and purge scripts, e.g:
  	* $ crontab -e
  	* Add the following lines:
  	* \* \* \* \* \* /var/www/htdocs/emailqueue/scripts/delivery
  	* 0 6 \* \* \* /var/www/htdocs/emailqueue/scripts/purge

  	* The delivery script delivers pending emails in the queue. Running it every minute is recommended.

  	* The purge script removes old & already sent emails from the queue to avoid the queue from growing too big. Running it every day is more than enough.


* You should be ready to go, now you can:

	* See the status of the queue by accessing /emailqueue/frontend in your browser.

	* You can now inject messages to the queue by updating the database by yourself (the "emails" table, field names are pretty self-explaining)

	* You can optimally use the provided emailqueue_inject PHP class, found in scripts/emailqueue_inject.class.php. See an example on how to use this class in scripts/emailqueue_inject_test.php


##License##

Emailqueue is released under the GNU GPL v2.0 (See LICENSE file). Emailqueue includes PHPMailer (https://github.com/Synchro/PHPMailer), which is also licensed under GNU GPL v2.0

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.