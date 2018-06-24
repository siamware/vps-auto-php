<?php
use PHPWee\Minify;

class PHUMIN_STUDIO_Template {

    var $template = [
        'template' => [
            'template.html',
        ],
        'component' => [
            'billing.html',
            'document.html',
            'setting.html',
            'vps/add.html',
            'vps/home.html',
            'vps/detail.html',
            'admin/user/list.html',
            'admin/user/detail.html',
            'admin/package/list.html',
            'admin/package/add.html',
            'admin/server/list.html',
            'admin/server/detail.html',
            'admin/server/ip.html',
            'admin/server/vm.html',
            'admin/server/add.html',
            'admin/setting/common.html',
            'admin/setting/payment.html',
            'admin/setting/sms.html',
            'admin/setting/license.html',
            'error/404.html',
        ],
    ];
    var $bundleJSFile = [
        'vendor' => [
            '../lib/jquery/js/jquery.js',
            '../lib/popper.js/js/popper.js',
            '../lib/bootstrap/js/bootstrap.js',
            '../lib/jquery.cookie/js/jquery.cookie.js',
            '../lib/chartist/js/chartist.js',
            '../lib/d3/js/d3.js',
            '../lib/rickshaw/js/rickshaw.min.js',
            '../lib/jquery.sparkline.bower/js/jquery.sparkline.min.js',
            '../lib/perfect-scrollbar/js/perfect-scrollbar.jquery.js',
            '../lib/select2/js/select2.full.min.js',
            'ResizeSensor.js',
            'slim.js',
            'vue.js',
            'vue-router.js',
            'vue-resource.js',
            'vuex.js',
            'store.js',
            'app.js',
        ],
        'apps' => [
            'engine.js',
        ],
    ];

    public function load() {
        global $engine;
        $r = [
            'template' => [],
            'component' => [],
        ];

        foreach ($this->template as $k => $ty) {
            foreach ($ty as $f) {
                $n = str_replace('.html', '', $f);
                $n = str_replace("/", "-", $n);
                if ($k === "template") {
                    $r[$k][$n] = file_get_contents(__DIR__ . '/../template/' . $f);
                    $r[$k][$n] = ($r[$k][$n]);
                } elseif ($k === "component") {
                    $cn = str_replace(".html", "", $f);
                    $cn = str_replace("/", "-", $cn);
                    $data = file_get_contents(__DIR__ . '/../template/' . $f);
                    $r[$k][$n] = [
                        'name' => $cn,
                        'data' => ($data)
                    ];
                }
            }
        }

        return $r;
    }

    public function id($file) {
        global $engine;

        $time = filemtime(__DIR__ . '/../dist/' . $file . '.js');
        return hash('crc32', $time);
    }

    public function bundleJS($file = 'bundle',$changed = false) {
        global $engine;
        $stop = false;
        $data = "";

        if(!file_exists(__DIR__ . '/../assets/js/.cache')) {
            mkdir(__DIR__ . '/../assets/js/.cache/');
        }
        if(isset($this->bundleJSFile[$file])){
            foreach($this->bundleJSFile[$file] as $f){

                if(!file_exists(__DIR__ . '/../assets/js/.cache/.' . str_replace('/','-',$f))) {
                    file_put_contents(__DIR__ . '/../assets/js/.cache/.' . str_replace('/','-',$f), time());
                }
                if(!$stop) {
                    if(!$changed) {
                        if(filemtime(__DIR__ . '/../assets/js/' . $f) != file_get_contents(__DIR__ . '/../assets/js/.cache/.' . str_replace('/','-',$f))) {
                            //echo filemtime(__DIR__ . '/../assets/js/' . $f) ." = ".file_get_contents(__DIR__ . '/../assets/js/.cache/.' . $f)."<br>";
                            touch(__DIR__ . '/../assets/js/' . $f);
                            file_put_contents(__DIR__ . '/../assets/js/.cache/.' . str_replace('/','-',$f), time());
                            $this->bundleJS($file,true);
                            $stop = true;
                        }else{
                        }
                    }else{
                        $data .= file_get_contents(__DIR__ . '/../assets/js/' . $f);
                    }
                }else{
                    touch(__DIR__ . '/../assets/js/' . $f);
                    file_put_contents(__DIR__ . '/../assets/js/.cache/.' . str_replace('/','-',$f), time());
                }
            }
        }else{
            foreach($this->bundleJSFile as $cat){
                foreach($cat as $f){
                    if(!$stop) {
                        if(!$changed) {
                            if(!file_exists(__DIR__ . '/../assets/js/.cache/.' . str_replace('/','-',$f))) {
                                file_put_contents(__DIR__ . '/../assets/js/.cache/.' . str_replace('/','-',$f), time());
                            }
                            if(filemtime(__DIR__ . '/../assets/js/' . $f) != file_get_contents(__DIR__ . '/../assets/js/.cache/.' . str_replace('/','-',$f))) {
                                touch(__DIR__ . '/../assets/js/' . $f);
                                file_put_contents(__DIR__ . '/../assets/js/.cache/.' . str_replace('/','-',$f), time());
                                $this->bundleJS($file,true);
                                $stop = true;
                            }
                        }else{
                            $data .= file_get_contents(__DIR__ . '/../assets/js/' . $f);
                        }
                    }else{
                        touch(__DIR__ . '/../assets/js/' . $f);
                        file_put_contents(__DIR__ . '/../assets/js/.cache/.' . str_replace('/','-',$f), time());
                    }
                }
            }
        }
        if(!$stop && $changed){
            $min = Minify::js($data);
            //$min = $data;

            $fs   = fopen(__DIR__ . '/../dist/' . $file . '.js', 'w');
            $pieces = str_split($min, 1024);
            foreach ($pieces as $piece) {
                fwrite($fs, $piece, strlen($piece));
            }
            fclose($fs);
            //echo "compress js '{$file}'<br>";
            return $min;
        }
    }
}