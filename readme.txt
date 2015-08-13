=== Church Metrics Dashboard ===
Contributors: danielmilner,firetree
Tags: church,metrics,stats,dashboard
Requires at least: 3.8
Tested up to: 4.3
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://ww.gnu.org/licenses/gpl-2.0.html

Display Church Metrics Dashboard Widgets on your WordPress Dashboard

== Description ==

Build custom metrics to display data from Church Metrics. Compare numbers from different time periods. Display them in your WordPress Dashboard or anywhere on your site using a shortcode. Limit metrics to a specific user or display them for everyone.

Requires a [Church Metrics](http://churchmetrics.com/) account.

Available Display Periods:
* This Week
* Last Week
* This Month
* Last Month
* This Year
* Last Year
* Weekly Average This Year
* Weekly Average Last Year
* Weekly Average Last Year (Year Over Year)
* Monthly Average This Year
* Monthly Average Last Year
* Monthly Average Last Year (Year Over Year)
* All Time

== Installation ==

1. Upload the church-metrics-dashboard folder to the /wp-content/plugins/ directory
2. Activate the Church Metrics Dashboard plugin through the \'Plugins\' menu in WordPress
3. Configure the plugin by going to the Church Metrics menu that appears in your admin menu

== Frequently Asked Questions ==

= What parameters can I use with the shortcode? =

Here is what the shortcode would look like with all of the parameters:
`[church_metrics_dashboard id=1 before="<div class='my-class'>" after="</div>" before_title="<h3>" after_title="</h3>"]`

* __id__ _(Required)_ the id of the metric to display.
* __before__ _(Optional)_ has no default value.
* __after__ _(Optional)_ has no default value.
* __before_title__ _(Optional)_ default value is "<h2>"
* __after_title__ _(Optional)_ default value is "</h2>".


== Screenshots ==

1. Dashboard Widgets

== Changelog ==

= 1.2.0 =
* Compatibility updates for WordPress 4.3 Admin Page Titles.
* Added: Combine counts from multiple categories.
* Added: More display periods:
    Weekly Average This Year,
    Weekly Average Last Year,
    Weekly Average Last Year (Year Over Year),
    Monthly Average This Year,
    Monthly Average Last Year,
    Monthly Average Last Year (Year Over Year)

= 1.1.0 =
* Added: Shortcode to display the data on the front-end.
* Added: The ability to not display a metric on the Dashboard.
* Added: Customizer settings to choose font colors.

= 1.0.1 =
* Fix: Some files were missing from the release.

= 1.0.0 =
* Initial release