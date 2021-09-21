<?php
    if (!isset($_POST["username"]) || !isset($_POST["amount"]) || !isset($_POST["tebex-secret"]) || !isset($_POST["note"])) {
        http_response_code(400);
        die("Invalid parameters");
    }

    $username = $_POST["username"];
    $amount = $_POST["amount"];
    $secret = $_POST["tebex-secret"];
    $note = $_POST["note"];

    $today = date("Y-m-d");
    $final = endCycle($today, 1);

    //The url you wish to send the POST request to
    $url = 'https://plugin.tebex.io/coupons';

    //The data you want to send via POST
    $fields = array(
        'code' => $username . '_' . generateRandomString(),
        'effective_on' => 'cart',
        'packages' => array(),
        'categories' => array(),
        'discount_type' => 'value',
        'discount_amount' => $amount,
        'discount_percentage' => '0',
        'redeem_unlimited' => 'false',
        'expire_never' => 'false',
        'expire_limit' => '1',
        'expire_date' => $final,
        'start_date' => $today,
        'basket_type' => 'both',
        'minimum' => '0',
        'discount_application_method' => '2',
        'note' => $note
    );

    //url-ify the data for the POST
    $fields_string = http_build_query($fields);

    //open connection
    $ch = curl_init();

    $headers = array(
        "X-Tebex-Secret: " . $secret,
        "Content-Type: application/x-www-form-urlencoded",
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, true);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

    //So that curl_exec returns the contents of the cURL; rather than echoing it
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

    //execute post
    $result = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($http_status != 200) {
        http_response_code(500);
        die("Coupon creation failed");
    }

    die($result);


    function add_months($months, DateTime $dateObject)
    {
        $next = new DateTime($dateObject->format('Y-m-d'));
        $next->modify('last day of +'.$months.' month');

        if($dateObject->format('d') > $next->format('d')) {
            return $dateObject->diff($next);
        } else {
            return new DateInterval('P'.$months.'M');
        }
    }

    function endCycle($d1, $months)
    {
        $date = new DateTime($d1);

        // call second function to add the months
        $newDate = $date->add(add_months($months, $date));

        // goes back 1 day from date, remove if you want same day of month
        $newDate->sub(new DateInterval('P1D'));

        //formats final date to Y-m-d form
        $dateReturned = $newDate->format('Y-m-d');

        return $dateReturned;
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }