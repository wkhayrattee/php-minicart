# # The Wak Minicart Project

- As a proof of concept of how I actually code when doing PHP from scratch, with no frameworks
- Created to be assessed by PHP Managers & Team Leads whenever I apply for a (remote) job

## ## Technical Requirements
- PHP v7.2.5+
- Tested with MySQL v5.7 and v8 (including on Percona)
- Give write access on folders:
	- **sessions**
	- **logs**
	- **cache**

## ## Summary of Project

- Project has been hosted on a LIVE server, see here: [https://minicart.7php.com/](https://minicart.7php.com/)
- all commits are signed
- Show how I can create my own minimal micro frameworks from just 3 mains components:
    - `symfony/http-foundation`
    - `symfony/http-kernel`
    - `nikic/fast-route`
- System uses an MVC approach, but morphed with ADR (Action Domain Responder)
- Used a templating approach, using: `sevenphp/savantphp`
- Uses `pimple/pimple` as DI container. Simple and effective

## ## Installation of Project
1) Simply `$ git clone` the project

I have intentionally committed the repo with the **vendor** and **composer.lock** file, so that you do not have to take the toruble to crun composer.

2) Install the MySql database:

```
$ cd /path/to/project/minicart/database
$ mysql -uroot -p < create.sql
```

That's it. 

The script will do 3 things:

    - create a user and corresponding database with same name
    - create all tables
    - dump (insert) default data for Products and pricing rules

3) Create a vhost in apache, making sure the **DirectoryIndex app.php** is set.

A sample apache vhost is founding folder **apache2** inside the project.

## ## Unit Testing with PHPUnit

I have created my test case in ```/path/to/minicart/Tests``.

Just run the following:

```
$ cd /path/to/minicart/
$ vendor/bin/phpunit Tests/CartItemLogicTest.php --testdox
```

NOTE:

- The logic I have written, is made to be run on a per SKU basis, not multiple SKU at once.
- So as you will see in my **dataProvider expectedProvider**, I have put it order: A, AA, AAA, AAAA, AAAAA, AAAAAA and B, BB, BBB..etc. **But** in the web interface you will be able to enter in any order: ABCD ABADAC, AADDCCC...etc.
	- This is also an assumption in my system.
- WARNING: 
	- I have made sure there are TWO failures, see comments **////WARNING: SHOULD FAIl** inside this provider to see where I have **intentionally** left a wrong expected total_price.

## ## The Main Logic Of The Project

The main logic of the project which cater for the "promo pricing calculation" is found in Class **CartItemLogic.php** at: ```path/to/minicart/src/Project/CartItemLogic.php```

### ### Reasoning behind the approach

Initially I had 3 approaches I could use:

1) use sessions or a json file to add all items into a **stack** (array). And then use a sorting algorithm, most simple one being QuickSort - which is inbuilt in PHP's **sort()** functions.

2) But later I found that instead of using sorting, which seems a bit complex for this purpose, I could instead make use of the PHP inbuilt function **array_count_values()** which would simply 'triage' all SKU together and giving me the count. This way I could just use the count and get the pricing - much simpler

3) Then after I started thinking into adding a Database into the picture, I found that my task becomes more simple.

I do not have to use any of the above #2 methods.
 
I can just INSERT/UPDATE items in cart in the database.

### ### Elaboration on approach #3 above

I opted to use database - I created a normalised database. See ```path/to/minicart/database/create.sql```

I then used **mathematics** principle of division and multiplication (or I could also used modulo) to get "occurrence" or multiple of the "promo bundle".

See, **/path/to/minicart/Tests/CartItemLogicTest.php** to see how the logic method **CartItemLogic::doApplicablePricingRule** works or simply head into the main Class file at: ```path/to/minicart/src/Project/CartItemLogic.php``` line 141

## ### Special Things of this project

In any problem solving, the logic is the main crux for sure. But I also think the following things I list, will give you an idea of how I do things:

- I have created my own mini **MVC framework from scratch** by re-using/glueing **symfony/http-foundation**, **symfony/http-kernel** and **nikic/fast-route**
	- Most precisely, I have made use of a light version of ADR - Action Domain Responder pattern, thereby modifying the way my Controllers are invoked, as opposed to traditional MVC controllers.
- I have used a **templating system**, using a php-based tpl library that I currently maintained, namely **sevenphp/savantphp**
	- Browse my **composer.json** file to see components that I have used.
	- I have made it possible to **queue css and JS** files for both in head or footer, making this flexible.
	- The theme has been coded in a plug-and-play manner, meaning tomorrow I can use another theme named "v2" instead of the current "v1"
- My system **traps** any error that any part of the system will **throw**. Meaning I have used "Try-Catch" to throw errors where needed, while I catch them in my APP.PHP.
- All errors in the system are saved in folder ```/path/to/minicart/log```
- I have made good use of *Filtering* and *Sanitisation* for all my inputs and outputs within the TPL.
- I created my own **MyFramework** class which bootstrap the whole system - see ```/path/to/minicart/src/MyFramework.php```
- I have used dependency injection and injecting a Pimple Container into my system
- I have made use of the OOP **Abstraction** pattern, and used it to create an abstract controller named **AbstractAction** which all Controllers should extend.
- I have made usage of **Enums**
- I have used **Factory pattern**, see ```/path/to/minicart/config/register_services.php```
- I have used routing mechanism, using Nikic FastRoute, see my routes in: ```/path/to/minicart/config/routes_cached.php```
	- These routes are also CACHED on LIVE
- I have also used the inbuilt "httpcache" system inbuilt within the Symfony http-kernel. FYI, I have cached **index** View, see line 30 in IndexAction class
- I have also used my own **Data Access Layer** - in which I make extensive usage of PDO and writing my own SQL scripts
- I have written my own **Pagination** logic, which loads records on a per page size basis, instead of all at once. This has been implemented on the Product List page and Pricing Rules List page.
	- See line 77 in Controller **ProductListAction** in file ```/path/to/minicart/src/MVC/Controller/ProductListAction.php```
	- I have created my own "NativeSession" class to handle session in which I store my **cart_id**
	- Well, I have make sure I have used a lot of stuffs to demonstrate my current skills - kindly see the project. I would be happy to walk you through it and answer any question(s) you may have.

### TOTAL TIME SPENT

- This exercise was done as a real test and so I objectively timed myself:
- Thursday (9th Jan 2020 + Friday 10th 2020) => 10hrs mutually
- Saturday 1hr
- Sunday 5hrs to complete the coding
- 2hrs (to draft the readme + host on LIVE & debug incompatibility issues with PHP and MySQL version)

TOTAL: 18hrs of coding

NOTE: 
I did not sit down at one-go to code as you see on the breakdown above.
It's a real scenario and I kept things real, with the fact that I work full-time, have busy personal life during weekends

