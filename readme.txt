=== Smart WP Login ===
Contributors: nishant_kumar, jsulinski, sjregan
Tags: login using email, registration using email, retrieve password using email, 
remove username, login, registration, password, authentication, wp-login, email, smart
Requires at least: 3.1.0
Tested up to: 4.7.4
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use email to login, register and retrieve password in WordPress.

== Description ==

Smart WP Login allows you to configure default WordPress Login, Registration or 
Password Reset system to work with email and not username. You can enable this
feature individually on Login, Registration or Retrieve Password. So you can make
only **Login using Email**, **Registration using Email** or **Retrieve Password
using Email**. 

= Features =
* Enables you to use email instead of username to login, register or retrieve 
password.
* Removes username field from WordPress login.
* Removes username field from WordPress registration.
* When using Login with Email, it changes the default WordPress login error and
doesn't show username in error message.
* You can also change login, registration and retrieve password related message.

= Note =
Smart WP Login generates username automatically. Please see our FAQ section to 
learn how it generates username.

== Installation ==

1. Upload entire **smart-wp-login** directory to `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to Settings > Smart WP Login and configure.

== Screenshots ==

1. Login using Email
2. Error message for login
3. Wow, custom error message, no WordPress default error
4. Register using Email
5. Retrieve password using Email

== FAQ ==
= What happens to username? How does this plugin generates username? =

WordPress registration system requires a username while registering a new user.
Adhering to WP rules, Smart WP Login provides a username on behalf of user.
It assigns local part of email as username, ex: if user registers with 
demo#demo@example.com, its username would be demodemo (no special chars).

= What happens if username already exists? =

In case username already exists, system tries to change username by adding a 
random number as suffix. Random number is between 1 to 999. Ex: if user registers
with demo$demo@example.com, and username demodemo already exists, its username
would be demodemo_546.

= What if I want a different type of username generated? =

You can optionally set for usernames to be a generated hash based on the user ID.

== Changelog ==

= 1.2 =
1. Added ability to filter new user notification emails

= 1.1 =
1. Added support for BuddyPress registrations.
1. Added option to use hashids instead of partial email address.

= 1.0.3 =
1. Update wp_new_user_notification override function to accommodate changes in
this pluggable function. This bug was preventing emails from being sent.

= 1.0.2 =
1. Improved registration mechanism.
1. Upon registration user receives an email with username, email and password,
previously only username and password was sent.
1. Focuses email field when page loads.
1. No jQuery dependency.

= 1.0.1 =
Minor Changes

= 1.0 =

1. A complete plugin renovation from the ground.
1. Now you can also set custom error message.
1. Shows error message when both email and password is empty in login form.
1. Support for i18n

= 0.9 =
Lets get start

== Upgrade Notice ==

= 1.0.2 =
Upgrade now so your users can also receive email in their registration mail, also
lot of enhancements.
