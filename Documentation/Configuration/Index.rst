.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _configuration:

Configuration Reference
=======================

Target group: **Developers**

This reference manual does not cover all possible configurations of this
extension. To help you and others learn and share your newsletter configurations
we have enabled the project `wiki`_ on GitHub for public contribution.

.. _wiki: https://github.com/Mirko/newsletter/wiki

.. _configuration-scheduler_and_mail_transport:

Scheduler and Mail Transport
----------------------------

#. If not done already, set up TYPO3 Sheduler. See `Scheduler
   documentation`_
#. Within Scheduler, schedule the task to send newsletters, and
   optionally to fetch bounced emails
#. Configure mail transport in ``LocalConfiguration.php`` (more info in
   `API documentation`_):

.. code:: php

    <?php

    return [
        'MAIL' => [
            'transport' => 'smtp',
            'transport_smtp_server' => 'smtp.example.com:587',
        ],
    ];

.. _API documentation: http://api.typo3.org/typo3cms/current/html/class_t_y_p_o3_1_1_c_m_s_1_1_core_1_1_mail_1_1_mailer.html
.. _Scheduler
   documentation: http://docs.typo3.org/typo3cms/extensions/scheduler/Installation/Index.html

.. _configuration-recipient_lists

Recipient Lists
---------------

There is several ways to define a list of recipients. Those are:

-  **SQL**: specify SQL queries to fetch data from any table with at least
   an ‘email’ field
-  BE-users: select existing backend users
-  FE-Groups with FE-Users: select frontend groups containing frontend
   users
-  Page with FE-Users: select pages where frontend users are stored
-  CSV file: upload a CSV file containing users
-  CSV list: specify CSV content (eg: copy/paste from a file)
-  CSV url: specify an URL to fetch a CSV file from
-  HTML: fetch an URL and parse its content to find emails

SQL recipient lists are, by far, the most flexible and powerful way do
define a list of recipients. It allows the dynamic composition of strings that
can be used in newsletter content. And it also allows you to take action (SQL
queries) upon specific events (bounced email, unsubscribe). Thus we
**strongly recommend the use of SQL Recipient List** and to read the
[[Recipient_List_SQL_Examples]].

For CSV, when asked for ``CSV Fields``, you should enter the column names,
eg: ``email,firstname,lastname``. Then file/list/url should only contains
the values without any column headers, eg: ``me@example.com,John,Connor``.

You can define a ``storagePid`` in the Constants if you want different Recipient
Lists for different Users. Otherwise every User can use every Recipient List.

.. _configuration-recipient_lists-special_fields

Special fields
^^^^^^^^^^^^^^

Recipient List can include as many fields as needed to compose the newsletter,
but there are a few reserved fields that have a special meaning:

===================== =========== ====== =============================== ===========================
 Field                 Mandatory   Type   Description                     Default
===================== =========== ====== =============================== ===========================
:code:`email`              ✓      string Recipient email
:code:`plain_only`                bool   Whether to send only plain text  The Recipient List value
:code:`L`                         int    Language code of content         The Recipient List value
:code:`sender_email`              string Sender email                     The Newsletter value
:code:`sender_name`               string Sender name                      The Newsletter value
:code:`replyto_email`             string Reply-To email                   The Newsletter value
:code:`replyto_name`              string Reply-To name                    The Newsletter value
===================== =========== ====== =============================== ===========================


.. _configuration-bounce_accounts

Bounce Accounts
---------------

A new record type called “Bounce account”. You should select a bounce
account for newsletter. The bounce account is used in two ways:

-  To provide an email address for the mail to bounce to ("Return-Path:" header)
-  To provide login information to the email account for the bounce-system to
   login to.

Once a newsletter has a BounceAccount and the bounce Scheduler task is
enabled, the extension Newsletter will automatically attach the address
as return-path, read the rejected emails and disable/delete the failed
email addresses. The bounced emails will also appear in the statistics.

The bounce account is accessed via `fetchmail`_.
It is possible to add your own fetchmail configurations to the bounce account.
The Fetchmail Configuration field supports 5 marker substitutions:

-  :code:`###SERVER###` Domain of the bounce account email server.
-  :code:`###PROTOCOL###` Connection protocol of the email server (IMAP|POP3).
-  :code:`###PORT###` Connection port of the bounce account email server.
-  :code:`###USERNAME###` Username for the bounce account.
-  :code:`###PASSWORD###` Password for the bounce account.

A simple default configuration is automatically supplied when you create a new
bounce account in the Typo3 Backend, however this simple configuration does not
support encrypted TLS/SSL connections. Encrypted connections are required for
connecting to popular email providers such as Google Gmail. Such configurations
are beyond the scope of this manual. For more information on writing fetchmail
configurations please consult the `fetchmail (1)`_  man pages or the internet.

You can define a ``storagePid`` in the Constants if you want different Bounce Accounts for different Users.
Otherwise every User can use every Bounce Accounts.

.. _fetchmail: http://www.fetchmail.info/
.. _fetchmail (1): http://www.fetchmail.info/fetchmail-man.html

.. _configuration-unsubscription_notifications

Unsubscription Notifications
----------------------------

Unsubscription should be automated, for example via proper configuration
of SQL for bounced email. However it is possible to receive an email
whenever a recipient requests for unsubscription. The “Notification
email” field needs to be specified in extension configuration (in Extension
Manager) or via template TypoScript.

.. _configuration-typoscript:

TypoScript Reference
--------------------

The extension configuration is set globally via the Extension Manager, but it can be overwritten by TypoScript setup. In TypoScript setup the configurations **must be prefixed** with ``module.tx_newsletter``.

.. t3-field-list-table::
 :header-rows: 1

 - :Setup:
      Config
   :Description:
      Description
   :Type:
      Type
   :Default:
      Default

 - :Setup:
      view.templateRootPath
   :Description:
      Path to template root (BE)
   :Type:
      string
   :Default:
      EXT:newsletter/Resources/Private/Templates/

 - :Setup:
      view.partialRootPath
   :Description:
      Path to template partials (BE)
   :Type:
      string
   :Default:
      EXT:newsletter/Resources/Private/Partials/

 - :Setup:
      view.layoutRootPath
   :Description:
      Path to template layouts (BE)
   :Type:
      string
   :Default:
      EXT:newsletter/Resources/Private/Layouts/

 - :Setup:
      config.sender_name
   :Description:
      Default sender name.

      Can be overridden for each newsletter. If "user", the be_user owning the
      newsletter will be used. If blank, will default to the site name from
      $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'].
      Otherwise can be any string.
   :Type:
      string
   :Default:
      user

 - :Setup:
      config.sender_email
   :Description:
      Default sender email.

      Can be overridden for each newsletter. If "user", the be_user owning the
      newsletter will be used. If blank, will default to generic settings for
      the site or the system. Otherwise must be a valid email address.
   :Type:
      string
   :Default:
      user

 - :Setup:
      config.replyto_name
   :Description:
      Default Reply-To name.

      Can be overridden for each newsletter.
      Can be any string.
   :Type:
      string
   :Default:


 - :Setup:
      config.replyto_email
   :Description:
      Default Reply-To email.

      Can be overridden for each newsletter. If blank, will not be set.
      Must be a valid email address.
   :Type:
      string
   :Default:


 - :Setup:
      config.notification_email
   :Description:
      Notification email.

      Specify a valid address email to receive notification when recipients
      unsubscribe. If "user", the be_user owning the newsletter will be used.
      If blank, no notification will be sent. Otherwise must be a valid email
      address.
   :Type:
      string
   :Default:
      user

 - :Setup:
      config.fetch_path
   :Description:
      Base URL (scheme + domain + path) from which to fetch content and
      encode links with.

      Leave blank to use domain-records from the page tree.
   :Type:
      string
   :Default:


 - :Setup:
      config.append_url
   :Description:
      String to append to URL's when fetching content.

      For example use &type=<num> allows you to implement your newsletter with a
      special template. Or disable cache with &no_cache=1.
   :Type:
      string
   :Default:


 - :Setup:
      config.path_to_lynx
   :Description:
      Path to `Lynx <http://lynx.isc.org/>`_ text web browser.

      This program is not required, but can be used by Newsletter to produce an
      acceptable plaintext conversion.
   :Type:
      string
   :Default:
      /usr/bin/lynx


 - :Setup:
      config.path_to_fetchmail
   :Description:
      Path to `fetchmail <http://www.fetchmail.info/>`_ program.

      This a standard unix mail-retrieval program. It is used to collect bounced
      emails with.
   :Type:
      string
   :Default:
      /usr/bin/fetchmail

 - :Setup:
      config.keep_messages
   :Description:
      Keep bounced emails on server.

      Checking this option will leave bounced emails on the server.
      Default behaviour is to *delete* bounce messages, once they have been
      processed.
   :Type:
      boolean
   :Default:
      0

 - :Setup:
      config.attach_images
   :Description:
      Attach inline images to newsletters.

      Leave this checked to enable attached, inline images. This will normally
      improve you viewers experience, but reduces the performance of the mailer,
      since it has to deliver much more (binary) data. Uncheck it to instead
      link to the images online.
   :Type:
      boolean
   :Default:
      1

 - :Setup:
      config.mails_per_round
   :Description:
      Number of mails to send per iteration.

      This can be used to limit the rate on which Newsletter will send out mails.
      You must set this if you wish to use the "Invoke mailer" button. If you
      specify 0 as value, it will send all mails in one go.
   :Type:
      integer
   :Default:
      100

 - :Setup:
      config.unsubscribe_redirect
   :Description:
      Redirect to an external URL or internal Typo3 page.

      When set to a valid URL or a numeric Page ID (pid) the user will be
      redirected to that location instead of the default rendering of the
      unsubscribe template.
   :Type:
      string
   :Default:


 - :Setup:
      config.no-track
   :Description:
      A no-track marker.

      A unique marker or keyword that can be added to links in your newsletter
      to exclude them from being tracked when 'Detect clinked links' is enabled.
      How you add the marker to your anchor links is up to you.
   :Type:
      string
   :Default:


.. _configuration-realurl:

RealURL
-------

It is possible to configure the extension for use with RealURL to shorten the
length of URLS inside your newsletters.

Here is a example trimmed from ``realurlconf.php``:

.. code:: php

    <?php
    $TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'] = [
        // ...
        'postVarSets' => [
            '_DEFAULT' => [
                // RealURL for newsletter extension
                'redirect' => [
                    [
                        'GETvar' => 'type',
                        'valueMap' => [
                            'z' => '1342671779',
                        ],
                    ],
                    [
                        'GETvar' => 'tx_newsletter_p[action]',
                        'valueMap' => [
                            'g' => 'clicked',
                            's' => 'show',
                            'u' => 'unsubscribe',
                            'o' => 'opened',
                        ],
                    ],
                    [
                        'GETvar' => 'tx_newsletter_p[controller]',
                        'valueMap' => [
                            't' => 'Link',
                            'e' => 'Email',
                        ],
                    ],
                    [
                        'GETvar' => 'tx_newsletter_p[c]',
                        'cond' => [
                            'prevValueInList' => 'Email',
                        ],
                    ],
                    [
                        'GETvar' => 'tx_newsletter_p[n]',
                    ],
                    [
                        'GETvar' => 'tx_newsletter_p[l]',
                    ],
                    [
                        'GETvar' => 'tx_newsletter_p[p]',
                    ],
                ],
            ],
        ],
        // ...
    ];
