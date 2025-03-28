# get-off-my-lawn - Minimal Web Framework
  				  
Have you ever been in hand-to-hand combat with a webpage? Does javascript scare you? Do you still know what a file is, and do you agree that everything is a file? In short, do you miss the good old days of the internet, when webpages could look like sheets of paper, and basically nothing moved? If your answer is a resounding yes on all accounts, then get-off-my-lawn might be for you! Rumored by many to be the new wordpress killer, get-off-my-lawn provides really (like, really) basic blogging functionality, along with an easy way to serve additional user created pages, without the unnecessary bloat that the kids these days cram into all their so-called 'Content Management Systems'.


> You have a problem. You decide to use javascript. Now, you have two problems. 
> — <cite>Sun Tsu, The Art of HTML</cite>

## Features
 - No Javascript
  - No WYSIWYG editor
   - No user accounts or registration
   - No comments
 - No JSON, Ajax requests or other Web 2.0 nonsense
  - Did I mention no javascript?
 - No custom CSS classes (I'm serious)
  - No moving parts
 - No clutter
 
## Additional Features

> All you need is three chords and the truth.
> — <cite>Johnny Cash, when first presented with Angular JS</cite>

 - Blog posting and editing by saving text files
  - Add new static pages easily (also as files)
 - Admin panel (htaccess based) to do stuff that's annoying to do by file
  - Tags, RSS, Hero banner, nav links, widgets - all the basic stuff that makes sense
 - mySQL database because, yes, data integrity is important I guess
 - Simple, clean and truly minimal design that is easy to style using CSS with 100+ classless CSS files included
 - High Accessibility: Made by a blind developer. *Screen readers hate this one trick!*
   - Optimized for mobile and desktop use
 - Designed to be easily extensible
 - Now stop asking you don't actually need more

## Installation

1. Make sure you have a webserver with PHP, as well as a mySQL database.

2. Copy the contents of the src folder to your desired http root, e.g.

```
$ cp -R get-off-my-lawn/src/* /var/www/
```

3. Edit globals.php and set your desired values for website title etc. Also set the database name and access credentials here.

If you have a user with mySQL root privileges step 4 will create a database for you. If you don't want that, or if your webhost prevents you from doing this, you can also create the database manually, either through your webhost's interface or by doing e.g.

```
 $ sudo mysql
  > create database goml_db;
 > CREATE USER 'new_user'@'localhost' IDENTIFIED BY 'password';
 > GRANT ALL PRIVILEGES ON goml_db.* TO 'new_user'@'localhost';
 > FLUSH PRIVILEGES;
```

After this, you would edit globals.php to contain the following

```
define('DB_HOST', 'localhost');
define('DB_USER', 'new_user');
define('DB_PASS', 'password');
define('DB_NAME', 'goml_db');
```

4. Visit `https:://yourdomain.com/setup.php`. This will create a database if you didn't create one in step 3, provided the user has the necessary privileges, It also creates all the required tables in the database, along with some other stuff and example content.

5. Done. You can now visit `https://yourdomain.com/admin.php` to commit posts or to change the stylesheet of the website. You can add static pages by creating them as php files to the static/ folder. They will be linked in the navigation header automatically. Add posts by putting text files in the staging/ folder and commiting them in the admin panel.

## Raison d'Être (that's french for "reason to be", you philistine)

I made this because I wanted to have a webpage where I can write text and present my projects and maybe occasionally add a small php script to a static page to do something fun. Naturally, I first tried to use wordpress for this. I had used wordpress many times in the past and I liked it. But when I tried out the latest version it just seemed bloated and overengineered to me. Everything was reactive, WYSIWYG, and customizable, yet I couldn't seem to get basic things done. When I found myself unable to do something as simple as add a header image, I finally broke down. Wordpress wasn't a bad product, but it wasn't for me anymore. I looked at other web frameworks, but they all had similar problems. It was all *too much*. Then, I had an epiphany.

*The internet is just web pages.*

It's true. That's how it all started. Content management systems, Javascript frameworks, bootstrap, angular, Ajax - none of it is necessary. It all started with just machines running apache, serving files. It was enough in the 00s and it's probably enough now. So with what little I know of PHP, HTML, and SQL, I went ahead and told my trusted local AI to whip up some web files that did exactly what I wanted, and nothing more.

And with that. I was free. 


> HTML cannot be taught, but, most intelligent Naropa, since you have undergone rigorous austerity, with forebearance in suffering and with devotion to your guru, blessed one, take this secret teaching to heart.
> — <cite>Tillopa at the bank of the Ganges, teaching his student Naropa about front-end development</cite>

This repo exists to serve others who may have similar needs, either to use as a framework, or merely as inspiration to do what I did and taylor something yourself that is specific to your needs. All others - can get off my lawn :)


## Credits

Thanks to [dropin-minimal-css](https://github.com/dohliam/dropin-minimal-css) for an amazing collection of classless CSS files.

