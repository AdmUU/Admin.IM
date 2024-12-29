import { request } from "@/utils/request.js";

import tool from "@/utils/tool";

/**
 * 获取验证码
 * @returns
 */
const getCaptcha = (data) => {
  return request({
    url: "plugin/captcha/get_captcha",
    method: "post",
    data,
  });
};
const preVerify = (captcha_id, data) => {
  data.captcha_id = captcha_id;
  const fs = {};
  //简单收集浏览器特征
  fs.webdriver = !!navigator.webdriver;
  fs.cdcFunction = !!window.document.$cdc_asdjflasutopfhvcZLmcfl_;
  fs.headlessChrome = navigator.userAgent.includes("HeadlessChrome");
  fs.seleniumIDE = !!window.document.SeleniumIDE;
  fs.webdriverEvaluate = typeof __webdriver_evaluate !== "undefined";
  fs.driverEvaluate = typeof __driver_evaluate !== "undefined";
  fs.seleniumEvaluate = typeof __selenium_evaluate !== "undefined";
  fs.webdriverScriptFunction =
    typeof __webdriver_script_function !== "undefined";
  fs.webdriverScriptFunc = typeof __webdriver_script_func !== "undefined";

  data.t = btoa(
    btoa(JSON.stringify(fs)) + tool.md5(JSON.stringify(fs) + captcha_id)
  );
  return request({
    url: "plugin/captcha/pre_verify",
    method: "post",
    data,
  });
};
export { getCaptcha, preVerify };
