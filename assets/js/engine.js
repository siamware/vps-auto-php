/*
Developer : Phumin Studio
Version : 0.0.1
*/
function PHUMIN_STUDIO_HOSTING(callback) {

    Vue.filter('date_format', function (timestamp) {
        var date = new Date(timestamp * 1000);
        var text = (date.getDate() >= 10) ? date.getDate() : ("0" + date.getDate());
        text += "/";
        text += ((date.getMonth() + 1) >= 10) ? (date.getMonth() + 1) : ("0" + (date.getMonth() + 1));
        text += "/";
        text += (date.getFullYear() >= 10) ? date.getFullYear() : ("0" + date.getFullYear());
        text += " เวลา ";
        text += (date.getHours() >= 10) ? date.getHours() : ("0" + date.getHours());
        text += ":";
        text += (date.getMinutes() >= 10) ? date.getMinutes() : ("0" + date.getMinutes());
        text += ":";
        text += (date.getSeconds() >= 10) ? date.getSeconds() : ("0" + date.getSeconds());
        text += " น.";
        return text;
    });

    var $engine = this;
    this.$facebook = false;
    this.vue = Vue;
    this.methods = {};
    this.computeds = {};
    this.setup = 0;
    this.max_step = 3;
    this.setup_watcher = null;
    this.payment_gateway = {
        "tmtopup": "บัตรทรูมันนี่",
        "tmpay": "บัตรทรูมันนี่",
        "truewallet": "True Wallet",
        "refer": "โค้ดเชิญชวน",
        "bank|kbank": "ธ.กสิกรไทย",
        "bank|scb": "ธ.ไทยพาณิชน์",
        "bank|ktb": "ธ.กรุงไทย",
    };

    this.store = createStore();

    toast = swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });

    // Ajax controller
    var networkCtlr = function () {

        this.post = function (controller, action, params, cb, er) {
            // Check parameter
            if (typeof params === "function") {
                cb = params;
                er = cb;
                params = {};
            }

            $engine.vue.http.post('api.php?controller=' + controller + '&action=' + action, {
                'controller': controller,
                'action': action,
                'params': params,
            }).then(function (res) {
                //success
                //console.log(res.body);
                /*if (!res.body.success) {
                    if (res.body.error.code == 403) {
                        $engine.store.commit('user_logout');
                        $engine.router.replace('/login');
                    }
                } else {
                    typeof cb === "function" ? cb(res.body.res) : 0;
                }*/
                res.body.event.forEach(function (e) {
                    if(e == "logout") {
                        $engine.store.commit('user_logout');
                        $engine.router.replace('/login');
                    }
                });
                typeof cb === "function" ? cb(res.body.res) : 0;
            }, function (res) {
                //error
                //console.log(res.body);
                typeof er === "function" ? er(res) : 0;
            });
        };

        return this;
    };
    this.network = new networkCtlr();

    // Template controller
    var templateCtlr = function () {
        var $self = this;
        // Data
        this.components = {};

        // Function
        this.get = function (home, cb) {

            $engine.network.post('template', 'load', {
                'home': home === true ? '✓' : '✕',
            }, function (res) {
                // Save template in x-template
                document.getElementById('template-container').innerHTML = "";
                Object.keys(res.template).forEach(function (key) {
                    var a = document.createElement('script');
                    a.setAttribute('type', 'text/x-template');
                    a.setAttribute('id', key);
                    a.innerHTML = "\n" + res.template[key] + "\n";
                    document.getElementById('template-container').append(a);
                });

                // Build component
                $self.components = {};
                Object.keys(res.component).forEach(function (key) {
                    $self.components[res.component[key].name] = {
                        template: res.component[key].data,
                        components: $self.components,
                        data: function () {
                            return {};
                        },
                        method: {},
                        computed: {},
                    };
                });
                Vue.component('error-404', {
                    template: res.component['error-404'].data,
                    methods: {
                        back: function () {
                            window.history.back();
                        },
                    }
                });

                // Preset components
                Vue.component('countdown', {
                    props: {
                        finish: [String, Number],
                        prefix: {
                            type: String,
                            default: "",
                        }
                    },
                    template: "<span>{{ prefix }} {{ days }} {{ hours }}:{{ minutes }}:{{ seconds }} ชั่วโมง</span>",
                    data: function () {
                        return {
                            now: null,
                            date: null,
                            timer: null,
                            diffSec: null,
                            diffMin: null,
                            diffHour: null,
                            diffDay: null,
                            runTimer: 'true',
                            infinite: 'false',
                            loaded: 'false',
                        };
                    },
                    watch: {
                        finish(value) {
                            this.date = Math.trunc(value);
                        },
                    },
                    mounted: function () {
                        var $self = this;
                        this.date = Math.trunc(this.finish);
                        this.now = Math.trunc(Date.now() / 1000);
                        this.timer = setInterval(function () {
                            $self.runTimer = 'true';
                            $self.now = Math.trunc(Date.now() / 1000);
                            $self.loaded = 'true';
                        }, 500);
                    },
                    methods: {
                        stopTimer: function () {
                            clearInterval(this.timer);
                            this.runTimer = 'false';
                        },
                        twoDigits: function (value) {
                            if (value.toString().length <= 1) {
                                return '0' + value.toString();
                            }
                            return value.toString();
                        }
                    },
                    computed: {
                        seconds: function () {
                            this.diffSec = Math.trunc(this.date - this.now) % 60;
                            if (this.diffSec < 0) {
                                this.diffSec = 0;
                                this.stopTimer();
                            }
                            return this.twoDigits(this.diffSec);
                        },
                        minutes: function () {
                            this.diffMin = Math.trunc((this.date - this.now) / 60) % 60;
                            if (this.diffMin < 0) {
                                this.diffMin = 0;
                            }
                            return this.twoDigits(this.diffMin);
                        },
                        hours: function () {
                            this.diffHour = Math.trunc((this.date - this.now) / 60 / 60) % 24;
                            if (this.diffHour < 0) {
                                this.diffHour = 0;
                            }
                            return this.twoDigits(this.diffHour);
                        },
                        days: function () {
                            this.diffDay = Math.trunc((this.date - this.now) / 60 / 60 / 24);
                            if (this.diffDay <= 0)
                                return '';
                            else
                                return this.twoDigits(this.diffDay) + ' วัน';
                        }
                    }
                });

                Vue.component('countup', {
                    props: {
                        start: [String, Number],
                        prefix: {
                            type: String,
                            default: "",
                        }
                    },
                    template: "<span>{{ prefix }} {{ days }} {{ hours }}:{{ minutes }}:{{ seconds }} ชั่วโมง</span>",
                    data: function () {
                        return {
                            now: null,
                            date: null,
                            timer: null,
                            diffSec: null,
                            diffMin: null,
                            diffHour: null,
                            diffDay: null,
                            runTimer: 'true',
                            infinite: 'false',
                            loaded: 'false',
                        };
                    },
                    watch: {
                        start(value) {
                            this.date = Math.trunc(value);
                        },
                    },
                    mounted: function () {
                        var $self = this;
                        this.date = Math.trunc(this.start);
                        this.now = Math.trunc(Date.now() / 1000);
                        this.timer = setInterval(function () {
                            $self.runTimer = 'true';
                            $self.now = Math.trunc(Date.now() / 1000);
                            $self.loaded = 'true';
                        }, 500);
                    },
                    methods: {
                        stopTimer: function () {
                            clearInterval(this.timer);
                            this.runTimer = 'false';
                        },
                        twoDigits: function (value) {
                            if (value.toString().length <= 1) {
                                return '0' + value.toString();
                            }
                            return value.toString();
                        }
                    },
                    computed: {
                        seconds: function () {
                            this.diffSec = Math.trunc(this.now - this.date) % 60;
                            if (this.diffSec < 0) {
                                this.diffSec = 0;
                                this.stopTimer();
                            }
                            return this.twoDigits(this.diffSec);
                        },
                        minutes: function () {
                            this.diffMin = Math.trunc((this.now - this.date) / 60) % 60;
                            if (this.diffMin < 0) {
                                this.diffMin = 0;
                            }
                            return this.twoDigits(this.diffMin);
                        },
                        hours: function () {
                            this.diffHour = Math.trunc((this.now - this.date) / 60 / 60) % 24;
                            if (this.diffHour < 0) {
                                this.diffHour = 0;
                            }
                            return this.twoDigits(this.diffHour);
                        },
                        days: function () {
                            this.diffDay = Math.trunc((this.now - this.date) / 60 / 60 / 24);
                            if (this.diffDay <= 0)
                                return '';
                            else
                                return this.twoDigits(this.diffDay) + ' วัน';
                        }
                    }
                });

                // Setup finish
                $engine.setup++;
                typeof cb === "function" ? cb() : 0;
            });
        };

        return this;
    };
    this.template = new templateCtlr();

    var routerCtrl = function () {
        return [{
                name: 'vps-list',
                path: '/',
                alias: '/vps',
                props: true,
                component: {
                    template: $engine.template.components['vps-home'].template,
                    data: function () {
                        return {
                            operation: {},
                        }
                    },
                    props: ['id'],
                    created: function () {
                        this.$store.commit('get_vps');
                    },
                    mounted: function () {
                        var $self = this;
                        $('.title').tooltip();
                        this.$store.watch(function () {
                            return $self.$store.getters.vps;
                        }, function () {
                            $('.title').tooltip('dispose');
                            $('.title').tooltip('update');
                        });
                    },
                    methods: {
                        detail: function (vps) {
                            $('.title').tooltip('dispose');
                            this.$router.push('/vps/' + vps.id);
                        },
                        start: function (vps) {
                            $('.title').tooltip('hide');
                            this.$set(this.operation, vps.id, true);
                            var $self = this;
                            $engine.network.post('vps', 'start', {
                                vps: vps.id,
                            }, function (res) {
                                $self.$set($self.operation, vps.id, false);
                                if (res === false) {

                                } else {
                                    $self.$store.commit('vps', res);
                                }
                            });
                        },
                        stop: function (vps) {
                            $('.title').tooltip('hide');
                            this.$set(this.operation, vps.id, true);
                            var $self = this;
                            $engine.network.post('vps', 'stop', {
                                vps: vps.id,
                            }, function (res) {
                                $self.$set($self.operation, vps.id, false);
                                if (res === false) {

                                } else {
                                    $self.$store.commit('vps', res);
                                }
                            });
                        },
                        forcestop: function (vps) {
                            $('.title').tooltip('hide');
                        },
                    },
                },
            },
            {
                name: 'vps-add',
                path: '/vps/add',
                component: {
                    template: $engine.template.components['vps-add'].template,
                    data: function () {
                        return {
                            step: 0,
                            package_select: false, // package data
                            host_select: false,
                            template_select: false,
                            promo_code: "",
                            promotion: false,
                        }
                    },
                    components: {
                        'dot-loading': {
                            template: '<h3>{{ text }}{{ dot }}</h3>',
                            props: ['text'],
                            data: function () {
                                return {
                                    timer: null,
                                    dot: "",
                                };
                            },
                            mounted: function () {
                                var $self = this;
                                this.timer = setInterval(function () {
                                    $self.dot += ".";
                                    if ($self.dot.length == 5)
                                        $self.dot = "";
                                }, 300);
                            },
                            beforeDestroy: function () {
                                clearInterval(this.timer);
                            }
                        },
                    },
                    methods: {
                        select_package: function (p) {
                            var $self = this;
                            this.$set(this, 'package_select', p);
                            this.$store.getters.host.forEach(function (e) {
                                if (e.ram_free > p.ram * 1024 * 1024 * 1024) {
                                    $self.$set($self, 'host_select', e);
                                }
                            });
                            this.step = 1;
                            this.$store.commit('get_template', {
                                host: this.host_select.id
                            });
                        },
                        select_template: function (template) {
                            this.$set(this, 'template_select', template);
                            this.step = 2;
                        },
                        undo: function (p) {
                            if (this.step == 1) {
                                this.step = 0;
                                this.package_select = false;
                                this.host_select = false;
                                this.$store.commit('clear_template');
                            } else if (this.step == 2) {
                                this.step = 1;
                                this.template_select = false;
                            }
                        },
                        available_package: function (p) {
                            var available = false;
                            if(p.soon != -1) {
                                return false;
                            } else {
                                if (this.$store.getters.host) {
                                    this.$store.getters.host.forEach(function (host) {
                                        console.log(host.ram_free, p.ram * 1024 * 1024 * 1024, host.ram_free > p.ram * 1024 * 1024 * 1024)
                                        if (host.ram_free >= p.ram * 1024 * 1024 * 1024) {
                                            available = true;
                                        }
                                    });
                                    //console.log(p.soon, (new Date()).getTime() / 1000, new Date(p.soon * 1000))
                                }
                                return available;
                            }
                        },
                        create: function () {
                            this.step = 3;
                            var $self = this;
                            $engine.network.post('vps', 'create', {
                                package: this.package_select.id,
                                type: this.host_select.type,
                                host: this.host_select.id,
                                template: this.template_select.id,
                                code: this.promo_code,
                            }, function (res) {
                                if (res === false) {
                                    $self.step = 2;
                                } else {
                                    $self.$store.commit('get_user');
                                    $self.$store.commit('vps', res.vm);

                                    $self.$store.commit('get_vps');
                                    $self.$router.push({
                                        path: '/vps/' + res.last
                                    });

                                    swal({
                                        title: 'เปลี่ยนรหัสผ่านทันทีที่ได้รับเครื่อง',
                                        text: 'เพื่อป้องกันการถูกผู้ไม่หวังแฮก',
                                        type: 'warning',
                                        confirmButtonText: 'รับทราบ',
                                    })
                                }
                            });
                        },
                        check_promo: function () {
                            var $self = this;
                            if (this.promo_code == "") {
                                swal('กรุณากรอกโค้ดส่วนลด', '', 'error');
                            } else {
                                $engine.network.post('promotion', 'check', {
                                    type: "discount",
                                    code: this.promo_code,
                                    option: {
                                        price: this.package_select.price,
                                    },
                                }, function (res) {
                                    if (res === false) {
                                        swal('ไม่พบโค้ดในระบบ', '', 'error');
                                        $self.$set($self, 'promotion', false);
                                    } else if (res == "own_refer") {
                                        swal('ไม่สามารถใช้โค้ดเชิญชวนของตัวเองได้', '', 'error');
                                        $self.$set($self, 'promotion', false);
                                    } else {
                                        swal('ตรวจพบโค้ดส่วนลด', '', 'success');
                                        $self.$set($self, 'promotion', res);
                                    }
                                });
                            }
                        },
                    },
                    computed: {
                        price: function () {
                            var r = "0.00";
                            if (this.package_select == false) {
                                return r;
                            } else {
                                if (this.promotion == false) {
                                    r = this.package_select.price;
                                } else {
                                    if (this.promotion.type == "percent") {
                                        r = this.package_select.price * (1 - (this.promotion.amount / 100));
                                    } else if (this.promotion.type == "amount") {
                                        r = this.package_select.price - this.promotion.amount;
                                    } else {
                                        r = this.package_select.price;
                                    }
                                }
                                return parseFloat(r).toFixed(2);
                            }
                        }
                    },
                    created: function () {
                        this.$store.commit('get_package');
                        this.$store.commit('get_host');
                    }
                },
            },
            {
                name: 'vps-detail',
                path: '/vps/:id',
                props: true,
                component: {
                    template: $engine.template.components['vps-detail'].template,
                    data: function () {
                        return {
                            expanding: false,
                        }
                    },
                    props: ['id'],
                    created: function () {},
                    mounted: function () {
                        $('.title').tooltip();
                    },
                    computed: {
                        vps: function () {
                            var $self = this;
                            var vps = false;
                            this.$store.getters.vps.forEach(function (vm) {
                                if (vm.id == $self.id) {
                                    vps = vm;
                                }
                            });
                            return vps;
                        },
                    },
                    methods: {
                        toggle_auto_expand: function () {
                            var $self = this;
                            $engine.network.post('vps', 'toggle_auto_expand', {
                                vps: $self.vps.id,
                            }, function (res) {
                                if (res == false) {
                                    toast('พบข้อผิดพลาด', '', 'error');
                                } else {
                                    if ($self.vps.auto_expand == 1) {
                                        $self.vps.auto_expand = 0;
                                    } else {
                                        $self.vps.auto_expand = 1;
                                    }
                                    toast('บันทึกสำเร็จ', '', 'success');
                                }
                            });
                        },
                        expand: function () {
                            this.expanding = true;
                            var $self = this;
                            swal({
                                title: 'คุณต้องการต่ออายุใช่หรือไม่?',
                                type: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'ต่ออายุเลย!',
                                cancelButtonColor: '#d33',
                            }).then(function (result) {
                                if (result.value) {
                                    swal({
                                        title: 'กำลังดำเนินการต่ออายุ...',
                                        onOpen: function () {
                                            swal.showLoading();
                                            $engine.network.post('vps', 'expand', {
                                                vps: $self.vps.id,
                                            }, function (res) {
                                                $self.expanding = false;
                                                if (res == false) {
                                                    swal({
                                                        type: 'error',
                                                        title: 'ต่ออายุไม่สำเร็จ',
                                                        text: 'หน้าต่างจะปิดใน 3 วินาที',
                                                        timer: 3000,
                                                    });
                                                } else {
                                                    swal({
                                                        type: 'success',
                                                        title: 'ต่ออายุสำเร็จ',
                                                        text: 'หน้าต่างจะปิดใน 3 วินาที',
                                                        timer: 3000,
                                                    });
                                                    $self.$store.commit('get_user');
                                                    $self.$store.commit('get_vps');
                                                }
                                            });
                                        },
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                    });
                                } else {
                                    $self.expanding = false;
                                }
                            })
                        },
                        start: function () {
                            var vps = this.vps;
                            $('.title').tooltip('hide');
                            this.$set(this.operation, vps.id, true);
                            var $self = this;
                            $engine.network.post('vps', 'start', {
                                vps: vps.id,
                            }, function (res) {
                                $self.$set($self.operation, vps.id, false);
                                if (res === false) {

                                } else {
                                    $self.$store.commit('vps', res);
                                }
                            });
                        },
                        stop: function () {
                            var vps = this.vps;
                            $('.title').tooltip('hide');
                            this.$set(this.operation, vps.id, true);
                            var $self = this;
                            $engine.network.post('vps', 'stop', {
                                vps: vps.id,
                            }, function (res) {
                                $self.$set($self.operation, vps.id, false);
                                if (res === false) {

                                } else {
                                    $self.$store.commit('vps', res);
                                }
                            });
                        },
                        console: function () {
                            var vps = this.vps;
                            var $self = this;

                            var newpopup = popup("console/", "Console", 1024, 806);

                            $engine.network.post('vps', 'console', {
                                id: vps.id,
                            }, function (uuid) {
                                newpopup.location.href = "console/?uuid=" + uuid;
                            });
                        },
                    }
                },
            },
            {
                name: 'setting',
                path: '/setting',
                component: {
                    template: $engine.template.components['setting'].template,
                    data: function () {
                        return {
                            form: {
                                profile: {
                                    name: this.$store.getters.user.name,
                                    company: this.$store.getters.user.company,
                                    address: this.$store.getters.user.address,
                                    phone: this.$store.getters.user.phone,
                                },
                                changepass: {
                                    old: '',
                                    new: '',
                                    confirm: '',
                                },
                            }
                        };
                    },
                    methods: {
                        copyRefer: function () {
                            var input = document.getElementById('refer_code');
                            input.select();
                            document.execCommand("copy");
                            toast('คัดลอกแล้ว', '', 'success');

                            if (window.getSelection) {
                                if (window.getSelection().empty) { // Chrome
                                    window.getSelection().empty();
                                } else if (window.getSelection().removeAllRanges) { // Firefox
                                    window.getSelection().removeAllRanges();
                                }
                            } else if (document.selection) { // IE?
                                document.selection.empty();
                            }
                        },
                        newRefer: function () {
                            var $self = this;
                            swal({
                                title: 'คุณต้องการรับรหัสชวนเพื่อนใหม่หรือไม่',
                                type: 'question',
                                confirmButtonText: 'ยืนยัน',
                                showCancelButton: true,
                                cancelButtonText: 'ยกเลิก',
                                cancelButtonColor: '#d33',
                            }).then(function (confirm) {
                                if (confirm) {
                                    swal({
                                        title: 'กำลัง',
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                        onOpen: function () {
                                            swal.showLoading();
                                        }
                                    });
                                    $engine.network.post('user', 'newRefer', {}, function (res) {
                                        $self.$store.commit('user', res.data);
                                        swal({
                                            title: 'คุณได้รับโค้ดเชิญชวนใหม่แล้ว',
                                            text: 'โค้ดเชิญชวนมีอายุ 1 ปีหลังจากวันที่ขอรับ',
                                            type: 'success',
                                            timer: 1500
                                        })
                                    });
                                }
                            })
                        },
                        openEdit: function () {
                            $(".modal-setting").modal();
                        },
                        saveProfile: function () {
                            var $self = this;
                            $engine.network.post('user', 'edit', {
                                id: this.$store.getters.user.id,
                                name: this.form.profile.name,
                                company: this.form.profile.company,
                                address: this.form.profile.address,
                                phone: this.form.profile.phone,
                            }, function (res) {
                                $self.$store.commit('user', res.data);
                                $(".modal-setting").modal('hide');
                            });
                        },
                        changePassword: function () {
                            var $self = this;
                            $engine.network.post('user', 'changePass', {
                                old: this.form.changepass.old,
                                new: this.form.changepass.new,
                                confirm: this.form.changepass.confirm,
                            }, function (res) {
                                if (res.success) {
                                    swal('เปลี่ยนรหัสผ่านสำเร็จ', '', 'success');
                                    $self.form.changepass.new = '';
                                    $self.form.changepass.confirm = '';
                                } else {
                                    if (res.error == "same") {
                                        swal('รหัสผ่านเหมือนเดิม', 'ตรวจสอบให้แน่ใจว่าคุณได้กรอกรหัสผ่านใหม่แล้ว', 'warning');
                                        $self.form.changepass.new = '';
                                        $self.form.changepass.confirm = '';
                                    } else if (res.error == "confirm") {
                                        swal('กรอกรหัสผ่านใหม่ไม่ถูกต้อง', 'ตรวจสอบให้แน่ใจว่ากรอกรหัสผ่านใหม่เหมือนกันทั้ง 2 ช่อง', 'warning');
                                        $self.form.changepass.confirm = '';
                                    } else if (res.error == "invalid") {
                                        swal('รหัสผ่านไม่ถูกต้อง', '', 'warning');
                                    } else {
                                        swal('พบข้อผิดพลาด', 'กรุณาลองใหม่ภายหลัง', 'error');
                                    }
                                }

                                $self.form.changepass.old = '';
                            });
                        },
                        confirmEmail: function () {
                            var $self = this;
                            swal({
                                title: 'กำลังส่งอีเมล์',
                                onOpen: function () {
                                    swal.showLoading();
                                    $engine.network.post('user', 'confirmEmail', {}, function (res) {
                                        swal({
                                            type: 'success',
                                            title: 'อีเมล์ถูกส่งแล้ว',
                                            text: 'หากไม่พบอีเมล์ ให้ลองดูในอีเมล์ขยะ',
                                        });
                                    });
                                },
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                            });
                        },
                        confirmPhone: function () {
                            var $self = this;
                            swal({
                                title: 'ตรวจเบอร์โทรศัพท์',
                                text: 'ระบบจะส่งรหัส OTP ไปให้เพื่อยืนยัน',
                                input: 'text',
                                inputValue: this.$store.getters.user.phone,
                                inputAttributes: {
                                    autocapitalize: 'off'
                                },
                                confirmButtonText: 'ส่งรหัส OTP',
                                showLoaderOnConfirm: true,
                                showCancelButton: function () {
                                    return !swal.isLoading()
                                },
                                allowOutsideClick: function () {
                                    return !swal.isLoading()
                                },
                                allowEscapeKey: function () {
                                    return !swal.isLoading()
                                },
                                preConfirm: function (phone) {
                                    return new Promise(function (resolve) {
                                        $engine.network.post('user', 'confirmPhone', {
                                            phone: phone,
                                        }, function (res) {
                                            swal({
                                                title: 'กรอก OTP',
                                                text: 'รหัส OTP มีอายุการใช้งาน 15 นาที',
                                                input: 'text',
                                                inputAttributes: {
                                                    maxlength: 6,
                                                },
                                                confirmButtonText: 'ยืนยัน',
                                                showLoaderOnConfirm: true,
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                preConfirm: function (otp) {
                                                    if (otp == "") {
                                                        swal.showValidationError('กรุณากรอก OTP');
                                                        swal.hideLoading();
                                                    } else {
                                                        return new Promise(function (resolve) {
                                                            $engine.network.post('user', 'confirmPhone', {
                                                                phone: phone,
                                                                otp: otp,
                                                            }, function (res) {
                                                                if (res) {
                                                                    $self.$store.commit('get_user');
                                                                    swal({
                                                                        type: 'success',
                                                                        title: 'ยืนยันเบอร์โทรศัพท์สำเร็จ',
                                                                        text: 'หน้าต่างนี้จะปิดใน 3 วินาที',
                                                                        timer: 3000,
                                                                    });
                                                                } else {
                                                                    swal.showValidationError('รหัส OTP ไม่ถูกต้อง');
                                                                    swal.hideLoading();
                                                                }
                                                            });
                                                        });
                                                    }
                                                }
                                            });
                                        });
                                    });
                                },
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                            });
                        },
                    },
                    beforeDestroy: function () {
                        $(".modal-setting").modal('dispose');
                        $("body").removeAttr('class style');
                        $(".modal-backdrop").remove();
                    },
                },
            },
            {
                name: 'document-detail',
                path: '/document/:id',
                props: true,
                component: {
                    props: ['id'],
                    template: $engine.template.components['document'].template,
                    data: function () {
                        return {
                            menus: {
                                1: 'วิธีตั้งรหัสผ่าน Window Server',
                                2: 'โปรแกรมที่มีอยู่ในเครื่อง',
                                3: 'วิธีเพิ่มพื้นที่เครื่องให้ได้เต็มจำนวน',
                            },
                        };
                    },
                    methods: {},
                    created: function () {}
                },
            },
            {
                name: 'document',
                path: '/document',
                component: {
                    template: $engine.template.components['document'].template,
                    data: function () {
                        return {
                            id: false,
                            menus: {
                                1: 'วิธีตั้งรหัสผ่าน Window Server',
                                2: 'โปรแกรมที่มีอยู่ในเครื่อง',
                                3: 'วิธีเพิ่มพื้นที่เครื่องให้ได้เต็มจำนวน',
                            },
                        };
                    },
                    methods: {},
                    created: function () {}
                },
            },
            {
                name: 'billing',
                path: '/billing',
                component: {
                    template: $engine.template.components['billing'].template,
                    data: function () {
                        return {
                            truemoney: {
                                number: '',
                                agree: true,
                                checking: false,
                                try: 0,
                            },
                            truewallet: {
                                transaction: '',
                                agree: true,
                                checking: false,
                                try: 0,
                            },
                            bank: {
                                bank: 'kbank',
                                amount: '0',
                                day: (new Date()).getDate(),
                                month: (new Date()).getMonth() + 1,
                                year: (new Date()).getFullYear(),
                                hour: (new Date()).getHours(),
                                minute: (new Date()).getMinutes(),
                                agree: true,
                                checking: false,
                                try: 0,
                            },
                            process: {
                                truemoney: null,
                                truewallet: null,
                                bank: null,
                            },
                            modal: {
                                statue: 'checking',
                                title: '',
                                text: '',
                            },
                            history_page_amount: 0,
                            history_page_current: 0,
                            history: {},
                        }
                    },
                    computed: {
                        cost_per_month: function () {
                            var cost = 0;
                            this.$store.getters.vps.forEach(function (vps) {
                                cost += parseFloat(vps.package.price) / vps.package.time * 30;
                            });
                            return cost;
                        },
                        history_on_page: function () {
                            if (typeof this.history[this.history_page_current] == "undefined") {
                                return [];
                            } else {
                                return this.history[this.history_page_current];
                            }
                        }
                    },
                    methods: {
                        bank_submit: function () {
                            $(".modal-check").modal({
                                keyboard: false,
                                backdrop: 'static',
                            });
                            var $self = this;
                            this.modal.title = 'กำลังตรวจสอบ...';
                            this.modal.text = '';
                            this.modal.statue = 'checking';

                            if (this.bank.agree) {
                                $engine.network.post('billing', 'topup', {
                                    gateway: 'bank',
                                    bank: this.bank.bank,
                                    amount: this.bank.amount,
                                    day: this.bank.day,
                                    month: this.bank.month,
                                    year: this.bank.year,
                                    hour: this.bank.hour,
                                    minute: this.bank.minute,
                                }, function (res) {
                                    if (res.success) {
                                        $self.$store.commit('user', res.data.user);
                                        $self.modal.title = 'ทำรายการสำเร็จ';
                                        $self.modal.text = 'คุณได้เติมเงินจำนวน ' + res.data.amount + ' บาท';
                                        $self.modal.statue = 'success';
                                    } else {
                                        if (res.error == "bank") {
                                            $self.modal.title = 'ไม่สามารถดำเนินการได้';
                                            $self.modal.text = 'กรุณาตรวจสอบอีกครั้งภายหลัง';
                                            $self.modal.statue = 'failed';

                                            $self.process.bank = setTimeout(function () {
                                                $(".modal-check").modal('hide');
                                            }, 5000);
                                        } else {
                                            $self.modal.title = 'ไม่พบรายการที่แจ้ง';
                                            $self.modal.text = 'ตรวจสอบอีกครั้งใน 10 วินาที';
                                            $self.modal.statue = 'failed';
                                            $self.bank.try++;

                                            if ($self.truewallet.try < 6) {
                                                setTimeout(function () {
                                                    $self.modal.title = 'กำลังตรวจสอบ...';
                                                    $self.modal.text = '';
                                                    $self.modal.statue = 'checking';
                                                }, 5000);
                                                $self.process.bank = setTimeout(function () {
                                                    $self.bank_check();
                                                }, 10000);
                                            }
                                        }
                                    }
                                });
                            } else {

                            }
                        },
                        bank_check: function () {
                            var $self = this;
                            this.modal.title = 'กำลังตรวจสอบ...';
                            this.modal.text = '';
                            this.modal.statue = 'checking';

                            $engine.network.post('billing', 'topup', {
                                gateway: 'bank',
                                bank: this.bank.bank,
                                amount: this.bank.amount,
                                day: this.bank.day,
                                month: this.bank.month,
                                year: this.bank.year,
                                hour: this.bank.hour,
                                minute: this.bank.minute,
                            }, function (res) {
                                if (res.success) {
                                    $self.$store.commit('user', res.data.user);
                                    $self.modal.title = 'ทำรายการสำเร็จ';
                                    $self.modal.text = 'คุณได้เติมเงินจำนวน ' + res.amount + ' บาท';
                                    $self.modal.statue = 'success';
                                } else {
                                    if (res.error == "bank") {
                                        $self.modal.title = 'ไม่สามารถดำเนินการได้';
                                        $self.modal.text = 'กรุณาตรวจสอบอีกครั้งภายหลัง';
                                        $self.modal.statue = 'failed';

                                        $self.process.bank = setTimeout(function () {
                                            $(".modal-check").modal('hide');
                                        }, 5000);
                                    } else {
                                        if ($self.bank.try < 6) {
                                            $self.modal.title = 'ไม่พบรายการที่แจ้ง';
                                            $self.modal.text = 'ตรวจสอบอีกครั้งใน 10 วินาที (' + $self.bank.try+'/6)';
                                            $self.modal.statue = 'failed';
                                            $self.bank.try++;

                                            setTimeout(function () {
                                                $self.modal.title = 'กำลังตรวจสอบ...';
                                                $self.modal.text = '';
                                                $self.modal.statue = 'checking';
                                            }, 7000);
                                            $self.process.bank = setTimeout(function () {
                                                $self.tw_check();
                                            }, 10000);
                                        } else {
                                            $self.modal.title = 'ไม่พบรายการที่แจ้ง';
                                            $self.modal.text = 'กรุณาตรวจสอบอีกครั้งภายหลัง';
                                            $self.modal.statue = 'failed';

                                            $self.process.bank = setTimeout(function () {
                                                $(".modal-check").modal('hide');
                                            }, 5000);
                                        }
                                    }
                                }
                            });
                        },
                        bank_cancel: function () {
                            clearTimeout(this.process.bank);
                            $(".modal-check").modal('hide');
                        },
                        tm_submit: function () {
                            $(".modal-check").modal({
                                keyboard: false,
                                backdrop: 'static',
                            });
                            var $self = this;
                            this.modal.title = 'กำลังตรวจสอบ...';
                            this.modal.text = '';
                            this.modal.statue = 'checking';

                            if (this.truemoney.agree) {
                                $engine.network.post('billing', 'topup', {
                                    gateway: 'truemoney',
                                    number: this.truemoney.number,
                                }, function (res) {
                                    $self.modal.title = 'กำลังตรวจสอบ...';
                                    $self.modal.text = '';
                                    $self.modal.statue = 'checking';

                                    $self.process.truemoney = setTimeout(function () {
                                        $self.tm_check(res.transaction_id);
                                    }, 3000);
                                });
                            } else {

                            }
                        },
                        tm_check: function (transaction_id) {
                            var $self = this;
                            this.modal.title = 'กำลังตรวจสอบ...';
                            this.modal.text = '';
                            this.modal.statue = 'checking';

                            $engine.network.post('billing', 'check', {
                                gateway: 'truemoney',
                                transaction: transaction_id,
                            }, function (res) {
                                if (res.success) {
                                    $self.$store.commit('user', res.data.user);
                                    $self.modal.title = 'ทำรายการสำเร็จ';
                                    $self.modal.text = 'คุณได้เติมเงินจำนวน ' + res.amount + ' บาท';
                                    $self.modal.statue = 'success';
                                } else {
                                    if (typeof res.error != "undefined") {
                                        if (res.error == "used") {
                                            $self.modal.title = 'บัตรถูกใช้งานแล้ว';
                                            $self.modal.text = 'กรุณาลองใช้บัตรอื่น';
                                            $self.modal.statue = 'failed';

                                            $self.process.truemoney = setTimeout(function () {
                                                $(".modal-check").modal('hide');
                                            }, 5000);
                                        } else {
                                            $self.modal.title = 'ไม่สามารถทำรายการได้';
                                            $self.modal.text = 'กรุณาทำรายการอีกครั้งภายหลัง';
                                            $self.modal.statue = 'failed';

                                            $self.process.truemoney = setTimeout(function () {
                                                $(".modal-check").modal('hide');
                                            }, 5000);
                                        }
                                    } else {
                                        if ($self.truemoney.try < 6) {
                                            $self.modal.title = 'ไม่พบรายการที่แจ้ง';
                                            $self.modal.text = 'ตรวจสอบอีกครั้งใน 10 วินาที (' + $self.truemoney.try+'/6)';
                                            $self.modal.statue = 'failed';
                                            $self.truemoney.try++;

                                            setTimeout(function () {
                                                $self.modal.title = 'กำลังตรวจสอบ...';
                                                $self.modal.text = '';
                                                $self.modal.statue = 'checking';
                                            }, 4000);
                                            $self.process.truemoney = setTimeout(function () {
                                                $self.tm_check(transaction_id);
                                            }, 6000);
                                        } else {
                                            $self.modal.title = 'ไม่พบรายการที่แจ้ง';
                                            $self.modal.text = 'กรุณาตรวจสอบอีกครั้งภายหลัง';
                                            $self.modal.statue = 'failed';

                                            $self.process.truemoney = setTimeout(function () {
                                                $(".modal-check").modal('hide');
                                            }, 5000);
                                        }
                                    }
                                }
                            });
                        },
                        tm_cancel: function () {
                            clearTimeout(this.process.truemoney);
                            $(".modal-check").modal('hide');
                        },
                        tw_submit: function () {
                            $(".modal-check").modal({
                                keyboard: false,
                                backdrop: 'static',
                            });
                            var $self = this;
                            this.modal.title = 'กำลังตรวจสอบ...';
                            this.modal.text = '';
                            this.modal.statue = 'checking';

                            $engine.network.post('billing', 'topup', {
                                gateway: 'truewallet',
                                transaction: this.truewallet.transaction,
                            }, function (res) {
                                if (res.success) {
                                    $self.$store.commit('user', res.data.user);
                                    $self.modal.title = 'ทำรายการสำเร็จ';
                                    $self.modal.text = 'คุณได้เติมเงินจำนวน ' + res.data.amount + ' บาท';
                                    $self.modal.statue = 'success';
                                } else {
                                    $self.modal.title = 'ไม่พบรายการที่แจ้ง';
                                    $self.modal.text = 'ตรวจสอบอีกครั้งใน 10 วินาที';
                                    $self.modal.statue = 'failed';
                                    $self.truewallet.try++;

                                    if ($self.truewallet.try < 6) {
                                        setTimeout(function () {
                                            $self.modal.title = 'กำลังตรวจสอบ...';
                                            $self.modal.text = '';
                                            $self.modal.statue = 'checking';
                                        }, 7000);
                                        $self.process.truewallet = setTimeout(function () {
                                            $self.tw_check();
                                        }, 10000);
                                    }
                                }
                            });
                        },
                        tw_check: function () {
                            var $self = this;
                            this.modal.title = 'กำลังตรวจสอบ...';
                            this.modal.text = '';
                            this.modal.statue = 'checking';

                            $engine.network.post('billing', 'topup', {
                                gateway: 'truewallet',
                                transaction: this.truewallet.transaction,
                            }, function (res) {
                                if (res.success) {
                                    $self.$store.commit('user', res.data.user);
                                    $self.modal.title = 'ทำรายการสำเร็จ';
                                    $self.modal.text = 'คุณได้เติมเงินจำนวน ' + res.amount + ' บาท';
                                    $self.modal.statue = 'success';
                                } else {
                                    if ($self.truewallet.try < 6) {
                                        $self.modal.title = 'ไม่พบรายการที่แจ้ง';
                                        $self.modal.text = 'ตรวจสอบอีกครั้งใน 10 วินาที (' + $self.truewallet.try+'/6)';
                                        $self.modal.statue = 'failed';
                                        $self.truewallet.try++;

                                        setTimeout(function () {
                                            $self.modal.title = 'กำลังตรวจสอบ...';
                                            $self.modal.text = '';
                                            $self.modal.statue = 'checking';
                                        }, 7000);
                                        $self.process.truewallet = setTimeout(function () {
                                            $self.tw_check();
                                        }, 10000);
                                    } else {
                                        $self.modal.title = 'ไม่พบรายการที่แจ้ง';
                                        $self.modal.text = 'กรุณาตรวจสอบอีกครั้งภายหลัง';
                                        $self.modal.statue = 'failed';

                                        $self.process.truewallet = setTimeout(function () {
                                            $(".modal-check").modal('hide');
                                        }, 5000);
                                    }
                                }
                            });
                        },
                        tw_cancel: function () {
                            clearTimeout(this.process.truewallet);
                            $(".modal-check").modal('hide');
                        },
                        history_page: function (page) {
                            var $self = this;
                            if (typeof page === "undefined") {
                                page = 1;
                            } else if (page === "-1") {
                                page = this.history_page_current - 1;
                            } else if (page === "+1") {
                                page = this.history_page_current + 1;
                            }
                            $engine.network.post('billing', 'history', {
                                page: page,
                                per_page: 7,
                            }, function (res) {
                                $self.history_page_amount = res.page_amount;
                                $self.history_page_current = res.page_current;
                                Vue.set($self.history, res.page_current, res.page_data);
                            });
                        },
                        history_gateway: function (gateway) {
                            if (typeof $engine.payment_gateway[gateway] != "undefined") {
                                return $engine.payment_gateway[gateway];
                            } else {
                                return "ไม่รู้จัก";
                            }
                        }
                    },
                    mounted: function () {
                        this.history_page();
                        /*OmiseCard.configure({
                            publicKey: 'pkey_test_5bmh37mqn0ghowhc72u',
                            image: 'https://cdn.omise.co/assets/dashboard/images/omise-logo.png',
                            frameLabel: 'Phumin Studio',
                        });
                        // Configuring your own custom button
                        OmiseCard.configureButton('#checkout-button-1', {
                            frameDescription: 'บริการ Web Hosting และ VPS ราคาถูก',
                            buttonLabel: 'ชำระเงิน 3,000 บาท',
                            submitLabel: 'PAY NOW',
                            amount: 450000,
                            locale: 'th'
                        });
                        // Configuring your own custom button
                        OmiseCard.configureButton('.checkout-button-2', {
                            frameLabel: 'ชื่อผู้ประกอบการค้า',
                            frameDescription: 'รายละเอียดของผู้ประกอบการค้า',
                            buttonLabel: 'จ่าย 1,250',
                            submitLabel: 'จ่ายเลยตอนนี้',
                            amount: 125000,
                            currency: 'thb',
                            locale: 'th'
                        });
                        // Configuring your own custom button
                        OmiseCard.configureButton('#checkout-button-3', {
                            frameLabel: 'ชื่อผู้ประกอบการค้า',
                            frameDescription: 'รายละเอียดของผู้ประกอบการค้า',
                            buttonLabel: 'จ่าย 1,250',
                            submitLabel: 'จ่ายเลยตอนนี้',
                            amount: 125000,
                            currency: 'thb',
                            locale: 'th',
                        });
                        // Then, attach all of the config and initiate it by 'OmiseCard.attach();' method
                        OmiseCard.attach();
                        */
                    },
                },
            },
            {
                name: 'admin-dashboard',
                path: '/admin',
                component: {
                    template: $engine.template.components['admin-dashboard'].template,
                    data: function () {
                        return {
                            history: {},
                        };
                    },
                    methods: {},
                    mounted: function () {}
                },
            },
            {
                name: 'admin-accounting-payment',
                path: '/admin/accounting-payment',
                component: {
                    template: $engine.template.components['admin-accounting-payment'].template,
                    data: function () {
                        return {
                            history_page_amount: 0,
                            history_page_current: 0,
                            history: {},
                        };
                    },
                    components: {
                        'line-chart': {
                            extends: VueChartJs.Line,
                            mounted: function () {
                                var $self = this;
                                $engine.network.post('payment', 'summary', {
                                }, function (res) {
                                    $self.renderChart({
                                        labels: res.label,
                                        datasets: [{
                                            label: 'รายได้ต่อเดือน',
                                            backgroundColor: '#1b84e7',
                                            data: res.data,
                                            fill: false,
                                        }],
                                    }, {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                    })
                                });
                            }
                        },
                    },
                    methods: {
                        history_page: function (page) {
                            var $self = this;
                            if (typeof page === "undefined") {
                                page = 1;
                            } else if (page === "-1") {
                                page = this.history_page_current - 1;
                            } else if (page === "+1") {
                                page = this.history_page_current + 1;
                            }
                            $engine.network.post('billing', 'history', {
                                page: page,
                                per_page: 25,
                                all: true,
                            }, function (res) {
                                $self.history_page_amount = res.page_amount;
                                $self.history_page_current = res.page_current;
                                Vue.set($self.history, res.page_current, res.page_data);
                            });
                        },
                        history_gateway: function (gateway) {
                            if (typeof $engine.payment_gateway[gateway] != "undefined") {
                                return $engine.payment_gateway[gateway];
                            } else {
                                return "ไม่รู้จัก";
                            }
                        }
                    },
                    computed: {
                        history_on_page: function () {
                            if (typeof this.history[this.history_page_current] == "undefined") {
                                return [];
                            } else {
                                return this.history[this.history_page_current];
                            }
                        }
                    },
                    mounted: function () {
                        this.history_page();
                    }
                },
            },
            {
                name: 'admin-accounting-invoice',
                path: '/admin/accounting-invoice',
                component: {
                    template: $engine.template.components['admin-accounting-invoice'].template,
                    data: function () {
                        return {
                            history_page_amount: 0,
                            history_page_current: 0,
                            history: {},
                        };
                    },
                    methods: {
                        history_page: function (page) {
                            var $self = this;
                            if (typeof page === "undefined") {
                                page = 1;
                            } else if (page === "-1") {
                                page = this.history_page_current - 1;
                            } else if (page === "+1") {
                                page = this.history_page_current + 1;
                            }
                            $engine.network.post('billing', 'invoice', {
                                page: page,
                                per_page: 25,
                                all: true,
                            }, function (res) {
                                $self.history_page_amount = res.page_amount;
                                $self.history_page_current = res.page_current;
                                Vue.set($self.history, res.page_current, res.page_data);
                            });
                        },
                        history_gateway: function (gateway) {
                            if (typeof $engine.payment_gateway[gateway] != "undefined") {
                                return $engine.payment_gateway[gateway];
                            } else {
                                return "ไม่รู้จัก";
                            }
                        },
                        promotion_calculate: function (package, promotion) {
                            var discount = false;
                            if (promotion.type == "refer") {
                                discount = promotion.promotion.discount;
                            } else if (promotion.type == "discount") {
                                discount = promotion.promotion;
                            }

                            if (discount.type == "percent") {
                                return package.price * (1 - (discount.amount / 100));
                            } else if (discount.type == "amount") {
                                return package.price - discount.amount;
                            }
                        }
                    },
                    computed: {
                        history_on_page: function () {
                            if (typeof this.history[this.history_page_current] == "undefined") {
                                return [];
                            } else {
                                return this.history[this.history_page_current];
                            }
                        }
                    },
                    mounted: function () {
                        this.history_page();
                    }
                },
            },
            {
                name: 'admin-package-list',
                path: '/admin/package-list',
                component: {
                    template: $engine.template.components['admin-package-list'].template,
                    data: function () {
                        return {
                            loading: false,
                        };
                    },
                    methods: {
                        delete_package: function (id) {
                            var $self = this;
                            this.loading = true;
                            $engine.network.post('package', 'delete', {
                                id: id,
                            }, function (res) {
                                $self.loading = false;
                                $self.$store.commit('get_package');
                            });
                        },
                    },
                    computed: {
                        all_vps: function () {
                            var vps = 0;
                            this.$store.getters.package.forEach(function (p) {
                                vps += parseInt(p.vps);
                            });
                            return vps;
                        }
                    },
                    created: function () {
                        this.$store.commit('get_package');
                    }
                },
            },
            {
                name: 'admin-package-add',
                path: '/admin/package-add',
                component: {
                    template: $engine.template.components['admin-package-add'].template,
                    data: function () {
                        return {
                            form: {
                                name: "",
                                cpu: 1,
                                ram: 1,
                                disk: 100,
                                time: 1,
                                price: 99.99,
                            },
                            loading: false,
                        };
                    },
                    methods: {
                        add: function () {
                            var $self = this;
                            this.loading = true;
                            $engine.network.post('package', 'add', {
                                name: this.form.name,
                                cpu: this.form.cpu,
                                ram: this.form.ram,
                                disk: this.form.disk,
                                time: this.form.time,
                                price: this.form.price,
                            }, function (res) {
                                $self.loading = false;
                                $self.$router.push('/admin/package-list');
                            });
                        },
                    },
                    created: function () {

                    }
                },
            },
            {
                name: 'admin-user-list',
                path: '/admin/user-list',
                component: {
                    template: $engine.template.components['admin-user-list'].template,
                    data: function () {
                        return {
                            loading: true,
                            search: '',
                            hide_users: [],
                        };
                    },
                    methods: {
                        getAll: function () {
                            var $self = this;
                            $engine.network.post('user', 'getAll', {}, function (res) {
                                if (res) {
                                    $self.$set($self, 'hide_users', res);
                                } else {
                                    swal({
                                        type: 'error',
                                        title: 'พบข้อผิดพลาด',
                                        text: 'กรุณาลองใหม่ภายหลัง',
                                        timer: 3500,
                                    });
                                }
                            });
                        },
                        loginAS: function (user) {
                            var $self = this;
                            $engine.network.post('user', 'loginAs', {
                                id: user.id
                            }, function (res) {
                                if (res === true) {
                                    $self.$store.commit('get_user');
                                    $self.$router.replace({
                                        name: 'vps-list'
                                    });
                                } else {
                                    swal({
                                        type: 'error',
                                        title: 'พบข้อผิดพลาด',
                                        text: 'กรุณาลองใหม่ภายหลัง',
                                        timer: 3500,
                                    });
                                }
                            });
                        },
                        detail: function (user) {
                            swal({
                                title: 'ข้อมูลบัญชี ' + user.name,
                                html: '<table class="table">' +
                                    '<tr><td class="text-left">ID</td><td class="text-left align-middle">' + user.id + '</td></tr>' +
                                    '<tr><td class="text-left">Email</td><td class="text-left align-middle">' + user.email + '</td></tr>' +
                                    '<tr><td class="text-left">ชื่อ</td><td class="text-left align-middle">' + user.name + '</td></tr>' +
                                    '<tr><td class="text-left">เบอร์</td><td class="text-left align-middle">' + user.phone + '</td></tr>' +
                                    '<tr><td class="text-left">ที่อยู่</td><td class="text-left align-middle">' + user.address + '</td></tr>' +
                                    '<tr><td class="text-left">บริษัท</td><td class="text-left align-middle">' + user.company + '</td></tr>' +
                                    '<tr><td class="text-left">เงิน</td><td class="text-left align-middle">' + user.credit + '</td></tr>' +
                                    '<tr><td class="text-left">สมัคร</td><td class="text-left align-middle">' + (new Date(user.time * 1000)).toLocaleString() + '</td></tr>' +
                                    '<tr><td class="text-left">ยืนยัน Email</td><td class="text-left align-middle">' + (
                                        (user.verify_email == 0 ? '<span class="text-danger">ยังไม่ได้ยืนยัน</span>' : '<span class="text-success">ยืนยันแล้ว</span>')
                                    ) + '</td></tr>' +
                                    '<tr><td class="text-left">ยืนยันเบอร์</td><td class="text-left align-middle">' + (
                                        (user.verify_phone == 0 ? '<span class="text-danger">ยังไม่ได้ยืนยัน</span>' : '<span class="text-success">ยืนยันแล้ว</span>')
                                    ) + '</td></tr>' +
                                    '</table>',
                                showCloseButton: true,
                                focusConfirm: false,
                            });
                        },
                    },
                    computed: {
                        users: function () {
                            var $self = this;
                            return this.hide_users.filter(function (user) {
                                if ($self.search == "") {
                                    return true;
                                } else {
                                    return user.id.indexOf($self.search) > -1 || user.email.indexOf($self.search) > -1 || user.name.indexOf($self.search) > -1 || user.phone.indexOf($self.search) > -1 || user.address.indexOf($self.search) > -1 || user.company.indexOf($self.search) > -1 || user.credit.indexOf($self.search) > -1;
                                }
                            });
                        },
                    },
                    created: function () {
                        this.getAll();
                    }
                },
            },
            {
                name: 'admin-server-list',
                path: '/admin/server-list',
                component: {
                    template: $engine.template.components['admin-server-list'].template,
                    data: function () {
                        return {

                        };
                    },
                    methods: {
                        ram_format: function (amount) {
                            return (amount / 1024 / 1024 / 1024).toFixed(2);
                        },
                    },
                    created: function () {
                        this.$store.commit('get_host');
                    }
                },
            },
            {
                name: 'admin-server-detail',
                path: '/admin/server-detail/:id',
                props: true,
                component: {
                    props: ['id'],
                    template: $engine.template.components['admin-server-detail'].template,
                    data: function () {
                        return {

                        };
                    },
                    methods: {
                        ram_format: function (amount) {
                            return (amount / 1024 / 1024 / 1024).toFixed(2);
                        },
                    },
                    computed: {
                        host: function () {
                            var $self = this;
                            var host = false;
                            if (this.$store.getters.host === false) {
                                return false;
                            } else {
                                this.$store.getters.host.forEach(function (h) {
                                    if ($self.id == h.id) {
                                        host = h;
                                    }
                                });
                            }
                            return host;
                        },
                    },
                    created: function () {
                        this.$store.commit('get_host');
                    }
                },
            },
            {
                name: 'admin-server-ip',
                path: '/admin/server-ip/:id',
                props: true,
                component: {
                    props: ['id'],
                    template: $engine.template.components['admin-server-ip'].template,
                    data: function () {
                        return {
                            ips: false,
                        };
                    },
                    methods: {
                        get_ip: function () {
                            var $self = this;
                            $engine.network.post('host', 'get_ip', {
                                host: this.id,
                            }, function (res) {
                                Vue.set($self, 'ips', res);
                            });
                        },
                        remove: function (id) {
                            var $self = this;
                            $engine.network.post('host', 'remove_ip', {
                                host: this.id,
                                id: id,
                            }, function (res) {
                                Vue.set($self, 'ips', res);
                            });
                        },
                        add: function () {
                            var $self = this;
                            swal({
                                title: 'กรอกข้อมูล IP',
                                html: '<input id="ipaddress" class="swal2-input" placeholder="IP">' +
                                    '<input id="subnetmask" class="swal2-input" placeholder="Subnetmask">' +
                                    '<input id="gateway" class="swal2-input" placeholder="Gateway">',
                                focusConfirm: false,
                                preConfirm: function () {
                                    var ip = document.getElementById('ipaddress').value;
                                    var subnet = document.getElementById('subnetmask').value;
                                    var gateway = document.getElementById('gateway').value;
                                    return swal({
                                        title: 'กำลังเพิ่ม IP',
                                        text: 'กรุณารอซักครู่...',
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                        onOpen: function () {
                                            swal.showLoading();
                                            $engine.network.post('host', 'add_ip', {
                                                host: $self.id,
                                                ip: ip,
                                                subnet: subnet,
                                                gateway: gateway,
                                            }, function (res) {
                                                if (res == false) {
                                                    swal('เกิดข้อผิดพลาด', 'กรุณาลองใหม่ภายหลัง', 'error');
                                                } else {
                                                    swal('เพิ่มเรียบร้อยแล้ว', '', 'success');
                                                    Vue.set($self, 'ips', res);
                                                }
                                            });
                                        }
                                    });
                                }
                            });
                        }
                    },
                    computed: {
                        host: function () {
                            var $self = this;
                            var host = false;
                            if (this.$store.getters.host === false) {
                                return false;
                            } else {
                                this.$store.getters.host.forEach(function (h) {
                                    if ($self.id == h.id) {
                                        host = h;
                                    }
                                });
                            }
                            return host;
                        },
                        ip_free: function () {
                            if(this.ips == false) {
                                return 0;
                            } else {
                                var n = 0;
                                this.ips.forEach(function (e) {
                                    if (e.useby == 0)
                                        n++;
                                })
                                return n;
                            }
                        },
                        ip_in_use: function () {
                            if(this.ips == false) {
                                return 0;
                            } else {
                                var n = 0;
                                this.ips.forEach(function (e) {
                                    if (e.useby != 0)
                                        n++;
                                })
                                return n;
                            }
                        }
                    },
                    created: function () {
                        this.$store.commit('get_host');
                        this.get_ip();
                    }
                },
            },
            {
                name: 'admin-server-vm',
                path: '/admin/server-vm/:id',
                props: true,
                component: {
                    props: ['id'],
                    template: $engine.template.components['admin-server-vm'].template,
                    data: function () {
                        return {
                            vms: false,
                        };
                    },
                    methods: {
                        get_vm: function () {
                            var $self = this;
                            $engine.network.post('host', 'get_vm', {
                                host: this.id,
                            }, function (res) {
                                Vue.set($self, 'vms', res);
                            });
                        },
                        remove: function (id) {
                            var $self = this;
                            $engine.network.post('host', 'remove_vm', {
                                id: id,
                            }, function (res) {
                                $self.get_vm();
                            });
                        },
                    },
                    computed: {
                        host: function () {
                            var $self = this;
                            var host = false;
                            if (this.$store.getters.host === false) {
                                return false;
                            } else {
                                this.$store.getters.host.forEach(function (h) {
                                    if ($self.id == h.id) {
                                        host = h;
                                    }
                                });
                            }
                            return host;
                        },
                    },
                    created: function () {
                        this.$store.commit('get_host');
                        this.get_vm();
                    }
                },
            },
            {
                name: 'admin-server-add',
                path: '/admin/server-add',
                component: {
                    template: $engine.template.components['admin-server-add'].template,
                    data: function () {
                        return {
                            step: 1,
                            form: {
                                hypervisor: '',
                                ip: '',
                                port: '80',
                                user: '',
                                pass: '',
                            },
                            loading: false,
                            message_mode: "",
                            message: "",
                        };
                    },
                    methods: {
                        usage_visible: function (name) {
                            return ['server', 'vm', 'cpu', 'ram', 'ip'].indexOf(name) > -1;
                        },
                        usage_format: function (name, value) {
                            if (value == -1) {
                                value = "<span class='tx-success'>ไม่จำกัด</span>";
                            }
                            if (name == "server") {
                                return value + ' เครื่อง';
                            } else if (name == "vm") {
                                return value + ' เครื่อง';
                            } else if (name == "cpu") {
                                return value + ' cores';
                            } else if (name == "ram") {
                                return value + ' GB';
                            } else if (name == "ip") {
                                return value + ' หมายเลข';
                            } else if (name == "network") {
                                return value + ' วง';
                            } else if (name == "disk") {
                                return value + ' GB';
                            }
                        },
                        usage_format_limit: function (name, value, limit) {
                            value = Math.round(value);
                            var value_str = '',
                                limit_str = '',
                                r = '';
                            if (value == -1) {
                                value_str = "<span class='tx-success'>ไม่จำกัด</span>";
                            } else {
                                value_str = value;
                            }
                            if (limit == -1) {
                                limit_str = "<span class='tx-success'>ไม่จำกัด</span>";
                            } else {
                                limit_str = limit;
                            }
                            if (name == "server") {
                                r = value_str + '/' + limit_str + ' เครื่อง';
                            } else if (name == "vm") {
                                r = value_str + '/' + limit_str + ' เครื่อง';
                            } else if (name == "cpu") {
                                r = value_str + '/' + limit_str + ' cores';
                            } else if (name == "ram") {
                                r = value_str + '/' + limit_str + ' GB';
                            } else if (name == "ip") {
                                r = value_str + '/' + limit_str + ' หมายเลข';
                            } else if (name == "network") {
                                r = value_str + '/' + limit_str + ' วง';
                            } else if (name == "disk") {
                                r = value_str + '/' + limit_str + ' GB';
                            }
                            if (value >= limit && limit != -1) {
                                return "<span class='tx-danger'>" + r + "</span>";
                            } else {
                                return r;
                            }
                        },
                        usage_label: function (name) {
                            if (name == "server") {
                                return "เครื่องเซิฟเวอร์";
                            } else if (name == "vm") {
                                return "VMs";
                            } else if (name == "cpu") {
                                return "CPU";
                            } else if (name == "ram") {
                                return "RAMs";
                            } else if (name == "ip") {
                                return "IP";
                            } else if (name == "network") {
                                return "เน็ตเวิร์ค";
                            } else if (name == "disk") {
                                return "พื้นที่";
                            }
                        },
                        check: function () {
                            var $self = this;
                            if (this.form.hypervisor == 'xenserver') {
                                this.loading = true;
                                this.message_mode = "";
                                this.message = "กำลังเชื่อมต่อ...";

                                $engine.network.post('xenserver', 'check', {
                                    ip: this.form.ip,
                                    port: this.form.port,
                                    user: this.form.user,
                                    pass: this.form.pass
                                }, function (res) {
                                    $self.loading = false;
                                    var code = "";
                                    if (res.success) {
                                        $self.message_mode = "success";
                                        $self.message = "เชื่อมต่อสำเร็จ";
                                        $self.step = 2;
                                    } else {
                                        if (typeof res.error.code == "undefined") {
                                            code = res.error[0];
                                        } else {
                                            code = res.error.code;
                                        }

                                        if (code.toUpperCase() == "ETIMEDOUT") {
                                            $self.message_mode = "error";
                                            $self.message = "ไม่สามารถเชื่อมต่อได้";
                                        } else if (code.toUpperCase() == "SESSION_AUTHENTICATION_FAILED") {
                                            $self.message_mode = "error";
                                            $self.message = "รหัสผ่านไม่ถูกต้อง";
                                        }
                                    }
                                });
                            }
                        },
                        active: function () {
                            var $self = this;
                            if (this.form.hypervisor == 'xenserver') {
                                this.loading = true;
                                this.message_mode = "";
                                this.message = "กำลังเชื่อมต่อ...";

                                $engine.network.post('xenserver', 'active', {
                                    license: this.$store.getters.license.key,
                                    ip: this.form.ip,
                                    port: this.form.port,
                                    user: this.form.user,
                                    pass: this.form.pass,
                                    location: window.location.origin + window.location.pathname,
                                }, function (res) {
                                    $self.loading = false;
                                    var code = "";
                                    if (res.success) {
                                        $self.message_mode = "success";
                                        $self.message = "เชื่อมต่อสำเร็จ";
                                        $self.$router.push({
                                            name: 'admin-server-list'
                                        });
                                    } else {
                                        if (typeof res.error.code == "undefined") {
                                            code = res.error[0];
                                        } else {
                                            code = res.error.code;
                                        }

                                        if (code.toUpperCase() == "ETIMEDOUT") {
                                            $self.message_mode = "error";
                                            $self.message = "ไม่สามารถเชื่อมต่อได้";
                                        } else if (code.toUpperCase() == "SESSION_AUTHENTICATION_FAILED") {
                                            $self.message_mode = "error";
                                            $self.message = "รหัสผ่านไม่ถูกต้อง";
                                        }
                                    }
                                });
                            }
                        },
                    },
                    created: function () {
                        this.$store.commit('get_license');
                    }
                },
            },
            {
                name: 'admin-setting-common',
                path: '/admin/setting-common',
                component: {
                    template: $engine.template.components['admin-setting-common'].template,
                    data: function () {
                        return {};
                    },
                    methods: {

                    },
                    mounted: function () {}
                },
            },
            {
                name: 'admin-setting-payment',
                path: '/admin/setting-payment',
                component: {
                    template: $engine.template.components['admin-setting-payment'].template,
                    data: function () {
                        return {
                            form: {
                                tm: {
                                    merchant: this.$store.getters.setting.truemoney_tmpay_merchant,
                                    loading: false,
                                },
                                tw: {
                                    phone: this.$store.getters.setting.truewallet_phone,
                                    pin: this.$store.getters.setting.truewallet_pin,
                                    loading: false,
                                },
                                kbank: {
                                    user: this.$store.getters.setting.bank_kbank.user,
                                    pass: this.$store.getters.setting.bank_kbank.pass,
                                    account: this.$store.getters.setting.bank_kbank.account,
                                    loading: false,
                                }
                            }
                        };
                    },
                    methods: {
                        save_tm: function () {
                            var $self = this;
                            swal({
                                title: 'กำลังบันทึกข้อมูล',
                                onOpen: function () {
                                    swal.showLoading();
                                    $engine.network.post('setting', 'save', {
                                        truemoney_tmpay_merchant: $self.form.tm.merchant,
                                    }, function (res) {
                                        $self.$store.commit('setting', res);
                                        swal({
                                            type: 'success',
                                            title: 'บันทึกสำเร็จ',
                                            timer: 3000,
                                        });
                                    });
                                }
                            });
                        },
                        save_tw: function () {
                            var $self = this;
                            swal({
                                title: 'กำลังบันทึกข้อมูล',
                                onOpen: function () {
                                    swal.showLoading();
                                    $engine.network.post('setting', 'save', {
                                        truewallet_phone: $self.form.tw.phone,
                                        truewallet_pin: $self.form.tw.pin,
                                    }, function (res) {
                                        $self.$store.commit('setting', res);
                                        swal({
                                            type: 'success',
                                            title: 'บันทึกสำเร็จ',
                                            timer: 3000,
                                        });
                                    });
                                }
                            });
                        },
                        save_kbank: function () {
                            var $self = this;
                            swal({
                                title: 'กำลังบันทึกข้อมูล',
                                onOpen: function () {
                                    swal.showLoading();
                                    $engine.network.post('setting', 'save', {
                                        bank_kbank: JSON.stringify({
                                            user: $self.form.kbank.user,
                                            pass: $self.form.kbank.pass,
                                            account: $self.form.kbank.account,
                                        }),
                                    }, function (res) {
                                        $self.$store.commit('setting', res);
                                        swal({
                                            type: 'success',
                                            title: 'บันทึกสำเร็จ',
                                            timer: 3000,
                                        });
                                    });
                                }
                            });
                        }
                    },
                    mounted: function () {}
                },
            },
            {
                name: 'admin-setting-sms',
                path: '/admin/setting-sms',
                component: {
                    template: $engine.template.components['admin-setting-sms'].template,
                    data: function () {
                        return {};
                    },
                    methods: {
                        gateway: function (gateway) {
                            var $self = this;

                            $engine.network.post('setting', 'save', {
                                sms_gateway: gateway,
                            }, function (res) {
                                $self.$store.commit('setting', res);
                                toast({
                                    type: 'success',
                                    title: 'บันทึกสำเร็จ',
                                });
                            });
                        },
                        enable: function (enable) {
                            var $self = this;

                            $engine.network.post('setting', 'save', {
                                sms_notification: enable ? "1" : "0",
                            }, function (res) {
                                $self.$store.commit('setting', res);
                                toast({
                                    type: 'success',
                                    title: 'บันทึกสำเร็จ',
                                });
                            });
                        },
                        save_setting: function () {
                            var $self = this;
                            var data = false;

                            if (this.$store.getters.setting.sms_gateway == "thsms") {
                                data = {
                                    sms_config_thsms: JSON.stringify({
                                        user: this.setting.user,
                                        pass: this.setting.pass,
                                        sender: this.setting.sender
                                    })
                                };
                            } else if (this.$store.getters.setting.sms_gateway == "molink") {
                                data = {
                                    sms_config_molinksms: JSON.stringify({
                                        user: this.setting.user,
                                        pass: this.setting.pass,
                                        sender: this.setting.sender
                                    })
                                };
                            } else if (this.$store.getters.setting.sms_gateway == "thaibulk") {
                                data = {
                                    sms_config_thaibulk: JSON.stringify({
                                        user: this.setting.user,
                                        pass: this.setting.pass,
                                        sender: this.setting.sender
                                    })
                                };
                            }

                            if (data) {
                                $engine.network.post('setting', 'save', data, function (res) {
                                    $self.$store.commit('setting', res);
                                    toast({
                                        type: 'success',
                                        title: 'บันทึกสำเร็จ',
                                    });
                                });
                            } else {
                                toast({
                                    type: 'error',
                                    title: 'พบข้อผิดพลาด',
                                });
                            }
                        }
                    },
                    computed: {
                        setting: function () {
                            if (this.$store.getters.setting.sms_gateway == "thsms") {
                                return this.$store.getters.setting.sms_config_thsms;
                            } else if (this.$store.getters.setting.sms_gateway == "molink") {
                                return this.$store.getters.setting.sms_config_molinksms;
                            } else if (this.$store.getters.setting.sms_gateway == "thaibulk") {
                                return this.$store.getters.setting.sms_config_thaibulk;
                            } else {
                                return {
                                    user: "",
                                    pass: "",
                                    sender: ""
                                }
                            }
                        }
                    },
                },
            },
            {
                name: 'admin-setting-license',
                path: '/admin/setting-license',
                component: {
                    template: $engine.template.components['admin-setting-license'].template,
                    data: function () {
                        return {
                            license: 'loading',
                        };
                    },
                    methods: {
                        usage_visible: function (name) {
                            return ['server', 'vm', 'cpu', 'ram', 'ip'].indexOf(name) > -1;
                        },
                        usage_format: function (name, value) {
                            if (value == -1) {
                                value = "<span class='tx-success'>ไม่จำกัด</span>";
                            }
                            if (name == "server") {
                                return value + ' เครื่อง';
                            } else if (name == "vm") {
                                return value + ' เครื่อง';
                            } else if (name == "cpu") {
                                return value + ' cores';
                            } else if (name == "ram") {
                                return value + ' GB';
                            } else if (name == "ip") {
                                return value + ' หมายเลข';
                            } else if (name == "network") {
                                return value + ' วง';
                            } else if (name == "disk") {
                                return value + ' GB';
                            }
                        },
                        usage_format_limit: function (name, value, limit) {
                            var value_str = '',
                                limit_str = '',
                                r = '';
                            if (value == -1) {
                                value_str = "<span class='tx-success'>ไม่จำกัด</span>";
                            } else {
                                value_str = value;
                            }
                            if (limit == -1) {
                                limit_str = "<span class='tx-success'>ไม่จำกัด</span>";
                            } else {
                                limit_str = limit;
                            }
                            if (name == "server") {
                                r = value_str + '/' + limit_str + ' เครื่อง';
                            } else if (name == "vm") {
                                r = value_str + '/' + limit_str + ' เครื่อง';
                            } else if (name == "cpu") {
                                r = value_str + '/' + limit_str + ' cores';
                            } else if (name == "ram") {
                                r = Math.round(value_str) + '/' + limit_str + ' GB';
                            } else if (name == "ip") {
                                r = value_str + '/' + limit_str + ' หมายเลข';
                            } else if (name == "network") {
                                r = value_str + '/' + limit_str + ' วง';
                            } else if (name == "disk") {
                                r = Math.round(value_str) + '/' + limit_str + ' GB';
                            }
                            if (value >= limit && limit != -1) {
                                return "<span class='tx-danger'>" + r + "</span>";
                            } else {
                                return r;
                            }
                        },
                        usage_label: function (name) {
                            if (name == "server") {
                                return "เครื่องเซิฟเวอร์";
                            } else if (name == "vm") {
                                return "VMs";
                            } else if (name == "cpu") {
                                return "CPU";
                            } else if (name == "ram") {
                                return "RAMs";
                            } else if (name == "ip") {
                                return "IP";
                            } else if (name == "network") {
                                return "เน็ตเวิร์ค";
                            } else if (name == "disk") {
                                return "พื้นที่";
                            }
                        }
                    },
                    created: function () {
                        this.$store.commit('get_license');
                    }
                },
            }
        ];
    }

    this.finishLoaded = function (loadingComponent) {};

    // Start application function
    this.init = function () {
        /*Vue.config.errorHandler = function (err, vm, info) {
            $engine.network.post('error', 'fatal', {
                'err': err,
                'info': info,
            });

            // handle error
            // `info` is a Vue-specific error info, e.g. which lifecycle hook
            // the error was found in. Only available in 2.2.0+
        }

        Vue.config.warnHandler = function (msg, vm, trace) {
            $engine.network.post('error', 'warn', {
                'err': err,
                'info': info,
            });

            // `trace` is the component hierarchy trace
        }*/

        //var loadingComponent = this.bue.LoadingProgrammatic.open();
        $engine.template.get(true, function () {});

        $engine.network.post('user', 'check', function (res) {
            if (res.success) {
                res.data.islogin = true;
                $engine.store.commit('user', res.data)

                // Get VPS data
                $engine.max_step++;
                $engine.network.post('vps', 'get', function (res) {
                    $engine.store.commit('vps', res);
                    $engine.setup++;
                });
            }
            $engine.setup++;
        });

        $engine.network.post('setting', 'get', function (res) {
            $engine.store.commit('setting', res);
            $engine.setup++;
        });

        $engine.setup_watcher = setInterval(function () {
            console.log('Check setup (' + $engine.setup + '/' + $engine.max_step + ')');
            if ($engine.setup >= $engine.max_step) {
                $engine.router = new VueRouter({
                    routes: routerCtrl(),
                });

                if (window.location.hostname != "localhost") {
                    ___ga()($engine.router, 'UA-44035659-7');
                }
                $engine.vm = new $engine.vue({
                    el: '#app',
                    template: "#template",
                    components: $engine.template.components,
                    methods: {
                        send_register: function () {
                            var $self = this;
                            $engine.network.post('user', 'register', {
                                name: this.form.register.name,
                                company: this.form.register.company,
                                address: this.form.register.address,
                                phone: this.form.register.phone,
                                email: this.form.register.email,
                                password: this.form.register.password,
                            }, function (res) {
                                Vue.set($self.form, 'register', {
                                    name: '',
                                    company: '',
                                    address: '',
                                    phone: '',
                                    email: '',
                                    password: '',
                                });
                                swal({
                                    type: 'warning',
                                    title: 'สมัครสมาชิกสำเร็จ',
                                    text: 'ระบบได้ส่งอีเมล์ยืนยันตัวตนแล้ว กรุณายืนยันตัวตนเพื่อประสิทธิภาพการใช้งานที่ดียิ่งขึ้น',
                                });
                                $self.$router.replace('/login');
                            });
                        },
                        send_login: function () {
                            var $self = this;
                            $engine.network.post('user', 'login', {
                                email: this.form.login.email,
                                password: this.form.login.password,
                            }, function (res) {
                                if (res.success) {
                                    res.data.islogin = true;
                                    $engine.store.commit('user', res.data)
                                    Vue.set($self.form, 'login', {
                                        email: '',
                                        password: '',
                                    });
                                    $self.$router.replace({
                                        name: 'vps-list'
                                    });
                                } else {
                                    swal({
                                        type: 'warning',
                                        title: 'เข้าสู่ระบบไม่สำเร็จ',
                                        text: 'อีเมล์หรือรหัสผ่านไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง',
                                    });
                                }
                            });
                        },
                        send_fotgot: function () {
                            var $self = this;
                            this.form.forgot.loading = true;
                            $engine.network.post('user', 'forgot', {
                                email: this.form.forgot.email,
                            }, function (res) {
                                $self.form.forgot.loading = false;
                                $self.form.forgot.sent = true;
                            });
                        },
                        check_resetpass_token: function (token) {
                            var $self = this;
                            this.form.reset.loading = true;
                            $engine.network.post('user', 'resetpass', {
                                mode: 'check',
                                token: token,
                            }, function (res) {
                                if (res === true) {
                                    $self.form.reset.loading = false;
                                    $self.form.reset.checked = true;
                                    $self.form.reset.token = token;
                                } else {
                                    $self.$router.replace({
                                        path: '/login'
                                    });
                                }
                            });
                        },
                        send_resetpass: function () {
                            var $self = this;
                            this.form.reset.loading = true;
                            $engine.network.post('user', 'resetpass', {
                                mode: 'reset',
                                password: this.form.reset.password,
                                token: this.form.reset.token,
                            }, function (res) {
                                if (res === true) {
                                    $self.form.reset.loading = false;
                                    $self.form.reset.reseted = true;
                                    setTimeout(function () {
                                        $self.$router.replace({
                                            path: '/login'
                                        });
                                    }, 5000);
                                } else {
                                    $self.$router.replace({
                                        path: '/login'
                                    });
                                }
                            });
                        },
                        send_logout: function () {
                            var $self = this;
                            $engine.network.post('user', 'logout', function (res) {
                                if (res === "admin") {
                                    $engine.store.commit('get_user');
                                    $self.$router.replace('/admin/user-list');
                                } else {
                                    $engine.store.commit('user_logout');
                                    $self.$router.replace('/login');
                                }
                            });
                            return false;
                        },
                        refreshChat: function () {
                            var $self = this;
                            $engine.network.post('support', 'get', function (res) {
                                $self.$store.commit('ticket', res.room);
                                Object.keys(res.chat).forEach(function (ticket) {
                                    $self.$store.commit('ticket_chat', {
                                        ticket: ticket,
                                        chat: res.chat[ticket],
                                    });
                                });
                                $self.process.chat = setTimeout($self.refreshChat, 7500);
                            });
                        },
                        refreshData: function () {
                            var $self = this;
                            $self.$store.commit('get_vps', function () {
                                $self.process.instance = setTimeout($self.refreshData, 4500);
                            });
                        }
                    },
                    data: function () {
                        return {
                            user: this.$store.getters.user,
                            notification: this.$store.getters.notification,
                            form: {
                                register: {
                                    name: '',
                                    company: '',
                                    address: '',
                                    phone: '',
                                    email: '',
                                    password: '',
                                },
                                login: {
                                    email: '',
                                    password: '',
                                },
                                forgot: {
                                    email: '',
                                    sent: false,
                                    loading: false,
                                },
                                reset: {
                                    token: '',
                                    checked: false,
                                    reseted: false,
                                    password: '',
                                    loading: false,
                                }
                            },
                            process: {
                                chat: null,
                            },
                            menu: {
                                admin: [
                                    'admin-server-list',
                                    'admin-server-add',
                                    'admin-server-detail',
                                    'admin-server-ip',
                                    'admin-server-vm',
                                    'admin-setting-common',
                                    'admin-setting-payment',
                                    'admin-setting-sms',
                                    'admin-setting-license',
                                    'admin-user-list',
                                    'admin-package-list',
                                    'admin-package-add',
                                ],
                            }
                        }
                    },
                    created: function () {
                        var $self = this;
                        if (this.$store.getters.islogin) {
                            if (this.$route.name === null) {
                                this.$router.replace({
                                    name: 'vps-list'
                                });
                            }
                            if (!this.$store.getters.user.verify_email) {
                                swal({
                                    type: 'warning',
                                    title: 'กรุณายืนยันอีเมล์',
                                    text: 'เพื่อความสะดวกในการใช้งาน ระบบจะส่งแจ้งเตือนต่างๆผ่านทางอีเมล์',
                                    confirmButtonText: 'ไปยังหน้าตั้งค่า',
                                }).then(function (result) {
                                    $self.$router.push({
                                        name: 'setting'
                                    });
                                })
                            } else if (!this.$store.getters.user.verify_phone) {
                                swal({
                                    type: 'warning',
                                    title: 'กรุณายืนยันเบอร์โทรศัพท์',
                                    text: 'เพื่อความสะดวกในการใช้งาน ระบบจะส่งแจ้งเตือนต่างๆผ่านทาง SMS',
                                    confirmButtonText: 'ไปยังหน้าตั้งค่า',
                                }).then(function (result) {
                                    $self.$router.push({
                                        name: 'setting'
                                    });
                                })
                            }
                        }
                        var $self = this;
                        //this.refreshChat();

                        //this.process.chat = setTimeout(this.refreshChat, 7500);
                        this.refreshData();
                    },
                    mounted: function () {
                        if (this.$route.path.indexOf('reset-password') > -1) {
                            var path = this.$route.path.split('/');
                            this.check_resetpass_token(path[2]);
                        }
                    },
                    router: $engine.router,
                    store: $engine.store,
                });
                clearInterval($engine.setup_watcher);
            }
        }, 50);

    };

    return this;
}