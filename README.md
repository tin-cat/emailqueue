# Emailqueue #
**A fast, simple yet very efficient email queuing system for PHP/MySQL**

By Tin.cat (https://tin.cat)

Almost anyone who has created a web application that sends emails to users in the form of newsletters, notifications, etc. has tried first to simply send the email from their code using the PHP email functions, or maybe even some advanced emailing library like the beautifully crafted PHPMailer (https://github.com/Synchro/PHPMailer). Sooner or later, though, they come to realize that triggering an SMTP connection from within their code is not the most efficient way to make a web application communicate via email with their users, mostly because this will make your code responsible about any SMTP connection errors and, specially, add all the SMTP delays to the user experience.

This is where solutions like Emailqueue come in handy: Emailqueue is not an SMTP relay, and not an email sending library like PHPMailer (though it uses PHPMailer for final deliver, actually). Think of it as an intermediate, extremely fast, post office where all the emails your application needs to send are temporarily stored, ordered and organized, this is how it works:

* Your application needs to send an email to a user (or 10 thousand emails to 10 thousand users), so instead of using the PHP mail functions or PHPMailer, it simply adds the email to Emailqueue. You can add emails to Emailqueue by calling the Emailqueue API, by injecting directly into Emailqueue's database, by using the provided PHP class.

* The insertion is made as fast as possible, and your application is free to go. Emailqueue will take care of them.

* Every minute, emailqueue checks the queue and sends the queued emails at its own pace. You can configure a delay between each email and the maximum number of emails sent each minute to even tune the delivery speed and be more friendly to external SMTPs.

* Emailqueue even does some basic job at retrying emails that cannot be sent for whatever reason, and stores a history of detected incidences.

* Sent emails are stored on emailqueue's database for you to check who received what. A purge process is performed automatically to remove sent emails that are too old and to avoid your emailqueue database to grow too big.


# Best features #
* Inject emails via API: super easy, super flexible, inject from anywhere. You can also insert directly into the database or via the provided PHP class.
* Inject any number of emails super-fast and inmediately free your app to do other things. Let Emailqueue do the job in the background.
* Prioritize emails: Specify a priority when injecting an email and it will be sent before any other queued emails with lower priorities. E.g: You can inject 100k emails for a newsletter with priority 10 (they will take a while to be sent), and still inject an important email (like a password reminder message) with priority 1 to be sent ASAP even before the huge newsletter has been sent.
* Schedule emails: Inject now an email and specify a future date/time for a scheduled delivery.
* The code base is quite old, with its roots in the early 2000s. Boy, it's been tested! Emailqueue is a funny, reliable grown old man.


# Docker version #
There is an official docker compose project for Emailqueue that will get you a running Emailqueue server in a blink. Check it out: (https://github.com/tin-cat/emailqueue-docker).


# Changelog #
* **Version 3.4**
  * Created the official docker compose project for Emailqueue (https://github.com/tin-cat/emailqueue-docker) that allows you to deploy a working emailqueue setup very easily.
  * Added a simple HTTP API to queue emails, useful when running Emailqueue in an isolated server, or as a docker service.
  * Now supports pausing and unpausing.
  * Better scripts.
  * Minor bugs tamed.

* **Version 3.3**
  * Support for Emoji.
  * Errors are now thrown using standard PHP Exceptions and are easily catchable.
  
* **Version 3.2**
  * Switched to MIT license, now Emailqueue can be used in commercial, non GNU-GPL projects.
  * emailqueue_inject::inject method is now called differently, see the "How to use" section or example_local.php for for info.
  * "is_send_now" emailqueue_inject::inject parameter sends the email without waiting for the next queue processing event, perfect for those cases where you can't even wait a minute to have your email delivered.
  * Now uses composer for library dependencies.
  * Using standard <?php instead of the shorthand <? version for improved compatibility.
  * No need to configure LIB_DIR or APP_DIR anymore.
  * "is_inmediate" parameter typo solve876543	, now it's called "is_immediate"
  
* **Version 3.1**
  * Finally Emailqueue supports attachments! See the "Hints" section for an interesting idea with this.
  * Super-powerful functionality to auto-embed all images in your messages as attachments, so they should appear really fast on the user's screen once received (no additional images to download by the client). Also, some email clients might show the images straightaway without asking the user to download them.
  * Much better documentation.
  * Some code cleanup. Still very naive code, tough.
  * Some visual improvements to the frontend, also including a new futuristic logo in all PNG glory, and a beautiful tiny favicon.
  * PHPMailer library is now a GIT submodule.
  * Added animal abuse manifesto.


# TO-DOs #
* Recode the file logging system.
* Recode it to modern standards.
* A way to provide secured hard-links to view individual emails, so that a hard link can be included within the email to the user with a link like "Can't see this message? click here to see it in your browser"
* A better back-end to explore the queue in realtime, plus some beautiful statistical graphs.

# How to install #
* Clone the emailqueue repository wherever you want it.
    * For security reasons, do not install Emailqueue in a web server public directory. Instead, create a new route on your web server configuration that points to the "frontend" directory of your Emailqueue installation.
	* If you'll be using the API, create another route on your web server pointing to the "api" directory of your Emailqueue installation. As an additional security measure, make sure this route is only accesible by the hosts you'll be calling the API from, and you're serving it via HTTPS.

    `$ git clone https://github.com/tin-cat/emailqueue.git`

* Emailqueue depends on the great PHPMailer, which is included using composer (https://getcomposer.org/). Install PHPMailer by running this command:

    `$ composer update`

* Create a database in your server with your desired name. e.g: emailqueue

* Run the provided SQL code found on install/emailqueue.sql on that database to create the initial database structure.

* Copy the files config/application.config.inc.php.example and config/db.config.inc.php.example to versions without the .php extension, like so:

`cp application.config.inc.php.example application.config.inc.php`
`cp db.config.inc.php.example db.config.inc.php`

* Customize the contents of those .config.inc.php newly created files to your needs. Both are well documented.

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
    * Send emails using the provided emailqueue_inject PHP class found in scripts/emailqueue_inject.class.php. See an example on how to use this class in example_local.php
    * You can also inject messages to the queue by manually inserting them on the database via SQL (Insert in the "emails" table, read the field comments for detailed explanations)

# Migrate from Version 3.2 to Version 3.3 #
* Run `$ git pull` in your emailqueue directory to get the latest version.
* Using your database manager, select your emailqueue database and run the install/migrate_from_v3.2_to_v3.2.sql SQL file.

# Migrate from Version 3.1 to Version 3.2 #
* Install this new 3.2 version following the regular Install process.
* Set up the same database connection parameters as your existing Emailqueue database. Note that some minor things have changed in db.config.inc.php and application.config.inc.php, so you cannot use the old ones.
* Using your database manager, select your emailqueue database and run the install/migrate_from_v3.1_to_v3.2.sql SQL file.
* Run `$ composer update` on the installation dir to get the latest phpmailer libraries.

# Migrating from versions older than v3.1 #
If you have a version of emailqueue older than v3.1 (released on december 26th, 2015), and want to upgrade to v.3.1 or above, using your database manager, select your emailqueue database and run the install/migrate_from_versions_older_than_v3.1.sql SQL file.

# How to use if Emailqueue is on the same server as your code #
The file example_local.php is a thoroughly documented example on how to send an email using emailqueue using the provided emailqueue_inject PHP class. Use this method of injecting emails when Emailqueue is installed in the same server as your code, and Emailqueue configuration files and the emailqueue_inject.class.php class file is accessible by your code.

Here's what to do:

* Include the following files (specify your path as needed):
    * config/application.config.inc.php
    * config/db.config.inc.php
    * scripts/emailqueue_inject.class.php
* Instantiate an emailqueue_inject object passing the database connection configuration, which can be taken from the very same configuration stored in db.config.inc.php by just specifying the defines as follows:
  
  `$emailqueue_inject = new emailqueue_inject(DB_HOST, DB_UID, DB_PWD, DB_DATABASE);`

* Send an email by calling the inject method of the emailqueue_inject object, passing a hash array with the keys as defined in the "Emailqueue injection keys" section of this document.

# How to use via API calls #
Use this method when you're running Emailqueue in an isolated server or in a dockerized environment like the official Emailqueue docker compose project (https://github.com/tin-cat/emailqueue-docker): Add emails to Emailqueue by calling the Emailqueue API via a standard HTTP request.

The API endpoint URL would be like: https://<domain or IP>/<the route you defined on your webserver>
Example of an API endpoint: https://192.168.1.100/emailqueue

Call your endpoint by making an HTTP request with the a parameter called ***q*** containing a JSon with the following keys:

* key: The API_KEY as defined in your application.config.inc.php
* message: An array defining the email message you want to inject, with the keys as defined in the "Emailqueue injection keys" section of this document.
  * Unfortunately, you cannot yet attach images when calling Emailqueue via API, so the "attachments" and "is_embed_images" keys won't have any affect when calling the API.

An example value for the ***q*** POST parameter to inject a single email would be:

```
{
	"key":"your_api_key",
	"message": {
		"from":"me@domain.com",
		"to":"him@domain.com",
		"subject":"Just testing",
		"content":"This is just an email to test Emailqueue"
	}
}
```

To inject multiple messages in a single API call, use the key "messages" instead of "message":
  * messages: An array of arrays defining the email messages, where each array defining the email message has the keys as defined in the "Emailqueue injection keys" section of this document.

An example value for the ***q*** POST parameter to inject multiple emails would be:

```
{
	"key":"your_api_key",
	"messages": {
		{
			"from":"me@domain.com",
			"to":"him@domain.com",
			"subject":"Just testing",
			"content":"This is just an email to test Emailqueue"
		},
		{
			"from":"me@domain.com",
			"to":"him@domain.com",
			"subject":"Testing again",
			"content":"This is another test"
		}
	}
}
```

The API will respond with a Json object containing the following keys:

 * result: True if the email or emails were injected ok, false otherwise.
 * errorDescription: A decription of the error, if any.

Take a look at the provided example_api.php to see an example on how to call the API in PHP.

# Emailqueue injection keys #
Whenever you inject an email using the emailqueue_inject class, calling the API or manually inserting into Emailqueue's database, these are the keys you can use and their description:

  * **foreign_id_a**: Optional, an id number for your internal records. e.g. Your internal id of the user who has sent this email.
  * **foreign_id_b**: Optional, a secondary id number for your internal records.
  * **priority**: The priority of this email in relation to others: The lower the priority, the sooner it will be sent. e.g. An email with priority 10 will be sent first even if one thousand emails with priority 11 have been injected before. Defaults to 10.
  * **is_immediate**: Set it to true to queue this email to be delivered as soon as possible. (doesn't overrides priority setting) Defaults to true.
  * **is_send_now**: Set it to true to make this email be sent right now, without waiting for the next delivery call. This effectively gets rid of the queueing capabilities of emailqueue and can delay the execution of your script a little while the SMTP connection is done. Use it in those cases where you don't want your users to wait not even a minute to receive your message. Defaults to false.
  * **date_queued**: If specified, this message will be sent only when the given timestamp has been reached. Leave it to false to send the message as soon as possible. (doesn't overrides priority setting).
  * **is_html**: Whether the given "content" parameter contains HTML or not. Defaults to true.
  * **from**: The sender email address
  * **from_name**: The sender name
  * **to**: The addressee email address
  * **replyto**: The email address where replies to this message will be sent by default
  * **replyto_name**: The name where replies to this message will be sent by default
  * **subject**: The email subject
  * **content**: The email content. Can contain HTML (set is_html parameter to true if so).
  * **content_nonhtml**: The plain text-only content for clients not supporting HTML emails (quite rare nowadays). If set to false, a text-only version of the given content will be automatically generated.
  * **list_unsubscribe_url**: Optional. Specify the URL where users can unsubscribe from your mailing list. Some email clients will show this URL as an option to the user, and it's likely to be considered by many SPAM filters as a good signal, so it's really recommended.
  * **attachments**: Optional. An array of hash arrays specifying the files you want to attach to your email. See example_local.php for an specific description on how to build this array.
  * **is_embed_images**: When set to true, Emailqueue will find all the <img ... /> tags in your provided HTML code on the "content" parameter and convert them into embedded images that are attached to the email itself instead of being referenced by URL. This might cause email clients to show the email straightaway without the user having to accept manually to load the images. Setting this option to true will greatly increase the bandwidth usage of your SMTP server, since each message will contain hard copies of all embedded messages. 10k emails with 300Kbs worth of images each means around 3Gb. of data to be transferred!
  * **custom_headers**: Optional. A hash array of additional headers where each key is the header name and each value is its value.

# Hints #
* Here's a neat trick: Attach a .vcf card to your emails so users can add you to their contacts lists with just a few clicks: Many email clients will trust you if your "from" email address is on the user's contacts list, improving dramatically the inbox placement.
* It's highly recommended to check all parameters with data coming from user input for SQL injections, XSS and other weird stuff before sending it to emailqueue!
* Tuning your SMTP for a good inbox placement is quite difficult. Be sure to test as many email providers as you can, implement SPF and DKIM properly (even better with DMARC also) and use tools like swaks (http://www.jetmore.org/john/code/swaks) for testing.
* Creating HTML code to be sent via email is tricky if you want to maximize inbox placement. Most email clients do not like modern HTML, CSS or advanced techniques, and you should stick to good-old tables, obsolete HTML and very simple CSS if you want your emails to appear consistently in as many clients as possible, and to not be classified as SPAM. Get info and take your time to perform extensive tests with different email clients and providers. Best way to start? See the source code of emails sent by the big players like Twitter and Facebook. Welcome back to 1998.

# A note for modern developers #
Emailqueue was built in the early 2000s, and there are many (_many_!) amateur developer techniques used, those were the days of Cold Fusion and PHP version 3, some lines of code here might make you nervous if you're used to modern programming techniques, specially the tricks around global variables, the lack of any meaningful object oriented programming and the convoluted way the system differentiates the frontend from the backend. Emailqueue should be completely rebuilt from the ground up to keep it up with modern techniques and security standards.

# Please #
Do not use Emailqueue to send unsolicited email, or emails about animal abuse.

# License #
Emailqueue is released under the MIT License (See LICENSE file). Emailqueue uses the library PHPMailer (https://github.com/Synchro/PHPMailer), which licensed under GNU GPL v2.1 (https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html) As per GNU GPL v2.1 Term number 5: [...] "A program that contains no derivative of any portion of the Library, but is designed to work with the Library by being compiled or linked with it, is called a "work that uses the Library". Such a work, in isolation, is not a derivative work of the Library, and therefore falls outside the scope of this License." [...], Emailqueue is not required to be released under a GNU GPL License.