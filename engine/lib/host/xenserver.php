<?php

class PHUMIN_STUDIO_Xenserver {

      public $protocal = "https";
      public $server = "server1.phumin.in.th";
      private $sandbox = false;

      private function request($action, $data) {
            global $engine;
            
            $url = "{$this->protocal}://{$this->server}/" . $action;
            $ch = curl_init("{$this->protocal}://{$this->server}/" . $action);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            // execute!
            $response = curl_exec($ch);
            curl_close($ch);

            return json_decode($response, true);
      }

      public function active_license($ip, $port, $user, $pass, $location) {
            global $engine;

            if($sandbox) {
                  query("INSERT INTO `{$engine->config['prefix']}xen_host` (`api_token`, `last_check`) VALUES (?,?);", ["sandbox", time()]);
            } else {
                  $r = $this->request('license_active', [
                        'hypervisor' => 'xenserver',
                        'license_key' => config('license_key'),
                        'host_label' => '',
                        'host_address' => $ip,
                        'host_port' => $port,
                        'host_username' => $user,
                        'host_password' => $pass,
                        'webhook' => $location . "webhook.php",
                  ]);
                  if(isset($r['data']['api_key'])) {
                        query("INSERT INTO `{$engine->config['prefix']}xen_host` (`api_token`, `last_check`) VALUES (?,?);", [$r['data']['api_key'], time()]);
                  }
            }
            return $r;
      }

      public function check_connect($ip, $port, $user, $pass) {
            global $engine;

            $r = $this->request('check_connect', [
                  'hypervisor' => 'xenserver',
                  'ip' => $ip,
                  'port' => $port,
                  'user' => $user,
                  'pass' => $pass,
            ]);
            return $r;
      }

      public function clone_vm($user, $package, $host, $template, $ip) {
            global $engine;

            
            $r = $this->request('vm_clone', [
                  'hypervisor' => 'xenserver',
                  'license_key' => config('license_key'),
                  'api_token' => $host['api_token'],
                  'name' => "ลูกค้า #{$engine->user->id} ({$ip['ip']})",
                  'template' => $template['opaqueRef'],
                  'cpu' => $package['cpu'],
                  'ram' => $package['ram'],
                  'disk' => $package['disk'],
                  'ip' => $ip['ip'],
                  'subnetmask' => $ip['subnet'],
                  'gateway' => $ip['gateway'],
            ]);

            if(isset($r['vms'])) {
                  $this->sync_vm($host['api_token'], $r['vms']);
            }
            if(isset($r['last'])) {
                  return $r['last'];
            }
      }

      public function start_vm($host, $vm) {
            global $engine;

            $r = $this->request('vm_start', [
                  'hypervisor' => 'xenserver',
                  'license_key' => config('license_key'),
                  'api_token' => $host['api_token'],
                  'opaqueRef' => $vm,
            ]);
            query("UPDATE `{$engine->config['prefix']}xen_vm` SET `powerState` = ? WHERE `opaqueRef` = ?;", [$r['powerState'], $r['opaqueRef']]);
            return $r;
      }

      public function shutdown_vm($host, $vm) {
            global $engine;

            $r = $this->request('vm_shutdown', [
                  'hypervisor' => 'xenserver',
                  'license_key' => config('license_key'),
                  'api_token' => $host['api_token'],
                  'opaqueRef' => $vm,
            ]);
            query("UPDATE `{$engine->config['prefix']}xen_vm` SET `powerState` = ? WHERE `opaqueRef` = ?;", [$r['powerState'], $r['opaqueRef']]);
            return $r;
      }

      public function pause_vm($host, $vm) {
            global $engine;

            $r = $this->request('vm_pause', [
                  'hypervisor' => 'xenserver',
                  'license_key' => config('license_key'),
                  'api_token' => $host['api_token'],
                  'opaqueRef' => $vm,
            ]);
            query("UPDATE `{$engine->config['prefix']}xen_vm` SET `powerState` = ? WHERE `opaqueRef` = ?;", [$r['powerState'], $r['opaqueRef']]);
            return $r;
      }

      public function unpause_vm($host, $vm) {
            global $engine;

            $r = $this->request('vm_unpause', [
                  'hypervisor' => 'xenserver',
                  'license_key' => config('license_key'),
                  'api_token' => $host['api_token'],
                  'opaqueRef' => $vm,
            ]);
            query("UPDATE `{$engine->config['prefix']}xen_vm` SET `powerState` = ? WHERE `opaqueRef` = ?;", [$r['powerState'], $r['opaqueRef']]);
            return $r;
      }

      public function remove_vm($host, $vm) {
            global $engine;

            $r = $this->request('vm_remove', [
                  'hypervisor' => 'xenserver',
                  'license_key' => config('license_key'),
                  'api_token' => $host['api_token'],
                  'opaqueRef' => $vm,
            ]);
            return $r;
      }

      public function console_vm($host, $vm) {
            global $engine;

            $r = $this->request('vm_console', [
                  'hypervisor' => 'xenserver',
                  'license_key' => config('license_key'),
                  'api_token' => $host['api_token'],
                  'opaqueRef' => $vm,
            ]);
            return $r;
      }

      public function update_vm_status($api_token = null, $vm = null, $status = 0) {
            global $engine;

            $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `api_token` = ?;", [$api_token])->fetch(PDO::FETCH_ASSOC);
            $vm_available = query("SELECT * FROM `{$engine->config['prefix']}xen_vm` WHERE `server` = ? AND `opaqueRef` = ?;", [$host['id'], $vm])->rowCount();
            if($vm_available == 1) {
                  $engine->vps->update_status($host['id'], $vm, $status);
            }
      }

      public function setip_vm($host, $vm, $ip, $subnet, $gateway) {
            global $engine;

            $r = $this->request('set_ip', [
                  'hypervisor' => 'xenserver',
                  'license_key' => config('license_key'),
                  'api_token' => $host['api_token'],
                  'opaqueRef' => $vm,
                  'ip' => $ip,
                  'subnetmask' => $subnet,
                  'gateway' => $gateway,
            ]);
            return $r;
            return true;
      }

      public function sync_vm($api_token = null, $vs = null) {
            global $engine;

            if($api_token === null) {
                  $api_token = $_POST['api_token'];
            }
            if($vs === null) {
                  $vs = $_POST['vm'];
            }

            $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `api_token` = ?;", [$api_token])->fetch(PDO::FETCH_ASSOC);
            if($host) {
                  $v_keep = query("SELECT * FROM `{$engine->config['prefix']}xen_vm` WHERE `server` = ?;", [$host['id']])->fetchAll(PDO::FETCH_ASSOC);
                  $v_delete = [];
                  $v_update = [];
                  $v_new = [];
                  // Check same vm in database
                  foreach($v_keep as $v1) {
                        $have = false;
                        foreach($vs as $v2) {
                              if($v1['opaqueRef'] == $v2['opaqueRef'] && $v1['uuid'] == $v2['uuid']) {
                                    $have = true;
                              }
                        }
                        if($have == false) {
                              $v_delete[] = $v1;
                        }
                  }
                  // Check same vm in new data
                  foreach($vs as $v1) {
                        $have = false;
                        foreach($v_keep as $v2) {
                              if($v1['opaqueRef'] == $v2['opaqueRef'] && $v1['uuid'] == $v2['uuid']) {
                                    $have = true;
                              }
                        }
                        if($have == false) {
                              $v_new[] = $v1;
                        }else{
                              $v_update[] = $v1;
                        }
                  }

                  // Remove vm
                  foreach($v_delete as $v) {
                        query("DELETE FROM `{$engine->config['prefix']}xen_vm` WHERE `id` = ?;", [$v['id']]);
                  }

                  // Add vm
                  foreach($v_new as $v) {
                        query("INSERT INTO `{$engine->config['prefix']}xen_vm` (`server`, `opaqueRef`, `uuid`, `name`, `powerState`, `ram`, `cpu`) VALUES (?,?,?,?,?,?,?);", [$host['id'], $v['opaqueRef'], $v['uuid'], $v['name'], $v['powerState'], $v['ram'], $v['vcpu']]);
                  }

                  // Update vm
                  foreach($v_update as $v) {
                        query("UPDATE `{$engine->config['prefix']}xen_vm` SET `server` = ?, `name` = ?, `powerState` = ?, `ram` = ?, `cpu` = ? WHERE `opaqueRef` = ? AND `uuid` = ? AND `server` = ?;", [$host['id'], $v['name'], $v['powerState'], $v['ram'], $v['vcpu'], $v['opaqueRef'], $v['uuid'], $host['id']]);
                  }
            }
      }

      public function sync_vbd($api_token = null, $vs = null) {
            global $engine;

            if($api_token === null) {
                  $api_token = $_POST['api_token'];
            }
            if($vs === null) {
                  $vs = $_POST['vbd'];
            }
            $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `api_token` = ?;", [$api_token])->fetch(PDO::FETCH_ASSOC);
            if($host) {
                  $v_keep = query("SELECT * FROM `{$engine->config['prefix']}xen_vbd` WHERE `server` = ?;", [$host['id']])->fetchAll(PDO::FETCH_ASSOC);
                  $v_delete = [];
                  $v_update = [];
                  $v_new = [];
                  // Check same data in database
                  foreach($v_keep as $v1) {
                        $have = false;
                        foreach($vs as $v2) {
                              if($v1['opaqueRef'] == $v2['opaqueRef'] && $v1['uuid'] == $v2['uuid']) {
                                    $have = true;
                              }
                        }
                        if($have == false) {
                              $v_delete[] = $v1;
                        }
                  }
                  // Check same data in new data
                  foreach($vs as $v1) {
                        $have = false;
                        foreach($v_keep as $v2) {
                              if($v1['opaqueRef'] == $v2['opaqueRef'] && $v1['uuid'] == $v2['uuid']) {
                                    $have = true;
                              }
                        }
                        if($have == false) {
                              $v_new[] = $v1;
                        }else{
                              $v_update[] = $v1;
                        }
                  }
                  // Remove
                  foreach($v_delete as $v) {
                        query("DELETE FROM `{$engine->config['prefix']}xen_vbd` WHERE `id` = ?;", [$v['id']]);
                  }
                  // Add
                  foreach($v_new as $v) {
                        query("INSERT INTO `{$engine->config['prefix']}xen_vbd` (`server`, `opaqueRef`, `uuid`, `vm`, `vdi`, `type`, `mode`) VALUES (?,?,?,?,?,?,?);", [$host['id'], $v['opaqueRef'], $v['uuid'], $v['VM'], $v['VDI'], $v['type'], $v['mode']]);
                  }
                  // Update
                  foreach($v_update as $v) {
                        query("UPDATE `{$engine->config['prefix']}xen_vbd` SET `vm` = ?, `vdi` = ?, `type` = ?, `mode` = ? WHERE `opaqueRef` = ? AND `uuid` = ? AND `server` = ?;", [$v['VM'], $v['VDI'], $v['type'], $v['mode'], $v['opaqueRef'], $v['uuid'], $host['id']]);
                  }
            }
      }

      public function sync_vdi($api_token = null, $vs = null) {
            global $engine;

            if($api_token === null) {
                  $api_token = $_POST['api_token'];
            }
            if($vs === null) {
                  $vs = $_POST['vdi'];
            }
            $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `api_token` = ?;", [$api_token])->fetch(PDO::FETCH_ASSOC);
            if($host) {
                  $v_keep = query("SELECT * FROM `{$engine->config['prefix']}xen_vdi` WHERE `server` = ?;", [$host['id']])->fetchAll(PDO::FETCH_ASSOC);
                  $v_delete = [];
                  $v_update = [];
                  $v_new = [];
                  // Check same data in database
                  foreach($v_keep as $v1) {
                        $have = false;
                        foreach($vs as $v2) {
                              if($v1['opaqueRef'] == $v2['opaqueRef'] && $v1['uuid'] == $v2['uuid']) {
                                    $have = true;
                              }
                        }
                        if($have == false) {
                              $v_delete[] = $v1;
                        }
                  }
                  // Check same data in new data
                  foreach($vs as $v1) {
                        $have = false;
                        foreach($v_keep as $v2) {
                              if($v1['opaqueRef'] == $v2['opaqueRef'] && $v1['uuid'] == $v2['uuid']) {
                                    $have = true;
                              }
                        }
                        if($have == false) {
                              $v_new[] = $v1;
                        }else{
                              $v_update[] = $v1;
                        }
                  }
                  // Remove
                  foreach($v_delete as $v) {
                        query("DELETE FROM `{$engine->config['prefix']}xen_vdi` WHERE `id` = ?;", [$v['id']]);
                  }
                  // Add
                  foreach($v_new as $v) {
                        query("INSERT INTO `{$engine->config['prefix']}xen_vdi` (`server`, `opaqueRef`, `uuid`, `name`, `size`, `sr`) VALUES (?,?,?,?,?,?);", [$host['id'], $v['opaqueRef'], $v['uuid'], $v['name'], $v['virtual_size'], $v['SR']]);
                  }
                  // Update
                  foreach($v_update as $v) {
                        query("UPDATE `{$engine->config['prefix']}xen_vdi` SET `name` = ?, `size` = ?, `sr` = ? WHERE `opaqueRef` = ? AND `uuid` = ? AND `server` = ?;", [$v['name'], $v['virtual_size'], $v['SR'], $v['opaqueRef'], $v['uuid'], $host['id']]);
                  }
            }
      }

      public function sync_vif($api_token = null, $vs = null) {
            global $engine;

            if($api_token === null) {
                  $api_token = $_POST['api_token'];
            }
            if($vs === null) {
                  $vs = $_POST['vif'];
            }
            $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `api_token` = ?;", [$api_token])->fetch(PDO::FETCH_ASSOC);
            if($host) {
                  $v_keep = query("SELECT * FROM `{$engine->config['prefix']}xen_vif` WHERE `server` = ?;", [$host['id']])->fetchAll(PDO::FETCH_ASSOC);
                  $v_delete = [];
                  $v_update = [];
                  $v_new = [];
                  // Check same data in database
                  foreach($v_keep as $v1) {
                        $have = false;
                        foreach($vs as $v2) {
                              if($v1['opaqueRef'] == $v2['opaqueRef'] && $v1['uuid'] == $v2['uuid']) {
                                    $have = true;
                              }
                        }
                        if($have == false) {
                              $v_delete[] = $v1;
                        }
                  }
                  // Check same data in new data
                  foreach($vs as $v1) {
                        $have = false;
                        foreach($v_keep as $v2) {
                              if($v1['opaqueRef'] == $v2['opaqueRef'] && $v1['uuid'] == $v2['uuid']) {
                                    $have = true;
                              }
                        }
                        if($have == false) {
                              $v_new[] = $v1;
                        }else{
                              $v_update[] = $v1;
                        }
                  }
                  // Remove
                  foreach($v_delete as $v) {
                        query("DELETE FROM `{$engine->config['prefix']}xen_vif` WHERE `id` = ?;", [$v['id']]);
                  }
                  // Add
                  foreach($v_new as $v) {
                        query("INSERT INTO `{$engine->config['prefix']}xen_vif` (`server`, `opaqueRef`, `uuid`, `vm`, `network`, `mac`) VALUES (?,?,?,?,?,?);", [$host['id'], $v['opaqueRef'], $v['uuid'], $v['vm'], $v['network'], $v['MAC']]);
                  }
                  // Update
                  foreach($v_update as $v) {
                        query("UPDATE `{$engine->config['prefix']}xen_vif` SET `vm` = ?, `network` = ?, `mac` = ? WHERE `opaqueRef` = ? AND `uuid` = ? AND `server` = ?;", [$v['vm'], $v['network'], $v['MAC'], $v['opaqueRef'], $v['uuid'], $host['id']]);
                  }
            }
      }

      public function sync_template($api_token = null, $vs = null) {
            global $engine;

            if($api_token === null) {
                  $api_token = $_POST['api_token'];
            }
            if($vs === null) {
                  $vs = $_POST['template'];
            }
            $host = query("SELECT * FROM `{$engine->config['prefix']}xen_host` WHERE `api_token` = ?;", [$api_token])->fetch(PDO::FETCH_ASSOC);
            if($host) {
                  $v_keep = query("SELECT * FROM `{$engine->config['prefix']}xen_template` WHERE `server` = ?;", [$host['id']])->fetchAll(PDO::FETCH_ASSOC);
                  $v_delete = [];
                  $v_update = [];
                  $v_new = [];
                  // Check same data in database
                  foreach($v_keep as $v1) {
                        $have = false;
                        foreach($vs as $v2) {
                              if($v1['opaqueRef'] == $v2['opaqueRef'] && $v1['uuid'] == $v2['uuid']) {
                                    $have = true;
                              }
                        }
                        if($have == false) {
                              $v_delete[] = $v1;
                        }
                  }
                  // Check same data in new data
                  foreach($vs as $v1) {
                        $have = false;
                        foreach($v_keep as $v2) {
                              if($v1['opaqueRef'] == $v2['opaqueRef'] && $v1['uuid'] == $v2['uuid']) {
                                    $have = true;
                              }
                        }
                        if($have == false) {
                              $v_new[] = $v1;
                        }else{
                              $v_update[] = $v1;
                        }
                  }
                  // Remove
                  foreach($v_delete as $v) {
                        query("DELETE FROM `{$engine->config['prefix']}xen_template` WHERE `id` = ?;", [$v['id']]);
                  }
                  // Add
                  foreach($v_new as $v) {
                        query("INSERT INTO `{$engine->config['prefix']}xen_template` (`server`, `opaqueRef`, `uuid`, `name`, `ram`, `cpu`) VALUES (?,?,?,?,?,?);", [$host['id'], $v['opaqueRef'], $v['uuid'], $v['name'], $v['ram_minimum'], $v['vcpu_minimum']]);
                  }
                  // Update
                  foreach($v_update as $v) {
                        query("UPDATE `{$engine->config['prefix']}xen_template` SET `name` = ?, `ram` = ?, `cpu` = ? WHERE `opaqueRef` = ? AND `uuid` = ? AND `server` = ?;", [$v['name'], $v['ram_minimum'], $v['vcpu_minimum'], $v['opaqueRef'], $v['uuid'], $host['id']]);
                  }
            }
      }
}