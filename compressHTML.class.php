<?php
/**
 * Compress (minify) HTML
 *
 * Use it to compress HTML output to save bandwidth.
 *
 * Code, example usage information, and bugs/issues can be found/reported here
 * https://github.com/bmcculley/CompressHTML
 * 
 * Forked from DVS
 * http://www.intert3chmedia.net/2011/12/minify-html-javascript-css-without.html
 */

class CompressHTML {
    // Variables
    protected $html;
    protected $ignoreComments;
    protected $compressJS;
    protected $compressCSS;
    protected $addBotComment;

    public function __construct($html, $igComments = false, $comJS = false, $comCSS = false, $bComment = false) {
        $this->ignoreComments = $igComments;
        $this->compressJS = $comJS;
        $this->compressCSS = $comCSS;
        $this->addBotComment = $bComment;
        if (!empty($html)) {
            $this->parseHTML($html);
        }
    }

    public function htmlOut() {
        return $this->html;
    }

    protected function bottomComment($raw, $compressed) {
        $raw = strlen($raw);
        $compressed = strlen($compressed);
        $savings = ($raw-$compressed) / $raw * 100;
        $savings = round($savings, 2);
        return '<!-- HTML compressed, size saved '.$savings.'%. From '.$raw.' bytes, now '.$compressed.' bytes -->';
    }

    protected function minifyHTML($html) {
        $pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
        $overriding = false;
        $raw_tag = false;
        // Variable reused for output
        $html = '';
        foreach ($matches as $token) {
            $tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;
            $content = $token[0];
            if (is_null($tag)) {
                if ( !empty($token['script']) ) {
                    $strip = $this->ignoreJS;
                }
                else if ( !empty($token['style']) ) {
                    $strip = $this->ignoreCSS;
                }
                else if ($content == '<!-- html-compression no compression -->') {
                    $overriding = !$overriding;
                    // Don't print the comment
                    continue;
                }
                else if ( $this->ignoreComments ) {
                    if (!$overriding && $raw_tag != 'textarea') {
                        // Remove any HTML comments, except MSIE conditional comments
                        $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
                    }
                }
            }
            else {
                if ($tag == 'pre' || $tag == 'textarea') {
                    $raw_tag = $tag;
                }
                else if ($tag == '/pre' || $tag == '/textarea') {
                    $raw_tag = false;
                }
                else {
                    if ($raw_tag || $overriding) {
                        $strip = false;
                    }
                    else {
                        $strip = true;
                        // Remove any empty attributes, except:
                        // action, alt, content, src
                        $content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc)="")/', '$1', $content);
                        // Remove any space before the end of self-closing XHTML tags
                        // JavaScript excluded
                        $content = str_replace(' />', '/>', $content);
                    }
                }
            }
            if ($strip) {
                $content = $this->removeWhiteSpace($content);
            }
            $html .= $content;
        }
        return $html;
    }

    public function parseHTML($html) {
        $this->html = $this->minifyHTML($html);
        if ($this->addBotComment) {
            $this->html .= "\n" . $this->bottomComment($html, $this->html);
        }
    }

    protected function removeWhiteSpace($str) {
        $str = str_replace("\t", ' ', $str);
        $str = str_replace("\n",  '', $str);
        $str = str_replace("\r",  '', $str);
        while (stristr($str, '  ')) {
            $str = str_replace('  ', ' ', $str);
        }
        return $str;
    }

}

?>