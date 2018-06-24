<?php

class BANK_KBANK {
    public static function check($USERNAME, $PASSWORD, $ACCOUNT_NAME) {
        global $engine;
        $COOKIEFILE = dirname(__FILE__) . '/cookies';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.6 (KHTML, like Gecko) Chrome/16.0.897.0 Safari/535.6");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . "/cacert.pem");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $COOKIEFILE);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $COOKIEFILE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $form_field = array();
        $form_field['isConfirm	'] = 'T';
        $post_string = '';
        foreach ($form_field as $key => $value) {
            $post_string .= $key . '=' . urlencode($value) . '&';
        }
        $post_string = substr($post_string, 0, -1);

        // pre login page
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_URL, 'https://online.kasikornbankgroup.com/K-Online/preLogin/popupPreLogin.jsp?lang=th&isConfirm=T');
        $data = curl_exec($ch);

        // load login
        curl_setopt($ch, CURLOPT_URL, 'https://online.kasikornbankgroup.com/K-Online/login.do');
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        $data = curl_exec($ch);

        $html = str_get_html($data);
        $form_field = array();
        foreach ($html->find('form input') as $element) {
            $form_field[$element->name] = $element->value;
        }
        $form_field['userName'] = $USERNAME;
        $form_field['password'] = $PASSWORD;
        $post_string = '';
        foreach ($form_field as $key => $value) {
            $post_string .= $key . '=' . urlencode($value) . '&';
        }
        $post_string = substr($post_string, 0, -1);

        // login
        curl_setopt($ch, CURLOPT_REFERER, 'https://online.kasikornbankgroup.com/K-Online/login.do');
        curl_setopt($ch, CURLOPT_URL, 'https://online.kasikornbankgroup.com/K-Online/login.do');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        $data = curl_exec($ch);

        // redirect after login
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        curl_setopt($ch, CURLOPT_URL, 'https://online.kasikornbankgroup.com/K-Online/ib/redirectToIB.jsp?r=7027');
        $data = curl_exec($ch);

        $html = str_get_html($data);
        $form_field = array();
        foreach ($html->find('form input') as $element) {
            $form_field[$element->name] = $element->value;
        }
        $post_string = '';
        foreach ($form_field as $key => $value) {
            $post_string .= $key . '=' . urlencode($value) . '&';
        }
        $post_string = substr($post_string, 0, -1);

        // welcome page
        curl_setopt($ch, CURLOPT_URL, 'https://ebank.kasikornbankgroup.com/retail/security/Welcome.do');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        $data = curl_exec($ch);


        // last statement page
        curl_setopt($ch, CURLOPT_URL, 'https://ebank.kasikornbankgroup.com/retail/cashmanagement/TodayAccountStatementInquiry.do');
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        $data = curl_exec($ch);

        $data = iconv("windows-874", "utf-8", $data);
        $html = str_get_html($data);
        $form_field = array();
        foreach ($html->find('form[name="TodayStatementForm"] input') as $element) {
            $form_field[$element->name] = $element->value;
        }
        // select account
        $s = $ACCOUNT_NAME;
        foreach ($html->find('select[name="acctId"] option') as $element) {
            $text = clean($element->plaintext);
            $ss = $s[0] . $s[1] . $s[2] . '-' . $s[3] . '-' . $s[4] . $s[5] . $s[6] . $s[7] . $s[8] . '-' . $s[9];
            $pos = strpos($text, $ss);
            if ($pos !== false) {
                $form_field['acctId'] = $element->value;
            }
        }
        $post_string = '';
        foreach ($form_field as $key => $value) {
            $post_string .= $key . '=' . urlencode($value) . '&';
        }


        $post_string = substr($post_string, 0, -1);
        curl_setopt($ch, CURLOPT_URL, 'https://ebank.kasikornbankgroup.com/retail/cashmanagement/TodayAccountStatementInquiry.do');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        $data = curl_exec($ch);

        $data = iconv("windows-874", "utf-8", $data);

        $total = array();
        $s = 'วันที่';
        $html = str_get_html($data);
        $table = $html->find('table[rules="rows"]', 0);

        if (!(empty($table))) {
            foreach ($table->find('tr') as $tr) {
                $td1 = clean($tr->find('td', 0)->plaintext);
                $pos = strpos($td1, $s);
                if ($pos !== false)
                    continue;

                $list = array();
                $date = explode("\n", $td1);
                if (is_array($date)) {
                    $days = explode("/", $date[0]);
                    $times = explode(":", $date[1]);
                    $days[2] = str_replace(" ", "", $days[2]);
                    $days[2] = str_replace("\n", "", $days[2]);
                    $days[2] = str_replace("\r", "", $days[2]);
                    $days[2] = str_replace("\t", "", $days[2]);
                }else{
                    $days = array(0,0,0);
                    $times = array(0,0,0);
                }

                $list['date'] = array(
                    "day" => array(
                        "day" => (int) $days[0],
                        "month" => (int) $days[1],
                        "year" => (int) (2000 + $days[2]),
                    ),
                    "time" => array(
                        "hour" => (int) $times[0],
                        "minute" => (int) $times[1],
                        "second" => (int) $times[2],
                    ),
                );
                $list['in'] = (float) str_replace(',', '', clean($tr->find('td', 4)->plaintext));
                $list['out'] = (float) str_replace(',', '', clean($tr->find('td', 3)->plaintext));
                $list['info'] = clean($tr->find('td', 1)->plaintext);// . ' ' . clean($tr->find('td', 6)->plaintext);

                if (empty($list['in']))
                    continue;
                $total[] = $list;
            }
        }
        return $total;
    }

}
