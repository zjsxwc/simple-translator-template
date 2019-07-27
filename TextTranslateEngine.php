<?php


namespace Common\Util;


use Symfony\Component\Translation\TranslatorInterface;

class TextTranslateEngine
{

    /** @var TranslatorInterface */
    private $translator;

    /**
     * StringTemplateEngine constructor.
     * @param $translator
     */
    public function __construct($translator)
    {
        $this->translator = $translator;
    }


    public $openDelimiter = "<%t";
    public $closeDelimiter = "%>";


    const TOKEN_TYPE_TEXT = 0;
    const TOKEN_TYPE_TRANS = 1;

    const EXPR_SEPERATOR = "___";
    const EXPR_MEESAGE_TRIM = "\"";

    /**
     * @param $content string
     * @return string
     */
    public function render($content)
    {
        //开始按照 erb 语法再次渲染，主要是为了多语言翻译　<%t "Hello %nickname%" ___ {"%nickname%": "李さん", "domain": "", "local": ""} %>
        // https://symfony.com/doc/current/reference/twig_reference.html#trans

        if (strpos($content, $this->openDelimiter) === false) {
            return $content;
        }

        //Lexer
        $tokens = [];
        $currentToken = [
            "type" => self::TOKEN_TYPE_TEXT,
            "text" => ""
        ];
        $pos = 0;
        while ($pos < strlen($content)) {
            if (substr($content, $pos, strlen($this->openDelimiter)) === $this->openDelimiter) {
                if (strlen($currentToken["text"])) {
                    $tokens[] = $currentToken;
                }

                $currentToken = [
                    "type" => self::TOKEN_TYPE_TRANS,
                    "text" => ""
                ];
                $pos = $pos + strlen($this->openDelimiter);

            } elseif (substr($content, $pos, strlen($this->closeDelimiter)) === $this->closeDelimiter) {
                if ($currentToken["type"] === self::TOKEN_TYPE_TRANS) {
                    if (strlen($currentToken["text"])) {
                        $tokens[] = $currentToken;
                    }
                    $currentToken = [
                        "type" => self::TOKEN_TYPE_TEXT,
                        "text" => ""
                    ];
                }
                $pos = $pos + strlen($this->closeDelimiter);
            } else {
                $currentToken["text"] .= substr($content, $pos, 1);
                $pos++;
            }
        }
        if ($currentToken["type"] === self::TOKEN_TYPE_TRANS) {
            throw new \RuntimeException("TextTranslateEngine missing closeDelimiter");
        }
        $tokens[] = $currentToken;

        //Compile
        $rv = "";
        foreach ($tokens as $token) {
            if ($token["type"] === self::TOKEN_TYPE_TEXT) {
                $rv .= $token["text"];
            }

            if ($token["type"] === self::TOKEN_TYPE_TRANS) {
                $expr = trim($token["text"]);
                if (!$expr) {
                    continue;
                }
                $segments = explode(self::EXPR_SEPERATOR, $expr);
                $message = trim($segments[0]);
                $message = trim($message, self::EXPR_MEESAGE_TRIM);
                if (!$message) {
                    continue;
                }

                $arguments = [];
                if (isset($segments[1])) {
                    $arguments = json_decode($segments[1], true);
                }
                $domain = null;
                if ($arguments && isset($arguments["domain"])) {
                    $domain = $arguments["domain"];
                    unset($arguments["domain"]);
                }
                $locale = null;
                if ($arguments && isset($arguments["locale"])) {
                    $locale = $arguments["locale"];
                    unset($arguments["locale"]);
                }

                $rv .= $this->translator->trans($message, $arguments, $domain, $locale);
            }
        }
        return $rv;
    }


}

