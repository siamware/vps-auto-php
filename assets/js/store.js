function createStore() {
      return new Vuex.Store({
            state: {
                  user: {
                        id: 0,
                        email: '',
                        name: '',
                        phone: '',
                        address: '',
                        company: '',
                        credit: 0,
                        avartar: '',
                        admin: false,
                        islogin: false,
                  },
                  vps: [],
                  ticket: [],
                  ticket_chat: {},
                  notification: [{
                        id: 0,
                        date: 1523457197,
                        text: 'Purchased christmas sale cloud storage',
                        read: false,
                  }],
                  license: false,
                  host: false,
                  package: false,
                  template: false,
                  setting: false,
            },
            mutations: { // call by `commit`
                  change: function (state, data) {
                        Vue.set(state, data.path, data.data);
                  },
                  user_logout: function (state) {
                        Vue.set(state, 'user', {
                              id: 0,
                              email: '',
                              name: '',
                              phone: '',
                              address: '',
                              company: '',
                              credit: 0,
                              avartar: '',
                              admin: false,
                              islogin: false,
                        });
                  },
                  user: function (state, data) {
                        if (data.id != 0) {
                              data.islogin = true;
                        }
                        Vue.set(state, 'user', data);
                  },
                  vps: function (state, data) {
                        Vue.set(state, 'vps', data);
                  },
                  ticket: function (state, data) {
                        Vue.set(state, 'ticket', data);
                  },
                  ticket_chat: function (state, data) {
                        Vue.set(state.ticket_chat, data.ticket, data.chat);
                  },
                  notification: function (state, data) {
                        Vue.set(state, 'notification', data);
                  },
                  setting: function (state, data) {
                        Vue.set(state, 'setting', data);
                  },
                  get_user: function (state) {
                        Vue.http.post('api.php?controller=user&action=get', {
                              controller: 'user',
                              action: 'get',
                        }).then(function (res) {
                              if (res.body.res.id != 0) {
                                    res.body.res.islogin = true;
                              }
                              Vue.set(state, 'user', res.body.res);
                        });
                  },
                  get_license: function (state) {
                        Vue.http.post('api.php?controller=license&action=get', {
                              controller: 'license',
                              action: 'get',
                        }).then(function (res) {
                              Vue.set(state, 'license', res.body.res.data);
                        });
                  },
                  get_host: function (state) {
                        Vue.http.post('api.php?controller=host&action=get', {
                              controller: 'host',
                              action: 'get',
                        }).then(function (res) {
                              Vue.set(state, 'host', res.body.res);
                        });
                  },
                  get_package: function (state) {
                        Vue.http.post('api.php?controller=package&action=get', {
                              controller: 'package',
                              action: 'get',
                        }).then(function (res) {
                              Vue.set(state, 'package', res.body.res);
                        });
                  },
                  get_template: function (state, data) {
                        Vue.http.post('api.php?controller=host&action=get_template', {
                              controller: 'host',
                              action: 'get_template',
                              host: data.host
                        }).then(function (res) {
                              Vue.set(state, 'template', res.body.res);
                        });
                  },
                  clear_template: function (state) {
                        Vue.set(state, 'template', false);
                  },
                  get_vps: function (state, cb) {
                        Vue.http.post('api.php?controller=vps&action=get', {
                              controller: 'vps',
                              action: 'get',
                        }).then(function (res) {
                              Vue.set(state, 'vps', res.body.res);
                              typeof cb === "function" && cb();
                        });
                  },
                  get_setting: function (state) {
                        Vue.http.post('api.php?controller=setting&action=get', {
                              controller: 'setting',
                              action: 'get',
                        }).then(function (res) {
                              Vue.set(state, 'setting', res.body.res);
                        });
                  },
            },
            actions: {
                  vps: function (context, id) {
                        var vps = false;
                        context.state.vps.forEach(e => {
                              if (e.id == id)
                                    vps = e;
                        });
                        return vps;
                  },
            },
            getters: {
                  vps: function (state) {
                        return state.vps;
                  },
                  user: function (state) {
                        return state.user;
                  },
                  notification: function (state) {
                        return state.notification;
                  },
                  islogin: function (state) {
                        return state.user.islogin;
                  },
                  ticket: function (state) {
                        return state.ticket;
                  },
                  ticket_chat: function (state) {
                        return function (roomId) {
                              return state.ticket_chat[roomId];
                        }
                  },
                  license: function (state) {
                        return state.license;
                  },
                  host: function (state) {
                        return state.host;
                  },
                  package: function (state) {
                        return state.package;
                  },
                  template: function (state) {
                        return state.template;
                  },
                  setting: function (state) {
                        return state.setting;
                  }
            }
      })
}