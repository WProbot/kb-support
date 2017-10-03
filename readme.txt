=== KB Support ===
Contributors: kbsupport, mikeyhoward1977
Tags: Helpdesk, Support, Customer Support, Support Desk, Ticket System, Knowledge Base, Tickets, Service, Service Desk, ITIL, Support Helpdesk, Ticket, Support Tickets, Helpdesk Tickets, Knowledgebase, Service Level, SLA, Help Desk
Requires at least: 4.1
Tested up to: 4.9
Requires PHP: 5.4
Stable tag: 1.1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://kb-support.com/donate-kb-support/

The ultimate help desk and knowledge base support tool plugin for WordPress.

== Description ==

An effective Helpdesk / Support desk tool is one that encourages users to find and implement the solution to their queries without actually logging a support ticket and KB Support has been developed to do exactly that!

From the moment your customers begin to log a ticket, KB Support will search your knowledgebase and offer potential solutions based upon the articles that are published on your site.

From your own experience in running a helpdesk for your product(s) you will know that in a busy environment, documenting solutions and making them readily available to your customers always seems to take a back seat. KB Support makes this process much easier and encourages your support desk agents to produce articles when working through resolution of a customers ticket. Those articles are then immediately available on your website, and automatically referenced during a support ticket submission as a potential solution.

**Key FREE Features of KB Support include**:

* Easily manage and customise submission forms. No coding required, multiple forms can be created and utilised
* Guest submission is fully supported. Customers do not need an account to create or manage support tickets
* Sequential ticket numbers
* Email notifications keep customers, admins and agents up to date with recent ticket events
* Restricted access ensures that only agents and the assigned customer are able to view tickets and correspondance
* A fully responsive and clean front end design where customers can manage their tickets, including viewing and creating replies
* Integrated Knowledge Base Articles can be easily created and referenced to try and offer solutions without tickets being created
* Ability to restrict access to individual knowledge base articles to logged in users only
* Auto assign new tickets to agents based on current ticket count, or randomly
* Tracks an agents status so you can see if they are online or offline
* Ability to assignt tickets to multiple agents
* A number of useful shortcodes to display submission forms, KB Article lists, ticket history, login/registration forms, profile editor, KB Article search form - and more
* Numerous template tags enable you to easily add ticket related content into email notifications
* Private ticket notes that are visible to agents only
* Restrict which tickets an agent can view. i.e. Just those to which they are assigned
* Group customers within a company
* Uses templates that allow for easy customisation of front end pages, shortcodes and CSS styles
* Ajax based ticket submissions provide a powerful, reliable and friendly interface for customers
* Built in SPAM protection
* Customer portal enabling access to existing and historic tickets
* Truly versatile - A bunch of hooks and filters for our developer friends
* A growing number of [extensions](https://kb-support.com/extensions/) providing even more functionality and customisation options

More information can be found at [https://kb-support.com/](https://kb-support.com/).

**Further enhance the features and functionality of KB Support with paid [extensions](https://kb-support.com/extensions/) such as**:

* **Easy Digital Downloads Integration** - Integrate your EDD store into KB Support providing a seamless support solution
* **Knowledge Base Integrations** - Fully integrate KB Support into your existing knowledge base solution
* **Ratings & Satisfaction** - Enables customers and visitors to provide feedback on their support experience as well as the quality of your KB articles
* **Canned Replies** - Instantly reply to tickets with a single click using pre-defined replies to questions you receive the most
* **Custom Ticket Status** - Define your own ticket statuses and enable email notifications when a ticket enters the status
* **Email Signatures** - Enables support workers to register a custom signature which can be auto inserted into email notifications sent to customers
* **MailChimp Integration** - Grow your subscriptions by enabling quick and seamless customer sign-ups to your MailChimp newsletter lists via KB Support

**Follow this plugin on [GitHub](https://github.com/KB-Support/kb-support)**

**Languages**

Would you like to help translate the plugin into more languages? [Join our WP-Translations Community](https://kb-support.com/articles/translating-kb-support/).

== Installation ==

**Automated Installation**

1. Activate the plugin
1. Go to Tickets > Settings and configure the options
1. Insert form shortcode into the ticket submission page
1. For detailed setup instructions, vist the official [Documentation](https://kb-support.com/articles/category/getting-started/)

**Manual Installation**

Once you have downloaded the plugin zip file, follow these simple instructions to get going;

1. Login to your WordPress administration screen and select the "Plugins" -> "Add New" from the menu
1. Select "Upload Plugin" from the top of the main page
1. Click "Choose File" and select the kb-support.zip file you downloaded
1. Click "Install Now"
1. Once installation has finished, select "Activate Plugin"

== Frequently Asked Questions ==

= Where can I find documentation? =

Searchable docs can be found at [https://kb-support.com]([https://kb-support.com)

= How do I Add a Submission Form? =

Create your submission form within Tickets > Submission Forms, and copy the shortcode to any page. i.e. [kbs_submit form="1277"].

A default form with the most popular fields is added during installation. Customise this form to meet your needs, or create new forms as required.

= How can a Customer view their Ticket History? =

Place the [kbs_tickets] shortcode on any page.

= Is there a Pro version with additional features? =

Extensions are available at [https://kb-support.com/extensions/](https://kb-support.com/extensions/ "KB Support Extensions") to further enhance KB Support features and functionality. New extensions are being added regularly. [Join our mailing list](http://eepurl.com/cnxWcz) to be the first to hear about new releases and to receive a 25% discount off of your first purchase!

== Screenshots ==

1. The interface for managing a ticket Submission Form. Create as many forms as you need, choose from a number of fields and add to your web page with a simple shortcode

2. What the default submission form might look like on your website when your customers log a support ticket. Theme in image is Twenty Seventeen

3. Suggested KB Article to resolve a ticket that a customer is in the process of submitted

4. The ticket overview screen a customer see's once their submission is completed

5. The edit ticket screen as seen by an agent

6. When a customer is accessing a KB Article that is restricted

== Changelog ==

= 1.1.3 =

**Tweaks**

* Ticket replies metabox overhaul with icon notifications and action links
* Register when a customer views an agent reply
* Numerous new hooks and filters for the reply fields enabling developers to extend functionality
* Private ticket notes metabox overhaul
* Better input field alignment within ticket metaboxes
* Added support for locally shipped translation files
* Added required PHP version to readme.txt
* Removed underscore from transient name

**Bug Fixes**

* Select fields that allow multiple selections within settings pages needed to store values as arrays
* Added spacing between ticket categories and tags within the tickets table
* Only a note author, or an agent with the `manage_ticket_settings` capability should be able to delete a note
* Removed duplicate CSS ID within admin CSS

= 1.1.2 =

**Tuesday, 12th September 2017**

**Bug Fix**

* Agents assigned as additional agents may not receive email notifications when a customer replies to a ticket

**Tweaks**

* Enable multiple select list argument when outputting a select field within settings API

= 1.1 =

**Monday, 11th September 2017**

**New**

* Sequential ticket numbers to ensure your tickets are always sequential with no gaps in the numbers
* Assign multiple agents to a ticket proving them with access to view and update
* Option to send agent notifications to all assigned agents when a customer reply is received
* Export tickets and customers to a CSV file

**Tweaks**

* Added `kbs_update_ticket_meta_key` filter that fires during ticket meta updates
* Filter the display of tickets when a category or tag is clicked within the ticket list
* Enabled removal of article excerpts during Ajax search
* Removed duplicated `kbs_article_excerpt_length` filter

= 1.0.9 =

**Monday, 4th September 2017**

**Tweaks**

* Enabled sorting of tickets by ID and date as well as title
* Added option to set `Search Excerpt Length` to `0` to have excerpts excluded from search results
* Filter ticket list by category/tag when a category or tag is clicked
* Removed duplicate `kbs_article_excerpt_length` filter
* Added `kbs_update_ticket_meta_key` hook for developers to hook in during ticket meta update
* Added the previous meta value to the `kbs_update_ticket_meta_{$meta_key}` filter

**Bug Fixes**

* HTML characters were not always correctly encoded during Ajax search which prevented all relevant results from being displayed
* Corrected option name which was preventing changes to excerpt length

= 1.0.8 =

**Wednesday, 30th August 2017**

**New**

* Created `KBS_Knowledgebase` class which manages the setup of the knowledgebase
* Added multiple filters during knowledgebase setup which allow for integrations of 3rd party knowledgebase tools
* Added Export and Import options for KBS settings

**Tweaks**

* If there is content in the reply field, request confirmation before saving ticket in admin
* Improvements to the article search query arguments
* Removed stray `</label>` tag from article post
* Fixed label alignment within article metaboxes
* Added `$article_id` var to `kbs_article_view_count` filter

= 1.0.7 =

**Monday, 3rd July 2017**

**Bug Fix**

* Logged in users may have still been unable to view restricted articles

**Tweaks**

* Updated cache helper. Do not cache articles that are restricted
* Updated cache helper. Refresh KBS page ID's transient after 24 hours

= 1.0.6 =

**Thursday, 15th June 2017**

**Tweaks**

* Added row action to reset article view count
* Enabled hourly cron schedule
* Corrected coding on Extensions page


**Bug Fixes**

* Uninstall procedure failing due to incorrect `exit` command
* If a reply is submitted by a logged in, admin showed the reply as from Agent
* Corrected All Extensions link
* Ticket categories may have appeared in sitemaps
* Corrected output of templated files within sysinfo

= 1.0.5 =

**Wednesday, 15th March 2017**

**Bug Fixes**

* Require login if accessing ticket via ID and without secure key
* Corrected taxonomy name and post type within uninstall.php

**Tweaks**

* If guest submissions are disabled, require login when accessing ticket with key and display notice
* Add link to company on customer table
* Consolidate all company scripts within folder
* Removed company from sortable columns on customers table

= 1.0.4 =

**Tuesday, 14th March 2017**

**New**

* Articles can now be sorted by views on the edit post screen
* Added hook `kbs_article_posts_orderby_by_custom_column_{$orderby}` to allow for custom ordering on article post screen
* Added cache helper to tell caching plugins not to cache the ticket submission or ticket management pages
* Added `before_kbsupport_init` hook
* Added `kbsupport_init` hook which is run after the main KB_Support class is initiated
* Added `kbs_articles_column_data_{$column_name}` filter to allow for custom content within the KB Article edit screen
* Adjusted priority for the `kbs_after_article_content` hook
* Added `kbs_agent_can_submit` function. By default agents cannot submit tickets from the front end. Overide by hooking into the `kbs_agent_can_submit` filter

**Tweaks**

* Added new notice for `agents_cannot_submit` to be displayed when an agent is attempting to log a ticket from the front end, but is not allowed to
* If an agent is submitting via the front end, do not auto complete customer name and email fields
* Do not search for existing customer/user email if an agent is submitting from front end
* Removed duplicate `<a>` tag from footer credits
* Removed `<p>` tag from within `<span>` in view-tickets.php template

**Bug Fixes**

* Corrected output of `[kbs_ticket]` and `[kbs_profile_editor]` shortcode. In some instances it was not contained within the page container

= 1.0.3 =

**Wednesday, 1st March 2017**

**New**

* Added logged date/time and updated date/time to update ticket metabox
* Added template tag `{close_ticket_url}` enabling 1-click ticket closures by customers
* Added filter `kbs_validate_customer_reply_email`
* Validate customers by `customer->id` property rather than `customer->user_id` property
* Search for existing WP users during submission and link to customer account if found
* Added KB Support extensions page

**Tweaks**

* Increased the height of the reply editor on edit ticket screen
* Remove scheduled task jobs during plugin deactivation
* Use WP constants to define time periods for scheduled tasks
* Moved welcome page CSS to kbs-admin.css file

**Bug Fixes**

* Ensure a ticket key is created when a ticket is submitted via admin
* Corrected scheduled task periods
* Potential PHP warning if company does not have a logo uploaded
* Corrected variable name `$kbs_tools_page`

= 1.0.2 =

**Sunday, 19th February 2017**

**New**

* Added KB Articles Categories front end widget
* Added KB Popular Articles front end widget

**Tweaks**

* Added most popular articles to KBS Ticket Summary dahboard widget
* Ensure agents always have access to restricted KB Articles

**Bug Fixes**

* Remove whitespace from start of file which may cause PHP notices with some server configurations

= 1.0.1 =

**Thursday, 16th February 2017**

**Bugs**

* Article view count was not being incremented for non-logged in users
* Random text being displayed under customer name on edit tickets page if no company assigned

**Tweaks**

* Do not display SLA Status heading on ticket screen if no targets are defined for ticket 

= 1.0 =

**Enhancements**

* Added KB Support Ticket Summary dashboard widget providing easy to access statistics about your support business

* Service Level Tracking
	* Enable SLA tracking within Tickets -> Settings -> Service Levels
	* Define your target response and resolution times
	* SLA targets are calculated based on your open hours
	* The ticket edit screen will display the status of the SLA for each newly created ticket. Green is good, amber if SLA is nearing expiry, red if expired

* Company Support
	* You can now create companies
	* Customers can be added to a company
	* Email tags added for `{company}`, `{company_contact}`, `{company_email}`, `{company_phone}`, `{company_website}`, `{company_logo}`

**Tweaks**

* Remove all SLA related meta keys from DB as SLA's were not tracked until this version
* Log the current KBS version number at the time each ticket was logged
* Ensure that the last modified date is updated for a ticket when a reply or note is added
* Add log entries when notes are added to tickets
* When a ticket is deleted, make sure to delete all associated replies and log entries from the `posts` and `postmeta` database tables
* Added ticket and article count to the At a Glance dashboard widget

**Bug Fixes**

* Corrected descriptions for email headers in settings
* Make sure `$current_meta` array exists to avoid potential PHP notices
* `kbs_agent_ticket_count()` was not always returning the correct totals

= 0.9.5 =

**IMPORTANT**

* Fatal error may occur when submitting a ticket from the front end if PHP 7.1 is running

= 0.9.4 =

**Bug Fix**

* Fixed an issue whereby new tickets created within admin were not set the correct status

**Tweaks**

* Ensure the trash view is displayed at the end of all views on the edit tickets screen

= 0.9.3 =

**New**

* Added **Link KB Article** media button to admin ticket reply form enabling quick and easy inserting of links to KB Articles from reply form

**Tweaks**

* Tidied javascript code
* Added draft updated message for articles and forms
* Users with manage_ticket_settings capability (Administrator & Support Manager by default) can now delete tickets
* Support Managers should always see all tickets, even when the `Restrict Agent Ticket View?` setting option is enabled

**Bug Fixes**

* Re-open ticket link failed to re-open ticket
* Don't count agent views for articles
* Support Customers should not have the upload_files capability

= 0.9.2 =

**Tweaks**

* Added upgrade functions
* Additional hooks within the view-ticket.php template
* Added plugin links
* Increment tested with to 4.7.2

= 0.9.1 =

**Tweaks**

* Added filter `kbs_user_profile_fields` to enable plugins to register user profile fields within the KB Support section
* Added hook `kbs_display_user_profile_fields` to enable plugins to output user profile fields
* Added banned emails to tools page. Form submissions containing banned addresses will be rejected
* Added System Info to tools page
* Updated call to `wp_register_style()` to display version
* Added `kbs_get_agent_id_from_ticket( $ticket_id )` function to retrieve an agent ID directly from the post meta table
* Removed dynamic `do_action()` calls from $_POST and $_GET submissions
* Only enqueue chosen menu's on submission page

= 0.9 =

**Release**

Our initial release!

== Upgrade Notice ==
