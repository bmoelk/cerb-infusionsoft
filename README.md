# cerb-infusionsoft

This is a Cerb plugin that integrates with InfusionSoft.

* Modeled from this plugin: https://github.com/cerb-plugins/wgm.profile.attachments
* It looks up an InfusionSoft record based on the email address on the ticket.
* It displays basic user information.
* Currently, the only write operation allowed is to add/remove tags (aka groups)
* It uses this to talk to InfusionSoft: https://github.com/novaksolutions/infusionsoft-php-sdk

### License

Plugin code is covered by The MIT License (MIT)

The following code is licensed under terms by their respective owners:

* api/Infusionsoft
* resources/js/jquery.tokeninput.js
* resources/js/jquery.notifyBar.js
