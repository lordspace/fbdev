<?php

/*
  This PHP script accesses Facebook's API to: Get access token, create/delete a test user, get test user list
  (C) 2011 Svetoslav Marinov <slavi@slavi.biz>
  Donation Link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SY8MJ66FXDPQS
  Version: 1.0
  License: LGPL
  Credits: Jacques Perrault's posts href="http://forum.developers.facebook.net/viewtopic.php?id=81947
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

$instructions = <<<INSTR_EOF
<strong>Instructions:</strong>
First, get an access token by entering App ID and App Secret Key. After that only the token will be used to access the other functions.
Note: None of the entered information is stored on my server.

If you like this script feel free to <a href="#" onclick="document.getElementById('pp_form').submit();return false;">Donate</a> (the botton is at the bottom of the page).

INSTR_EOF;

$output = '';

$app_id = empty($_REQUEST['app_id']) ? '' : $_REQUEST['app_id'];
$app_secret_key = empty($_REQUEST['app_secret_key']) ? '' : $_REQUEST['app_secret_key'];
$api_access_token = empty($_REQUEST['api_access_token']) ? '' : $_REQUEST['api_access_token'];
$owner_access_token = empty($_REQUEST['owner_access_token']) ? '' : $_REQUEST['owner_access_token'];
$user_id = empty($_REQUEST['user_id']) ? '' : $_REQUEST['user_id'];
    
$args = array(
    'grant_type'    => 'client_credentials',
    'client_id'     => $app_id,
    'client_secret' => $app_secret_key,
);

if (!empty($_POST)) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_POST, true); 
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HEADER, false); 
    
    if (!empty($_REQUEST['token'])) {
        $url = 'https://graph.facebook.com/oauth/access_token';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args);        
    } elseif (!empty($_REQUEST['create_user'])) {   
        $url = 'https://graph.facebook.com/' . $app_id . '/accounts/test-users';
        $args['access_token'] = $api_access_token;
        $args['installed'] = 'true';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
    }  elseif (!empty($_REQUEST['delete_user'])) {   
        $url = 'https://graph.facebook.com/' . $user_id . '?access_token=' . urlencode($api_access_token);        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    } elseif (!empty($_REQUEST['get_user_list'])) {    
        $url = 'https://graph.facebook.com/' . $app_id . '/accounts/test-users?access_token=' . urlencode($api_access_token);        
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    } else {
        return;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);

    //php safe mode? who runs that? // $redirects = 0; $res  = curl_redirect_exec($ch, &$redirects, $curlopt_header = false);
    $res  = curl_exec($ch);
    
    $output .= "<h3>Result:</h3>";    
    
    if (empty($res)) {
        $output .= "Curl Error: " . curl_error($ch);
    } else {
        $output .= var_export($res, 1);
        
        // JSON sting ?
        if (strpos($res, '}') !== false) {
            $php_obj = json_decode($res);
            $output .= "<h3>Decoding the JSON content </h3><pre>";
            $output .= var_export($php_obj, 1);
            $output .= "</pre>";
        } elseif (!empty($_REQUEST['token'])) {
            // let's prefill the token field
            $data_arr = array();
            parse_str($res, $data_arr);
            $api_access_token = $data_arr['access_token'];
            
            if (!empty($api_access_token)) {
                $output .= "<br/><span class='success'> Access token has been prefilled in the form.</span>";
            }
        }
    }
    
    curl_close($ch);
    
    $output .= "<br/>";
    
    /*
    string(56) "access_token=133697610029197|D6T2gfq3nTF95li4blB-Y2fhsog" 
    */
}
?> 
<html>
    <head>
        <title>PHP script accessing Facebook Test Users API</title>
	    <meta name="keywords" content="FB,facebook,test users,create test facebook accounts,api test accounts,api test fb accounts" />
	    <meta name="description" content="This PHP script accesses Facebook's API to: Get access token, create/delete a test user, get test user list" />
        <meta name="MSSmartTagsPreventParsing" content="true">
        <meta HTTP-EQUIV="content-type" content="text/html; charset=utf-8">
        <link rel="stylesheet" href="fbapi.style.css" type="text/css" media="screen" />
    </head>
<body>
<div id="site-wrapper">

<h2>Facebook</h2>
<p>
<?php echo nl2br($instructions); ?>
</p>
<div class="search">
    <form method="post" action="">
        <table width="50%">
        <tr>
            <td>App ID: </td>
            <td><input type="text" name="app_id" value="<?php echo $app_id;?>"/> </td>
        </tr>
        <tr>
            <td>App Secret Key: </td>
            <td><input type="text" name="app_secret_key" value="<?php echo $app_secret_key;?>"/> </td>
        </tr>
        <tr>
            <td>Access Token: </td>
            <td><input type="text" name="api_access_token" value="<?php echo $api_access_token;?>"/>  </td>
        </tr>    
        <!--<tr>
            <td>Owner Access Token: </td>
            <td><input type="text" name="owner_access_token" value="<?php echo $owner_access_token;?>"/>   </td>
        </tr>-->
        <tr>
            <td>User ID (req. when deleting user): </td>
            <td><input type="text" name="user_id" value="<?php echo $user_id;?>"/> </td>
        </tr>               
        <tr>
            <td colspan="2">
                <input type="submit" name="token" value="1. Get signed access token"/>
                <input type="submit" name="create_user" value="2. Create a user"/>
                <input type="submit" name="delete_user" value="3. Delete a user" onclick="return confirm('Are you sure?');"/>
                <input type="submit" name="get_user_list" value="4. Get user list"/>
            </td>
        </tr>               
        </table>
    </form>
</div>

    <div>
        <?php echo $output;?>
    </div>

    <div id="footer">
        <div class="clearer">&nbsp;</div>
        <div id="footer-right" class="right">
            (c) Svetoslav Marinov  <strong>&lt;slavi@slavi.biz&gt;</strong> <span class="text-separator">|</span>            
       
       <form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="pp_form" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="SY8MJ66FXDPQS">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>     <span class="text-separator">|</span>
            <a target="_blank" href="http://devcha.com">My Blog</a> <span class="text-separator">|</span>
            <a target="_blank" href="http://webweb.ca">http://webweb.ca</a> <span class="text-separator">|</span>
            Credits: <a  target="_blank" href="http://forum.developers.facebook.net/viewtopic.php?id=81947">Jacques Perrault's posts</a> 
        </div>        
    </div>

</div>

</body>
</html>
