Real Nonce
===============

A very simple NONCE system for ClassicPress developers.

Author
------
Tim Kaye. Forked from wp-simple-nonce by Cal Evans ([calevans](http://github.com/calevans)) with a major bug fix.


Description
-----------
A NONCE is a Number Used Once. They are very useful for preventing CSRF. They are also useful in preventing users from doing stupid things like submitting a form more than once. (Whether maliciously or accidentally.)

ClassicPress has a useful NONCE system built into its core, but it is not a true NONCE. Real Nonces are true NONCEs. You can easily create one. Then you add it to a form or URL. When you get it back, you check it. NONCEs can only be checked once. Once you've tested it, the NONCE is destroyed so that it can't ever be used again. There is also a backup expiration time (in case the NONCE is never used).

This plugin does not override the existing ClassicPress NONCE system.

This plugin does not have an admin interface. It is intended for developers only.
