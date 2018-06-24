<?php

class PHUMIN_STUDIO_Setting {

      public function get() {
            global $engine;

            $r = [
                  'maintance_mode' => config('maintance_mode'),
                  'sitename' => config('sitename'),
                  'sitedesc' => config('sitedesc'),
                  'auto_choose_server' => config('auto_choose_server'),
                  'keep_before_remove' => config('keep_before_remove'),
                  'round_day' => config('round_day'),
                  'time_before_remove' => config('time_before_remove'),
                  'sms_notification' => config('sms_notification'),
                  'refer_share' => config('refer_share'),
            ];

            if($engine->user->admin) {
                  $r['worker_updating'] = config('worker_updating');
                  $r['current_version'] = config('current_version');
                  $r['license_key'] = config('license_key');
                  $r['truemoney_gateway'] = config('truemoney_gateway');
                  $r['truemoney_tmpay_merchant'] = config('truemoney_tmpay_merchant');
                  $r['truemoney_tmpay_50'] = config('truemoney_tmpay_50');
                  $r['truemoney_tmpay_90'] = config('truemoney_tmpay_90');
                  $r['truemoney_tmpay_150'] = config('truemoney_tmpay_150');
                  $r['truemoney_tmpay_300'] = config('truemoney_tmpay_300');
                  $r['truemoney_tmpay_500'] = config('truemoney_tmpay_500');
                  $r['truemoney_tmpay_1000'] = config('truemoney_tmpay_1000');
                  $r['truewallet_phone'] = config('truewallet_phone');
                  $r['truewallet_pin'] = config('truewallet_pin');
                  $r['truewallet_range'] = json_decode(config('truewallet_range'), true);
                  $r['sms_gateway'] = config('sms_gateway');
                  $r['sms_config_thaibulk'] = json_decode(config('sms_config_thaibulk'), true);
                  $r['sms_config_thsms'] = json_decode(config('sms_config_thsms'), true);
                  $r['sms_config_molinksms'] = json_decode(config('sms_config_molinksms'), true);
                  $r['bank_kbank'] = json_decode(config('bank_kbank'), true);
            }

            return $r;
      }

      public function save($data) {
            global $engine;

            if($engine->user->admin) {
                  save_config($data);
            }

            return $this->get();
      }
}