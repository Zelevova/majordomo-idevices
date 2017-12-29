<?php
/*
*
* Russian language file for iDevices module
*
*/

$dictionary=array(
'ABOUT' => 'О модуле',
'DEBUG' => 'Отладка',
'CLOSE' => 'Закрыть',
'PAGINATION' => 'Кол-во объектов на странице',

'HELP' => 'Помощь',
'HELP_APPLEID' =>'Ваш Apple ID',
'HELP_PASSWORD' =>'Пароль от вашего Apple ID',
'HELP_NAME' =>'Название',
'HELP_CHECK_INTERVAL' =>'Интервал проверки местоположения',
'HELP_BATTERY_LEVEL' =>'Текущий уровень заряда',
'HELP_UPDATED' =>'Дата и время обновления',
'HELP_LATITUDE' =>'Текущая широта',
'HELP_LONGITUDE' =>'Текущая долгота',
'HELP_ACCURACY' =>'Текущая точность',

'GET_DEVICES' =>'Получить список устройств',
'SEND_MESSAGE' => 'Отправить сообщение',
'PLAY_SOUND' =>'Воспроизвести звук',
'LOCATE' =>'Найти',
'LOST_MODE' =>'Включить режим пропажи на этом устройстве',
'INPUT_MESSAGE_TEXT' => 'Введите текст сообщения',
'AppleIDs' => 'Список AppleID'
);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}

?>
