<?php

$payload = file_get_contents('php://input');
if (json_decode($payload)) {
    $payload = json_decode($payload, true);
}
header('Content-Type: text/json; charset=UTF-8');
require_once __DIR__ . '/engine/autoload.php';

$j = [
    'success' => false,
    'res' => [],
    'event' => [],
    'time' => 0,
];

if (isset($payload['controller']) && isset($payload['action'])) {
    if(isset($payload['params'])) {
        $data = $payload['params'];
    } else {
        $data = [];
    }

    ///////////////////////////// Common /////////////////////////////
    if ($payload['controller'] == "template") {
        if ($payload['action'] == "load") {
            $j['res'] = $engine->template->load();
        }
    } elseif ($payload['controller'] == "setting") {
        if ($payload['action'] == "get") {
            $j['success'] = true;
            $j['res'] = $engine->setting->get();
        }
    } elseif ($payload['controller'] == "error") {
        if ($payload['action'] == "fatal" || $payload['action'] == "warn") {
            $j['success'] = true;

            $row = query("SELECT * FROM `error_js` WHERE `type`=? AND `err`=? AND `info`=? AND `agent`=?;", [$payload['action'], json_encode($data['err']), $data['info'], $_SERVER['HTTP_USER_AGENT']])->fetchAll(PDO::FETCH_ASSOC);
            if (count($row) == 0) {
                query("INSERT INTO `error_js` (`type`,`err`,`info`,`agent`,`count`,`time`) VALUES (?,?,?,?,?,?);", [$payload['action'], json_encode($data['err']), $data['info'], $_SERVER['HTTP_USER_AGENT'], 1, date('Y/m/d H:i:s', time())]);
            } else {
                query("UPDATE `error_js` SET `count`=`count`+1,`time`=? WHERE `id`=?;", [$row[0]['id'], date('Y/m/d H:i:s', time())]);
            }
        }
    } elseif ($payload['controller'] == "user") {
        if ($payload['action'] == "register") {
            $j['res'] = $engine->user->register($data['email'], $data['password'], $data['name'], $data['address'], $data['phone'], $data['company']);
        }elseif ($payload['action'] == "login") {
            $j['res'] = $engine->user->login($data['email'], $data['password']);
        }elseif ($payload['action'] == "check") {
            $j['res'] = $engine->user->check();
        }elseif ($payload['action'] == "forgot") {
            $engine->user->forgotPassword($data['email']);
            $j['success'] = true;
            $j['res'] = null;
        }elseif ($payload['action'] == "resetpass") {
            $j['success'] = true;
            if($data['mode'] == "check") {
                $j['res'] = $engine->user->resetPassword($data['token']);
            }elseif($data['mode'] == "reset"){
                $j['res'] = $engine->user->resetPassword($data['token'], $data['password']);
            }
        }
    }

    ///////////////////////////// User /////////////////////////////
    if($engine->user->islogin) {
        if ($payload['controller'] == "user") {
            if ($payload['action'] == "logout") {
                $j['res'] = $engine->user->logout();
            }elseif ($payload['action'] == "edit") {
                $j['res'] = $engine->user->edit($data['id'], $data);
            }elseif ($payload['action'] == "get") {
                $j['res'] = $engine->user->get();
            }elseif ($payload['action'] == "changePass") {
                $j['res'] = $engine->user->changePassword($data['old'], $data['new'], $data['confirm']);
            }elseif ($payload['action'] == "confirmEmail") {
                $j['res'] = $engine->user->confirmEmail();
            }elseif ($payload['action'] == "confirmPhone") {
                if(isset($data['otp'])) {
                    $j['res'] = $engine->user->confirmPhone($data['phone'], $data['otp']);
                }else{
                    $j['res'] = $engine->user->confirmPhone($data['phone']);
                }
            }
        } elseif ($payload['controller'] == "billing") {
            if ($payload['action'] == "topup") {
                $j['success'] = true;
                if($data['gateway'] == "truemoney") {
                    $j['res'] = $engine->payment->truemoney($data['number']);
                }elseif($data['gateway'] == "truewallet") {
                    $j['res'] = $engine->payment->truewallet($data['transaction']);
                }elseif($data['gateway'] == "bank") {
                    $date = "{$data['day']}/{$data['month']}/{$data['year']}";
                    $time = "{$data['hour']}:{$data['minute']}";
                    $j['res'] = $engine->payment->bank($data['bank'], $date, $time, $data['amount']);
                }
            } elseif ($payload['action'] == "check") {
                $j['success'] = true;
                if($data['gateway'] == "truemoney") {
                    if(isset($data['transaction'])) {
                        $j['res'] = $engine->payment->truemoney_check($data['transaction']);
                    }else{
                        $j['res'] = [
                            'success' => false,
                            'error' => 'transaction',
                        ];
                    }
                }
            }
        } elseif ($payload['controller'] == "support") {
            if ($payload['action'] == "open") {
                $j['success'] = true;
                $j['res'] = $engine->ticket->open($data['category'], $data['message']);
            }elseif ($payload['action'] == "chat") {
                $j['success'] = true;
                $j['res'] = $engine->ticket->chat($data['ticket'], $data['message']);
            }elseif ($payload['action'] == "get") {
                $j['success'] = true;
                $j['res'] = [
                    "room" => $engine->ticket->get_room($engine->user->id),
                    "chat" => $engine->ticket->get_chats($engine->user->id),
            ];
            }
        } elseif ($payload['controller'] == "host") {
            if ($payload['action'] == "get") {
                $j['success'] = true;
                $j['res'] = $engine->host->get();
            }elseif ($payload['action'] == "get_template") {
                $j['success'] = true;
                $j['res'] = query("SELECT `id`, `name` FROM `{$engine->config['prefix']}xen_template` WHERE `visible` = 1;")->fetchAll(PDO::FETCH_ASSOC);
            }
        } elseif ($payload['controller'] == "vps") {
            if ($payload['action'] == "create") {
                $j['success'] = true;
                $j['res'] = $engine->vps->create($data['package'], $data['type'], $data['host'], $data['template'], $data['code']);
            } elseif ($payload['action'] == "get") {
                $j['success'] = true;
                $j['res'] = $engine->vps->getAll();
            } elseif ($payload['action'] == "expand") {
                $j['success'] = true;
                $j['res'] = $engine->vps->expand($data['vps']);
            } elseif ($payload['action'] == "start") {
                $j['success'] = true;
                $j['res'] = $engine->vps->start($data['vps']);
            } elseif ($payload['action'] == "stop") {
                $j['success'] = true;
                $j['res'] = $engine->vps->shutdown($data['vps']);
            } elseif ($payload['action'] == "setip") {
                $j['success'] = true;
                $j['res'] = $engine->vps->setip($data['vps']);
            } elseif ($payload['action'] == "toggle_auto_expand") {
                $j['success'] = true;
                $j['res'] = $engine->vps->toggle_auto_expand($data['vps']);
            } elseif ($payload['action'] == "console") {
                $j['success'] = true;
                $j['res'] = $engine->vps->console($data['id']);
            }
        } elseif ($payload['controller'] == "package") {
            if ($payload['action'] == "get") {
                $j['success'] = true;
                $j['res'] = $engine->package->getAll();
            }
        } elseif ($payload['controller'] == "ping") {
            $host = false;
            if(isset($data['vps'])) {
                $ip = query("SELECT `ip` FROM `{$engine->config['prefix']}ip` WHERE `useby` = ?;", [$data['vps']])->fetch(PDO::FETCH_ASSOC);
                $host = $ip['ip'];
            }elseif(isset($data['host'])){
                $host = $data['host'];
            }
            if($host) {
                $ch = curl_init("{$engine->xenserver->protocal}://{$engine->xenserver->server}/ping?host=" . $host);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                $response = curl_exec($ch);
                curl_close($ch);
                    
                $j['success'] = true;
                $j['res'] = $response;
            }
        } elseif ($payload['controller'] == "promotion") {
            if ($payload['action'] == "check") {
                $j['success'] = true;
                $j['res'] = $engine->code->check($data['type'], $data['code'], isset($data['option']) ? $data['option'] : []);
            }
        }
    }
    
    ///////////////////////////// ADMIN /////////////////////////////
    if($engine->user->islogin && $engine->user->admin) {
        if ($payload['controller'] == "user") {
            if ($payload['action'] == "getAll") {
                $j['success'] = true;
                $j['res'] = $engine->user->getAll();
            } elseif ($payload['action'] == "loginAs") {
                $j['success'] = true;
                $j['res'] = $engine->user->loginAs($data['id']);
            }
        } elseif ($payload['controller'] == "license") {
            if ($payload['action'] == "get") {
                $j['success'] = true;
                $j['res'] = $engine->license->get_detail();
            }
        } elseif ($payload['controller'] == "package") {
            if ($payload['action'] == "add") {
                $j['success'] = true;
                $j['res'] = $engine->package->add($data);
            } elseif ($payload['action'] == "delete") {
                $j['success'] = true;
                $j['res'] = $engine->package->delete($data['id']);
            }
        } elseif ($payload['controller'] == "host") {
            if ($payload['action'] == "get_ip") {
                $j['success'] = true;
                $j['res'] = $engine->host->ip($data['host']);
            } elseif ($payload['action'] == "add_ip") {
                $j['success'] = true;
                $j['res'] = $engine->host->ip_add($data['host'], $data['ip'], $data['subnet'], $data['gateway']);
            } elseif ($payload['action'] == "remove_ip") {
                $j['success'] = true;
                $j['res'] = $engine->host->ip_remove($data['host'], $data['id']);
            } elseif ($payload['action'] == "get_vm") {
                $j['success'] = true;
                $j['res'] = $engine->host->vm($data['host']);
            } elseif ($payload['action'] == "remove_vm") {
                $j['success'] = true;
                $j['res'] = $engine->vps->remove($data['id']);
            }
        } elseif ($payload['controller'] == "xenserver") {
            if ($payload['action'] == "check") {
                $j['success'] = true;
                $j['res'] = $engine->xenserver->check_connect($data['ip'], $data['port'], $data['user'], $data['pass']);
            } elseif ($payload['action'] == "active") {
                $j['success'] = true;
                $j['res'] = $engine->xenserver->active_license($data['ip'], $data['port'], $data['user'], $data['pass'], $data['location']);
            }
        } elseif ($payload['controller'] == "setting") {
            if ($payload['action'] == "save") {
                $j['success'] = true;
                $j['res'] = $engine->setting->save($data);
            }
        }
    }
}

if ($j['res'] === [] && $j['success'] == false) {
    $j['error'] = [
        'message' => 'Unknow request',
        'code' => 404,
    ];
} else {
    $j['success'] = true;
}
$j['time'] = microtime(true) * 10000;
if($engine->user->admin) {
    $j['debug']['payload'] = $payload;
}
echo json_encode($j);
close_connection();
exit();