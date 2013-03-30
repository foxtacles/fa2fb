<?

function collect($string, $start, $end){
    $found = array();
    $pos = 0;

    while (true) {
        $pos = strpos($string, $start, $pos);
        if ($pos === false)
            return $found;
        $pos += strlen($start);
        $len = strpos($string, $end, $pos) - $pos;
        $found[] = substr($string, $pos, $len);
    }
}

include('config.php');
include('facebook.php');

$data = file_get_contents($FA_URL);
$last = @file_get_contents(dirname(__FILE__) . '/last_submission');

$submissions = collect($data, 'sid_', '"');

$facebook = new Facebook(array(
	'appId' => $FB_APPID,
	'secret' => $FB_SECRET
));

$facebook->setAccessToken($FB_AUTH);

$accounts = $facebook->api('/me/accounts');

$FB_AUTH = null;

foreach ($accounts['data'] as $account)
	if ($account['id'] == $FB_PAGE)
		$FB_AUTH = $account['access_token'];

if (!$FB_AUTH)
	exit('Could not find access token for page');

if (!empty($submissions)) {
	if ($last == '') {
		file_put_contents(dirname(__FILE__) . '/last_submission', $submissions[0]);
	}
	else {
		$last = trim($last);
		$find = array_search($last, $submissions);

		if ($find !== false)
			$submissions = array_slice($submissions, 0, $find);
		else
		{
			file_put_contents(dirname(__FILE__) . '/last_submission', $submissions[0]);
			exit();
		}

		if (!empty($submissions)) {
                	$last = $submissions[0];
			file_put_contents(dirname(__FILE__) . '/last_submission', $last);
                	$submissions = array_reverse($submissions);

			$i = count($submissions);
			$titles = collect($data, '<span title="', '"');

			foreach ($submissions as $submission) {
				$url = 'https://www.furaffinity.net/view/' . $submission;

				$img = 't.facdn.net/' . $submission . '@';
				$img = 'https://' . $img . collect($data, $img, '"')[0];

				$title = $titles[$i - 1];

				$facebook->api('/' . $FB_PAGE . '/feed', 'post', array('access_token' => $FB_AUTH, 'link' => $url, 'picture' => $img, 'caption' => $title, 'message' => $title));

				--$i;
			}
		}
	}
}
