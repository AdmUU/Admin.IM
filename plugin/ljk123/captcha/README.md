安装方法

```sh
php bin/hyperf.php mine-extension:install ljk123/captcha -y
```

若未按照 MineAdmin 文档正确配置`mine-extension.front_directory`,则需要手动覆盖前端文件.需要注意的是

1. main.js 需手动增加插件入口

```javascript
import plugin from "./plugin";
app.use(plugin);
```

2. 新建`plugin/index.js`文件(若不存在)

```javascript
export default {
  install: (Vue) => {
    const mains = import.meta.glob("./*/main.js");
    for (const path in mains) {
      mains[path]().then((module) => {
        Vue.use(module.default);
      });
    }
  },
};
```

3. 新建语言包插件文件`i18n/zh_CN/plugin.js`(若不存在),其他的看需要创建

```javascript
export default {};
```

使用方法
插件直接替换了后台 login 页面路由
可参考文件`pluginRoot/views/login.vue`

 **若需还原路由**

 > 前端环境方法中增加 VITE_APP_CAPTCHA_NOT_REPLACE_LOGIN = true

 **若后端默认登录控制器不再验证**

 > 后端本插件配置文件中 close_sys_login_verify 设置为 true

提供直接 js 调用方法

```javascript
// 省略其他代码...
captcha({
  type: 0, //可选 0旋转验证码  更多在路上
  feedbackUrl: "https://www.mineadmin.com/store/captcha", //可选
}).then((res) => {
  if (res.validated) {
    // 通过验证逻辑,取出验证结果,验证id提交后端
    const captcha_key = res.captchaKey;
    const captcha_id = res.captchaId;
  }
});
```

使用组件调用

```javascript

import captcha from '@/plugin/ljk123-captcha/components/captcha'
const visible = ref(false)
// 省略其他代码...
<captcha :type="0" :visible="visible" feedbackUrl="https://www.mineadmin.com/store/captcha">
//一些插槽 后面完善文档出来
</captcha>

```

后端 在需要方法添加注解,验证失败会抛出一个`Mine\Exception\NormalStatusException`异常

```php
use Plugin\Captcha\Annotation;

#[Captcha]
public function abc(){

}
```

其他待补充

如果您有想法,建议或反馈,可以在 mineadmin 官方交流群反馈
