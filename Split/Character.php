<?php
namespace Split;

use Split\Constants\Character as CharConst;

/**
 * 字符操作类.
 *
 * @author jabxun <jabxun@163.com>
 * @version 1.0
 */
class Character
{
    /**
     * 输入字符转码.
     * 
     * @param string $text      输入字符
     * @param string $charSet   字符编码
     * @param string $toCharSet 转换的编码字符
     * @return bool|string
     */
    public static function in($text, $charSet = CharConst::SUPPORT_CHARSET_UTF8, $toCharSet = CharConst::CHARSET_IN)
    {
        $charSet = strtolower($charSet);

        if (! in_array($toCharSet, [CharConst::CHARSET_IN, CharConst::CHARSET_OUT])) {
            return false;
        }

        if (preg_match("/^utf/", $charSet)) {
            return iconv(CharConst::SUPPORT_CHARSET_UTF8, $toCharSet, $text);
        } elseif (preg_match("/^gb/", $charSet)) {
            return iconv(CharConst::SUPPORT_CHARSET_UTF8, $toCharSet, iconv(CharConst::SUPPORT_CHARSET_GBK, CharConst::SUPPORT_CHARSET_UTF8, $text));
        } elseif (preg_match("/^big/", $charSet)) {
            return iconv(CharConst::SUPPORT_CHARSET_UTF8, $toCharSet, iconv(CharConst::SUPPORT_CHARSET_BIG5, CharConst::SUPPORT_CHARSET_UTF8, $text));
        }
        
        return false;
    }

    /**
     * 输出字符转码.
     * 
     * @param string $text      输出字符
     * @param string $charSet   字符编码
     * @return bool|string
     */
    public static function out($text, $charSet = 'utf-8')
    {
        $charSet = strtolower($charSet);

        if (preg_match("/^utf/", $charSet)) {
            return iconv(CharConst::CHARSET_OUT, CharConst::SUPPORT_CHARSET_UTF8, $text);
        } elseif (preg_match("/^gb/", $charSet)) {
            return  iconv(
                CharConst::SUPPORT_CHARSET_UTF8,
                CharConst::SUPPORT_CHARSET_GBK,
                iconv(CharConst::CHARSET_OUT, CharConst::SUPPORT_CHARSET_UTF8, $text)
            );
        } elseif (preg_match("/^big/", $charSet)) {
            return iconv(
                CharConst::SUPPORT_CHARSET_UTF8,
                CharConst::SUPPORT_CHARSET_BIG5,
                iconv(CharConst::CHARSET_OUT, CharConst::SUPPORT_CHARSET_UTF8, $text)
            );
        }
        
        return false;
    }

    /**
     * 批量输入字符转码.
     * 
     * @param array $array      词组字符输入
     * @param string $charSet   载入字符集
     * @param string $toCharSet 转换字符集
     * @return array
     */
    public static function arrayIn($array, $charSet = CharConst::SUPPORT_CHARSET_UTF8, $toCharSet = CharConst::CHARSET_IN)
    {
        $separator = chr(0xFE).chr(0xFF);
        
        $text = join(iconv($toCharSet, $charSet, $separator), $array);
        $text = Character::in($text, $charSet, $toCharSet);
        
        if (empty($text)) {
            return [];
        }
        
        return explode($separator, $text);
    }
}
