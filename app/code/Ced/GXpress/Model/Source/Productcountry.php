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

class Productcountry extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    public function getAllOptions()
    {
        return array(
            array(
                'value' => __(''),
                'label' => __('-- Please Select Target Country --')
            ),
            Array
            (
                'value' => '001',
                'label' => __('World')
            ),
            Array
            (
                'value' => '002',
                'label' => __('Africa')
            ),
            Array
            (
                'value' => '003',
                'label' => __('North America')
            ),
            Array
            (
                'value' => '005',
                'label' => __('South America')
            ),
            Array
            (
                'value' => '009',
                'label' => __('Oceania')
            ),
            Array
            (
                'value' => '011',
                'label' => __('Western Africa')
            ),
            Array
            (
                'value' => '013',
                'label' => __('Central America')
            ),
            Array
            (
                'value' => '014',
                'label' => __('Eastern Africa')
            ),
            Array
            (
                'value' => '015',
                'label' => __('Northern Africa')
            ),
            Array
            (
                'value' => '017',
                'label' => __('Middle Africa')
            ),
            Array
            (
                'value' => '018',
                'label' => __('Southern Africa')
            ),
            Array
            (
                'value' => '019',
                'label' => __('Americas')
            ),
            Array
            (
                'value' => '021',
                'label' => __('Northern America')
            ),
            Array
            (
                'value' => '029',
                'label' => __('Caribbean')
            ),
            Array
            (
                'value' => '030',
                'label' => __('Eastern Asia')
            ),
            Array
            (
                'value' => '034',
                'label' => __('Southern Asia')
            ),
            Array
            (
                'value' => '035',
                'label' => __('Southeast Asia')
            ),
            Array
            (
                'value' => '039',
                'label' => __('Southern Europe')
            ),
            Array
            (
                'value' => '053',
                'label' => __('Australasia')
            ),
            Array
            (
                'value' => '054',
                'label' => __('Melanesia')
            ),
            Array
            (
                'value' => '057',
                'label' => __('Micronesian Region')
            ),
            Array
            (
                'value' => '061',
                'label' => __('Polynesia')
            ),
            Array
            (
                'value' => '142',
                'label' => __('Asia')
            ),
            Array
            (
                'value' => '143',
                'label' => __('Central Asia')
            ),
            Array
            (
                'value' => '145',
                'label' => __('Western Asia')
            ),
            Array
            (
                'value' => '150',
                'label' => __('Europe')
            ),
            Array
            (
                'value' => '151',
                'label' => __('Eastern Europe')
            ),
            Array
            (
                'value' => '154',
                'label' => __('Northern Europe')
            ),
            Array
            (
                'value' => '155',
                'label' => __('Western Europe')
            ),
            Array
            (
                'value' => '202',
                'label' => __('Sub-Saharan Africa')
            ),
            Array
            (
                'value' => '419',
                'label' => __('Latin America')
            ),
            Array
            (
                'value' => 'AC',
                'label' => __('Ascension Island')
            ),
            Array
            (
                'value' => 'AD',
                'label' => __('Andorra')
            ),
            Array
            (
                'value' => 'AE',
                'label' => __('United Arab Emirates')
            ),
            Array
            (
                'value' => 'AF',
                'label' => __('Afghanistan')
            ),
            Array
            (
                'value' => 'AG',
                'label' => __('Antigua & Barbuda')
            ),
            Array
            (
                'value' => 'AI',
                'label' => __('Anguilla')
            ),
            Array
            (
                'value' => 'AL',
                'label' => __('Albania')
            ),
            Array
            (
                'value' => 'AM',
                'label' => __('Armenia')
            ),
            Array
            (
                'value' => 'AO',
                'label' => __('Angola')
            ),
            Array
            (
                'value' => 'AQ',
                'label' => __('Antarctica')
            ),
            Array
            (
                'value' => 'AR',
                'label' => __('Argentina')
            ),
            Array
            (
                'value' => 'AS',
                'label' => __('American Samoa')
            ),
            Array
            (
                'value' => 'AT',
                'label' => __('Austria')
            ),
            Array
            (
                'value' => 'AU',
                'label' => __('Australia')
            ),
            Array
            (
                'value' => 'AW',
                'label' => __('Aruba')
            ),
            Array
            (
                'value' => 'AX',
                'label' => __('Åland Islands')
            ),
            Array
            (
                'value' => 'AZ',
                'label' => __('Azerbaijan')
            ),
            Array
            (
                'value' => 'BA',
                'label' => __('Bosnia & Herzegovina')
            ),
            Array
            (
                'value' => 'BB',
                'label' => __('Barbados')
            ),
            Array
            (
                'value' => 'BD',
                'label' => __('Bangladesh')
            ),
            Array
            (
                'value' => 'BE',
                'label' => __('Belgium')
            ),
            Array
            (
                'value' => 'BF',
                'label' => __('Burkina Faso')
            ),
            Array
            (
                'value' => 'BG',
                'label' => __('Bulgaria')
            ),
            Array
            (
                'value' => 'BH',
                'label' => __('Bahrain')
            ),
            Array
            (
                'value' => 'BI',
                'label' => __('Burundi')
            ),
            Array
            (
                'value' => 'BJ',
                'label' => __('Benin')
            ),
            Array
            (
                'value' => 'BL',
                'label' => __('St. Barthélemy')
            ),
            Array
            (
                'value' => 'BM',
                'label' => __('Bermuda')
            ),
            Array
            (
                'value' => 'BN',
                'label' => __('Brunei')
            ),
            Array
            (
                'value' => 'BO',
                'label' => __('Bolivia')
            ),
            Array
            (
                'value' => 'BQ',
                'label' => __('Caribbean Netherlands')
            ),
            Array
            (
                'value' => 'BR',
                'label' => __('Brazil')
            ),
            Array
            (
                'value' => 'BS',
                'label' => __('Bahamas')
            ),
            Array
            (
                'value' => 'BT',
                'label' => __('Bhutan')
            ),
            Array
            (
                'value' => 'BV',
                'label' => __('Bouvet Island')
            ),
            Array
            (
                'value' => 'BW',
                'label' => __('Botswana')
            ),
            Array
            (
                'value' => 'BY',
                'label' => __('Belarus')
            ),
            Array
            (
                'value' => 'BZ',
                'label' => __('Belize')
            ),
            Array
            (
                'value' => 'CA',
                'label' => __('Canada')
            ),
            Array
            (
                'value' => 'CC',
                'label' => __('Cocos (Keeling) Islands')
            ),
            Array
            (
                'value' => 'CD',
                'label' => __('Congo - Kinshasa')
            ),
            Array
            (
                'value' => 'CF',
                'label' => __('Central African Republic')
            ),
            Array
            (
                'value' => 'CG',
                'label' => __('Congo - Brazzaville')
            ),
            Array
            (
                'value' => 'CH',
                'label' => __('Switzerland')
            ),
            Array
            (
                'value' => 'CI',
                'label' => __('Côte d’Ivoire')
            ),
            Array
            (
                'value' => 'CK',
                'label' => __('Cook Islands')
            ),
            Array
            (
                'value' => 'CL',
                'label' => __('Chile')
            ),
            Array
            (
                'value' => 'CM',
                'label' => __('Cameroon')
            ),
            Array
            (
                'value' => 'CN',
                'label' => __('China')
            ),
            Array
            (
                'value' => 'CO',
                'label' => __('Colombia')
            ),
            Array
            (
                'value' => 'CP',
                'label' => __('Clipperton Island')
            ),
            Array
            (
                'value' => 'CR',
                'label' => __('Costa Rica')
            ),
            Array
            (
                'value' => 'CU',
                'label' => __('Cuba')
            ),
            Array
            (
                'value' => 'CV',
                'label' => __('Cape Verde')
            ),
            Array
            (
                'value' => 'CW',
                'label' => __('Curaçao')
            ),
            Array
            (
                'value' => 'CX',
                'label' => __('Christmas Island')
            ),
            Array
            (
                'value' => 'CY',
                'label' => __('Cyprus')
            ),
            Array
            (
                'value' => 'CZ',
                'label' => __('Czechia')
            ),
            Array
            (
                'value' => 'DE',
                'label' => __('Germany')
            ),
            Array
            (
                'value' => 'DG',
                'label' => __('Diego Garcia')
            ),
            Array
            (
                'value' => 'DJ',
                'label' => __('Djibouti')
            ),
            Array
            (
                'value' => 'DK',
                'label' => __('Denmark')
            ),
            Array
            (
                'value' => 'DM',
                'label' => __('Dominica')
            ),
            Array
            (
                'value' => 'DO',
                'label' => __('Dominican Republic')
            ),
            Array
            (
                'value' => 'DZ',
                'label' => __('Algeria')
            ),
            Array
            (
                'value' => 'EA',
                'label' => __('Ceuta & Melilla')
            ),
            Array
            (
                'value' => 'EC',
                'label' => __('Ecuador')
            ),
            Array
            (
                'value' => 'EE',
                'label' => __('Estonia')
            ),
            Array
            (
                'value' => 'EG',
                'label' => __('Egypt')
            ),
            Array
            (
                'value' => 'EH',
                'label' => __('Western Sahara')
            ),
            Array
            (
                'value' => 'ER',
                'label' => __('Eritrea')
            ),
            Array
            (
                'value' => 'ES',
                'label' => __('Spain')
            ),
            Array
            (
                'value' => 'ET',
                'label' => __('Ethiopia')
            ),
            Array
            (
                'value' => 'EU',
                'label' => __('European Union')
            ),
            Array
            (
                'value' => 'EZ',
                'label' => __('Eurozone')
            ),
            Array
            (
                'value' => 'FI',
                'label' => __('Finland')
            ),
            Array
            (
                'value' => 'FJ',
                'label' => __('Fiji')
            ),
            Array
            (
                'value' => 'FK',
                'label' => __('Falkland Islands')
            ),
            Array
            (
                'value' => 'FM',
                'label' => __('Micronesia')
            ),
            Array
            (
                'value' => 'FO',
                'label' => __('Faroe Islands')
            ),
            Array
            (
                'value' => 'FR',
                'label' => __('France')
            ),
            Array
            (
                'value' => 'GA',
                'label' => __('Gabon')
            ),
            Array
            (
                'value' => 'GB',
                'label' => __('United Kingdom')
            ),
            Array
            (
                'value' => 'GD',
                'label' => __('Grenada')
            ),
            Array
            (
                'value' => 'GE',
                'label' => __('Georgia')
            ),
            Array
            (
                'value' => 'GF',
                'label' => __('French Guiana')
            ),
            Array
            (
                'value' => 'GG',
                'label' => __('Guernsey')
            ),
            Array
            (
                'value' => 'GH',
                'label' => __('Ghana')
            ),
            Array
            (
                'value' => 'GI',
                'label' => __('Gibraltar')
            ),
            Array
            (
                'value' => 'GL',
                'label' => __('Greenland')
            ),
            Array
            (
                'value' => 'GM',
                'label' => __('Gambia')
            ),
            Array
            (
                'value' => 'GN',
                'label' => __('Guinea')
            ),
            Array
            (
                'value' => 'GP',
                'label' => __('Guadeloupe')
            ),
            Array
            (
                'value' => 'GQ',
                'label' => __('Equatorial Guinea')
            ),
            Array
            (
                'value' => 'GR',
                'label' => __('Greece')
            ),
            Array
            (
                'value' => 'GS',
                'label' => __('South Georgia & South Sandwich Islands')
            ),
            Array
            (
                'value' => 'GT',
                'label' => __('Guatemala')
            ),
            Array
            (
                'value' => 'GU',
                'label' => __('Guam')
            ),
            Array
            (
                'value' => 'GW',
                'label' => __('Guinea-Bissau')
            ),
            Array
            (
                'value' => 'GY',
                'label' => __('Guyana')
            ),
            Array
            (
                'value' => 'HK',
                'label' => __('Hong Kong SAR China')
            ),
            Array
            (
                'value' => 'HM',
                'label' => __('Heard & McDonald Islands')
            ),
            Array
            (
                'value' => 'HN',
                'label' => __('Honduras')
            ),
            Array
            (
                'value' => 'HR',
                'label' => __('Croatia')
            ),
            Array
            (
                'value' => 'HT',
                'label' => __('Haiti')
            ),
            Array
            (
                'value' => 'HU',
                'label' => __('Hungary')
            ),
            Array
            (
                'value' => 'IC',
                'label' => __('Canary Islands')
            ),
            Array
            (
                'value' => 'ID',
                'label' => __('Indonesia')
            ),
            Array
            (
                'value' => 'IE',
                'label' => __('Ireland')
            ),
            Array
            (
                'value' => 'IL',
                'label' => __('Israel')
            ),
            Array
            (
                'value' => 'IM',
                'label' => __('Isle of Man')
            ),
            Array
            (
                'value' => 'IN',
                'label' => __('India')
            ),
            Array
            (
                'value' => 'IO',
                'label' => __('British Indian Ocean Territory')
            ),
            Array
            (
                'value' => 'IQ',
                'label' => __('Iraq')
            ),
            Array
            (
                'value' => 'IR',
                'label' => __('Iran')
            ),
            Array
            (
                'value' => 'IS',
                'label' => __('Iceland')
            ),
            Array
            (
                'value' => 'IT',
                'label' => __('Italy')
            ),
            Array
            (
                'value' => 'JE',
                'label' => __('Jersey')
            ),
            Array
            (
                'value' => 'JM',
                'label' => __('Jamaica')
            ),
            Array
            (
                'value' => 'JO',
                'label' => __('Jordan')
            ),
            Array
            (
                'value' => 'JP',
                'label' => __('Japan')
            ),
            Array
            (
                'value' => 'KE',
                'label' => __('Kenya')
            ),
            Array
            (
                'value' => 'KG',
                'label' => __('Kyrgyzstan')
            ),
            Array
            (
                'value' => 'KH',
                'label' => __('Cambodia')
            ),
            Array
            (
                'value' => 'KI',
                'label' => __('Kiribati')
            ),
            Array
            (
                'value' => 'KM',
                'label' => __('Comoros')
            ),
            Array
            (
                'value' => 'KN',
                'label' => __('St. Kitts & Nevis')
            ),
            Array
            (
                'value' => 'KP',
                'label' => __('North Korea')
            ),
            Array
            (
                'value' => 'KR',
                'label' => __('South Korea')
            ),
            Array
            (
                'value' => 'KW',
                'label' => __('Kuwait')
            ),
            Array
            (
                'value' => 'KY',
                'label' => __('Cayman Islands')
            ),
            Array
            (
                'value' => 'KZ',
                'label' => __('Kazakhstan')
            ),
            Array
            (
                'value' => 'LA',
                'label' => __('Laos')
            ),
            Array
            (
                'value' => 'LB',
                'label' => __('Lebanon')
            ),
            Array
            (
                'value' => 'LC',
                'label' => __('St. Lucia')
            ),
            Array
            (
                'value' => 'LI',
                'label' => __('Liechtenstein')
            ),
            Array
            (
                'value' => 'LK',
                'label' => __('Sri Lanka')
            ),
            Array
            (
                'value' => 'LR',
                'label' => __('Liberia')
            ),
            Array
            (
                'value' => 'LS',
                'label' => __('Lesotho')
            ),
            Array
            (
                'value' => 'LT',
                'label' => __('Lithuania')
            ),
            Array
            (
                'value' => 'LU',
                'label' => __('Luxembourg')
            ),
            Array
            (
                'value' => 'LV',
                'label' => __('Latvia')
            ),
            Array
            (
                'value' => 'LY',
                'label' => __('Libya')
            ),
            Array
            (
                'value' => 'MA',
                'label' => __('Morocco')
            ),
            Array
            (
                'value' => 'MC',
                'label' => __('Monaco')
            ),
            Array
            (
                'value' => 'MD',
                'label' => __('Moldova')
            ),
            Array
            (
                'value' => 'ME',
                'label' => __('Montenegro')
            ),
            Array
            (
                'value' => 'MF',
                'label' => __('St. Martin')
            ),
            Array
            (
                'value' => 'MG',
                'label' => __('Madagascar')
            ),
            Array
            (
                'value' => 'MH',
                'label' => __('Marshall Islands')
            ),
            Array
            (
                'value' => 'MK',
                'label' => __('Macedonia')
            ),
            Array
            (
                'value' => 'ML',
                'label' => __('Mali')
            ),
            Array
            (
                'value' => 'MM',
                'label' => __('Myanmar (Burma)')
            ),
            Array
            (
                'value' => 'MN',
                'label' => __('Mongolia')
            ),
            Array
            (
                'value' => 'MO',
                'label' => __('Macau SAR China')
            ),
            Array
            (
                'value' => 'MP',
                'label' => __('Northern Mariana Islands')
            ),
            Array
            (
                'value' => 'MQ',
                'label' => __('Martinique')
            ),
            Array
            (
                'value' => 'MR',
                'label' => __('Mauritania')
            ),
            Array
            (
                'value' => 'MS',
                'label' => __('Montserrat')
            ),
            Array
            (
                'value' => 'MT',
                'label' => __('Malta')
            ),
            Array
            (
                'value' => 'MU',
                'label' => __('Mauritius')
            ),
            Array
            (
                'value' => 'MV',
                'label' => __('Maldives')
            ),
            Array
            (
                'value' => 'MW',
                'label' => __('Malawi')
            ),
            Array
            (
                'value' => 'MX',
                'label' => __('Mexico')
            ),
            Array
            (
                'value' => 'MY',
                'label' => __('Malaysia')
            ),
            Array
            (
                'value' => 'MZ',
                'label' => __('Mozambique')
            ),
            Array
            (
                'value' => 'NA',
                'label' => __('Namibia')
            ),
            Array
            (
                'value' => 'NC',
                'label' => __('New Caledonia')
            ),
            Array
            (
                'value' => 'NE',
                'label' => __('Niger')
            ),
            Array
            (
                'value' => 'NF',
                'label' => __('Norfolk Island')
            ),
            Array
            (
                'value' => 'NG',
                'label' => __('Nigeria')
            ),
            Array
            (
                'value' => 'NI',
                'label' => __('Nicaragua')
            ),
            Array
            (
                'value' => 'NL',
                'label' => __('Netherlands')
            ),
            Array
            (
                'value' => 'NO',
                'label' => __('Norway')
            ),
            Array
            (
                'value' => 'NP',
                'label' => __('Nepal')
            ),
            Array
            (
                'value' => 'NR',
                'label' => __('Nauru')
            ),
            Array
            (
                'value' => 'NU',
                'label' => __('Niue')
            ),
            Array
            (
                'value' => 'NZ',
                'label' => __('New Zealand')
            ),
            Array
            (
                'value' => 'OM',
                'label' => __('Oman')
            ),
            Array
            (
                'value' => 'PA',
                'label' => __('Panama')
            ),
            Array
            (
                'value' => 'PE',
                'label' => __('Peru')
            ),
            Array
            (
                'value' => 'PF',
                'label' => __('French Polynesia')
            ),
            Array
            (
                'value' => 'PG',
                'label' => __('Papua New Guinea')
            ),
            Array
            (
                'value' => 'PH',
                'label' => __('Philippines')
            ),
            Array
            (
                'value' => 'PK',
                'label' => __('Pakistan')
            ),
            Array
            (
                'value' => 'PL',
                'label' => __('Poland')
            ),
            Array
            (
                'value' => 'PM',
                'label' => __('St. Pierre & Miquelon')
            ),
            Array
            (
                'value' => 'PN',
                'label' => __('Pitcairn Islands')
            ),
            Array
            (
                'value' => 'PR',
                'label' => __('Puerto Rico')
            ),
            Array
            (
                'value' => 'PS',
                'label' => __('Palestinian Territories')
            ),
            Array
            (
                'value' => 'PT',
                'label' => __('Portugal')
            ),
            Array
            (
                'value' => 'PW',
                'label' => __('Palau')
            ),
            Array
            (
                'value' => 'PY',
                'label' => __('Paraguay')
            ),
            Array
            (
                'value' => 'QA',
                'label' => __('Qatar')
            ),
            Array
            (
                'value' => 'QO',
                'label' => __('Outlying Oceania')
            ),
            Array
            (
                'value' => 'RE',
                'label' => __('Réunion')
            ),
            Array
            (
                'value' => 'RO',
                'label' => __('Romania')
            ),
            Array
            (
                'value' => 'RS',
                'label' => __('Serbia')
            ),
            Array
            (
                'value' => 'RU',
                'label' => __('Russia')
            ),
            Array
            (
                'value' => 'RW',
                'label' => __('Rwanda')
            ),
            Array
            (
                'value' => 'SA',
                'label' => __('Saudi Arabia')
            ),
            Array
            (
                'value' => 'SB',
                'label' => __('Solomon Islands')
            ),
            Array
            (
                'value' => 'SC',
                'label' => __('Seychelles')
            ),
            Array
            (
                'value' => 'SD',
                'label' => __('Sudan')
            ),
            Array
            (
                'value' => 'SE',
                'label' => __('Sweden')
            ),
            Array
            (
                'value' => 'SG',
                'label' => __('Singapore')
            ),
            Array
            (
                'value' => 'SH',
                'label' => __('St. Helena')
            ),
            Array
            (
                'value' => 'SI',
                'label' => __('Slovenia')
            ),
            Array
            (
                'value' => 'SJ',
                'label' => __('Svalbard & Jan Mayen')
            ),
            Array
            (
                'value' => 'SK',
                'label' => __('Slovakia')
            ),
            Array
            (
                'value' => 'SL',
                'label' => __('Sierra Leone')
            ),
            Array
            (
                'value' => 'SM',
                'label' => __('San Marino')
            ),
            Array
            (
                'value' => 'SN',
                'label' => __('Senegal')
            ),
            Array
            (
                'value' => 'SO',
                'label' => __('Somalia')
            ),
            Array
            (
                'value' => 'SR',
                'label' => __('Suriname')
            ),
            Array
            (
                'value' => 'SS',
                'label' => __('South Sudan')
            ),
            Array
            (
                'value' => 'ST',
                'label' => __('São Tomé & Príncipe')
            ),
            Array
            (
                'value' => 'SV',
                'label' => __('El Salvador')
            ),
            Array
            (
                'value' => 'SX',
                'label' => __('Sint Maarten')
            ),
            Array
            (
                'value' => 'SY',
                'label' => __('Syria')
            ),
            Array
            (
                'value' => 'SZ',
                'label' => __('Swaziland')
            ),
            Array
            (
                'value' => 'TA',
                'label' => __('Tristan da Cunha')
            ),
            Array
            (
                'value' => 'TC',
                'label' => __('Turks & Caicos Islands')
            ),
            Array
            (
                'value' => 'TD',
                'label' => __('Chad')
            ),
            Array
            (
                'value' => 'TF',
                'label' => __('French Southern Territories')
            ),
            Array
            (
                'value' => 'TG',
                'label' => __('Togo')
            ),
            Array
            (
                'value' => 'TH',
                'label' => __('Thailand')
            ),
            Array
            (
                'value' => 'TJ',
                'label' => __('Tajikistan')
            ),
            Array
            (
                'value' => 'TK',
                'label' => __('Tokelau')
            ),
            Array
            (
                'value' => 'TL',
                'label' => __('Timor-Leste')
            ),
            Array
            (
                'value' => 'TM',
                'label' => __('Turkmenistan')
            ),
            Array
            (
                'value' => 'TN',
                'label' => __('Tunisia')
            ),
            Array
            (
                'value' => 'TO',
                'label' => __('Tonga')
            ),
            Array
            (
                'value' => 'TR',
                'label' => __('Turkey')
            ),
            Array
            (
                'value' => 'TT',
                'label' => __('Trinidad & Tobago')
            ),
            Array
            (
                'value' => 'TV',
                'label' => __('Tuvalu')
            ),
            Array
            (
                'value' => 'TW',
                'label' => __('Taiwan')
            ),
            Array
            (
                'value' => 'TZ',
                'label' => __('Tanzania')
            ),
            Array
            (
                'value' => 'UA',
                'label' => __('Ukraine')
            ),
            Array
            (
                'value' => 'UG',
                'label' => __('Uganda')
            ),
            Array
            (
                'value' => 'UM',
                'label' => __('U.S. Outlying Islands')
            ),
            Array
            (
                'value' => 'UN',
                'label' => __('United Nations')
            ),
            Array
            (
                'value' => 'US',
                'label' => __('United States')
            ),
            Array
            (
                'value' => 'UY',
                'label' => __('Uruguay')
            ),
            Array
            (
                'value' => 'UZ',
                'label' => __('Uzbekistan')
            ),
            Array
            (
                'value' => 'VA',
                'label' => __('Vatican City')
            ),
            Array
            (
                'value' => 'VC',
                'label' => __('St. Vincent & Grenadines')
            ),
            Array
            (
                'value' => 'VE',
                'label' => __('Venezuela')
            ),
            Array
            (
                'value' => 'VG',
                'label' => __('British Virgin Islands')
            ),
            Array
            (
                'value' => 'VI',
                'label' => __('U.S. Virgin Islands')
            ),
            Array
            (
                'value' => 'VN',
                'label' => __('Vietnam')
            ),
            Array
            (
                'value' => 'VU',
                'label' => __('Vanuatu')
            ),
            Array
            (
                'value' => 'WF',
                'label' => __('Wallis & Futuna')
            ),
            Array
            (
                'value' => 'WS',
                'label' => __('Samoa')
            ),
            Array
            (
                'value' => 'XA',
                'label' => __('Pseudo-Accents')
            ),
            Array
            (
                'value' => 'XB',
                'label' => __('Pseudo-Bidi')
            ),
            Array
            (
                'value' => 'XK',
                'label' => __('Kosovo')
            ),
            Array
            (
                'value' => 'YE',
                'label' => __('Yemen')
            ),
            Array
            (
                'value' => 'YT',
                'label' => __('Mayotte')
            ),
            Array
            (
                'value' => 'ZA',
                'label' => __('South Africa')
            ),
            Array
            (
                'value' => 'ZM',
                'label' => __('Zambia')
            ),
            Array
            (
                'value' => 'ZW',
                'label' => __('Zimbabwe')
            ),
            Array
            (
                'value' => 'ZZ',
                'label' => __('Unknown Region')
            )
        );
    }
}