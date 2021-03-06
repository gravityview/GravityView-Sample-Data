GravityView Sample Data Importer
=======================

Import sample data for the GravityView Preset forms.

### First, update your `wp-config.php` file with your Mockaroo.com API key

* Sign up for an account on Mockaroo.com
* Go to the [API page](http://mockaroo.com/api/docs)
* Under "Gaining Access", copy the API key they provide
* Add the following to your `wp-config.php` file, replacing `example123` with your copied API key:  
```
define( 'GV_MOCKAROO_API_KEY', 'example123' );
```
* Save the `wp-config.php` file

### Once you've added your API key:

* Use "View > New View" and click Start Fresh to create a View for each preset.
* Activate the plugin
* Go to the Views > Sample Data Import menu
* Map existing forms to the View Presets
* Save the settings form
* Click "Import" link next to each form

### Uses Mockaroo.com APIs

Mockaroo allows you to configure sample data. It's great.

We've set up Mockaroo data generators for the different presets. Here are links to the data generators.

* http://www.mockaroo.com/8b3ad4d0 => Issue Tracker
* http://www.mockaroo.com/1d9905e0 => Website Directory
* http://www.mockaroo.com/2157c7a0 => Profiles
* http://www.mockaroo.com/e8799370 => Staff Profiles
* http://www.mockaroo.com/31e534d0 => Event Listing
* http://www.mockaroo.com/5d69bc10 => Business Data
* http://www.mockaroo.com/5d69bc10 => Business Listing
* http://www.mockaroo.com/4d7d9b40 => Resume Board
* http://www.mockaroo.com/2b1c5470 => Job Board
