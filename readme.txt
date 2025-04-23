=== Upstock ===
Contributors: Philip Rudy
Tags: comments, spam
Requires at least: 4.5
Tested up to: 6.4.2
Requires PHP: 5.6
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple plugin to manage the stock quantity changes in a bulk
fashion.

== Description ==

Custom plugin for WooCommerce Storefront Developer Exercise.

== Manual Installation ==

1. Upload `stock-quantity-mgmt.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action('plugin_name_hook'); ?>` in your templates


== Introduction ==

Woocommerce, by default, allows you to manage the stock quantity of each product
individually. Furthermore, you can only set the available stock quantity for a product, not the
change in-stock quantity. For example, if you have a product with 10 available stocks and you
want to increase it by 5, you will have to manually change the stock quantity to 15. This is not
ideal if you have a large number of products and you want to increase the stock quantity by a
large number.

== Technical Details ==

For this challenge, you will build a simple plugin to manage the stock quantity changes in a bulk
fashion. Set up a local development WordPress and Woocommerce environment. Create two
fictional products with a full description, price, and stock quantity. It can be anything you like.
Tasks

* Create a new page under the Woocommerce menu called "Manage Stocks"
* Create a table that has three columns: “Product Name”, “Current Stock”, and “Stock
Change”. The table should list all the products in the system. The "Stock Change"
column should be a text input field that allows you to enter a number.
* Add a button called "Update" at the bottom of the table that will update the current
stock quantity for all the products that have stock changes.
Here is a screenshot of what the page will look like:
Deliverable
* A video recording of your screen demonstrating the functionality
* A zip file of your codebase, including a database dump (GitHub repo is preferred)
* A comprehensive README file detailing the project setup and how to run it locally
