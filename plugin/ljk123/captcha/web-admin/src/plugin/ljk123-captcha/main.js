import router from "@/router";
import { createApp, h } from "vue";
import Captcha from "./components/captcha.vue";
import i18n from "@/i18n";
let instance = null;

const captcha = (option = null) => {
  return new Promise((resolve, reject) => {
    if (instance) {
      reject(new Error("Cannot be called repeatedly"));
      return;
    }
    const container = document.createElement("div");
    document.body.appendChild(container);
    const app = createApp({
      render() {
        return h(Captcha, {
          visible: true,
          ...(option || {}),
          onClose: (res) => {
            //先卸载
            app.unmount();
            document.body.removeChild(container);
            instance = null;
            resolve(res);
          },
        });
      },
    });
    app.use(i18n);
    instance = app.mount(container);
  });
};

// 导出函数供外部调用
export { captcha };

import pluginI18n from "./i18n/zh_CN/plugin";
import sysI18n from "@/i18n/zh_CN/plugin";
import pluginI18nEn from "./i18n/en/plugin";
import sysI18nEn from "@/i18n/en/plugin";
const { t } = i18n.global;
export default {
  install: () => {
    if (false === (import.meta.env.VITE_APP_CAPTCHA_NOT_REPLACE_LOGIN || false)) {
      //替换路由
      router.removeRoute("login");
      router.addRoute({
        name: "login",
        path: "/login",
        component: () => import("./views/login.vue"),
        meta: { title: t("adm.signIn") },
      });
      router.replace(router.currentRoute.value.fullPath);
    }
    //合并语言包

    Object.assign(sysI18n, pluginI18n);
    Object.assign(sysI18nEn, pluginI18nEn);
  },
};
