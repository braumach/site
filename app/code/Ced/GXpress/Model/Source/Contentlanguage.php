<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_GXpress
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GXpress\Model\Source;

class Contentlanguage extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions()
    {
        return array(
            Array
            (
                'value' => '',
                'label' => __('-- Please Select Content Language--')
            ),
            Array
            (
                'value' => 'aa',
                'label' => __('Afar')
            ),
            Array
            (
                'value' => 'ab',
                'label' => __('Abkhazian')
            ),
            Array
            (
                'value' => 'ace',
                'label' => __('Achinese')
            ),
            Array
            (
                'value' => 'ach',
                'label' => __('Acoli')
            ),
            Array
            (
                'value' => 'ada',
                'label' => __('Adangme')
            ),
            Array
            (
                'value' => 'ady',
                'label' => __('Adyghe')
            ),
            Array
            (
                'value' => 'ae',
                'label' => __('Avestan')
            ),
            Array
            (
                'value' => 'aeb',
                'label' => __('Tunisian Arabic')
            ),
            Array
            (
                'value' => 'af',
                'label' => __('Afrikaans')
            ),
            Array
            (
                'value' => 'afh',
                'label' => __('Afrihili')
            ),
            Array
            (
                'value' => 'agq',
                'label' => __('Aghem')
            ),
            Array
            (
                'value' => 'ain',
                'label' => __('Ainu')
            ),
            Array
            (
                'value' => 'ak',
                'label' => __('Akan')
            ),
            Array
            (
                'value' => 'akk',
                'label' => __('Akkadian')
            ),
            Array
            (
                'value' => 'akz',
                'label' => __('Alabama')
            ),
            Array
            (
                'value' => 'ale',
                'label' => __('Aleut')
            ),
            Array
            (
                'value' => 'aln',
                'label' => __('Gheg Albanian')
            ),
            Array
            (
                'value' => 'alt',
                'label' => __('Southern Altai')
            ),
            Array
            (
                'value' => 'am',
                'label' => __('Amharic')
            ),
            Array
            (
                'value' => 'an',
                'label' => __('Aragonese')
            ),
            Array
            (
                'value' => 'ang',
                'label' => __('Old English')
            ),
            Array
            (
                'value' => 'anp',
                'label' => __('Angika')
            ),
            Array
            (
                'value' => 'ar',
                'label' => __('Arabic')
            ),
            Array
            (
                'value' => 'ar_001',
                'label' => __('Modern Standard Arabic')
            ),
            Array
            (
                'value' => 'arc',
                'label' => __('Aramaic')
            ),
            Array
            (
                'value' => 'arn',
                'label' => __('Mapuche')
            ),
            Array
            (
                'value' => 'aro',
                'label' => __('Araona')
            ),
            Array
            (
                'value' => 'arp',
                'label' => __('Arapaho')
            ),
            Array
            (
                'value' => 'arq',
                'label' => __('Algerian Arabic')
            ),
            Array
            (
                'value' => 'ars',
                'label' => __('Najdi Arabic')
            ),
            Array
            (
                'value' => 'arw',
                'label' => __('Arawak')
            ),
            Array
            (
                'value' => 'ary',
                'label' => __('Moroccan Arabic')
            ),
            Array
            (
                'value' => 'arz',
                'label' => __('Egyptian Arabic')
            ),
            Array
            (
                'value' => 'as',
                'label' => __('Assamese')
            ),
            Array
            (
                'value' => 'asa',
                'label' => __('Asu')
            ),
            Array
            (
                'value' => 'ase',
                'label' => __('American Sign Language')
            ),
            Array
            (
                'value' => 'ast',
                'label' => __('Asturian')
            ),
            Array
            (
                'value' => 'av',
                'label' => __('Avaric')
            ),
            Array
            (
                'value' => 'avk',
                'label' => __('Kotava')
            ),
            Array
            (
                'value' => 'awa',
                'label' => __('Awadhi')
            ),
            Array
            (
                'value' => 'ay',
                'label' => __('Aymara')
            ),
            Array
            (
                'value' => 'az',
                'label' => __('Azerbaijani')
            ),
            Array
            (
                'value' => 'ba',
                'label' => __('Bashkir')
            ),
            Array
            (
                'value' => 'bal',
                'label' => __('Baluchi')
            ),
            Array
            (
                'value' => 'ban',
                'label' => __('Balinese')
            ),
            Array
            (
                'value' => 'bar',
                'label' => __('Bavarian')
            ),
            Array
            (
                'value' => 'bas',
                'label' => __('Basaa')
            ),
            Array
            (
                'value' => 'bax',
                'label' => __('Bamun')
            ),
            Array
            (
                'value' => 'bbc',
                'label' => __('Batak Toba')
            ),
            Array
            (
                'value' => 'bbj',
                'label' => __('Ghomala')
            ),
            Array
            (
                'value' => 'be',
                'label' => __('Belarusian')
            ),
            Array
            (
                'value' => 'bej',
                'label' => __('Beja')
            ),
            Array
            (
                'value' => 'bem',
                'label' => __('Bemba')
            ),
            Array
            (
                'value' => 'bew',
                'label' => __('Betawi')
            ),
            Array
            (
                'value' => 'bez',
                'label' => __('Bena')
            ),
            Array
            (
                'value' => 'bfd',
                'label' => __('Bafut')
            ),
            Array
            (
                'value' => 'bfq',
                'label' => __('Badaga')
            ),
            Array
            (
                'value' => 'bg',
                'label' => __('Bulgarian')
            ),
            Array
            (
                'value' => 'bgn',
                'label' => __('Western Balochi')
            ),
            Array
            (
                'value' => 'bho',
                'label' => __('Bhojpuri')
            ),
            Array
            (
                'value' => 'bi',
                'label' => __('Bislama')
            ),
            Array
            (
                'value' => 'bik',
                'label' => __('Bikol')
            ),
            Array
            (
                'value' => 'bin',
                'label' => __('Bini')
            ),
            Array
            (
                'value' => 'bjn',
                'label' => __('Banjar')
            ),
            Array
            (
                'value' => 'bkm',
                'label' => __('Kom')
            ),
            Array
            (
                'value' => 'bla',
                'label' => __('Siksika')
            ),
            Array
            (
                'value' => 'bm',
                'label' => __('Bambara')
            ),
            Array
            (
                'value' => 'bn',
                'label' => __('Bangla')
            ),
            Array
            (
                'value' => 'bo',
                'label' => __('Tibetan')
            ),
            Array
            (
                'value' => 'bpy',
                'label' => __('Bishnupriya')
            ),
            Array
            (
                'value' => 'bqi',
                'label' => __('Bakhtiari')
            ),
            Array
            (
                'value' => 'br',
                'label' => __('Breton')
            ),
            Array
            (
                'value' => 'bra',
                'label' => __('Braj')
            ),
            Array
            (
                'value' => 'brh',
                'label' => __('Brahui')
            ),
            Array
            (
                'value' => 'brx',
                'label' => __('Bodo')
            ),
            Array
            (
                'value' => 'bs',
                'label' => __('Bosnian')
            ),
            Array
            (
                'value' => 'bss',
                'label' => __('Akoose')
            ),
            Array
            (
                'value' => 'bua',
                'label' => __('Buriat')
            ),
            Array
            (
                'value' => 'bug',
                'label' => __('Buginese')
            ),
            Array
            (
                'value' => 'bum',
                'label' => __('Bulu')
            ),
            Array
            (
                'value' => 'byn',
                'label' => __('Blin')
            ),
            Array
            (
                'value' => 'byv',
                'label' => __('Medumba')
            ),
            Array
            (
                'value' => 'ca',
                'label' => __('Catalan')
            ),
            Array
            (
                'value' => 'cad',
                'label' => __('Caddo')
            ),
            Array
            (
                'value' => 'car',
                'label' => __('Carib')
            ),
            Array
            (
                'value' => 'cay',
                'label' => __('Cayuga')
            ),
            Array
            (
                'value' => 'cch',
                'label' => __('Atsam')
            ),
            Array
            (
                'value' => 'ccp',
                'label' => __('Chakma')
            ),
            Array
            (
                'value' => 'ce',
                'label' => __('Chechen')
            ),
            Array
            (
                'value' => 'ceb',
                'label' => __('Cebuano')
            ),
            Array
            (
                'value' => 'cgg',
                'label' => __('Chiga')
            ),
            Array
            (
                'value' => 'ch',
                'label' => __('Chamorro')
            ),
            Array
            (
                'value' => 'chb',
                'label' => __('Chibcha')
            ),
            Array
            (
                'value' => 'chg',
                'label' => __('Chagatai')
            ),
            Array
            (
                'value' => 'chk',
                'label' => __('Chuukese')
            ),
            Array
            (
                'value' => 'chm',
                'label' => __('Mari')
            ),
            Array
            (
                'value' => 'chn',
                'label' => __('Chinook Jargon')
            ),
            Array
            (
                'value' => 'cho',
                'label' => __('Choctaw')
            ),
            Array
            (
                'value' => 'chp',
                'label' => __('Chipewyan')
            ),
            Array
            (
                'value' => 'chr',
                'label' => __('Cherokee')
            ),
            Array
            (
                'value' => 'chy',
                'label' => __('Cheyenne')
            ),
            Array
            (
                'value' => 'ckb',
                'label' => __('Central Kurdish')
            ),
            Array
            (
                'value' => 'co',
                'label' => __('Corsican')
            ),
            Array
            (
                'value' => 'cop',
                'label' => __('Coptic')
            ),
            Array
            (
                'value' => 'cps',
                'label' => __('Capiznon')
            ),
            Array
            (
                'value' => 'cr',
                'label' => __('Cree')
            ),
            Array
            (
                'value' => 'crh',
                'label' => __('Crimean Turkish')
            ),
            Array
            (
                'value' => 'crs',
                'label' => __('Seselwa Creole French')
            ),
            Array
            (
                'value' => 'cs',
                'label' => __('Czech')
            ),
            Array
            (
                'value' => 'csb',
                'label' => __('Kashubian')
            ),
            Array
            (
                'value' => 'cu',
                'label' => __('Church Slavic')
            ),
            Array
            (
                'value' => 'cv',
                'label' => __('Chuvash')
            ),
            Array
            (
                'value' => 'cy',
                'label' => __('Welsh')
            ),
            Array
            (
                'value' => 'da',
                'label' => __('Danish')
            ),
            Array
            (
                'value' => 'dak',
                'label' => __('Dakota')
            ),
            Array
            (
                'value' => 'dar',
                'label' => __('Dargwa')
            ),
            Array
            (
                'value' => 'dav',
                'label' => __('Taita')
            ),
            Array
            (
                'value' => 'de',
                'label' => __('German')
            ),
            Array
            (
                'value' => 'de_AT',
                'label' => __('Austrian German')
            ),
            Array
            (
                'value' => 'de_CH',
                'label' => __('Swiss High German')
            ),
            Array
            (
                'value' => 'del',
                'label' => __('Delaware')
            ),
            Array
            (
                'value' => 'den',
                'label' => __('Slave')
            ),
            Array
            (
                'value' => 'dgr',
                'label' => __('Dogrib')
            ),
            Array
            (
                'value' => 'din',
                'label' => __('Dinka')
            ),
            Array
            (
                'value' => 'dje',
                'label' => __('Zarma')
            ),
            Array
            (
                'value' => 'doi',
                'label' => __('Dogri')
            ),
            Array
            (
                'value' => 'dsb',
                'label' => __('Lower Sorbian')
            ),
            Array
            (
                'value' => 'dtp',
                'label' => __('Central Dusun')
            ),
            Array
            (
                'value' => 'dua',
                'label' => __('Duala')
            ),
            Array
            (
                'value' => 'dum',
                'label' => __('Middle Dutch')
            ),
            Array
            (
                'value' => 'dv',
                'label' => __('Divehi')
            ),
            Array
            (
                'value' => 'dyo',
                'label' => __('Jola-Fonyi')
            ),
            Array
            (
                'value' => 'dyu',
                'label' => __('Dyula')
            ),
            Array
            (
                'value' => 'dz',
                'label' => __('Dzongkha')
            ),
            Array
            (
                'value' => 'dzg',
                'label' => __('Dazaga')
            ),
            Array
            (
                'value' => 'ebu',
                'label' => __('Embu')
            ),
            Array
            (
                'value' => 'ee',
                'label' => __('Ewe')
            ),
            Array
            (
                'value' => 'efi',
                'label' => __('Efik')
            ),
            Array
            (
                'value' => 'egl',
                'label' => __('Emilian')
            ),
            Array
            (
                'value' => 'egy',
                'label' => __('Ancient Egyptian')
            ),
            Array
            (
                'value' => 'eka',
                'label' => __('Ekajuk')
            ),
            Array
            (
                'value' => 'el',
                'label' => __('Greek')
            ),
            Array
            (
                'value' => 'elx',
                'label' => __('Elamite')
            ),
            Array
            (
                'value' => 'en',
                'label' => __('English')
            ),
            Array
            (
                'value' => 'en_AU',
                'label' => __('Australian English')
            ),
            Array
            (
                'value' => 'en_CA',
                'label' => __('Canadian English')
            ),
            Array
            (
                'value' => 'en_GB',
                'label' => __('British English')
            ),
            Array
            (
                'value' => 'en_US',
                'label' => __('American English')
            ),
            Array
            (
                'value' => 'enm',
                'label' => __('Middle English')
            ),
            Array
            (
                'value' => 'eo',
                'label' => __('Esperanto')
            ),
            Array
            (
                'value' => 'es',
                'label' => __('Spanish')
            ),
            Array
            (
                'value' => 'es_419',
                'label' => __('Latin American Spanish')
            ),
            Array
            (
                'value' => 'es_ES',
                'label' => __('European Spanish')
            ),
            Array
            (
                'value' => 'es_MX',
                'label' => __('Mexican Spanish')
            ),
            Array
            (
                'value' => 'esu',
                'label' => __('Central Yupik')
            ),
            Array
            (
                'value' => 'et',
                'label' => __('Estonian')
            ),
            Array
            (
                'value' => 'eu',
                'label' => __('Basque')
            ),
            Array
            (
                'value' => 'ewo',
                'label' => __('Ewondo')
            ),
            Array
            (
                'value' => 'ext',
                'label' => __('Extremaduran')
            ),
            Array
            (
                'value' => 'fa',
                'label' => __('Persian')
            ),
            Array
            (
                'value' => 'fa_AF',
                'label' => __('Dari')
            ),
            Array
            (
                'value' => 'fan',
                'label' => __('Fang')
            ),
            Array
            (
                'value' => 'fat',
                'label' => __('Fanti')
            ),
            Array
            (
                'value' => 'ff',
                'label' => __('Fulah')
            ),
            Array
            (
                'value' => 'fi',
                'label' => __('Finnish')
            ),
            Array
            (
                'value' => 'fil',
                'label' => __('Filipino')
            ),
            Array
            (
                'value' => 'fit',
                'label' => __('Tornedalen Finnish')
            ),
            Array
            (
                'value' => 'fj',
                'label' => __('Fijian')
            ),
            Array
            (
                'value' => 'fo',
                'label' => __('Faroese')
            ),
            Array
            (
                'value' => 'fon',
                'label' => __('Fon')
            ),
            Array
            (
                'value' => 'fr',
                'label' => __('French')
            ),
            Array
            (
                'value' => 'fr_CA',
                'label' => __('Canadian French')
            ),
            Array
            (
                'value' => 'fr_CH',
                'label' => __('Swiss French')
            ),
            Array
            (
                'value' => 'frc',
                'label' => __('Cajun French')
            ),
            Array
            (
                'value' => 'frm',
                'label' => __('Middle French')
            ),
            Array
            (
                'value' => 'fro',
                'label' => __('Old French')
            ),
            Array
            (
                'value' => 'frp',
                'label' => __('Arpitan')
            ),
            Array
            (
                'value' => 'frr',
                'label' => __('Northern Frisian')
            ),
            Array
            (
                'value' => 'frs',
                'label' => __('Eastern Frisian')
            ),
            Array
            (
                'value' => 'fur',
                'label' => __('Friulian')
            ),
            Array
            (
                'value' => 'fy',
                'label' => __('Western Frisian')
            ),
            Array
            (
                'value' => 'ga',
                'label' => __('Irish')
            ),
            Array
            (
                'value' => 'gaa',
                'label' => __('Ga')
            ),
            Array
            (
                'value' => 'gag',
                'label' => __('Gagauz')
            ),
            Array
            (
                'value' => 'gan',
                'label' => __('Gan Chinese')
            ),
            Array
            (
                'value' => 'gay',
                'label' => __('Gayo')
            ),
            Array
            (
                'value' => 'gba',
                'label' => __('Gbaya')
            ),
            Array
            (
                'value' => 'gbz',
                'label' => __('Zoroastrian Dari')
            ),
            Array
            (
                'value' => 'gd',
                'label' => __('Scottish Gaelic')
            ),
            Array
            (
                'value' => 'gez',
                'label' => __('Geez')
            ),
            Array
            (
                'value' => 'gil',
                'label' => __('Gilbertese')
            ),
            Array
            (
                'value' => 'gl',
                'label' => __('Galician')
            ),
            Array
            (
                'value' => 'glk',
                'label' => __('Gilaki')
            ),
            Array
            (
                'value' => 'gmh',
                'label' => __('Middle High German')
            ),
            Array
            (
                'value' => 'gn',
                'label' => __('Guarani')
            ),
            Array
            (
                'value' => 'goh',
                'label' => __('Old High German')
            ),
            Array
            (
                'value' => 'gom',
                'label' => __('Goan Konkani')
            ),
            Array
            (
                'value' => 'gon',
                'label' => __('Gondi')
            ),
            Array
            (
                'value' => 'gor',
                'label' => __('Gorontalo')
            ),
            Array
            (
                'value' => 'got',
                'label' => __('Gothic')
            ),
            Array
            (
                'value' => 'grb',
                'label' => __('Grebo')
            ),
            Array
            (
                'value' => 'grc',
                'label' => __('Ancient Greek')
            ),
            Array
            (
                'value' => 'gsw',
                'label' => __('Swiss German')
            ),
            Array
            (
                'value' => 'gu',
                'label' => __('Gujarati')
            ),
            Array
            (
                'value' => 'guc',
                'label' => __('Wayuu')
            ),
            Array
            (
                'value' => 'gur',
                'label' => __('Frafra')
            ),
            Array
            (
                'value' => 'guz',
                'label' => __('Gusii')
            ),
            Array
            (
                'value' => 'gv',
                'label' => __('Manx')
            ),
            Array
            (
                'value' => 'gwi',
                'label' => __('Gwichʼin')
            ),
            Array
            (
                'value' => 'ha',
                'label' => __('Hausa')
            ),
            Array
            (
                'value' => 'hai',
                'label' => __('Haida')
            ),
            Array
            (
                'value' => 'hak',
                'label' => __('Hakka Chinese')
            ),
            Array
            (
                'value' => 'haw',
                'label' => __('Hawaiian')
            ),
            Array
            (
                'value' => 'he',
                'label' => __('Hebrew')
            ),
            Array
            (
                'value' => 'hi',
                'label' => __('Hindi')
            ),
            Array
            (
                'value' => 'hif',
                'label' => __('Fiji Hindi')
            ),
            Array
            (
                'value' => 'hil',
                'label' => __('Hiligaynon')
            ),
            Array
            (
                'value' => 'hit',
                'label' => __('Hittite')
            ),
            Array
            (
                'value' => 'hmn',
                'label' => __('Hmong')
            ),
            Array
            (
                'value' => 'ho',
                'label' => __('Hiri Motu')
            ),
            Array
            (
                'value' => 'hr',
                'label' => __('Croatian')
            ),
            Array
            (
                'value' => 'hsb',
                'label' => __('Upper Sorbian')
            ),
            Array
            (
                'value' => 'hsn',
                'label' => __('Xiang Chinese')
            ),
            Array
            (
                'value' => 'ht',
                'label' => __('Haitian Creole')
            ),
            Array
            (
                'value' => 'hu',
                'label' => __('Hungarian')
            ),
            Array
            (
                'value' => 'hup',
                'label' => __('Hupa')
            ),
            Array
            (
                'value' => 'hy',
                'label' => __('Armenian')
            ),
            Array
            (
                'value' => 'hz',
                'label' => __('Herero')
            ),
            Array
            (
                'value' => 'ia',
                'label' => __('Interlingua')
            ),
            Array
            (
                'value' => 'iba',
                'label' => __('Iban')
            ),
            Array
            (
                'value' => 'ibb',
                'label' => __('Ibibio')
            ),
            Array
            (
                'value' => 'id',
                'label' => __('Indonesian')
            ),
            Array
            (
                'value' => 'ie',
                'label' => __('Interlingue')
            ),
            Array
            (
                'value' => 'ig',
                'label' => __('Igbo')
            ),
            Array
            (
                'value' => 'ii',
                'label' => __('Sichuan Yi')
            ),
            Array
            (
                'value' => 'ik',
                'label' => __('Inupiaq')
            ),
            Array
            (
                'value' => 'ilo',
                'label' => __('Iloko')
            ),
            Array
            (
                'value' => 'inh',
                'label' => __('Ingush')
            ),
            Array
            (
                'value' => 'io',
                'label' => __('Ido')
            ),
            Array
            (
                'value' => 'is',
                'label' => __('Icelandic')
            ),
            Array
            (
                'value' => 'it',
                'label' => __('Italian')
            ),
            Array
            (
                'value' => 'iu',
                'label' => __('Inuktitut')
            ),
            Array
            (
                'value' => 'izh',
                'label' => __('Ingrian')
            ),
            Array
            (
                'value' => 'ja',
                'label' => __('Japanese')
            ),
            Array
            (
                'value' => 'jam',
                'label' => __('Jamaican Creole English')
            ),
            Array
            (
                'value' => 'jbo',
                'label' => __('Lojban')
            ),
            Array
            (
                'value' => 'jgo',
                'label' => __('Ngomba')
            ),
            Array
            (
                'value' => 'jmc',
                'label' => __('Machame')
            ),
            Array
            (
                'value' => 'jpr',
                'label' => __('Judeo-Persian')
            ),
            Array
            (
                'value' => 'jrb',
                'label' => __('Judeo-Arabic')
            ),
            Array
            (
                'value' => 'jut',
                'label' => __('Jutish')
            ),
            Array
            (
                'value' => 'jv',
                'label' => __('Javanese')
            ),
            Array
            (
                'value' => 'ka',
                'label' => __('Georgian')
            ),
            Array
            (
                'value' => 'kaa',
                'label' => __('Kara-Kalpak')
            ),
            Array
            (
                'value' => 'kab',
                'label' => __('Kabyle')
            ),
            Array
            (
                'value' => 'kac',
                'label' => __('Kachin')
            ),
            Array
            (
                'value' => 'kaj',
                'label' => __('Jju')
            ),
            Array
            (
                'value' => 'kam',
                'label' => __('Kamba')
            ),
            Array
            (
                'value' => 'kaw',
                'label' => __('Kawi')
            ),
            Array
            (
                'value' => 'kbd',
                'label' => __('Kabardian')
            ),
            Array
            (
                'value' => 'kbl',
                'label' => __('Kanembu')
            ),
            Array
            (
                'value' => 'kcg',
                'label' => __('Tyap')
            ),
            Array
            (
                'value' => 'kde',
                'label' => __('Makonde')
            ),
            Array
            (
                'value' => 'kea',
                'label' => __('Kabuverdianu')
            ),
            Array
            (
                'value' => 'ken',
                'label' => __('Kenyang')
            ),
            Array
            (
                'value' => 'kfo',
                'label' => __('Koro')
            ),
            Array
            (
                'value' => 'kg',
                'label' => __('Kongo')
            ),
            Array
            (
                'value' => 'kgp',
                'label' => __('Kaingang')
            ),
            Array
            (
                'value' => 'kha',
                'label' => __('Khasi')
            ),
            Array
            (
                'value' => 'kho',
                'label' => __('Khotanese')
            ),
            Array
            (
                'value' => 'khq',
                'label' => __('Koyra Chiini')
            ),
            Array
            (
                'value' => 'khw',
                'label' => __('Khowar')
            ),
            Array
            (
                'value' => 'ki',
                'label' => __('Kikuyu')
            ),
            Array
            (
                'value' => 'kiu',
                'label' => __('Kirmanjki')
            ),
            Array
            (
                'value' => 'kj',
                'label' => __('Kuanyama')
            ),
            Array
            (
                'value' => 'kk',
                'label' => __('Kazakh')
            ),
            Array
            (
                'value' => 'kkj',
                'label' => __('Kako')
            ),
            Array
            (
                'value' => 'kl',
                'label' => __('Kalaallisut')
            ),
            Array
            (
                'value' => 'kln',
                'label' => __('Kalenjin')
            ),
            Array
            (
                'value' => 'km',
                'label' => __('Khmer')
            ),
            Array
            (
                'value' => 'kmb',
                'label' => __('Kimbundu')
            ),
            Array
            (
                'value' => 'kn',
                'label' => __('Kannada')
            ),
            Array
            (
                'value' => 'ko',
                'label' => __('Korean')
            ),
            Array
            (
                'value' => 'koi',
                'label' => __('Komi-Permyak')
            ),
            Array
            (
                'value' => 'kok',
                'label' => __('Konkani')
            ),
            Array
            (
                'value' => 'kos',
                'label' => __('Kosraean')
            ),
            Array
            (
                'value' => 'kpe',
                'label' => __('Kpelle')
            ),
            Array
            (
                'value' => 'kr',
                'label' => __('Kanuri')
            ),
            Array
            (
                'value' => 'krc',
                'label' => __('Karachay-Balkar')
            ),
            Array
            (
                'value' => 'kri',
                'label' => __('Krio')
            ),
            Array
            (
                'value' => 'krj',
                'label' => __('Kinaray-a')
            ),
            Array
            (
                'value' => 'krl',
                'label' => __('Karelian')
            ),
            Array
            (
                'value' => 'kru',
                'label' => __('Kurukh')
            ),
            Array
            (
                'value' => 'ks',
                'label' => __('Kashmiri')
            ),
            Array
            (
                'value' => 'ksb',
                'label' => __('Shambala')
            ),
            Array
            (
                'value' => 'ksf',
                'label' => __('Bafia')
            ),
            Array
            (
                'value' => 'ksh',
                'label' => __('Colognian')
            ),
            Array
            (
                'value' => 'ku',
                'label' => __('Kurdish')
            ),
            Array
            (
                'value' => 'kum',
                'label' => __('Kumyk')
            ),
            Array
            (
                'value' => 'kut',
                'label' => __('Kutenai')
            ),
            Array
            (
                'value' => 'kv',
                'label' => __('Komi')
            ),
            Array
            (
                'value' => 'kw',
                'label' => __('Cornish')
            ),
            Array
            (
                'value' => 'ky',
                'label' => __('Kyrgyz')
            ),
            Array
            (
                'value' => 'la',
                'label' => __('Latin')
            ),
            Array
            (
                'value' => 'lad',
                'label' => __('Ladino')
            ),
            Array
            (
                'value' => 'lag',
                'label' => __('Langi')
            ),
            Array
            (
                'value' => 'lah',
                'label' => __('Lahnda')
            ),
            Array
            (
                'value' => 'lam',
                'label' => __('Lamba')
            ),
            Array
            (
                'value' => 'lb',
                'label' => __('Luxembourgish')
            ),
            Array
            (
                'value' => 'lez',
                'label' => __('Lezghian')
            ),
            Array
            (
                'value' => 'lfn',
                'label' => __('Lingua Franca Nova')
            ),
            Array
            (
                'value' => 'lg',
                'label' => __('Ganda')
            ),
            Array
            (
                'value' => 'li',
                'label' => __('Limburgish')
            ),
            Array
            (
                'value' => 'lij',
                'label' => __('Ligurian')
            ),
            Array
            (
                'value' => 'liv',
                'label' => __('Livonian')
            ),
            Array
            (
                'value' => 'lkt',
                'label' => __('Lakota')
            ),
            Array
            (
                'value' => 'lmo',
                'label' => __('Lombard')
            ),
            Array
            (
                'value' => 'ln',
                'label' => __('Lingala')
            ),
            Array
            (
                'value' => 'lo',
                'label' => __('Lao')
            ),
            Array
            (
                'value' => 'lol',
                'label' => __('Mongo')
            ),
            Array
            (
                'value' => 'lou',
                'label' => __('Louisiana Creole')
            ),
            Array
            (
                'value' => 'loz',
                'label' => __('Lozi')
            ),
            Array
            (
                'value' => 'lrc',
                'label' => __('Northern Luri')
            ),
            Array
            (
                'value' => 'lt',
                'label' => __('Lithuanian')
            ),
            Array
            (
                'value' => 'ltg',
                'label' => __('Latgalian')
            ),
            Array
            (
                'value' => 'lu',
                'label' => __('Luba-Katanga')
            ),
            Array
            (
                'value' => 'lua',
                'label' => __('Luba-Lulua')
            ),
            Array
            (
                'value' => 'lui',
                'label' => __('Luiseno')
            ),
            Array
            (
                'value' => 'lun',
                'label' => __('Lunda')
            ),
            Array
            (
                'value' => 'luo',
                'label' => __('Luo')
            ),
            Array
            (
                'value' => 'lus',
                'label' => __('Mizo')
            ),
            Array
            (
                'value' => 'luy',
                'label' => __('Luyia')
            ),
            Array
            (
                'value' => 'lv',
                'label' => __('Latvian')
            ),
            Array
            (
                'value' => 'lzh',
                'label' => __('Literary Chinese')
            ),
            Array
            (
                'value' => 'lzz',
                'label' => __('Laz')
            ),
            Array
            (
                'value' => 'mad',
                'label' => __('Madurese')
            ),
            Array
            (
                'value' => 'maf',
                'label' => __('Mafa')
            ),
            Array
            (
                'value' => 'mag',
                'label' => __('Magahi')
            ),
            Array
            (
                'value' => 'mai',
                'label' => __('Maithili')
            ),
            Array
            (
                'value' => 'mak',
                'label' => __('Makasar')
            ),
            Array
            (
                'value' => 'man',
                'label' => __('Mandingo')
            ),
            Array
            (
                'value' => 'mas',
                'label' => __('Masai')
            ),
            Array
            (
                'value' => 'mde',
                'label' => __('Maba')
            ),
            Array
            (
                'value' => 'mdf',
                'label' => __('Moksha')
            ),
            Array
            (
                'value' => 'mdr',
                'label' => __('Mandar')
            ),
            Array
            (
                'value' => 'men',
                'label' => __('Mende')
            ),
            Array
            (
                'value' => 'mer',
                'label' => __('Meru')
            ),
            Array
            (
                'value' => 'mfe',
                'label' => __('Morisyen')
            ),
            Array
            (
                'value' => 'mg',
                'label' => __('Malagasy')
            ),
            Array
            (
                'value' => 'mga',
                'label' => __('Middle Irish')
            ),
            Array
            (
                'value' => 'mgh',
                'label' => __('Makhuwa-Meetto')
            ),
            Array
            (
                'value' => 'mgo',
                'label' => __('Metaʼ')
            ),
            Array
            (
                'value' => 'mh',
                'label' => __('Marshallese')
            ),
            Array
            (
                'value' => 'mi',
                'label' => __('Maori')
            ),
            Array
            (
                'value' => 'mic',
                'label' => __("Mi'kmaq")
            ),
            Array
            (
                'value' => 'min',
                'label' => __('Minangkabau')
            ),
            Array
            (
                'value' => 'mk',
                'label' => __('Macedonian')
            ),
            Array
            (
                'value' => 'ml',
                'label' => __('Malayalam')
            ),
            Array
            (
                'value' => 'mn',
                'label' => __('Mongolian')
            ),
            Array
            (
                'value' => 'mnc',
                'label' => __('Manchu')
            ),
            Array
            (
                'value' => 'mni',
                'label' => __('Manipuri')
            ),
            Array
            (
                'value' => 'moh',
                'label' => __('Mohawk')
            ),
            Array
            (
                'value' => 'mos',
                'label' => __('Mossi')
            ),
            Array
            (
                'value' => 'mr',
                'label' => __('Marathi')
            ),
            Array
            (
                'value' => 'mrj',
                'label' => __('Western Mari')
            ),
            Array
            (
                'value' => 'ms',
                'label' => __('Malay')
            ),
            Array
            (
                'value' => 'mt',
                'label' => __('Maltese')
            ),
            Array
            (
                'value' => 'mua',
                'label' => __('Mundang')
            ),
            Array
            (
                'value' => 'mul',
                'label' => __('Multiple languages')
            ),
            Array
            (
                'value' => 'mus',
                'label' => __('Creek')
            ),
            Array
            (
                'value' => 'mwl',
                'label' => __('Mirandese')
            ),
            Array
            (
                'value' => 'mwr',
                'label' => __('Marwari')
            ),
            Array
            (
                'value' => 'mwv',
                'label' => __('Mentawai')
            ),
            Array
            (
                'value' => 'my',
                'label' => __('Burmese')
            ),
            Array
            (
                'value' => 'mye',
                'label' => __('Myene')
            ),
            Array
            (
                'value' => 'myv',
                'label' => __('Erzya')
            ),
            Array
            (
                'value' => 'mzn',
                'label' => __('Mazanderani')
            ),
            Array
            (
                'value' => 'na',
                'label' => __('Nauru')
            ),
            Array
            (
                'value' => 'nan',
                'label' => __('Min Nan Chinese')
            ),
            Array
            (
                'value' => 'nap',
                'label' => __('Neapolitan')
            ),
            Array
            (
                'value' => 'naq',
                'label' => __('Nama')
            ),
            Array
            (
                'value' => 'nb',
                'label' => __('Norwegian Bokmål')
            ),
            Array
            (
                'value' => 'nd',
                'label' => __('North Ndebele')
            ),
            Array
            (
                'value' => 'nds',
                'label' => __('Low German')
            ),
            Array
            (
                'value' => 'nds_NL',
                'label' => __('Low Saxon')
            ),
            Array
            (
                'value' => 'ne',
                'label' => __('Nepali')
            ),
            Array
            (
                'value' => 'new',
                'label' => __('Newari')
            ),
            Array
            (
                'value' => 'ng',
                'label' => __('Ndonga')
            ),
            Array
            (
                'value' => 'nia',
                'label' => __('Nias')
            ),
            Array
            (
                'value' => 'niu',
                'label' => __('Niuean')
            ),
            Array
            (
                'value' => 'njo',
                'label' => __('Ao')
            ),
            Array
            (
                'value' => 'nl',
                'label' => __('Dutch')
            ),
            Array
            (
                'value' => 'nl_BE',
                'label' => __('Flemish')
            ),
            Array
            (
                'value' => 'nmg',
                'label' => __('Kwasio')
            ),
            Array
            (
                'value' => 'nn',
                'label' => __('Norwegian Nynorsk')
            ),
            Array
            (
                'value' => 'nnh',
                'label' => __('Ngiemboon')
            ),
            Array
            (
                'value' => 'no',
                'label' => __('Norwegian')
            ),
            Array
            (
                'value' => 'nog',
                'label' => __('Nogai')
            ),
            Array
            (
                'value' => 'non',
                'label' => __('Old Norse')
            ),
            Array
            (
                'value' => 'nov',
                'label' => __('Novial')
            ),
            Array
            (
                'value' => 'nqo',
                'label' => __('N’Ko')
            ),
            Array
            (
                'value' => 'nr',
                'label' => __('South Ndebele')
            ),
            Array
            (
                'value' => 'nso',
                'label' => __('Northern Sotho')
            ),
            Array
            (
                'value' => 'nus',
                'label' => __('Nuer')
            ),
            Array
            (
                'value' => 'nv',
                'label' => __('Navajo')
            ),
            Array
            (
                'value' => 'nwc',
                'label' => __('Classical Newari')
            ),
            Array
            (
                'value' => 'ny',
                'label' => __('Nyanja')
            ),
            Array
            (
                'value' => 'nym',
                'label' => __('Nyamwezi')
            ),
            Array
            (
                'value' => 'nyn',
                'label' => __('Nyankole')
            ),
            Array
            (
                'value' => 'nyo',
                'label' => __('Nyoro')
            ),
            Array
            (
                'value' => 'nzi',
                'label' => __('Nzima')
            ),
            Array
            (
                'value' => 'oc',
                'label' => __('Occitan')
            ),
            Array
            (
                'value' => 'oj',
                'label' => __('Ojibwa')
            ),
            Array
            (
                'value' => 'om',
                'label' => __('Oromo')
            ),
            Array
            (
                'value' => ' or ',
                'label' => __('Odia')
            ),
            Array
            (
                'value' => 'os',
                'label' => __('Ossetic')
            ),
            Array
            (
                'value' => 'osa',
                'label' => __('Osage')
            ),
            Array
            (
                'value' => 'ota',
                'label' => __('Ottoman Turkish')
            ),
            Array
            (
                'value' => 'pa',
                'label' => __('Punjabi')
            ),
            Array
            (
                'value' => 'pag',
                'label' => __('Pangasinan')
            ),
            Array
            (
                'value' => 'pal',
                'label' => __('Pahlavi')
            ),
            Array
            (
                'value' => 'pam',
                'label' => __('Pampanga')
            ),
            Array
            (
                'value' => 'pap',
                'label' => __('Papiamento')
            ),
            Array
            (
                'value' => 'pau',
                'label' => __('Palauan')
            ),
            Array
            (
                'value' => 'pcd',
                'label' => __('Picard')
            ),
            Array
            (
                'value' => 'pcm',
                'label' => __('Nigerian Pidgin')
            ),
            Array
            (
                'value' => 'pdc',
                'label' => __('Pennsylvania German')
            ),
            Array
            (
                'value' => 'pdt',
                'label' => __('Plautdietsch')
            ),
            Array
            (
                'value' => 'peo',
                'label' => __('Old Persian')
            ),
            Array
            (
                'value' => 'pfl',
                'label' => __('Palatine German')
            ),
            Array
            (
                'value' => 'phn',
                'label' => __('Phoenician')
            ),
            Array
            (
                'value' => 'pi',
                'label' => __('Pali')
            ),
            Array
            (
                'value' => 'pl',
                'label' => __('Polish')
            ),
            Array
            (
                'value' => 'pms',
                'label' => __('Piedmontese')
            ),
            Array
            (
                'value' => 'pnt',
                'label' => __('Pontic')
            ),
            Array
            (
                'value' => 'pon',
                'label' => __('Pohnpeian')
            ),
            Array
            (
                'value' => 'prg',
                'label' => __('Prussian')
            ),
            Array
            (
                'value' => 'pro',
                'label' => __('Old Provençal')
            ),
            Array
            (
                'value' => 'ps',
                'label' => __('Pashto')
            ),
            Array
            (
                'value' => 'pt',
                'label' => __('Portuguese')
            ),
            Array
            (
                'value' => 'pt_BR',
                'label' => __('Brazilian Portuguese')
            ),
            Array
            (
                'value' => 'pt_PT',
                'label' => __('European Portuguese')
            ),
            Array
            (
                'value' => 'qu',
                'label' => __('Quechua')
            ),
            Array
            (
                'value' => 'quc',
                'label' => __('Kʼicheʼ')
            ),
            Array
            (
                'value' => 'qug',
                'label' => __('Chimborazo Highland Quichua')
            ),
            Array
            (
                'value' => 'raj',
                'label' => __('Rajasthani')
            ),
            Array
            (
                'value' => 'rap',
                'label' => __('Rapanui')
            ),
            Array
            (
                'value' => 'rar',
                'label' => __('Rarotongan')
            ),
            Array
            (
                'value' => 'rgn',
                'label' => __('Romagnol')
            ),
            Array
            (
                'value' => 'rif',
                'label' => __('Riffian')
            ),
            Array
            (
                'value' => 'rm',
                'label' => __('Romansh')
            ),
            Array
            (
                'value' => 'rn',
                'label' => __('Rundi')
            ),
            Array
            (
                'value' => 'ro',
                'label' => __('Romanian')
            ),
            Array
            (
                'value' => 'ro_MD',
                'label' => __('Moldavian')
            ),
            Array
            (
                'value' => 'rof',
                'label' => __('Rombo')
            ),
            Array
            (
                'value' => 'rom',
                'label' => __('Romany')
            ),
            Array
            (
                'value' => 'root',
                'label' => __('Root')
            ),
            Array
            (
                'value' => 'rtm',
                'label' => __('Rotuman')
            ),
            Array
            (
                'value' => 'ru',
                'label' => __('Russian')
            ),
            Array
            (
                'value' => 'rue',
                'label' => __('Rusyn')
            ),
            Array
            (
                'value' => 'rug',
                'label' => __('Roviana')
            ),
            Array
            (
                'value' => 'rup',
                'label' => __('Aromanian')
            ),
            Array
            (
                'value' => 'rw',
                'label' => __('Kinyarwanda')
            ),
            Array
            (
                'value' => 'rwk',
                'label' => __('Rwa')
            ),
            Array
            (
                'value' => 'sa',
                'label' => __('Sanskrit')
            ),
            Array
            (
                'value' => 'sad',
                'label' => __('Sandawe')
            ),
            Array
            (
                'value' => 'sah',
                'label' => __('Sakha')
            ),
            Array
            (
                'value' => 'sam',
                'label' => __('Samaritan Aramaic')
            ),
            Array
            (
                'value' => 'saq',
                'label' => __('Samburu')
            ),
            Array
            (
                'value' => 'sas',
                'label' => __('Sasak')
            ),
            Array
            (
                'value' => 'sat',
                'label' => __('Santali')
            ),
            Array
            (
                'value' => 'saz',
                'label' => __('Saurashtra')
            ),
            Array
            (
                'value' => 'sba',
                'label' => __('Ngambay')
            ),
            Array
            (
                'value' => 'sbp',
                'label' => __('Sangu')
            ),
            Array
            (
                'value' => 'sc',
                'label' => __('Sardinian')
            ),
            Array
            (
                'value' => 'scn',
                'label' => __('Sicilian')
            ),
            Array
            (
                'value' => 'sco',
                'label' => __('Scots')
            ),
            Array
            (
                'value' => 'sd',
                'label' => __('Sindhi')
            ),
            Array
            (
                'value' => 'sdc',
                'label' => __('Sassarese Sardinian')
            ),
            Array
            (
                'value' => 'sdh',
                'label' => __('Southern Kurdish')
            ),
            Array
            (
                'value' => 'se',
                'label' => __('Northern Sami')
            ),
            Array
            (
                'value' => 'see',
                'label' => __('Seneca')
            ),
            Array
            (
                'value' => 'seh',
                'label' => __('Sena')
            ),
            Array
            (
                'value' => 'sei',
                'label' => __('Seri')
            ),
            Array
            (
                'value' => 'sel',
                'label' => __('Selkup')
            ),
            Array
            (
                'value' => 'ses',
                'label' => __('Koyraboro Senni')
            ),
            Array
            (
                'value' => 'sg',
                'label' => __('Sango')
            ),
            Array
            (
                'value' => 'sga',
                'label' => __('Old Irish')
            ),
            Array
            (
                'value' => 'sgs',
                'label' => __('Samogitian')
            ),
            Array
            (
                'value' => 'sh',
                'label' => __('Serbo - Croatian')
            ),
            Array
            (
                'value' => 'shi',
                'label' => __('Tachelhit')
            ),
            Array
            (
                'value' => 'shn',
                'label' => __('Shan')
            ),
            Array
            (
                'value' => 'shu',
                'label' => __('Chadian Arabic')
            ),
            Array
            (
                'value' => 'si',
                'label' => __('Sinhala')
            ),
            Array
            (
                'value' => 'sid',
                'label' => __('Sidamo')
            ),
            Array
            (
                'value' => 'sk',
                'label' => __('Slovak')
            ),
            Array
            (
                'value' => 'sl',
                'label' => __('Slovenian')
            ),
            Array
            (
                'value' => 'sli',
                'label' => __('Lower Silesian')
            ),
            Array
            (
                'value' => 'sly',
                'label' => __('Selayar')
            ),
            Array
            (
                'value' => 'sm',
                'label' => __('Samoan')
            ),
            Array
            (
                'value' => 'sma',
                'label' => __('Southern Sami')
            ),
            Array
            (
                'value' => 'smj',
                'label' => __('Lule Sami')
            ),
            Array
            (
                'value' => 'smn',
                'label' => __('Inari Sami')
            ),
            Array
            (
                'value' => 'sms',
                'label' => __('Skolt Sami')
            ),
            Array
            (
                'value' => 'sn',
                'label' => __('Shona')
            ),
            Array
            (
                'value' => 'snk',
                'label' => __('Soninke')
            ),
            Array
            (
                'value' => 'so',
                'label' => __('Somali')
            ),
            Array
            (
                'value' => 'sog',
                'label' => __('Sogdien')
            ),
            Array
            (
                'value' => 'sq',
                'label' => __('Albanian')
            ),
            Array
            (
                'value' => 'sr',
                'label' => __('Serbian')
            ),
            Array
            (
                'value' => 'sr_ME',
                'label' => __('Montenegrin')
            ),
            Array
            (
                'value' => 'srn',
                'label' => __('Sranan Tongo')
            ),
            Array
            (
                'value' => 'srr',
                'label' => __('Serer')
            ),
            Array
            (
                'value' => 'ss',
                'label' => __('Swati')
            ),
            Array
            (
                'value' => 'ssy',
                'label' => __('Saho')
            ),
            Array
            (
                'value' => 'st',
                'label' => __('Southern Sotho')
            ),
            Array
            (
                'value' => 'stq',
                'label' => __('Saterland Frisian')
            ),
            Array
            (
                'value' => 'su',
                'label' => __('Sundanese')
            ),
            Array
            (
                'value' => 'suk',
                'label' => __('Sukuma')
            ),
            Array
            (
                'value' => 'sus',
                'label' => __('Susu')
            ),
            Array
            (
                'value' => 'sux',
                'label' => __('Sumerian')
            ),
            Array
            (
                'value' => 'sv',
                'label' => __('Swedish')
            ),
            Array
            (
                'value' => 'sw',
                'label' => __('Swahili')
            ),
            Array
            (
                'value' => 'sw_CD',
                'label' => __('Congo Swahili')
            ),
            Array
            (
                'value' => 'swb',
                'label' => __('Comorian')
            ),
            Array
            (
                'value' => 'syc',
                'label' => __('Classical Syriac')
            ),
            Array
            (
                'value' => 'syr',
                'label' => __('Syriac')
            ),
            Array
            (
                'value' => 'szl',
                'label' => __('Silesian')
            ),
            Array
            (
                'value' => 'ta',
                'label' => __('Tamil')
            ),
            Array
            (
                'value' => 'tcy',
                'label' => __('Tulu')
            ),
            Array
            (
                'value' => 'te',
                'label' => __('Telugu')
            ),
            Array
            (
                'value' => 'tem',
                'label' => __('Timne')
            ),
            Array
            (
                'value' => 'teo',
                'label' => __('Teso')
            ),
            Array
            (
                'value' => 'ter',
                'label' => __('Tereno')
            ),
            Array
            (
                'value' => 'tet',
                'label' => __('Tetum')
            ),
            Array
            (
                'value' => 'tg',
                'label' => __('Tajik')
            ),
            Array
            (
                'value' => 'th',
                'label' => __('Thai')
            ),
            Array
            (
                'value' => 'ti',
                'label' => __('Tigrinya')
            ),
            Array
            (
                'value' => 'tig',
                'label' => __('Tigre')
            ),
            Array
            (
                'value' => 'tiv',
                'label' => __('Tiv')
            ),
            Array
            (
                'value' => 'tk',
                'label' => __('Turkmen')
            ),
            Array
            (
                'value' => 'tkl',
                'label' => __('Tokelau')
            ),
            Array
            (
                'value' => 'tkr',
                'label' => __('Tsakhur')
            ),
            Array
            (
                'value' => 'tl',
                'label' => __('Tagalog')
            ),
            Array
            (
                'value' => 'tlh',
                'label' => __('Klingon')
            ),
            Array
            (
                'value' => 'tli',
                'label' => __('Tlingit')
            ),
            Array
            (
                'value' => 'tly',
                'label' => __('Talysh')
            ),
            Array
            (
                'value' => 'tmh',
                'label' => __('Tamashek')
            ),
            Array
            (
                'value' => 'tn',
                'label' => __('Tswana')
            ),
            Array
            (
                'value' => 'to',
                'label' => __('Tongan')
            ),
            Array
            (
                'value' => 'tog',
                'label' => __('Nyasa Tonga')
            ),
            Array
            (
                'value' => 'tpi',
                'label' => __('Tok Pisin')
            ),
            Array
            (
                'value' => 'tr',
                'label' => __('Turkish')
            ),
            Array
            (
                'value' => 'tru',
                'label' => __('Turoyo')
            ),
            Array
            (
                'value' => 'trv',
                'label' => __('Taroko')
            ),
            Array
            (
                'value' => 'ts',
                'label' => __('Tsonga')
            ),
            Array
            (
                'value' => 'tsd',
                'label' => __('Tsakonian')
            ),
            Array
            (
                'value' => 'tsi',
                'label' => __('Tsimshian')
            ),
            Array
            (
                'value' => 'tt',
                'label' => __('Tatar')
            ),
            Array
            (
                'value' => 'ttt',
                'label' => __('Muslim Tat')
            ),
            Array
            (
                'value' => 'tum',
                'label' => __('Tumbuka')
            ),
            Array
            (
                'value' => 'tvl',
                'label' => __('Tuvalu')
            ),
            Array
            (
                'value' => 'tw',
                'label' => __('Twi')
            ),
            Array
            (
                'value' => 'twq',
                'label' => __('Tasawaq')
            ),
            Array
            (
                'value' => 'ty',
                'label' => __('Tahitian')
            ),
            Array
            (
                'value' => 'tyv',
                'label' => __('Tuvinian')
            ),
            Array
            (
                'value' => 'tzm',
                'label' => __('Central Atlas Tamazight')
            ),
            Array
            (
                'value' => 'udm',
                'label' => __('Udmurt')
            ),
            Array
            (
                'value' => 'ug',
                'label' => __('Uyghur')
            ),
            Array
            (
                'value' => 'uga',
                'label' => __('Ugaritic')
            ),
            Array
            (
                'value' => 'uk',
                'label' => __('Ukrainian')
            ),
            Array
            (
                'value' => 'umb',
                'label' => __('Umbundu')
            ),
            Array
            (
                'value' => 'und',
                'label' => __('Unknown language')
            ),
            Array
            (
                'value' => 'ur',
                'label' => __('Urdu')
            ),
            Array
            (
                'value' => 'uz',
                'label' => __('Uzbek')
            ),
            Array
            (
                'value' => 'vai',
                'label' => __('Vai')
            ),
            Array
            (
                'value' => 've',
                'label' => __('Venda')
            ),
            Array
            (
                'value' => 'vec',
                'label' => __('Venetian')
            ),
            Array
            (
                'value' => 'vep',
                'label' => __('Veps')
            ),
            Array
            (
                'value' => 'vi',
                'label' => __('Vietnamese')
            ),
            Array
            (
                'value' => 'vls',
                'label' => __('West Flemish')
            ),
            Array
            (
                'value' => 'vmf',
                'label' => __('Main - Franconian')
            ),
            Array
            (
                'value' => 'vo',
                'label' => __('Volapük')
            ),
            Array
            (
                'value' => 'vot',
                'label' => __('Votic')
            ),
            Array
            (
                'value' => 'vro',
                'label' => __('Võro')
            ),
            Array
            (
                'value' => 'vun',
                'label' => __('Vunjo')
            ),
            Array
            (
                'value' => 'wa',
                'label' => __('Walloon')
            ),
            Array
            (
                'value' => 'wae',
                'label' => __('Walser')
            ),
            Array
            (
                'value' => 'wal',
                'label' => __('Wolaytta')
            ),
            Array
            (
                'value' => 'war',
                'label' => __('Waray')
            ),
            Array
            (
                'value' => 'was',
                'label' => __('Washo')
            ),
            Array
            (
                'value' => 'wbp',
                'label' => __('Warlpiri')
            ),
            Array
            (
                'value' => 'wo',
                'label' => __('Wolof')
            ),
            Array
            (
                'value' => 'wuu',
                'label' => __('Wu Chinese')
            ),
            Array
            (
                'value' => 'xal',
                'label' => __('Kalmyk')
            ),
            Array
            (
                'value' => 'xh',
                'label' => __('Xhosa')
            ),
            Array
            (
                'value' => 'xmf',
                'label' => __('Mingrelian')
            ),
            Array
            (
                'value' => 'xog',
                'label' => __('Soga')
            ),
            Array
            (
                'value' => 'yao',
                'label' => __('Yao')
            ),
            Array
            (
                'value' => 'yap',
                'label' => __('Yapese')
            ),
            Array
            (
                'value' => 'yav',
                'label' => __('Yangben')
            ),
            Array
            (
                'value' => 'ybb',
                'label' => __('Yemba')
            ),
            Array
            (
                'value' => 'yi',
                'label' => __('Yiddish')
            ),
            Array
            (
                'value' => 'yo',
                'label' => __('Yoruba')
            ),
            Array
            (
                'value' => 'yrl',
                'label' => __('Nheengatu')
            ),
            Array
            (
                'value' => 'yue',
                'label' => __('Cantonese')
            ),
            Array
            (
                'value' => 'za',
                'label' => __('Zhuang')
            ),
            Array
            (
                'value' => 'zap',
                'label' => __('Zapotec')
            ),
            Array
            (
                'value' => 'zbl',
                'label' => __('Blissymbols')
            ),
            Array
            (
                'value' => 'zea',
                'label' => __('Zeelandic')
            ),
            Array
            (
                'value' => 'zen',
                'label' => __('Zenaga')
            ),
            Array
            (
                'value' => 'zgh',
                'label' => __('Standard Moroccan Tamazight')
            ),
            Array
            (
                'value' => 'zh',
                'label' => __('Chinese')
            ),
            Array
            (
                'value' => 'zh_Hans',
                'label' => __('Simplified Chinese')
            ),
            Array
            (
                'value' => 'zh_Hant',
                'label' => __('Traditional Chinese')
            ),
            Array
            (
                'value' => 'zu',
                'label' => __('Zulu')
            ),
            Array
            (
                'value' => 'zun',
                'label' => __('Zuni')
            ),
            Array
            (
                'value' => 'zxx',
                'label' => __('No linguistic content')
            ),
            Array
            (
                'value' => 'zza',
                'label' => __('Zaza')
            )
        );
    }
}