# Emailqueue #
**A fast, simple yet very efficient email queuing system for PHP/MySQL**

https://github.com/tin-cat/emailqueue

By Lorenzo Herrera (lorenzo@tin.cat)


Almost anyone who has created a web application that sends emails to users in the form of newsletters, notifications, etc. has tried first to simply send the email from their code using the PHP email functions, or maybe even some advanced emailing library like the beautifully crafted PHPMailer (https://github.com/Synchro/PHPMailer). Sooner or later, though, they come to realize that triggering an SMTP connection from within their code is not the most efficient way to make a web application communicate via email with their users, mostly because this will make your code responsible about any SMTP connection errors and, specially, add all the SMTP delays to the user experience.

This is where solutions like Emailqueue come in handy: Emailqueue is not an SMTP relay, and not an email sending library like PHPMailer (though it uses PHPMailer for final deliver, actually). Think of it as an intermediate, extremely fast, post office where all the emails your application needs to send are temporarily stored, ordered and organized, this is how it works:

* Your application needs to send an email to a user (or 10 thousand emails to 10 thousand users), so instead of using the PHP mail functions or PHPMailer, it simply adds the email to Emailqueue. You can add emails to Emailqueue by injecting directly into Emailqueue's database or using the provided PHP class.

* The insertion is made as fast as possible, and your application is free to go. Emailqueue will take care of them.

* Every minute, a cronjob calls the Emailqueue's delivery script, completely apart from your running application. Emailqueue checks the queue and sends the queued emails at its own pace. You can configure a delay between each email and the maximum number of emails sent each minute to even tune the delivery speed and be more friendly to external SMTPs.

* Emailqueue even does some basic job at retrying emails that cannot be sent for whatever reason, and stores a history of detected incidences.

* Sent emails are stored on emailqueue's database for you to check who received what. A purge script can be regularly called via cronjob to automatically delete old, already sent emails to avoid your emailqueue database grow too big.


##Best features##

* Inject any number of emails super-fast and inmediately free your app to do other things. Let Emailqueue do the job in the background.
* Prioritize emails: Specify a priority when injecting an email and it will be sent before any other queued emails with lower priorities. E.g: You can inject 100k emails for a newsletter with priority 10 (they will take a while to be sent), and still inject an important email (like a password reminder message) with priority 1 to be sent ASAP even before the huge newsletter has been sent.
* Schedule emails: Inject now an email and specify a future date/time for a scheduled delivery.
* The code is quite naive, built about 15 years ago. But boy, it's been tested! This means it will be very easy for you if you decide to branch/fork it and improve it. Emailqueue is a funny grown man.

##Changelog##

* **Version 3.0.13**
  * Finally Emailqueue supports attachments! See the "Hints" section for an interesting idea with this.
  * Super-powerful functionality to auto-embed all images in your messages as attachments, so they should appear really fast on the user's screen once received (no additional images to download by the client). Also, some email clients might show the images straightaway without asking the user to download them.
  * Much better documentation.
  * Some code cleanup. Still very naive code, tough.
  * Some visual improvements to the frontend, also including a new futuristic logo in all PNG glory, and a beautiful tiny favicon.
  * PHPMailer library is now a GIT submodule.
  * Added animal abuse manifesto.

##Caveats##

* Since the delivery of the emails is triggered via a cronjob, and the fastest rate at which a cronjob can be fired is once per minute, emails sent with Emailqueue may take up to 1 minute to be finally delivered.

##TO-DOs##

* Recode the file logging system.
* Recode it to modern standards.
* A way to provide secured hard-links to view individual emails, so that a hard link can be included within the email to the user with a link like "Can't see this message? click here to see it in your browser"

##How to install##

* Clone the emailqueue repository wherever you want it.
    * It's not strictly mandatory to install emailqueue under a web public directory, but will make installation easier. ".htaccess" files are already properly placed to avoid sensible directories from being served publicly.

* Emailqueue depends on the great PHPMailer, and it's builtin as a GIT submodule. To clone it properly, add the `--recursive` commandline parameter when doing your git clone, like so:

    `git clone https://github.com/tin-cat/emailqueue.git --recursive`

    Or you can simply clone normally, and then execute the `git submodule init` and then `git submodule update`commands to download dependant libraries.

* Create a database in your server with your desired name. e.g: emailqueue

* Run the provided docs/emailqueue.sql on that database to create the initial database structure.

* Be sure the shell scripts scripts/delivery and scripts/purge are executable.

* Modify the scripts/delivery and scripts/purge files to match the installation directory of your Emailqueue

* Setup two cronjobs in your linux to execute regularly the delivery and purge scripts, e.g:
    
    `$ crontab -e`

    Add the following lines:
    `* * * * * /var/www/htdocs/emailqueue/scripts/delivery`
    `0 6 * * * /var/www/htdocs/emailqueue/scripts/purge`

    * The delivery script delivers pending emails in the queue. Running it every minute is recommended.

    * The purge script removes old & already sent emails from the queue to avoid the queue from growing too big. Running it every day is more than enough.


* You should be ready to go, now you can:

  * See the status of the queue by accessing /emailqueue/frontend in your browser.
    * You can now inject messages to the queue by updating the database by yourself (the "emails" table, field names are pretty self-explaining)
    * You can optimally use the provided emailqueue_inject PHP class, found in scripts/emailqueue_inject.class.php. See an example on how to use this class in scripts/emailqueue_inject_test.php

* Take a look at the provided example.php

##Migrating from versions < v3.0.13##

If you have a version of emailqueue less than v3.0.13 (released on december 25th, 2015), and want to upgrade to v.3.0.13 or above, execute the following SQL in your emailqueue database in order to migrate:

`ALTER TABLE emails ADD attachments TEXT NULL DEFAULT NULL;`
`ALTER TABLE emails ADD is_embed_images TINYINT(1) NOT NULL DEFAULT 0;`

No other changes are needed for the migration.

##How to use##

The file example.php is a thoroughly documented example on how to send an email using emailqueue using the provided emailqueue_inject PHP class, which is the recommended method. Here's what to do, anyway:

* Include the following files (specify your path as needed):
    * config/application.config.inc.php
    * config/db.config.inc.php
    * scripts/emailqueue_inject.class.php
* Instantiate an emailqueue_inject object passing the database connection configuration, which can be taken from the very same configuration stored in db.config.inc.php by just specifying the defines as follows:
  
  `$emailqueue_inject = new emailqueue_inject(DB_HOST, DB_UID, DB_PWD, DB_DATABASE);`

* Send an email by calling the inject method of the emailqueue_inject object. Here's an explanation of all the parameters:

  * **foreign_id_a**: Optional, an id number for your internal records. e.g. Your internal id of the user who has sent this email.
  * **foreign_id_b**: Optional, a secondary id number for your internal records.
  * **priority**: The priority of this email in relation to others: The lower the priority, the sooner it will be sent. e.g. An email with priority 10 will be sent first even if one thousand emails with priority 11 have been injected before.
  * **is_inmediate**: Set it to true to send this email as soon as possible. (doesn't overrides priority setting)
  * **date_queued: If specified, this message will be sent only when the given timestamp has been reached. Leave it to false to send the message as soon as possible. (doesn't overrides priority setting)
  * **is_html**: Whether the given "content" parameter contains HTML or not.
  * **from**: The sender email address
  * **from_name**: The sender name
  * **to**: The addressee email address
  * **replyto**: The email address where replies to this message will be sent by default
  * **replyto_name**: The name where replies to this message will be sent by default
  * **subject**: The email subject
  * **content**: The email content. Can contain HTML (set is_html parameter to true if so).
  * **content_nonhtml**: The plain text-only content for clients not supporting HTML emails (quite rare nowadays). If set to false, a text-only version of the given content will be automatically generated.
  * **list_unsubscribe_url**: Optional. Specify the URL where users can unsubscribe from your mailing list. Some email clients will show this URL as an option to the user, and it's likely to be considered by many SPAM filters as a good signal, so it's really recommended.
  * **attachments**: Optional. An array of hash arrays specifying the files you want to attach to your email. See example.php for an specific description on how to build this array.
  * **is_embed_images**: When set to true, Emailqueue will find all the <img ... /> tags in your provided HTML code on the "content" parameter and convert them into embedded images that are attached to the email itself instead of being referenced by URL. This might cause email clients to show the email straightaway without the user having to accept manually to load the images. Setting this option to true will greatly increase the bandwith usage of your SMTP server, since each message will contain hard copies of all embedded messages. 10k emails with 300Kbs worth of images each means around 3Gb. of data to be transferred!

##Hints##

* Here's a neat trick: Attach a .vcf card to your emails so users can add you to their contacts lists with just a few clicks: Many email clients will trust you if your "from" email address is on the user's contacts list, improving dramatically the inbox placement.
* It's highly recommended to check all parameters with data coming from user input for SQL injections, XSS and other weird stuff before sending it to emailqueue!
* Tuning your SMTP for a good inbox placement is _really_ difficult. Be sure to test as many email providers as you can, implement SPF and DKIM properly (even better with DMARC also) and use tools like swaks (http://www.jetmore.org/john/code/swaks) for testing.
* Creating HTML code to be sent via email is quite difficult if you want to maximize inbox placement. Most email clients do not like modern HTML, CSS or advanced techniques, and you should stick to good-old tables, obsolete HTML and very simple CSS if you want your emails to appear consistently in as many clients as possible, and to not be classified as SPAM. Get info and take your time to perform extensive tests with different email clients and providers.

##Please##

Do not use Emailqueue to send unsolicited email, or emails about animal abuse.

##License##

Emailqueue is released under the GNU GPL v2.0 (See LICENSE file). Emailqueue includes PHPMailer (https://github.com/Synchro/PHPMailer), which is also licensed under GNU GPL v2.0

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.