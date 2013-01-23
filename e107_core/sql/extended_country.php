<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/sql/extended_country.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

header("location:../index.php");
exit;
?>

CREATE TABLE user_extended_country (
  country_code char(3) NOT NULL default '',
  country_name char(52) NOT NULL default '',
  country_continent enum('Asia','Europe','North America','Africa','Oceania','Antarctica','South America') NOT NULL default 'Asia',
  country_region char(26) NOT NULL default '',
  country_iso char(2) NOT NULL default '',
  PRIMARY KEY  (country_code),
  KEY country_iso (country_iso)
) ENGINE=MyISAM;

INSERT INTO user_extended_country VALUES('AFG', 'Afghanistan', 'Asia', 'Southern and Central Asia', 'AF');
INSERT INTO user_extended_country VALUES('NLD', 'Netherlands', 'Europe', 'Western Europe', 'NL');
INSERT INTO user_extended_country VALUES('ANT', 'Netherlands Antilles', 'North America', 'Caribbean', 'AN');
INSERT INTO user_extended_country VALUES('ALB', 'Albania', 'Europe', 'Southern Europe', 'AL');
INSERT INTO user_extended_country VALUES('DZA', 'Algeria', 'Africa', 'Northern Africa', 'DZ');
INSERT INTO user_extended_country VALUES('ASM', 'American Samoa', 'Oceania', 'Polynesia', 'AS');
INSERT INTO user_extended_country VALUES('AND', 'Andorra', 'Europe', 'Southern Europe', 'AD');
INSERT INTO user_extended_country VALUES('AGO', 'Angola', 'Africa', 'Central Africa', 'AO');
INSERT INTO user_extended_country VALUES('AIA', 'Anguilla', 'North America', 'Caribbean', 'AI');
INSERT INTO user_extended_country VALUES('ATG', 'Antigua and Barbuda', 'North America', 'Caribbean', 'AG');
INSERT INTO user_extended_country VALUES('ARE', 'United Arab Emirates', 'Asia', 'Middle East', 'AE');
INSERT INTO user_extended_country VALUES('ARG', 'Argentina', 'South America', 'South America', 'AR');
INSERT INTO user_extended_country VALUES('ARM', 'Armenia', 'Asia', 'Middle East', 'AM');
INSERT INTO user_extended_country VALUES('ABW', 'Aruba', 'North America', 'Caribbean', 'AW');
INSERT INTO user_extended_country VALUES('AUS', 'Australia', 'Oceania', 'Australia and New Zealand', 'AU');
INSERT INTO user_extended_country VALUES('AZE', 'Azerbaijan', 'Asia', 'Middle East', 'AZ');
INSERT INTO user_extended_country VALUES('BHS', 'Bahamas', 'North America', 'Caribbean', 'BS');
INSERT INTO user_extended_country VALUES('BHR', 'Bahrain', 'Asia', 'Middle East', 'BH');
INSERT INTO user_extended_country VALUES('BGD', 'Bangladesh', 'Asia', 'Southern and Central Asia', 'BD');
INSERT INTO user_extended_country VALUES('BRB', 'Barbados', 'North America', 'Caribbean', 'BB');
INSERT INTO user_extended_country VALUES('BEL', 'Belgium', 'Europe', 'Western Europe', 'BE');
INSERT INTO user_extended_country VALUES('BLZ', 'Belize', 'North America', 'Central America', 'BZ');
INSERT INTO user_extended_country VALUES('BEN', 'Benin', 'Africa', 'Western Africa', 'BJ');
INSERT INTO user_extended_country VALUES('BMU', 'Bermuda', 'North America', 'North America', 'BM');
INSERT INTO user_extended_country VALUES('BTN', 'Bhutan', 'Asia', 'Southern and Central Asia', 'BT');
INSERT INTO user_extended_country VALUES('BOL', 'Bolivia', 'South America', 'South America', 'BO');
INSERT INTO user_extended_country VALUES('BIH', 'Bosnia and Herzegovina', 'Europe', 'Southern Europe', 'BA');
INSERT INTO user_extended_country VALUES('BWA', 'Botswana', 'Africa', 'Southern Africa', 'BW');
INSERT INTO user_extended_country VALUES('BRA', 'Brazil', 'South America', 'South America', 'BR');
INSERT INTO user_extended_country VALUES('GBR', 'United Kingdom', 'Europe', 'British Islands', 'GB');
INSERT INTO user_extended_country VALUES('VGB', 'Virgin Islands, British', 'North America', 'Caribbean', 'VG');
INSERT INTO user_extended_country VALUES('BRN', 'Brunei', 'Asia', 'Southeast Asia', 'BN');
INSERT INTO user_extended_country VALUES('BGR', 'Bulgaria', 'Europe', 'Eastern Europe', 'BG');
INSERT INTO user_extended_country VALUES('BFA', 'Burkina Faso', 'Africa', 'Western Africa', 'BF');
INSERT INTO user_extended_country VALUES('BDI', 'Burundi', 'Africa', 'Eastern Africa', 'BI');
INSERT INTO user_extended_country VALUES('CYM', 'Cayman Islands', 'North America', 'Caribbean', 'KY');
INSERT INTO user_extended_country VALUES('CHL', 'Chile', 'South America', 'South America', 'CL');
INSERT INTO user_extended_country VALUES('COK', 'Cook Islands', 'Oceania', 'Polynesia', 'CK');
INSERT INTO user_extended_country VALUES('CRI', 'Costa Rica', 'North America', 'Central America', 'CR');
INSERT INTO user_extended_country VALUES('DJI', 'Djibouti', 'Africa', 'Eastern Africa', 'DJ');
INSERT INTO user_extended_country VALUES('DMA', 'Dominica', 'North America', 'Caribbean', 'DM');
INSERT INTO user_extended_country VALUES('DOM', 'Dominican Republic', 'North America', 'Caribbean', 'DO');
INSERT INTO user_extended_country VALUES('ECU', 'Ecuador', 'South America', 'South America', 'EC');
INSERT INTO user_extended_country VALUES('EGY', 'Egypt', 'Africa', 'Northern Africa', 'EG');
INSERT INTO user_extended_country VALUES('SLV', 'El Salvador', 'North America', 'Central America', 'SV');
INSERT INTO user_extended_country VALUES('ERI', 'Eritrea', 'Africa', 'Eastern Africa', 'ER');
INSERT INTO user_extended_country VALUES('ESP', 'Spain', 'Europe', 'Southern Europe', 'ES');
INSERT INTO user_extended_country VALUES('ZAF', 'South Africa', 'Africa', 'Southern Africa', 'ZA');
INSERT INTO user_extended_country VALUES('ETH', 'Ethiopia', 'Africa', 'Eastern Africa', 'ET');
INSERT INTO user_extended_country VALUES('FLK', 'Falkland Islands', 'South America', 'South America', 'FK');
INSERT INTO user_extended_country VALUES('FJI', 'Fiji Islands', 'Oceania', 'Melanesia', 'FJ');
INSERT INTO user_extended_country VALUES('PHL', 'Philippines', 'Asia', 'Southeast Asia', 'PH');
INSERT INTO user_extended_country VALUES('FRO', 'Faroe Islands', 'Europe', 'Nordic Countries', 'FO');
INSERT INTO user_extended_country VALUES('GAB', 'Gabon', 'Africa', 'Central Africa', 'GA');
INSERT INTO user_extended_country VALUES('GMB', 'Gambia', 'Africa', 'Western Africa', 'GM');
INSERT INTO user_extended_country VALUES('GEO', 'Georgia', 'Asia', 'Middle East', 'GE');
INSERT INTO user_extended_country VALUES('GHA', 'Ghana', 'Africa', 'Western Africa', 'GH');
INSERT INTO user_extended_country VALUES('GIB', 'Gibraltar', 'Europe', 'Southern Europe', 'GI');
INSERT INTO user_extended_country VALUES('GRD', 'Grenada', 'North America', 'Caribbean', 'GD');
INSERT INTO user_extended_country VALUES('GRL', 'Greenland', 'North America', 'North America', 'GL');
INSERT INTO user_extended_country VALUES('GLP', 'Guadeloupe', 'North America', 'Caribbean', 'GP');
INSERT INTO user_extended_country VALUES('GUM', 'Guam', 'Oceania', 'Micronesia', 'GU');
INSERT INTO user_extended_country VALUES('GTM', 'Guatemala', 'North America', 'Central America', 'GT');
INSERT INTO user_extended_country VALUES('GIN', 'Guinea', 'Africa', 'Western Africa', 'GN');
INSERT INTO user_extended_country VALUES('GNB', 'Guinea-Bissau', 'Africa', 'Western Africa', 'GW');
INSERT INTO user_extended_country VALUES('GUY', 'Guyana', 'South America', 'South America', 'GY');
INSERT INTO user_extended_country VALUES('HTI', 'Haiti', 'North America', 'Caribbean', 'HT');
INSERT INTO user_extended_country VALUES('HND', 'Honduras', 'North America', 'Central America', 'HN');
INSERT INTO user_extended_country VALUES('HKG', 'Hong Kong', 'Asia', 'Eastern Asia', 'HK');
INSERT INTO user_extended_country VALUES('SJM', 'Svalbard and Jan Mayen', 'Europe', 'Nordic Countries', 'SJ');
INSERT INTO user_extended_country VALUES('IDN', 'Indonesia', 'Asia', 'Southeast Asia', 'ID');
INSERT INTO user_extended_country VALUES('IND', 'India', 'Asia', 'Southern and Central Asia', 'IN');
INSERT INTO user_extended_country VALUES('IRQ', 'Iraq', 'Asia', 'Middle East', 'IQ');
INSERT INTO user_extended_country VALUES('IRN', 'Iran', 'Asia', 'Southern and Central Asia', 'IR');
INSERT INTO user_extended_country VALUES('IRL', 'Ireland', 'Europe', 'British Islands', 'IE');
INSERT INTO user_extended_country VALUES('ISL', 'Iceland', 'Europe', 'Nordic Countries', 'IS');
INSERT INTO user_extended_country VALUES('ISR', 'Israel', 'Asia', 'Middle East', 'IL');
INSERT INTO user_extended_country VALUES('ITA', 'Italy', 'Europe', 'Southern Europe', 'IT');
INSERT INTO user_extended_country VALUES('TMP', 'East Timor', 'Asia', 'Southeast Asia', 'TP');
INSERT INTO user_extended_country VALUES('AUT', 'Austria', 'Europe', 'Western Europe', 'AT');
INSERT INTO user_extended_country VALUES('JAM', 'Jamaica', 'North America', 'Caribbean', 'JM');
INSERT INTO user_extended_country VALUES('JPN', 'Japan', 'Asia', 'Eastern Asia', 'JP');
INSERT INTO user_extended_country VALUES('YEM', 'Yemen', 'Asia', 'Middle East', 'YE');
INSERT INTO user_extended_country VALUES('JOR', 'Jordan', 'Asia', 'Middle East', 'JO');
INSERT INTO user_extended_country VALUES('CXR', 'Christmas Island', 'Oceania', 'Australia and New Zealand', 'CX');
INSERT INTO user_extended_country VALUES('YUG', 'Serbia and Montenegro', 'Europe', 'Southern Europe', 'YU');
INSERT INTO user_extended_country VALUES('KHM', 'Cambodia', 'Asia', 'Southeast Asia', 'KH');
INSERT INTO user_extended_country VALUES('CMR', 'Cameroon', 'Africa', 'Central Africa', 'CM');
INSERT INTO user_extended_country VALUES('CAN', 'Canada', 'North America', 'North America', 'CA');
INSERT INTO user_extended_country VALUES('CPV', 'Cape Verde', 'Africa', 'Western Africa', 'CV');
INSERT INTO user_extended_country VALUES('KAZ', 'Kazakstan', 'Asia', 'Southern and Central Asia', 'KZ');
INSERT INTO user_extended_country VALUES('KEN', 'Kenya', 'Africa', 'Eastern Africa', 'KE');
INSERT INTO user_extended_country VALUES('CAF', 'Central African Republic', 'Africa', 'Central Africa', 'CF');
INSERT INTO user_extended_country VALUES('CHN', 'China', 'Asia', 'Eastern Asia', 'CN');
INSERT INTO user_extended_country VALUES('KGZ', 'Kyrgyzstan', 'Asia', 'Southern and Central Asia', 'KG');
INSERT INTO user_extended_country VALUES('KIR', 'Kiribati', 'Oceania', 'Micronesia', 'KI');
INSERT INTO user_extended_country VALUES('COL', 'Colombia', 'South America', 'South America', 'CO');
INSERT INTO user_extended_country VALUES('COM', 'Comoros', 'Africa', 'Eastern Africa', 'KM');
INSERT INTO user_extended_country VALUES('COG', 'Congo', 'Africa', 'Central Africa', 'CG');
INSERT INTO user_extended_country VALUES('COD', 'Congo, The Democratic Republic of the', 'Africa', 'Central Africa', 'CD');
INSERT INTO user_extended_country VALUES('CCK', 'Cocos (Keeling) Islands', 'Oceania', 'Australia and New Zealand', 'CC');
INSERT INTO user_extended_country VALUES('PRK', 'North Korea', 'Asia', 'Eastern Asia', 'KP');
INSERT INTO user_extended_country VALUES('KOR', 'South Korea', 'Asia', 'Eastern Asia', 'KR');
INSERT INTO user_extended_country VALUES('GRC', 'Greece', 'Europe', 'Southern Europe', 'GR');
INSERT INTO user_extended_country VALUES('HRV', 'Croatia', 'Europe', 'Southern Europe', 'HR');
INSERT INTO user_extended_country VALUES('CUB', 'Cuba', 'North America', 'Caribbean', 'CU');
INSERT INTO user_extended_country VALUES('KWT', 'Kuwait', 'Asia', 'Middle East', 'KW');
INSERT INTO user_extended_country VALUES('CYP', 'Cyprus', 'Asia', 'Middle East', 'CY');
INSERT INTO user_extended_country VALUES('LAO', 'Laos', 'Asia', 'Southeast Asia', 'LA');
INSERT INTO user_extended_country VALUES('LVA', 'Latvia', 'Europe', 'Baltic Countries', 'LV');
INSERT INTO user_extended_country VALUES('LSO', 'Lesotho', 'Africa', 'Southern Africa', 'LS');
INSERT INTO user_extended_country VALUES('LBN', 'Lebanon', 'Asia', 'Middle East', 'LB');
INSERT INTO user_extended_country VALUES('LBR', 'Liberia', 'Africa', 'Western Africa', 'LR');
INSERT INTO user_extended_country VALUES('LBY', 'Libyan Arab Jamahiriya', 'Africa', 'Northern Africa', 'LY');
INSERT INTO user_extended_country VALUES('LIE', 'Liechtenstein', 'Europe', 'Western Europe', 'LI');
INSERT INTO user_extended_country VALUES('LTU', 'Lithuania', 'Europe', 'Baltic Countries', 'LT');
INSERT INTO user_extended_country VALUES('LUX', 'Luxembourg', 'Europe', 'Western Europe', 'LU');
INSERT INTO user_extended_country VALUES('ESH', 'Western Sahara', 'Africa', 'Northern Africa', 'EH');
INSERT INTO user_extended_country VALUES('MAC', 'Macao', 'Asia', 'Eastern Asia', 'MO');
INSERT INTO user_extended_country VALUES('MDG', 'Madagascar', 'Africa', 'Eastern Africa', 'MG');
INSERT INTO user_extended_country VALUES('MKD', 'Macedonia', 'Europe', 'Southern Europe', 'MK');
INSERT INTO user_extended_country VALUES('MWI', 'Malawi', 'Africa', 'Eastern Africa', 'MW');
INSERT INTO user_extended_country VALUES('MDV', 'Maldives', 'Asia', 'Southern and Central Asia', 'MV');
INSERT INTO user_extended_country VALUES('MYS', 'Malaysia', 'Asia', 'Southeast Asia', 'MY');
INSERT INTO user_extended_country VALUES('MLI', 'Mali', 'Africa', 'Western Africa', 'ML');
INSERT INTO user_extended_country VALUES('MLT', 'Malta', 'Europe', 'Southern Europe', 'MT');
INSERT INTO user_extended_country VALUES('MAR', 'Morocco', 'Africa', 'Northern Africa', 'MA');
INSERT INTO user_extended_country VALUES('MHL', 'Marshall Islands', 'Oceania', 'Micronesia', 'MH');
INSERT INTO user_extended_country VALUES('MTQ', 'Martinique', 'North America', 'Caribbean', 'MQ');
INSERT INTO user_extended_country VALUES('MRT', 'Mauritania', 'Africa', 'Western Africa', 'MR');
INSERT INTO user_extended_country VALUES('MUS', 'Mauritius', 'Africa', 'Eastern Africa', 'MU');
INSERT INTO user_extended_country VALUES('MYT', 'Mayotte', 'Africa', 'Eastern Africa', 'YT');
INSERT INTO user_extended_country VALUES('MEX', 'Mexico', 'North America', 'Central America', 'MX');
INSERT INTO user_extended_country VALUES('FSM', 'Micronesia, Federated States of', 'Oceania', 'Micronesia', 'FM');
INSERT INTO user_extended_country VALUES('MDA', 'Moldova', 'Europe', 'Eastern Europe', 'MD');
INSERT INTO user_extended_country VALUES('MCO', 'Monaco', 'Europe', 'Western Europe', 'MC');
INSERT INTO user_extended_country VALUES('MNG', 'Mongolia', 'Asia', 'Eastern Asia', 'MN');
INSERT INTO user_extended_country VALUES('MSR', 'Montserrat', 'North America', 'Caribbean', 'MS');
INSERT INTO user_extended_country VALUES('MOZ', 'Mozambique', 'Africa', 'Eastern Africa', 'MZ');
INSERT INTO user_extended_country VALUES('MMR', 'Myanmar', 'Asia', 'Southeast Asia', 'MM');
INSERT INTO user_extended_country VALUES('NAM', 'Namibia', 'Africa', 'Southern Africa', 'NA');
INSERT INTO user_extended_country VALUES('NRU', 'Nauru', 'Oceania', 'Micronesia', 'NR');
INSERT INTO user_extended_country VALUES('NPL', 'Nepal', 'Asia', 'Southern and Central Asia', 'NP');
INSERT INTO user_extended_country VALUES('NIC', 'Nicaragua', 'North America', 'Central America', 'NI');
INSERT INTO user_extended_country VALUES('NER', 'Niger', 'Africa', 'Western Africa', 'NE');
INSERT INTO user_extended_country VALUES('NGA', 'Nigeria', 'Africa', 'Western Africa', 'NG');
INSERT INTO user_extended_country VALUES('NIU', 'Niue', 'Oceania', 'Polynesia', 'NU');
INSERT INTO user_extended_country VALUES('NFK', 'Norfolk Island', 'Oceania', 'Australia and New Zealand', 'NF');
INSERT INTO user_extended_country VALUES('NOR', 'Norway', 'Europe', 'Nordic Countries', 'NO');
INSERT INTO user_extended_country VALUES('CIV', 'Côte d\'Ivoire', 'Africa', 'Western Africa', 'CI');
INSERT INTO user_extended_country VALUES('OMN', 'Oman', 'Asia', 'Middle East', 'OM');
INSERT INTO user_extended_country VALUES('PAK', 'Pakistan', 'Asia', 'Southern and Central Asia', 'PK');
INSERT INTO user_extended_country VALUES('PLW', 'Palau', 'Oceania', 'Micronesia', 'PW');
INSERT INTO user_extended_country VALUES('PAN', 'Panama', 'North America', 'Central America', 'PA');
INSERT INTO user_extended_country VALUES('PNG', 'Papua New Guinea', 'Oceania', 'Melanesia', 'PG');
INSERT INTO user_extended_country VALUES('PRY', 'Paraguay', 'South America', 'South America', 'PY');
INSERT INTO user_extended_country VALUES('PER', 'Peru', 'South America', 'South America', 'PE');
INSERT INTO user_extended_country VALUES('PCN', 'Pitcairn', 'Oceania', 'Polynesia', 'PN');
INSERT INTO user_extended_country VALUES('MNP', 'Northern Mariana Islands', 'Oceania', 'Micronesia', 'MP');
INSERT INTO user_extended_country VALUES('PRT', 'Portugal', 'Europe', 'Southern Europe', 'PT');
INSERT INTO user_extended_country VALUES('PRI', 'Puerto Rico', 'North America', 'Caribbean', 'PR');
INSERT INTO user_extended_country VALUES('POL', 'Poland', 'Europe', 'Eastern Europe', 'PL');
INSERT INTO user_extended_country VALUES('GNQ', 'Equatorial Guinea', 'Africa', 'Central Africa', 'GQ');
INSERT INTO user_extended_country VALUES('QAT', 'Qatar', 'Asia', 'Middle East', 'QA');
INSERT INTO user_extended_country VALUES('FRA', 'France', 'Europe', 'Western Europe', 'FR');
INSERT INTO user_extended_country VALUES('GUF', 'French Guiana', 'South America', 'South America', 'GF');
INSERT INTO user_extended_country VALUES('PYF', 'French Polynesia', 'Oceania', 'Polynesia', 'PF');
INSERT INTO user_extended_country VALUES('REU', 'Réunion', 'Africa', 'Eastern Africa', 'RE');
INSERT INTO user_extended_country VALUES('ROM', 'Romania', 'Europe', 'Eastern Europe', 'RO');
INSERT INTO user_extended_country VALUES('RWA', 'Rwanda', 'Africa', 'Eastern Africa', 'RW');
INSERT INTO user_extended_country VALUES('SWE', 'Sweden', 'Europe', 'Nordic Countries', 'SE');
INSERT INTO user_extended_country VALUES('SHN', 'Saint Helena', 'Africa', 'Western Africa', 'SH');
INSERT INTO user_extended_country VALUES('KNA', 'Saint Kitts and Nevis', 'North America', 'Caribbean', 'KN');
INSERT INTO user_extended_country VALUES('LCA', 'Saint Lucia', 'North America', 'Caribbean', 'LC');
INSERT INTO user_extended_country VALUES('VCT', 'Saint Vincent and the Grenadines', 'North America', 'Caribbean', 'VC');
INSERT INTO user_extended_country VALUES('SPM', 'Saint Pierre and Miquelon', 'North America', 'North America', 'PM');
INSERT INTO user_extended_country VALUES('DEU', 'Germany', 'Europe', 'Western Europe', 'DE');
INSERT INTO user_extended_country VALUES('SLB', 'Solomon Islands', 'Oceania', 'Melanesia', 'SB');
INSERT INTO user_extended_country VALUES('ZMB', 'Zambia', 'Africa', 'Eastern Africa', 'ZM');
INSERT INTO user_extended_country VALUES('WSM', 'Samoa', 'Oceania', 'Polynesia', 'WS');
INSERT INTO user_extended_country VALUES('SMR', 'San Marino', 'Europe', 'Southern Europe', 'SM');
INSERT INTO user_extended_country VALUES('STP', 'Sao Tome and Principe', 'Africa', 'Central Africa', 'ST');
INSERT INTO user_extended_country VALUES('SAU', 'Saudi Arabia', 'Asia', 'Middle East', 'SA');
INSERT INTO user_extended_country VALUES('SEN', 'Senegal', 'Africa', 'Western Africa', 'SN');
INSERT INTO user_extended_country VALUES('SYC', 'Seychelles', 'Africa', 'Eastern Africa', 'SC');
INSERT INTO user_extended_country VALUES('SLE', 'Sierra Leone', 'Africa', 'Western Africa', 'SL');
INSERT INTO user_extended_country VALUES('SGP', 'Singapore', 'Asia', 'Southeast Asia', 'SG');
INSERT INTO user_extended_country VALUES('SVK', 'Slovakia', 'Europe', 'Eastern Europe', 'SK');
INSERT INTO user_extended_country VALUES('SVN', 'Slovenia', 'Europe', 'Southern Europe', 'SI');
INSERT INTO user_extended_country VALUES('SOM', 'Somalia', 'Africa', 'Eastern Africa', 'SO');
INSERT INTO user_extended_country VALUES('LKA', 'Sri Lanka', 'Asia', 'Southern and Central Asia', 'LK');
INSERT INTO user_extended_country VALUES('SDN', 'Sudan', 'Africa', 'Northern Africa', 'SD');
INSERT INTO user_extended_country VALUES('FIN', 'Finland', 'Europe', 'Nordic Countries', 'FI');
INSERT INTO user_extended_country VALUES('SUR', 'Suriname', 'South America', 'South America', 'SR');
INSERT INTO user_extended_country VALUES('SWZ', 'Swaziland', 'Africa', 'Southern Africa', 'SZ');
INSERT INTO user_extended_country VALUES('CHE', 'Switzerland', 'Europe', 'Western Europe', 'CH');
INSERT INTO user_extended_country VALUES('SYR', 'Syria', 'Asia', 'Middle East', 'SY');
INSERT INTO user_extended_country VALUES('TJK', 'Tajikistan', 'Asia', 'Southern and Central Asia', 'TJ');
INSERT INTO user_extended_country VALUES('TWN', 'Taiwan', 'Asia', 'Eastern Asia', 'TW');
INSERT INTO user_extended_country VALUES('TZA', 'Tanzania', 'Africa', 'Eastern Africa', 'TZ');
INSERT INTO user_extended_country VALUES('DNK', 'Denmark', 'Europe', 'Nordic Countries', 'DK');
INSERT INTO user_extended_country VALUES('THA', 'Thailand', 'Asia', 'Southeast Asia', 'TH');
INSERT INTO user_extended_country VALUES('TGO', 'Togo', 'Africa', 'Western Africa', 'TG');
INSERT INTO user_extended_country VALUES('TKL', 'Tokelau', 'Oceania', 'Polynesia', 'TK');
INSERT INTO user_extended_country VALUES('TON', 'Tonga', 'Oceania', 'Polynesia', 'TO');
INSERT INTO user_extended_country VALUES('TTO', 'Trinidad and Tobago', 'North America', 'Caribbean', 'TT');
INSERT INTO user_extended_country VALUES('TCD', 'Chad', 'Africa', 'Central Africa', 'TD');
INSERT INTO user_extended_country VALUES('CZE', 'Czech Republic', 'Europe', 'Eastern Europe', 'CZ');
INSERT INTO user_extended_country VALUES('TUN', 'Tunisia', 'Africa', 'Northern Africa', 'TN');
INSERT INTO user_extended_country VALUES('TUR', 'Turkey', 'Asia', 'Middle East', 'TR');
INSERT INTO user_extended_country VALUES('TKM', 'Turkmenistan', 'Asia', 'Southern and Central Asia', 'TM');
INSERT INTO user_extended_country VALUES('TCA', 'Turks and Caicos Islands', 'North America', 'Caribbean', 'TC');
INSERT INTO user_extended_country VALUES('TUV', 'Tuvalu', 'Oceania', 'Polynesia', 'TV');
INSERT INTO user_extended_country VALUES('UGA', 'Uganda', 'Africa', 'Eastern Africa', 'UG');
INSERT INTO user_extended_country VALUES('UKR', 'Ukraine', 'Europe', 'Eastern Europe', 'UA');
INSERT INTO user_extended_country VALUES('HUN', 'Hungary', 'Europe', 'Eastern Europe', 'HU');
INSERT INTO user_extended_country VALUES('URY', 'Uruguay', 'South America', 'South America', 'UY');
INSERT INTO user_extended_country VALUES('NCL', 'New Caledonia', 'Oceania', 'Melanesia', 'NC');
INSERT INTO user_extended_country VALUES('NZL', 'New Zealand', 'Oceania', 'Australia and New Zealand', 'NZ');
INSERT INTO user_extended_country VALUES('UZB', 'Uzbekistan', 'Asia', 'Southern and Central Asia', 'UZ');
INSERT INTO user_extended_country VALUES('BLR', 'Belarus', 'Europe', 'Eastern Europe', 'BY');
INSERT INTO user_extended_country VALUES('WLF', 'Wallis and Futuna', 'Oceania', 'Polynesia', 'WF');
INSERT INTO user_extended_country VALUES('VUT', 'Vanuatu', 'Oceania', 'Melanesia', 'VU');
INSERT INTO user_extended_country VALUES('VAT', 'Holy See (Vatican City State)', 'Europe', 'Southern Europe', 'VA');
INSERT INTO user_extended_country VALUES('VEN', 'Venezuela', 'South America', 'South America', 'VE');
INSERT INTO user_extended_country VALUES('RUS', 'Russian Federation', 'Europe', 'Eastern Europe', 'RU');
INSERT INTO user_extended_country VALUES('VNM', 'Vietnam', 'Asia', 'Southeast Asia', 'VN');
INSERT INTO user_extended_country VALUES('EST', 'Estonia', 'Europe', 'Baltic Countries', 'EE');
INSERT INTO user_extended_country VALUES('USA', 'United States', 'North America', 'North America', 'US');
INSERT INTO user_extended_country VALUES('VIR', 'Virgin Islands, U.S.', 'North America', 'Caribbean', 'VI');
INSERT INTO user_extended_country VALUES('ZWE', 'Zimbabwe', 'Africa', 'Eastern Africa', 'ZW');
INSERT INTO user_extended_country VALUES('PSE', 'Palestine', 'Asia', 'Middle East', 'PS');
INSERT INTO user_extended_country VALUES('ATA', 'Antarctica', 'Antarctica', 'Antarctica', 'AQ');
INSERT INTO user_extended_country VALUES('BVT', 'Bouvet Island', 'Antarctica', 'Antarctica', 'BV');
INSERT INTO user_extended_country VALUES('IOT', 'British Indian Ocean Territory', 'Africa', 'Eastern Africa', 'IO');
INSERT INTO user_extended_country VALUES('SGS', 'South Georgia and the South Sandwich Islands', 'Antarctica', 'Antarctica', 'GS');
INSERT INTO user_extended_country VALUES('HMD', 'Heard Island and McDonald Islands', 'Antarctica', 'Antarctica', 'HM');
INSERT INTO user_extended_country VALUES('ATF', 'French Southern territories', 'Antarctica', 'Antarctica', 'TF');
INSERT INTO user_extended_country VALUES('UMI', 'United States Minor Outlying Islands', 'Oceania', 'Micronesia/Caribbean', 'UM');