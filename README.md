fa2fb
=====

Publish FurAffinity submissions on a facebook page (cronjob)

Use
=====
Create a file 'config.php' (see config_new.php). Add your Facebook app details, page ID, and access_token (generate one which doesn't expire too soon). Install a crontab, for example:

> crontab -e

Append:

*/1 * * * * php -f /home/foxtacles/fa2fb/fa2fb.php &> /dev/null
