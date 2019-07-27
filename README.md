
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
