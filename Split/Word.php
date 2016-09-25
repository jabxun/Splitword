<?php
namespace Split;

use Split\Dict;
use Split\Character;
use Split\Constants\Dict as DictConst;

/**
 * 分词处理类.
 *
 * @author jabxun <jabxun@163.com>
 * @version 1.0
 */
class Word
{
    public static $text;
    public static $inCode;
    public static $lastType;
    public static $splitWord = [];
    public static $textLength;
    public static $nowWordGroup;
    public $finallyResult = [];
    public $tryMergeWord = false;
    public $newWordsArray = [];
    public $newWordsStr = '';
    public $toLowerCase = false;
    protected $simpleSplitResult = array();
    protected $newWords = [];


    public function __construct()
    {
        Dict::open();
    }


    /**
     * 分词核心方法
     *
     * @param string $text
     * @param string $charSet
     */
    public function toSplit($text, $charSet = 'utf-8')
    {
        self::$splitWord = $this->finallyResult = array();

        self::$text = Character::in($text, $charSet);
        self::$textLength = strlen(self::$text);
        self::$lastType = 0;

        while (self::$textLength > 0) {
            self::startFind();
        }
        if (!empty(self::$nowWordGroup)) {
            self::$splitWord[] = array('word' => self::$nowWordGroup, 'type' => self::$lastType);
        }
        
        $this->depFind();
    }

    /**
     * 开始查找.
     */
    public static function startFind()
    {
        self::$inCode = self::$text[--self::$textLength] . self::$text[--self::$textLength];
        $codeNum = hexdec(bin2hex(self::$inCode));
        if ($codeNum < 0X80) {
            self::findEnglish($codeNum);
        } else {
            self::findChinese($codeNum);
        }
    }

    /**
     * 中文查找.
     * 
     * @param integer $codeNum
     */
    public static function findChinese($codeNum)
    {
        if (DictConst::WORD_TYPE_CN != self::$lastType) {
            self::$splitWord[] = ['word' => self::$nowWordGroup, 'type' => self::$lastType];
            self::$nowWordGroup = '';
            self::$lastType = DictConst::WORD_TYPE_CN;
        }
        if (!isset(Dict::$assistDict['s'][self::$inCode]) && --self::$textLength > 0) {
            $wordTrieArr = Dict::getWordTree($codeNum);
            $nextInnerCode = self::$text[self::$textLength] . self::$text[--self::$textLength];
            if (isset($wordTrieArr[$nextInnerCode])) {
                if (--self::$textLength > 0) {
                    $nextInnerCode2 = self::$text[self::$textLength] . self::$text[--self::$textLength];
                    if (isset($wordTrieArr[$nextInnerCode][$nextInnerCode2])) {
                        if (--self::$textLength > 0) {
                            $nextInnerCode3 = self::$text[self::$textLength] . self::$text[--self::$textLength];
                            if (isset($wordTrieArr[$nextInnerCode][$nextInnerCode2][$nextInnerCode3])) {
                                self::$splitWord[] = ['word' => $nextInnerCode3 . $nextInnerCode2 . $nextInnerCode . self::$inCode, 'type' => DictConst::WORD_TYPE_CN];
                            } else {
                                self::$textLength += 2;
                                self::$splitWord[] = ['word' => $nextInnerCode2 . $nextInnerCode . self::$inCode, 'type' => DictConst::WORD_TYPE_CN];
                            }
                        } else {
                            if (isset($wordTrieArr[$nextInnerCode][$nextInnerCode2][0])) {
                                self::$splitWord[] = ['word' => $nextInnerCode2 . $nextInnerCode . self::$inCode, 'type' => DictConst::WORD_TYPE_CN];
                            } else {
                                if (isset($wordTrieArr[$nextInnerCode][0])) {
                                    self::$textLength += 2;
                                    self::$splitWord[] = ['word' => $nextInnerCode . self::$inCode, 'type' => 1];
                                } else {
                                    self::$textLength += 4;
                                    self::$splitWord[] = ['word' => self::$inCode, 'type' => 1];
                                }
                            }
                        }
                    } else {
                        if (isset($wordTrieArr[$nextInnerCode][0])) {
                            self::$textLength += 2;
                            self::$splitWord[] = ['word' => $nextInnerCode . self::$inCode, 'type' => DictConst::WORD_TYPE_CN];
                        } else {
                            self::$textLength += 4;
                            self::$splitWord[] = ['word' => self::$inCode, 'type' => DictConst::WORD_TYPE_CN];
                        }
                    }
                } else {
                    if (isset($wordTrieArr[$nextInnerCode][0])) {
                        self::$splitWord[] = ['word' => $nextInnerCode . self::$inCode, 'type' => DictConst::WORD_TYPE_CN];
                    } else {
                        self::$textLength += 2;
                        self::$splitWord[] = ['word' => self::$inCode, 'type' => DictConst::WORD_TYPE_CN];
                    }
                }
            } else {
                self::$textLength += 2;
                self::$splitWord[] = ['word' => self::$inCode, 'type' => DictConst::WORD_TYPE_CN];
            }
        } else {
            self::$splitWord[] = ['word' => self::$inCode, 'type' => DictConst::WORD_TYPE_CN];
        }
    }

    /**
     * 查找英文.
     * 
     * @param $codeNum
     */
    public static function findEnglish($codeNum)
    {
        switch (Dict::$asciiDict[$codeNum]) {
            case DictConst::WORD_TYPE_NO :
                if (! empty(self::$nowWordGroup)) {
                    self::$splitWord[] = ['word' => self::$nowWordGroup, 'type' => self::$lastType];
                    self::$nowWordGroup = '';
                }
                break;
            case DictConst::WORD_TYPE_NU :
                if (! empty(self::$nowWordGroup) && self::$lastType != DictConst::WORD_TYPE_NU) {
                    self::$splitWord[] = ['word' => self::$nowWordGroup, 'type' => self::$lastType];
                    self::$nowWordGroup = '';
                }
                self::$lastType = DictConst::WORD_TYPE_NU;
                self::$nowWordGroup = chr(0).chr($codeNum).self::$nowWordGroup;
                break;

            case DictConst::WORD_TYPE_EN :
                if (! empty(self::$nowWordGroup) && self::$lastType != DictConst::WORD_TYPE_EN) {
                    self::$splitWord[] = ['word' => self::$nowWordGroup, 'type' => self::$lastType];
                    self::$nowWordGroup = '';
                }
                self::$lastType = DictConst::WORD_TYPE_EN;
                self::$nowWordGroup = chr(0).chr($codeNum).self::$nowWordGroup;
                break;
            case DictConst::WORD_TYPE_PU :
                if (! empty(self::$nowWordGroup) && self::$lastType != DictConst::WORD_TYPE_PU) {
                    self::$splitWord[] = ['word' => self::$nowWordGroup, 'type' => self::$lastType];
                    self::$nowWordGroup = '';
                }
                self::$lastType = DictConst::WORD_TYPE_PU;
                self::$nowWordGroup = chr(0).chr($codeNum).self::$nowWordGroup;
                break;
        }
    }

    /**
     * 深度查找.
     */
    public function depFind()
    {
        $splitArraySize = count(self::$splitWord);
        while (--$splitArraySize > 0) {
            if (1 == self::$splitWord[$splitArraySize]['type'] && $this->tryMergeWord) {
                if (1 == strlen(self::$splitWord[$splitArraySize]['word'])) {
                    self::$nowWordGroup = self::$splitWord[$splitArraySize]['word'];
                    if (isset(Dict::$assistDict['b'][self::$nowWordGroup]) && !isset(Dict::$assistDict['s'][self::$nowWordGroup])) {
                        if (isset(self::$splitWord[--$splitArraySize]) && !isset(Dict::$assistDict['s'][self::$splitWord[$splitArraySize]['word']])) {
                            if (1 == strlen(self::$splitWord[$splitArraySize]['word'])) {
                                self::$nowWordGroup .= self::$splitWord[$splitArraySize]['word'];
                                unset(self::$splitWord[$splitArraySize]);
                                self::$splitWord[$splitArraySize + 1]['word'] = '';
                                if (isset(self::$splitWord[--$splitArraySize]) && 1 == strlen(self::$splitWord[$splitArraySize]['word']) && !isset(Dict::$assistDict['s'][self::$splitWord[$splitArraySize]['word']])) {
                                    self::$nowWordGroup .= self::$splitWord[$splitArraySize]['word'];
                                    unset(self::$splitWord[$splitArraySize]);
                                    if (!in_array(self::$nowWordGroup, $this->newWords)) {
                                        $this->newWords[] = self::$nowWordGroup;
                                    }
                                    self::$splitWord[$splitArraySize + 1]['word'] = self::$nowWordGroup;
                                } else {
                                    ++$splitArraySize;
                                    if (!in_array(self::$nowWordGroup, $this->newWords)) {
                                        $this->newWords[] = self::$nowWordGroup;
                                    }
                                    self::$splitWord[$splitArraySize + 1]['word'] = self::$nowWordGroup;
                                }
                            } elseif (2 == strlen(self::$splitWord[$splitArraySize]['word']) && !isset(Dict::$assistDict['b'][self::$splitWord[$splitArraySize]['word']])) {
                                self::$nowWordGroup .= self::$splitWord[$splitArraySize]['word'];
                                if (!in_array(self::$nowWordGroup, $this->newWords)) {
                                    $this->newWords[] = self::$nowWordGroup;
                                }
                                unset(self::$splitWord[$splitArraySize]);
                                self::$splitWord[$splitArraySize + 1]['word'] = self::$nowWordGroup;
                            }
                        } else {
                            ++$splitArraySize;
                            continue;
                        }
                    } elseif (isset(Dict::$assistDict['e'][self::$splitWord[$splitArraySize]['word']])) {
                        if (isset(self::$splitWord[++$splitArraySize]) && (!isset(Dict::$assistDict['s'][self::$splitWord[$splitArraySize]['word']]))) {
                            if (1 == self::$splitWord[$splitArraySize]['type'] && 1 == strlen(self::$splitWord[$splitArraySize]['word'])) {
                                self::$splitWord[$splitArraySize]['word'] .= self::$splitWord[$splitArraySize - 1]['word'];
                                if (!in_array(self::$splitWord[$splitArraySize]['word'], $this->newWords)) {
                                    $this->newWords[] = self::$splitWord[$splitArraySize]['word'];
                                }
                                self::$splitWord[$splitArraySize - 1]['word'] = '';
                            } elseif (3 == self::$splitWord[$splitArraySize]['type']) {
                                self::$splitWord[$splitArraySize]['word'] .= self::$splitWord[$splitArraySize - 1]['word'];
                                if (!in_array(self::$splitWord[$splitArraySize]['word'], $this->newWords)) {
                                    $this->newWords[] = self::$splitWord[$splitArraySize]['word'];
                                }
                                self::$splitWord[$splitArraySize - 1]['word'] = '';
                            }
                        }
                        --$splitArraySize;
                    }
                } elseif (2 == strlen(self::$splitWord[$splitArraySize]['word']) && isset(Dict::$assistDict['b'][self::$splitWord[$splitArraySize]['word']])) {
                    if (isset(self::$splitWord[--$splitArraySize]) && 1 == strlen(self::$splitWord[$splitArraySize]['word'])) {
                        self::$splitWord[$splitArraySize + 1]['word'] .= self::$splitWord[$splitArraySize]['word'];
                        unset(self::$splitWord[$splitArraySize]);
                        if (!in_array(self::$splitWord[$splitArraySize + 1]['word'], $this->newWords)) {
                            $this->newWords[] = self::$splitWord[$splitArraySize + 1]['word'];
                        }
                    } elseif (2 == strlen(self::$splitWord[$splitArraySize]['word'])) {
                    }
                }
            } elseif (3 == self::$splitWord[$splitArraySize]['type'] && $this->toLowerCase) {
                self::$splitWord[$splitArraySize]['word'] = strtolower(self::$splitWord[$splitArraySize]['word']);
            }
        }
    }

    /**
     * 获取分词后用分隔符分隔的字符串.
     *
     * @param string $splitSep 输出结果分隔符
     * @param string $charSet  输出结果字符集
     * @return string
     */
    public function GetFinallyResult($splitSep = '', $charSet = 'utf-8')
    {
        $resetStr = '';
        $wordNum = count(self::$splitWord);
        foreach (self::$splitWord as $key => $word) {
            $word = Character::out($word['word']);
            $this->finallyResult[--$wordNum] = $word;
            $resetStr = $word . $splitSep . $resetStr;
        }
        self::$splitWord = array();
        foreach ($this->newWords as $v) {
            $temStr = Character::out($v, $charSet);
            $this->newWordsStr .= $temStr . $splitSep;
            $this->newWordsArray[] = $temStr;
        }
        $this->newWords = array();
        return $resetStr;
    }

    public function __destruct()
    {
        Dict::close();
    }
}
