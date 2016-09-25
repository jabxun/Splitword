<?php
namespace Split;

use Split\Character;
use Split\Constants\Dict as DictConst;
use Split\Constants\Character as CharConst;

/**
 * 分词依赖字典处理类.
 * 
 * @author jabxun <jabxun@163.com>
 * @version 1.0
 */

class Dict
{
    public static $assistDictInfo = [
        'b'=>'零,一,二,三,四,五,六,七,八,九,十,百,千,万,亿,第,半,几,俩,卅,两,壹,贰,叁,肆,伍,陆,柒,捌,玖,拾,伯,仟,陶,新,肖,胡,罗,程,施,满,石,秦,苏,范,包,袁,许,舒,薛,蒋,董,白,田,季,丁,汪,段,梁,林,杜,杨,毛,江,熊,王,潘,沈,汤,谢,谭,韩,顾,雷,陈,阎,陆,马,高,龙,龚,黎,黄,魏,钱,钟,赵,邓,赖,贾,贺,邱,邵,郭,金,郝,郑,邹,李,武,余,夏,唐,朱,何,姚,孟,孙,孔,姜,周,吴,卢,单,刘,冯,史,叶,吕,候,傅,宋,任,文,戴,徐,张,万,方,曾,曹,易,廖,彭,常,尹,乔,于,康,崔,布,钟离,令狐,公冶,公孙,闻人,鲜于,上官,仲孙,万俟,东方,闾丘,长孙,诸葛,申屠,皇甫,尉迟,濮阳,澹台,欧阳,慕容,淳于,宗政,宇文,司徒,轩辕,单于,赫连,司空,太叔,夏侯,司马,公羊,勿,成吉,埃,哈',
        'e'=>'u‰,℃,℉,网,院,法,毛,段,步,毫,池,滴,派,洲,款,次,桩,档,桌,桶,梯,楼,棵,炮,点,盏,盆,界,盒,盘,眼,画,男,环,版,片,班,瓣,生,瓶,案,格,族,方,斤,日,时,期,月,曲,斗,文,指,拳,拨,掌,排,丈,撮,本,朵,栋,柜,柄,栏,株,根,样,架,枪,条,束,村,杯,枝,枚,石,码,辈,辆,轮,连,通,里,部,遍,转,车,言,角,袋,课,起,路,趟,重,针,项,顷,顶,顿,颗,首,餐,页,集,锅,钱,钟,门,间,隅,队,行,节,筐,笔,筒,箱,篮,篓,篇,章,站,磅,碟,碗,种,科,窝,秒,簇,米,脚,股,群,船,艇,色,艘,罐,级,粒,类,组,维,缸,缕,招,支,发,双,厘,口,句,台,只,厅,卷,包,勺,匙,匹,升,区,叶,号,地,圈,圆,场,块,堆,坪,团,回,吨,名,拍,员,周,副,剑,代,付,件,伏,份,人,亩,世,下,两,个,串,伙,位,划,分,列,则,剂,刻,刀,出,倍,例,元,克,册,具,声,听,幅,帧,房,批,师,岁,尾,尺,局,层,届,手,壶,成,张,截,户,扇,年,度,座,尊,幢,室,寸,头,宗,字,孔,所,女,套,拉,家,处,折,天,把,夜,担,號,亿,万,千,萬语,署,苑,街,省,湖,乡,海,观,路,娃,山,阁,部,镇,江,河,厅,郡,厂,楼,园,区,党,井,亭,塔,县,家,市,弄,巷,寺,局,机,型,率',
        's'=>'》,《,【,】,。,并,让,才,上,被,把,近,而,是,为,由,等,合,子,除,均,很,也,称,还,分,据,后,向,经,对,但,只,则,设,靠,至,到,将,及,与,或,来,了,从,说,就,的,和,在,方,以,已,有,都,给,要',
    ];
    /**
     * @var array
     */
    public static $asciiDict = [];
    /**
     * @var array 辅助字典.
     */
    public static $assistDict = [];
    /**
     * @var array 字典数组.
     */
    public static $wordTree = [];
    /**
     * @var bool|object 主字典操作句柄.
     */
    private static $mainDictHandle = false;
    /**
     * 打开字典.
     */
    public static function open()
    {
        self::$mainDictHandle = fopen(self::getDictDir(), DictConst::MODE_READ);
        self::loadAssistDict();
        self::getAsciiDict();
    }

    /**
     * 从字典中查找词组信息.
     *
     * @param integer $key 字典索引
     * @return array
     */
    public static function findWordFromDict($key)
    {
        if (empty(self::$mainDictHandle)) {
            self::open();
        }
        fseek(self::$mainDictHandle, $key << 3, SEEK_SET);
        $wordIndex = unpack('I1s/n1l/n1c', fread(self::$mainDictHandle, 8));
        if ($wordIndex['l'] == 0) {
            return [];
        }
        fseek(self::$mainDictHandle, $wordIndex['s'], SEEK_SET);

        return  @unserialize(fread(self::$mainDictHandle, $wordIndex['l']));
    }
    /**
     * 获取词组索引树.
     *
     * @param integer $key 字转的integer
     * @return array
     */
    public static function getWordTree($key)
    {
        if (isset(Dict::$wordTree[$key])) {
            return Dict::$wordTree[$key];
        }

        self::$wordTree[$key] = self::findWordFromDict($key);
        
        return self::$wordTree[$key];
    }

    /**
     * 关闭字典.
     */
    public static function close()
    {
        if (self::$mainDictHandle !== false) {
            @fclose(self::$mainDictHandle);
        }
    }

    /**
     * 载入辅助字典.
     */
    public static function loadAssistDict()
    {
        foreach (self::$assistDictInfo as $attr => $text) {
            $words = Character::arrayIn(explode(',', $text), CharConst::SUPPORT_CHARSET_UTF8, CharConst::CHARSET_OUT);
            foreach ($words as $w) {
                self::$assistDict[$attr][$w] = 1;
            }
        }
    }

    /**
     * 获取主字典路径.
     *
     * @return string
     */
    public static function getDictDir()
    {
        return dirname(__FILE__).DIRECTORY_SEPARATOR.'dict/base.dic';
    }

    /**
     * 全角半角转换关系
     *
     * @return array
     */
    public static function scbRelation()
    {
        $scbCaseBinary = 0x20;
        for ($i = 0xFF00; $i < 0xFF5F; ++$i) {
            $sbcArr[$i] = $scbCaseBinary++;
        }

        return $sbcArr;
    }
    /**
     * 获取辅助词典
     *
     * @return array
     */
    public static function getAssistDict()
    {
        if (empty(self::$assistDict)) {
            self::loadAssistDict();
        }
        return self::$assistDict;
    }

    public static function getAsciiDict()
    {
        for ($codeNum = DictConst::WORD_EN_START; $codeNum < DictConst::WORD_EN_ENDED; ++$codeNum) {
            if ($codeNum > DictConst::WORD_EN_NU_START && $codeNum < DictConst::WORD_EN_NU_ENDED) {
                self::$asciiDict[$codeNum] = DictConst::WORD_TYPE_NU;
            } elseif ($codeNum > DictConst::WORD_EN_UP_START && $codeNum < DictConst::WORD_EN_UP_ENDED) {
                self::$asciiDict[$codeNum] = DictConst::WORD_TYPE_EN;
            } elseif ($codeNum > DictConst::WORD_EN_LOW_START && $codeNum < DictConst::WORD_EN_LOW_ENDED) {
                self::$asciiDict[$codeNum] = DictConst::WORD_TYPE_EN;
            } elseif ($codeNum < DictConst::WORD_EN_NO_ENDED) {
                self::$asciiDict[$codeNum] = DictConst::WORD_TYPE_NO;
            } else {
                self::$asciiDict[$codeNum] = DictConst::WORD_TYPE_PU;
            }
        }
    }
    /**
     * @param string $inputFile 字典源文件路径
     * @param string $targetFile 编译目标路径
     * @return bool
     */
    public static function MakeDict($inputFile, $targetFile = '')
    {
        $targetFile = empty($targetFile) ? $targetFile : self::getDictDir();
        $trieArray = array();
        if (!file_exists($inputFile)) {
            return false;
        }
        $fp = fopen($inputFile, 'r');
        while ($line = fgets($fp, 128)) {
            if ($line == '') {
                continue;
            }
            list($word, $rate) = explode(',', $line);
            $word = Character::in($word, 'utf-8');
            $len = strlen($word);
            $lastChrInCode = $word[--$len].$word[--$len];
            $chrInCodeNum = hexdec(bin2hex($lastChrInCode));
            switch ($len) {
                case 6:
                    $trieArray[$chrInCodeNum][$word[--$len].$word[--$len]][$word[--$len].$word[--$len]][$word[--$len].$word[--$len]] = $rate;
                    break;
                case 4:
                    $trieArray[$chrInCodeNum][$word[--$len].$word[--$len]][$word[--$len].$word[--$len]][0] = $rate;
                    break;
                case 2:
                    $trieArray[$chrInCodeNum][$word[--$len].$word[--$len]][0][0] = $rate;
                    break;
            }
        }
        fclose($fp);
        $fp = fopen($targetFile, 'w');
        $allData = '';
        $startPosition = DictConst::WORD_INDEX_MASK << 3;

        for ($i=0; $i < DictConst::WORD_INDEX_MASK; $i++) {
            if (!isset($trieArray[$i])) {
                $indexArray = array(0, 0, 0);
            } else {
                $theIndexData  = serialize($trieArray[$i]);
                $dataLength = strlen($theIndexData);
                $allData .= $theIndexData;
                $indexArray = array($startPosition,$dataLength,count($trieArray[$i]));
                $startPosition += $dataLength;
            }
            fwrite($fp, pack("Inn", $indexArray[0], $indexArray[1], $indexArray[2]));
        }
        unset($trieArray);
        fwrite($fp, $allData);
        fclose($fp);
        return true;
    }
}
