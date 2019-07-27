需求是老项目要做多语言，

但是前端用ｖｕｅ1.x 时为了和ｔｗｉｇ都用{{}}不冲突，于是用了vｅrbatim，

然后现在要多语言时，默认的方式就犯难了，要么前端用ｖｕｅ　i18再重写一遍不好需要维护两套翻译，

要么后端渲染，于是就有了这个项目，思路是把ｒｅｓｐｏｎｓｅ返回的ｈｔｍｌ再次通过渲染一遍

##### php使用


```php


$kernel = new AppKernel('dev', true);
//$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);

if ($response->headers->has("Content-Type")) {
    $contentType = $response->headers->get("Content-Type");
    if (strpos($contentType,"text/html") !== false) {
        $content = $response->getContent();

        $translator = $kernel->getContainer()->get("translator")；
        $tte = new \Common\Util\TextTranslateEngine($translator);
        $newContent = $tte->render($content);
		
        $response->setContent($newContent);
    }
}

$response->send();
$kernel->terminate($request, $response);

```


##### twig verbatim里使用类erb语法`<%t %>`

```twig

{% verbatim %}
   <li> 欢迎 <%t "Hello %nickname%" ___ {"%nickname%": "李さん", "domain": "", "local": ""} %>
                         </li>
	
    <p> <%t  "需要店铺认证后才能发布商品" %> </p>	
{% endverbatim %}
```

##### 结果
![image](https://raw.githubusercontent.com/zjsxwc/simple-translator-template/master/example.png)

